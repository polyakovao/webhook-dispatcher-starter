# Webhook Dispatcher: .NET starter

Read `BRIEF.md` first. Stack: .NET 10, ASP.NET Core minimal API, Npgsql, xUnit.

## Run the tests (they fail until you implement `src/Dispatcher/Lib.cs`)

```bash
dotnet test tests/Dispatcher.Tests
```

## Run everything locally

```bash
docker compose up --build
./smoke.sh            # in another terminal
```

API: http://localhost:8080, receiver: http://localhost:9000, Postgres: localhost:5432.

Without Docker for the app itself (Postgres from compose):

```bash
docker compose up db receiver -d
ASPNETCORE_URLS=http://localhost:8080 dotnet run --project src/Dispatcher
```

## Layout

| Path | Purpose |
|---|---|
| `src/Dispatcher/Program.cs` | HTTP API (`/health` works, `POST /events` is a stub) |
| `src/Dispatcher/Worker.cs` | `BackgroundService` delivery loop (stub) |
| `src/Dispatcher/Lib.cs` | Pure functions with the failing tests: `Backoff.Seconds`, `Signer.Sign` |
| `src/Dispatcher/Db.cs` | Turns `DATABASE_URL` into an Npgsql connection string |
| `tests/Dispatcher.Tests/LibTests.cs` | Unit tests to make pass |

Environment variables: `DATABASE_URL`, `WEBHOOK_SECRET`, `ASPNETCORE_URLS` (the Dockerfile sets port 8080), `WORKER_ENABLED` (set `false` to run the API without the worker).
