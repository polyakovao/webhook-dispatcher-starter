FROM golang:1.23-alpine AS build
WORKDIR /src

# go.sum is not committed in the starter. `go mod tidy` resolves it at build time.
# TODO(candidate): run `go mod tidy` locally, commit go.sum, and replace the
# tidy below with a cached `go mod download` layer for reproducible builds.
COPY . .
RUN go mod tidy && CGO_ENABLED=0 go build -o /out/server .

FROM gcr.io/distroless/static-debian12:nonroot
COPY --from=build /out/server /server
EXPOSE 8080
ENTRYPOINT ["/server"]
