package dispatcher

import (
	"math"
	"testing"
)

func approx(t *testing.T, got, want, tol float64) {
	t.Helper()
	if math.Abs(got-want) > tol {
		t.Fatalf("got %v, want %v (±%v)", got, want, tol)
	}
}

func TestFirstRetryWaitsBaseDelayWhenJitterFactorIsOne(t *testing.T) {
	approx(t, BackoffSeconds(1, 0.5), 60, 1e-9)
}

func TestDelayDoublesEachAttempt(t *testing.T) {
	approx(t, BackoffSeconds(2, 0.5), 120, 1e-9)
	approx(t, BackoffSeconds(3, 0.5), 240, 1e-9)
	approx(t, BackoffSeconds(4, 0.5), 480, 1e-9)
}

func TestDelayIsCappedAtSixHoursBeforeJitter(t *testing.T) {
	approx(t, BackoffSeconds(30, 0.5), 21600, 1e-9)
}

func TestJitterSpansHalfToOneAndAHalfTimesTheDelay(t *testing.T) {
	approx(t, BackoffSeconds(1, 0), 30, 1e-9)
	approx(t, BackoffSeconds(1, 0.999999), 89.99994, 1e-3)
}

func TestSignProducesKnownHMACSHA256HexVector(t *testing.T) {
	body := `{"id":"evt_1","type":"order.created"}`
	got := Sign("whsec_test", "1700000000", body)
	want := "b4299661e5387b192e6a17c961991dce11e3adb10aabc368d96f3b7a148387e3"
	if got != want {
		t.Fatalf("got %s, want %s", got, want)
	}
}
