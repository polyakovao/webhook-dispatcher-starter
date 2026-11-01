import { createHmac } from 'node:crypto';

export const BASE_DELAY_SECONDS = 60;
export const MAX_DELAY_SECONDS = 6 * 60 * 60;

/**
 * Seconds to wait before the next attempt.
 * `attempt` = number of failed attempts so far (1 for the first retry).
 * `rand` is in [0, 1) and is injectable so tests are deterministic.
 *
 * delay = min(60 * 2^(attempt-1), 21600) * (0.5 + rand)
 *
 * TODO(candidate): implement.
 */
export function backoffSeconds(attempt, rand = Math.random()) {
  throw new Error('TODO: implement backoffSeconds');
}

/**
 * Hex HMAC-SHA256 of `${timestamp}.${body}` using `secret`.
 * The caller builds the header: `t=<timestamp>,v1=<hex>`.
 *
 * TODO(candidate): implement.
 */
export function sign(secret, timestamp, body) {
  throw new Error('TODO: implement sign');
}
