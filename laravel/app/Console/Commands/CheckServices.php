<?php

namespace App\Console\Commands;

use App\Services\TicketIntelligenceClient;
use Illuminate\Console\Command;
use Throwable;

class CheckServices extends Command
{
    protected $signature = 'services:check';

    protected $description = 'Check connectivity to internal services (Python, Node)';

    public function handle(TicketIntelligenceClient $python): int
    {
        $results = [
            $this->probe('Python', fn() => $python->health()),
        ];

        return in_array(false, $results, true) ? self::FAILURE : self::SUCCESS;
    }

    private function probe(string $name, callable $check): bool
    {
        $start = microtime(true);

        try {
            $data = $check();
            $ms = (int) round((microtime(true) - $start) * 1000);

            $this->components->twoColumnDetail(
                $name,
                "<fg=green;options=bold>OK</> {$data['service']} v{$data['version']} ({$ms} ms)"
            );

            return true;
        } catch (Throwable $e) {
            $this->components->twoColumnDetail(
                $name,
                '<fg=red;options=bold>FAIL</> ' . $e->getMessage()
            );

            return false;
        }
    }
}
