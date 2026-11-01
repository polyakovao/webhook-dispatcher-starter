import express from 'express';
import { pool } from './db.js';
import { startWorker } from './worker.js';

const app = express();
app.use(express.json({ limit: '256kb' }));

app.get('/health', async (_req, res) => {
  try {
    await pool.query('SELECT 1');
    res.json({ status: 'ok' });
  } catch {
    res.status(503).json({ status: 'db_unavailable' });
  }
});

// Body: { "url": "...", "type": "order.created", "payload": { ... } }
app.post('/events', async (req, res) => {
  // TODO(candidate): validate input, insert into `events`, return 202 { id }.
  res.status(501).json({ error: 'not implemented' });
});

const port = Number(process.env.PORT || 8080);
const server = app.listen(port, () => console.log(`api listening on ${port}`));

if (process.env.WORKER_ENABLED !== 'false') {
  startWorker();
}

// TODO(candidate, stretch): on SIGTERM stop claiming work, finish in-flight
// deliveries, then close `server` and `pool`.
process.on('SIGTERM', () => server.close(() => process.exit(0)));
