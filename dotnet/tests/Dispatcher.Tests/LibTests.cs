using Dispatcher;
using Xunit;

namespace Dispatcher.Tests;

public class LibTests
{
    [Fact]
    public void First_retry_waits_base_delay_when_jitter_factor_is_one()
    {
        Assert.Equal(60, Backoff.Seconds(1, 0.5), 9);
    }

    [Fact]
    public void Delay_doubles_each_attempt()
    {
        Assert.Equal(120, Backoff.Seconds(2, 0.5), 9);
        Assert.Equal(240, Backoff.Seconds(3, 0.5), 9);
        Assert.Equal(480, Backoff.Seconds(4, 0.5), 9);
    }

    [Fact]
    public void Delay_is_capped_at_six_hours_before_jitter()
    {
        Assert.Equal(21600, Backoff.Seconds(30, 0.5), 9);
    }

    [Fact]
    public void Jitter_spans_half_to_one_and_a_half_times_the_delay()
    {
        Assert.Equal(30, Backoff.Seconds(1, 0.0), 9);
        Assert.Equal(89.99994, Backoff.Seconds(1, 0.999999), 3);
    }

    [Fact]
    public void Sign_produces_known_hmac_sha256_hex_vector()
    {
        var body = "{\"id\":\"evt_1\",\"type\":\"order.created\"}";
        Assert.Equal(
            "b4299661e5387b192e6a17c961991dce11e3adb10aabc368d96f3b7a148387e3",
            Signer.Sign("whsec_test", "1700000000", body));
    }
}
