<?php

namespace App\Console\Commands;

use App\Models\VashiMarketBill;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillVashiMarketPaymentDifference extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vashi-market:backfill-payment-difference
                            {--dry-run : Show how many bills would be updated without saving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill interest/offer difference for previously paid Vashi Market bills';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = VashiMarketBill::query()
            ->where('is_paid', true)
            ->whereNotNull('paid_amount')
            ->whereNotNull('total_bill_amount');

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->info('No paid bills found to backfill.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info("Dry run: {$total} paid bill(s) would be updated.");

            return self::SUCCESS;
        }

        $updated = $query->update([
            'payment_difference' => DB::raw('paid_amount - total_bill_amount'),
        ]);

        $this->info("Updated payment_difference for {$updated} paid bill(s).");
        $this->line('Formula used: paid_amount - total_bill_amount');
        $this->line('Positive = interest paid, Negative = offer received.');

        return self::SUCCESS;
    }
}
