<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Db;

header('Content-Type: application/json');

// Any uncaught error: details to the log, a plain 500 to the client (not a 200 with a stack trace).
set_exception_handler(function (Throwable $e): void {
    error_log((string) $e);
    http_response_code(500);
    echo json_encode(['error' => 'internal error']);
});

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($method === 'GET' && $path === '/health') {
    try {
        Db::pdo()->query('SELECT 1');
        echo json_encode(['status' => 'ok']);
    } catch (Throwable) {
        http_response_code(503);
        echo json_encode(['status' => 'db_unavailable']);
    }
    exit;
}

// Body: {"url": "...", "type": "order.created", "payload": {...}}
if ($method === 'POST' && $path === '/events') {
    $body = file_get_contents('php://input');
    // objects stay objects: with `true`, {} would become []
    $data = json_decode($body);

    $url = $data->url ?? null;
    $type = $data->type ?? null;
    $payload = $data->payload ?? null;

    $scheme = is_string($url) ? parse_url($url, PHP_URL_SCHEME) : null;
    if (!filter_var($url, FILTER_VALIDATE_URL) || !in_array($scheme, ['http', 'https'], true)
        || !is_string($type) || $type === '' || !$payload instanceof stdClass) {
        http_response_code(400);
        echo json_encode(['error' => 'expected {"url": "http(s)://...", "type": "...", "payload": {...}}']);
        exit;
    }

    $sql = 'INSERT INTO events (target_url, event_type, payload) '
          .' VALUES (:url, :type, :payload) RETURNING id';

    $stmt = Db::pdo()->prepare($sql);
    $stmt->execute([
        'url' => $url,
        'type' => $type,
        'payload' => json_encode($payload),
    ]);

    http_response_code(202);
    echo json_encode(['id' => $stmt->fetchColumn()]); //id of inserted rec
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'not found']);
