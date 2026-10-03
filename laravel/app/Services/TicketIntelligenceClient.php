<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class TicketIntelligenceClient
{
    /**
     * Ping the Python service.
     *
     * @return array{status: string, service: string, version: string, timestamp: string}
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function health(): array
    {
        return $this->http()->get('/health')->throw()->json();
    }

    protected function http(): PendingRequest
    {
        return Http::baseUrl(config('services.python.url'))
            ->timeout(config('services.python.timeout'))
            ->acceptJson();
    }
}
