package main

import (
	"context"
	"encoding/json"
	"errors"
	"log"
	"net/http"
	"os"
	"os/signal"
	"syscall"
	"time"

	"github.com/jackc/pgx/v5/pgxpool"

	"webhookdispatcher/internal/dispatcher"
)

func main() {
	ctx, stop := signal.NotifyContext(context.Background(), os.Interrupt, syscall.SIGTERM)
	defer stop()

	dsn := getenv("DATABASE_URL", "postgres://dispatcher:dispatcher@localhost:5432/dispatcher")
	pool, err := pgxpool.New(ctx, dsn) // connects lazily
	if err != nil {
		log.Fatalf("db config: %v", err)
	}
	defer pool.Close()

	mux := http.NewServeMux()

	mux.HandleFunc("GET /health", func(w http.ResponseWriter, r *http.Request) {
		c, cancel := context.WithTimeout(r.Context(), 2*time.Second)
		defer cancel()
		if err := pool.Ping(c); err != nil {
			writeJSON(w, http.StatusServiceUnavailable, map[string]string{"status": "db_unavailable"})
			return
		}
		writeJSON(w, http.StatusOK, map[string]string{"status": "ok"})
	})

	// Body: {"url": "...", "type": "order.created", "payload": {...}}
	mux.HandleFunc("POST /events", func(w http.ResponseWriter, r *http.Request) {
		// TODO(candidate): validate input, insert into `events`, return 202 {"id": "<uuid>"}.
		writeJSON(w, http.StatusNotImplemented, map[string]string{"error": "not implemented"})
	})

	if os.Getenv("WORKER_ENABLED") != "false" {
		go dispatcher.Run(ctx, pool, os.Getenv("WEBHOOK_SECRET"))
	}

	srv := &http.Server{
		Addr:              ":" + getenv("PORT", "8080"),
		Handler:           mux,
		ReadHeaderTimeout: 5 * time.Second,
	}

	go func() {
		<-ctx.Done()
		// TODO(candidate, stretch): wait for in-flight deliveries before exiting.
		shutdownCtx, cancel := context.WithTimeout(context.Background(), 10*time.Second)
		defer cancel()
		_ = srv.Shutdown(shutdownCtx)
	}()

	log.Printf("api listening on %s", srv.Addr)
	if err := srv.ListenAndServe(); err != nil && !errors.Is(err, http.ErrServerClosed) {
		log.Fatal(err)
	}
}

func getenv(key, fallback string) string {
	if v := os.Getenv(key); v != "" {
		return v
	}
	return fallback
}

func writeJSON(w http.ResponseWriter, status int, v any) {
	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(status)
	_ = json.NewEncoder(w).Encode(v)
}
