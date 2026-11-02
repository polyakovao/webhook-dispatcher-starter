<?php

declare(strict_types=1);

namespace App;

final class Outcome
{
    public const MAX_ATTEMPTS = 8;

    /**
     * Event status after a delivery attempt: 'sent', 'retry' or 'dead'.
     *
     * $httpCode = response status, 0 for a timeout or network error.
     * $attempt = number of failed attempts so far, including this one.
     *
     *   2xx -> sent; 0, 408, 429, 5xx -> retry until MAX_ATTEMPTS, then dead; anything else -> dead
     */
    public static function status(int $httpCode, int $attempt): string
    {
        if ($httpCode >= 200 && $httpCode < 300) {
            return 'sent';
        }

        $retryable = $httpCode === 0 || $httpCode === 408 || $httpCode === 429 || $httpCode >= 500;

        return $retryable && $attempt < self::MAX_ATTEMPTS ? 'retry' : 'dead';
    }
}
