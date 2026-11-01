using System.Text.Json;
using Dispatcher;
using Npgsql;

var builder = WebApplication.CreateBuilder(args);

var databaseUrl = builder.Configuration["DATABASE_URL"]
    ?? "postgres://dispatcher:dispatcher@localhost:5432/dispatcher";
builder.Services.AddSingleton(NpgsqlDataSource.Create(Db.ConnectionStringFromUrl(databaseUrl)));

if (builder.Configuration["WORKER_ENABLED"] != "false")
{
    builder.Services.AddHostedService<Worker>();
}

var app = builder.Build();

app.MapGet("/health", async (NpgsqlDataSource db) =>
{
    try
    {
        await using var cmd = db.CreateCommand("SELECT 1");
        await cmd.ExecuteScalarAsync();
        return Results.Ok(new { status = "ok" });
    }
    catch
    {
        return Results.Json(new { status = "db_unavailable" }, statusCode: 503);
    }
});

// Body: { "url": "...", "type": "order.created", "payload": { ... } }
app.MapPost("/events", (EventIn input) =>
{
    // TODO(candidate): validate input, insert into `events`, return 202 { id }.
    return Results.Json(new { error = "not implemented" }, statusCode: 501);
});

app.Run();

public record EventIn(string Url, string Type, JsonElement Payload);
