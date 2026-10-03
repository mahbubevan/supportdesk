import express from 'express';
import { createServer } from 'node:http';
import { Server } from 'socket.io';

const PORT = Number(process.env.PORT ?? 3000);
const CORS_ORIGIN = process.env.CORS_ORIGIN ?? '*';

const SERVICE_NAME = 'supportdesk-node';
const SERVICE_VERSION = '0.1.0';

const app = express();
app.use(express.json());

const httpServer = createServer(app);

const io = new Server(httpServer, {
    cors: { origin: CORS_ORIGIN },
});

app.get('/health', (req, res) => {
    res.json({
        status: 'ok',
        service: SERVICE_NAME,
        version: SERVICE_VERSION,
        connections: io.engine.clientsCount,
        timestamp: new Date().toISOString(),
    });
});

io.on('connection', (socket) => {
    console.log(`[socket] connected: ${socket.id}`);

    socket.on('disconnect', (reason) => {
        console.log(`[socket] disconnected: ${socket.id} (${reason})`);
    });
});

httpServer.listen(PORT, '0.0.0.0', () => {
    console.log(`[${SERVICE_NAME}] listening on port ${PORT}`);
});

const shutdown = (signal) => {
    console.log(`[${SERVICE_NAME}] ${signal} received, shutting down`);
    io.close(() => process.exit(0));
};

process.on('SIGTERM', () => shutdown('SIGTERM'));
process.on('SIGINT', () => shutdown('SIGINT'));