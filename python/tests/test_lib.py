import pytest

from app.lib import backoff_seconds, sign


def test_first_retry_waits_base_delay_when_jitter_factor_is_one():
    assert backoff_seconds(1, 0.5) == 60


def test_delay_doubles_each_attempt():
    assert backoff_seconds(2, 0.5) == 120
    assert backoff_seconds(3, 0.5) == 240
    assert backoff_seconds(4, 0.5) == 480


def test_delay_is_capped_at_six_hours_before_jitter():
    assert backoff_seconds(30, 0.5) == 21600


def test_jitter_spans_half_to_one_and_a_half_times_the_delay():
    assert backoff_seconds(1, 0) == 30
    assert backoff_seconds(1, 0.999999) == pytest.approx(89.99994, abs=1e-3)


def test_sign_produces_known_hmac_sha256_hex_vector():
    body = '{"id":"evt_1","type":"order.created"}'
    assert (
        sign("whsec_test", "1700000000", body)
        == "b4299661e5387b192e6a17c961991dce11e3adb10aabc368d96f3b7a148387e3"
    )
