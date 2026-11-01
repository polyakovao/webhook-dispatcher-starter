import logging
import os  # noqa: F401  (WEBHOOK_SECRET, DATABASE_URL)
import threading

import httpx  # noqa: F401  (delivery client, remember timeouts)
import psycopg  # noqa: F401

from .lib import backoff_seconds, sign  # noqa: F401

log = logging.getLogger("worker")
_stop = threading.Event()


def run() -> None:
    log.info("worker started (stub, does nothing yet)")
    # TODO(candidate): loop until _stop is set that
    #   1. claims due events safely (two workers must not take the same row),
    #   2. POSTs each one to target_url with a timeout and the signature headers,
    #   3. classifies the outcome (success / retryable / permanent),
    #   4. updates status, attempts, next_attempt_at, last_error,
    #   5. waits briefly (use _stop.wait(seconds)) when there is nothing to do.
    _stop.wait()


def start() -> threading.Thread:
    thread = threading.Thread(target=run, name="worker", daemon=True)
    thread.start()
    return thread


def stop() -> None:
    _stop.set()
