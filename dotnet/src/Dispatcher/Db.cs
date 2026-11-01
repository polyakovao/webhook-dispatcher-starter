namespace Dispatcher;

public static class Db
{
    /// <summary>Converts postgres://user:pass@host:port/db into an Npgsql connection string.</summary>
    public static string ConnectionStringFromUrl(string url)
    {
        var u = new Uri(url);
        var userInfo = u.UserInfo.Split(':', 2);
        var port = u.Port > 0 ? u.Port : 5432;
        var user = Uri.UnescapeDataString(userInfo[0]);
        var password = Uri.UnescapeDataString(userInfo.Length > 1 ? userInfo[1] : "");
        var database = u.AbsolutePath.TrimStart('/');
        return $"Host={u.Host};Port={port};Username={user};Password={password};Database={database}";
    }
}
