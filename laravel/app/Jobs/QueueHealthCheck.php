<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class QueueHealthCheck implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $token) {}

    public function handle(): void
    {
        Cache::put("queue-health:{$this->token}", now()->toIso8601String(), 300);

        Log::info('Queue health check processed', ['token' => $this->token]);
    }
}
