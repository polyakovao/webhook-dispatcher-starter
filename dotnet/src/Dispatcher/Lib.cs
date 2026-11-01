namespace Dispatcher;

public static class Backoff
{
    public const double BaseDelaySeconds = 60;
    public const double MaxDelaySeconds = 6 * 60 * 60;

    /// <summary>
    /// Seconds to wait before the next attempt.
    /// <paramref name="attempt"/> = number of failed attempts so far (1 for the first retry).
    /// <paramref name="rand"/> is in [0, 1) and injectable so tests are deterministic;
    /// when null, use Random.Shared.NextDouble().
    ///
    ///   delay = min(60 * 2^(attempt-1), 21600) * (0.5 + rand)
    ///
    /// TODO(candidate): implement.
    /// </summary>
    public static double Seconds(int attempt, double? rand = null) =>
        throw new NotImplementedException("TODO: implement Backoff.Seconds");
}

public static class Signer
{
    /// <summary>
    /// Lowercase hex HMAC-SHA256 of $"{timestamp}.{body}" using <paramref name="secret"/>.
    /// The caller builds the header: $"t={timestamp},v1={hex}".
    ///
    /// TODO(candidate): implement.
    /// </summary>
    public static string Sign(string secret, string timestamp, string body) =>
        throw new NotImplementedException("TODO: implement Signer.Sign");
}
