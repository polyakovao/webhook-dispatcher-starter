# Webhook Dispatcher: One-Hour Interview Starter Kit

A hands-on tech lead exercise: **design, implement, containerize and deploy** a small webhook delivery service in 60 minutes. One starter per stack, all with the same spec, tests and local test receiver.

| Folder | Stack | Run the tests |
|---|---|---|
| `dotnet/` | .NET 10, minimal API, Npgsql, xUnit | `dotnet test tests/Dispatcher.Tests` |
| `node/` | Node 22, Express, pg, `node:test` | `npm install && npm test` |
| `python/` | Python 3.13, FastAPI, psycopg 3, pytest | `pip install -r requirements-dev.txt && python -m pytest` |
| `go/` | Go 1.22+, net/http, pgx, `testing` | `go mod tidy && go test ./...` |
| `php/` | PHP 8.3, plain PHP, PDO, PHPUnit 11 | `composer install && vendor/bin/phpunit` |

`_shared/` holds the single source for the files that are copied into every stack (`BRIEF.md`, `db/schema.sql`, `receiver/`, `smoke.sh`, `docker-compose.yml`, `.gitignore`). If you edit one, re-copy it into each stack folder. The PHP compose file is its own copy because PHP needs a separate worker service.

## Before the session

1. **Give the candidate one stack folder only.** Each is self-contained: zip it or push it to a fresh repo. `BRIEF.md` inside is the task statement.
2. **Pre-provision the deploy target** (Fly.io, Render, Cloud Run, a VM...) or agree that a local `docker compose up` demo counts. Account signup eats 20 minutes.
3. **Decide the AI and Google policy** and tell the candidate up front.
4. **Do a dry run yourself** with a timer. If you cannot finish the Must and Should tiers in about 35 minutes, cut scope.
5. Candidates need Docker installed, or you provide a machine.

## What a good session looks like

| Signal | Strong | Weak |
|---|---|---|
| **First 10 min** | Asks about ordering, volume, retention. Writes a short design note. Chooses Postgres-as-queue and says when they would change it. | Starts coding immediately, or designs Kafka plus Kubernetes. |
| **Unit tests** | Makes the two pure-function tests pass quickly and uses them to pin the spec. | Ignores them or "fixes" the tests. |
| **Claiming work** | Atomic claim with `FOR UPDATE SKIP LOCKED` and a lease (`locked_until`), so crashed workers' rows come back. | `SELECT` then `UPDATE` in two steps (race between workers). |
| **Delivery call** | Timeout set (connect and total), redirects off, body size sane, signature headers per `BRIEF.md`. | No timeout. Only `200` counts as success. |
| **Outcome handling** | 2xx sent. Timeout, 408, 429 (honoring `Retry-After`) and 5xx retry. 410 and other 4xx dead. Max 8 attempts. | Retries everything forever, or loses failures silently. |
| **Deploy** | Container builds from scratch, config via env, health check, CI runs tests and builds the image, no committed secrets. | "Works on my machine", hardcoded secrets. |
| **Wrap-up** | Honest list of what was cut and why, and what they would do next. | Claims it is production-ready. |

## Built-in traps (the receiver makes these easy to check)

| Receiver path | Checks that the candidate... |
|---|---|
| `/slow` | set a timeout (without one the worker hangs for 30s, and sequential workers stall everything behind it) |
| `/flaky/<key>` | retries and eventually succeeds (500, 500, then 200) |
| `/gone` | treats 410 as permanent |
| `/ratelimit` | treats 429 as retryable, bonus for honoring `Retry-After` |
| `GET /log` | sends the same event id on every retry, and the signature headers |

Two more probes: run two workers at once and look for double delivery (PHP: `docker compose up --scale worker=2`; others: run a second instance or process), and `kill -TERM` the worker mid-delivery to see what happens to the in-flight row.

## Stack notes for the interviewer

- **PHP** has no background threads, so the worker is a separate process and compose service. Concurrency across workers comes for free.
- **Node** is single-threaded: deliveries must be concurrent via promises, otherwise a slow endpoint blocks everything.
- **Python** runs the worker in a daemon thread inside the API process. A candidate may split it into its own process, which is a good sign.
- **Go** passes a cancellable `context` to the worker, so graceful shutdown is natural to add.
- **.NET** uses a `BackgroundService`. Watch for a new `HttpClient` per request (socket exhaustion) versus `IHttpClientFactory`.

## Reference claim query (sketch, for your answer key)

```sql
WITH due AS (
  SELECT id FROM events
  WHERE (status IN ('pending', 'retry') AND next_attempt_at <= now())
     OR (status = 'in_progress' AND locked_until < now())
  ORDER BY next_attempt_at
  LIMIT 20
  FOR UPDATE SKIP LOCKED
)
UPDATE events e
SET status = 'in_progress', locked_until = now() + interval '60 seconds', updated_at = now()
FROM due
WHERE e.id = due.id
RETURNING e.*;
```

This is a sketch and was not executed against Postgres while building the kit.

## Scoring

Reuse the rubric from the interview plan: Architecture, Code quality, DevOps and delivery, Communication, Pragmatism, each scored 1-4. For this exercise weigh **prioritization** heavily: a thin end-to-end slice that runs beats a polished fragment.

## What was verified while building this kit

| Check | Result |
|---|---|
| Starter unit tests fail as intended (Node) | Ran: 5 of 5 fail |
| Real test files pass against a reference implementation (Node, Python, Go) | Ran: all pass |
| Same test vectors against a reference implementation (PHP) | Ran as a plain script, since PHPUnit could not be installed |
| Starter stubs fail correctly (Python, PHP, Go) | Ran |
| PHP source files lint | Ran: no syntax errors |
| Receiver: every path, flaky counters, `Retry-After`, `/slow` hang, `/log` | Ran: matches `BRIEF.md` |
| `smoke.sh` and `receiver/server.cjs` syntax | Ran |
| All five `docker-compose.yml` files parse | Ran (`docker compose config`) |
| **Not verified:** `npm install`, `pip install`, `go mod tidy`, `composer install` | The sandbox blocked the package registries, so dependencies could not be installed and no lockfiles exist |
| **Not verified:** the full API and worker starting against Postgres, Docker image builds, CI workflows | Not run |
| **Not verified:** the entire .NET starter, including compilation | No .NET SDK was available |

Because of the gaps above, **do your dry run before using this with a candidate**. Likely first-run snags: pinned package versions (`pgx v5.7.2`, `Npgsql 9.0.3`, xUnit packages), the `net10.0` target and `dotnet/sdk:10.0` images, and `go mod tidy` or `npm install` writing lockfiles you may want to commit. The Node and Go Dockerfiles are written to build without lockfiles (the Go one runs `go mod tidy` at build time) and say so in comments.
