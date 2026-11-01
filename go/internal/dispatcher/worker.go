package dispatcher

import (
	"context"
	"log"

	"github.com/jackc/pgx/v5/pgxpool"
)

// Run is the delivery loop. It must return when ctx is cancelled.
func Run(ctx context.Context, pool *pgxpool.Pool, secret string) {
	log.Println("worker started (stub, does nothing yet)")

	// TODO(candidate): loop until ctx is done that
	//   1. claims due events safely (two workers must not take the same row),
	//   2. POSTs each one to target_url with a timeout and the signature headers,
	//   3. classifies the outcome (success / retryable / permanent),
	//   4. updates status, attempts, next_attempt_at, last_error,
	//   5. waits briefly when there is nothing to do.
	<-ctx.Done()
}
