# Webhook Dispatcher: Go starter

Read `BRIEF.md` first. Stack: Go 1.22+, `net/http` (1.22 pattern routing), `pgx/v5`, standard `testing`.

## Run the tests (they fail until you implement `internal/dispatcher/lib.go`)

```bash
go mod tidy      # first time only: fetches pgx and writes go.sum
go test ./...
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
| `main.go` | HTTP API (`/health` works, `POST /events` is a stub), starts the worker |
| `internal/dispatcher/worker.go` | Delivery loop (stub) |
| `internal/dispatcher/lib.go` | Pure functions with the failing tests: `BackoffSeconds`, `Sign` |
| `internal/dispatcher/lib_test.go` | Unit tests to make pass |

Environment variables: `DATABASE_URL`, `WEBHOOK_SECRET`, `PORT` (default 8080), `WORKER_ENABLED` (set `false` to run the API without the worker).
