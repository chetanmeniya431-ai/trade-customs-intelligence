<?php

namespace App\Console\Commands;

use App\Services\SignalsEngineService;
use Illuminate\Console\Command;

class CheckSignals extends Command
{
    protected $signature = 'signals:check';

    protected $description = 'Evaluate all active signals and record new signal events.';

    public function handle(SignalsEngineService $engine): int
    {
        $fired = $engine->runAll();
        $this->info("Signals check complete. {$fired} new signal event(s) fired.");

        return self::SUCCESS;
    }
}
