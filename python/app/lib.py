import hashlib
import hmac  # noqa: F401  (you will need these)
import random  # noqa: F401

BASE_DELAY_SECONDS = 60
MAX_DELAY_SECONDS = 6 * 60 * 60


def backoff_seconds(attempt: int, rand: float | None = None) -> float:
    """Seconds to wait before the next attempt.

    `attempt` = number of failed attempts so far (1 for the first retry).
    `rand` is in [0, 1) and is injectable so tests are deterministic; when
    None, use random.random().

        delay = min(60 * 2^(attempt-1), 21600) * (0.5 + rand)

    TODO(candidate): implement.
    """
    raise NotImplementedError("TODO: implement backoff_seconds")


def sign(secret: str, timestamp: str, body: str) -> str:
    """Hex HMAC-SHA256 of f"{timestamp}.{body}" using `secret`.

    The caller builds the header: f"t={timestamp},v1={hex}".

    TODO(candidate): implement.
    """
    raise NotImplementedError("TODO: implement sign")
