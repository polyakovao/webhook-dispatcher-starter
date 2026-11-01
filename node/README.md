# Webhook Dispatcher: Node.js starter

Read `BRIEF.md` first. Stack: Node 22, Express, `pg`, built-in `node:test`.

## Run the tests (they fail until you implement `src/lib.js`)

```bash
npm install      # also writes package-lock.json, worth committing
npm test
```

## Run everything locally

```bash
docker compose up --build
./smoke.sh            # in another terminal
```

API: http://localhost:8080, receiver: http://localhost:9000, Postgres: localhost:5432.

## Layout

| Path | Purpose |
|---|---|
| `src/server.js` | HTTP API (`/health` works, `POST /events` is a stub) |
| `src/worker.js` | Delivery loop (stub) |
| `src/lib.js` | Pure functions with the failing tests: `backoffSeconds`, `sign` |
| `src/db.js` | Postgres pool, reads `DATABASE_URL` |
| `test/lib.test.js` | Unit tests to make pass |

Environment variables: `DATABASE_URL`, `WEBHOOK_SECRET`, `PORT` (default 8080), `WORKER_ENABLED` (set `false` to run the API without the worker).
