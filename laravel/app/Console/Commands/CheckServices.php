<?php

namespace App\Console\Commands;

use App\Services\RealtimeClient;
use App\Services\TicketIntelligenceClient;
use Illuminate\Console\Command;
use Throwable;

class CheckServices extends Command
{
    protected $signature = 'services:check';

    protected $description = 'Check connectivity to internal services (Python, Node)';

    public function handle(TicketIntelligenceClient $python, RealtimeClient $node): int
    {
        $results = [
            $this->probe('Python', function () use ($python) {
                $h = $python->health();

                return "{$h['service']} v{$h['version']}";
            }),
            $this->probe('Node', function () use ($node) {
                $h = $node->health();

                return "{$h['service']} v{$h['version']}, {$h['connections']} sockets";
            }),
            $this->probe('Node event', function () use ($node) {
                $r = $node->publish('system.ping', ['system'], ['at' => now()->toIso8601String()]);

                return "{$r['type']} accepted ({$r['recipients']} recipients)";
            }),
        ];

        return in_array(false, $results, true) ? self::FAILURE : self::SUCCESS;
    }

    private function probe(string $name, callable $check): bool
    {
        $start = microtime(true);

        try {
            $detail = $check();
            $ms = (int) round((microtime(true) - $start) * 1000);

            $this->components->twoColumnDetail($name, "<fg=green;options=bold>OK</> {$detail} ({$ms} ms)");

            return true;
        } catch (Throwable $e) {
            $this->components->twoColumnDetail($name, '<fg=red;options=bold>FAIL</> ' . $e->getMessage());

            return false;
        }
    }
}
