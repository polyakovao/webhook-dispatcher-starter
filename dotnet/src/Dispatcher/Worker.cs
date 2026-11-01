using Npgsql;

namespace Dispatcher;

public class Worker(NpgsqlDataSource db, IConfiguration config, ILogger<Worker> log) : BackgroundService
{
    // Env you will need: config["WEBHOOK_SECRET"]
    protected override async Task ExecuteAsync(CancellationToken stoppingToken)
    {
        log.LogInformation("Worker started (stub, does nothing yet)");

        // TODO(candidate): loop until stoppingToken is cancelled that
        //   1. claims due events safely (two workers must not take the same row),
        //   2. POSTs each one to target_url with a timeout and the signature headers
        //      (use IHttpClientFactory or one shared HttpClient),
        //   3. classifies the outcome (success / retryable / permanent),
        //   4. updates status, attempts, next_attempt_at, last_error,
        //   5. waits briefly (Task.Delay) when there is nothing to do.
        try
        {
            await Task.Delay(Timeout.Infinite, stoppingToken);
        }
        catch (OperationCanceledException)
        {
            // shutting down
        }
    }
}
