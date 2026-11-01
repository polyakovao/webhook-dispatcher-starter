package dispatcher

const (
	BaseDelaySeconds = 60.0
	MaxDelaySeconds  = 6 * 60 * 60.0
)

// BackoffSeconds returns the seconds to wait before the next attempt.
//
// attempt is the number of failed attempts so far (1 for the first retry).
// rand is in [0, 1) and is a parameter so tests are deterministic; callers
// pass rand.Float64().
//
//	delay = min(60 * 2^(attempt-1), 21600) * (0.5 + rand)
//
// TODO(candidate): implement.
func BackoffSeconds(attempt int, rand float64) float64 {
	return 0 // TODO: implement
}

// Sign returns the hex HMAC-SHA256 of timestamp + "." + body using secret.
// The caller builds the header: "t=<timestamp>,v1=<hex>".
//
// TODO(candidate): implement.
func Sign(secret, timestamp, body string) string {
	return "" // TODO: implement
}
