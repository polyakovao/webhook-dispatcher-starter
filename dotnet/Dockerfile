FROM mcr.microsoft.com/dotnet/sdk:10.0 AS build
WORKDIR /src

COPY src/Dispatcher/Dispatcher.csproj src/Dispatcher/
RUN dotnet restore src/Dispatcher/Dispatcher.csproj

COPY src/ src/
RUN dotnet publish src/Dispatcher/Dispatcher.csproj -c Release -o /out --no-restore

FROM mcr.microsoft.com/dotnet/aspnet:10.0
WORKDIR /app
COPY --from=build /out .

ENV ASPNETCORE_URLS=http://+:8080
EXPOSE 8080
USER $APP_UID
ENTRYPOINT ["dotnet", "Dispatcher.dll"]
