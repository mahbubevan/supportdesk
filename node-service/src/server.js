import express from 'express';
import { createServer } from 'node:http';
import { timingSafeEqual } from 'node:crypto';
import { Server } from 'socket.io';

const PORT = Number(process.env.PORT ?? 3000);
const CORS_ORIGIN = process.env.CORS_ORIGIN ?? '*';
const INTERNAL_TOKEN = process.env.NODE_INTERNAL_TOKEN ?? '';

const SERVICE_NAME = 'supportdesk-node';
const SERVICE_VERSION = '0.2.0';

// e.g. ticket.created, ticket.reply.created, system.ping
const EVENT_TYPE_PATTERN = /^[a-z]+(\.[a-z_]+)+$/;

if (INTERNAL_TOKEN.length < 32) {
    console.error(`[${SERVICE_NAME}] NODE_INTERNAL_TOKEN is missing or shorter than 32 chars`);
    process.exit(1);
}

const app = express();
app.use(express.json({ limit: '100kb' }));

const httpServer = createServer(app);

const io = new Server(httpServer, {
    cors: { origin: CORS_ORIGIN },
});

const tokenMatches = (provided) => {
    const a = Buffer.from(String(provided ?? ''));
    const b = Buffer.from(INTERNAL_TOKEN);
    return a.length === b.length && timingSafeEqual(a, b);
};

const requireInternalToken = (req, res, next) => {
    if (!tokenMatches(req.get('X-Internal-Token'))) {
        return res.status(401).json({ error: 'unauthorized' });
    }
    next();
};

app.get('/health', (req, res) => {
    res.json({
        status: 'ok',
        service: SERVICE_NAME,
        version: SERVICE_VERSION,
        connections: io.engine.clientsCount,
        timestamp: new Date().toISOString(),
    });
});

// Internal: Laravel/Worker -> Node -> Socket.IO rooms
app.post('/event', requireInternalToken, async (req, res) => {
    const { type, rooms, payload = {} } = req.body ?? {};

    if (typeof type !== 'string' || !EVENT_TYPE_PATTERN.test(type)) {
        return res.status(422).json({ error: 'type must look like "ticket.created"' });
    }

    if (!Array.isArray(rooms) || rooms.length === 0 || !rooms.every((r) => typeof r === 'string' && r !== '')) {
        return res.status(422).json({ error: 'rooms must be a non-empty array of strings' });
    }

    if (typeof payload !== 'object' || payload === null || Array.isArray(payload)) {
        return res.status(422).json({ error: 'payload must be an object' });
    }

    const recipients = (await io.in(rooms).fetchSockets()).length;
    io.to(rooms).emit(type, payload);

    console.log(`[event] ${type} -> [${rooms.join(', ')}] (${recipients} sockets)`);

    res.status(202).json({ accepted: true, type, rooms, recipients });
});

io.on('connection', (socket) => {
    console.log(`[socket] connected: ${socket.id}`);

    socket.on('disconnect', (reason) => {
        console.log(`[socket] disconnected: ${socket.id} (${reason})`);
    });
});

httpServer.listen(PORT, '0.0.0.0', () => {
    console.log(`[${SERVICE_NAME}] v${SERVICE_VERSION} listening on port ${PORT}`);
});

const shutdown = (signal) => {
    console.log(`[${SERVICE_NAME}] ${signal} received, shutting down`);
    io.close(() => process.exit(0));
};

process.on('SIGTERM', () => shutdown('SIGTERM'));
process.on('SIGINT', () => shutdown('SIGINT'));