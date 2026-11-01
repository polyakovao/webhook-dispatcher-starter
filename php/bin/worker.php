<?php

declare(strict_types=1);

// Delivery worker. Runs as its own process (see the `worker` service in
// docker-compose.yml), so you can scale it: docker compose up --scale worker=2

require __DIR__ . '/../vendor/autoload.php';

use App\Backoff; // phpcs:ignore
use App\Db;      // phpcs:ignore
use App\Signer;  // phpcs:ignore

$running = true;
if (function_exists('pcntl_async_signals')) {
    pcntl_async_signals(true);
    $stop = function () use (&$running): void {
        $running = false;
    };
    pcntl_signal(SIGTERM, $stop);
    pcntl_signal(SIGINT, $stop);
}

$secret = getenv('WEBHOOK_SECRET');
$pdo = Db::pdo();

fwrite(STDOUT, "worker started\n");

//docker compose restart worker
//docker logs -f php-worker-1
while ($running) {
    $event = $pdo->query("
        UPDATE events SET next_attempt_at = now() + interval '60 seconds'
        WHERE id = (
            SELECT id FROM events
            WHERE status IN ('pending', 'retry') AND next_attempt_at <= now()
            ORDER BY next_attempt_at
            LIMIT 1
            FOR UPDATE SKIP LOCKED
        )
        RETURNING *
    ")->fetch(PDO::FETCH_ASSOC);

    if (!$event) { // slep just not to run it cont
        sleep(1);
        continue;
    }

    $body = json_encode([
        'id' => $event['id'],
        'type' => $event['event_type'],
        'payload' => json_decode($event['payload']),
        'created_at' => $event['created_at'],
    ], JSON_UNESCAPED_SLASHES);
    $ts = (string) time();

    $ch = curl_init($event['target_url']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Webhook-Id: ' . $event['id'],
        'X-Webhook-Timestamp: ' . $ts,
        'X-Webhook-Signature: t=' . $ts . ',v1=' . Signer::sign($secret, $ts, $body), //go to Signer.php
    ]);
    $res = curl_exec($ch);
    $code = $res === false ? 0 : curl_getinfo($ch, CURLINFO_RESPONSE_CODE); // 0 = таймаут / сеть

    $attempts = $event['attempts'];
    $error = null;
    $delay = 0;

    if ($code >= 200 && $code < 300) {
        $status = 'sent';
    } else {
        $attempts++;
        $error = $code ? "http $code" : curl_error($ch);
        $retryable = $code == 0 || $code == 408 || $code == 429 || $code >= 500; // 410 и остальные 4xx - dead
        $status = $retryable && $attempts < 8 ? 'retry' : 'dead';
        $delay = Backoff::seconds($attempts);
    }

    $pdo->prepare("
        UPDATE events SET status = ?, attempts = ?, last_error = ?,
            next_attempt_at = now() + make_interval(secs => ?), updated_at = now()
        WHERE id = ?
    ")->execute([$status, $attempts, $error, $delay, $event['id']]);

    echo "{$event['id']} $status $attempts $error\n";
}

fwrite(STDOUT, "worker stopped\n");
