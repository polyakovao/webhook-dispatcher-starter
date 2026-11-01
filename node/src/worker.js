import { pool } from './db.js';
import { backoffSeconds, sign } from './lib.js';

// Env you will need: process.env.WEBHOOK_SECRET

export function startWorker() {
  console.log('worker started (stub, does nothing yet)');

  // TODO(candidate): loop that
  //   1. claims due events safely (two workers must not take the same row),
  //   2. POSTs each one to target_url with a timeout and the signature headers,
  //   3. classifies the outcome (success / retryable / permanent),
  //   4. updates status, attempts, next_attempt_at, last_error,
  //   5. sleeps briefly when there is nothing to do.
}
