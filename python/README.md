# Webhook Dispatcher: Python starter

Read `BRIEF.md` first. Stack: Python 3.13, FastAPI, psycopg 3, httpx, pytest.

## Run the tests (they fail until you implement `app/lib.py`)

```bash
python -m venv .venv && source .venv/bin/activate
pip install -r requirements-dev.txt
python -m pytest
```

## Run everything locally

```bash
docker compose up --build
./smoke.sh            # in another terminal
```

API: http://localhost:8080 (docs at `/docs`), receiver: http://localhost:9000, Postgres: localhost:5432.

## Layout

| Path | Purpose |
|---|---|
| `app/main.py` | HTTP API (`/health` works, `POST /events` is a stub) |
| `app/worker.py` | Delivery loop in a background thread (stub) |
| `app/lib.py` | Pure functions with the failing tests: `backoff_seconds`, `sign` |
| `tests/test_lib.py` | Unit tests to make pass |

Environment variables: `DATABASE_URL`, `WEBHOOK_SECRET`, `PORT` (compose sets 8080), `WORKER_ENABLED` (set `false` to run the API without the worker).
