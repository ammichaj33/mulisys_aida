<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PenaltyCalculationService;

class CalculatePenalties extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'penalties:calculate {--force : Force calculation even if auto_calculate is disabled}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate penalties for loans with overdue repayments';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!config('penalties.auto_calculate', true) && !$this->option('force')) {
            $this->info('Penalty calculation is disabled. Use --force to override.');
            return 0;
        }

        $this->info('Calculating penalties for overdue loans...');

        $penaltyService = new PenaltyCalculationService();
        $totalPenalties = $penaltyService->calculateAllPenalties();

        $this->info("Calculated penalties for {$totalPenalties} loans.");

        return 0;
    }
}


