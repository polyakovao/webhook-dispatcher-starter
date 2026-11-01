import os
from contextlib import asynccontextmanager

import psycopg
from fastapi import FastAPI, HTTPException
from fastapi.responses import JSONResponse
from pydantic import BaseModel

from . import worker

DATABASE_URL = os.environ.get(
    "DATABASE_URL", "postgresql://dispatcher:dispatcher@localhost:5432/dispatcher"
)


@asynccontextmanager
async def lifespan(_: FastAPI):
    if os.getenv("WORKER_ENABLED", "true") != "false":
        worker.start()
    yield
    # TODO(candidate, stretch): graceful shutdown. Stop claiming, finish in-flight work.
    worker.stop()


app = FastAPI(title="webhook-dispatcher", lifespan=lifespan)


class EventIn(BaseModel):
    url: str
    type: str
    payload: dict


@app.get("/health")
def health():
    try:
        with psycopg.connect(DATABASE_URL, connect_timeout=2) as conn:
            conn.execute("SELECT 1")
        return {"status": "ok"}
    except Exception:
        return JSONResponse({"status": "db_unavailable"}, status_code=503)


@app.post("/events", status_code=202)
def create_event(event: EventIn):
    # TODO(candidate): validate, insert into `events`, return {"id": "<uuid>"}.
    raise HTTPException(status_code=501, detail="not implemented")
