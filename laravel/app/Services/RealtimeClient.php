<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class RealtimeClient
{
    /**
     * @return array{status: string, service: string, version: string, connections: int, timestamp: string}
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function health(): array
    {
        return $this->http()->get('/health')->throw()->json();
    }

    /**
     * Send an event to Node, which broadcasts it to the given Socket.IO rooms.
     *
     * @param  array<int, string>  $rooms
     * @return array{accepted: bool, type: string, rooms: array<int, string>, recipients: int}
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function publish(string $type, array $rooms, array $payload = []): array
    {
        return $this->http()
            ->withHeaders(['X-Internal-Token' => config('services.node.token')])
            ->post('/event', [
                'type' => $type,
                'rooms' => array_values($rooms),
                'payload' => (object) $payload,
            ])
            ->throw()
            ->json();
    }

    protected function http(): PendingRequest
    {
        return Http::baseUrl(config('services.node.url'))
            ->timeout(config('services.node.timeout'))
            ->acceptJson();
    }
}
