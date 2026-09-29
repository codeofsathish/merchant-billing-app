<?php
namespace App\Console\Commands;

use App\Jobs\GenerateInvoiceJob;
use App\Models\Subscription;
use Illuminate\Console\Command;

class GenerateDueInvoices extends Command
{
    protected $signature = 'billing:generate-due-invoices {--chunk=500}';
    protected $description = 'Queue invoice generation for subscriptions whose billing cycle has ended';

    public function handle(): int
    {
        Subscription::where('status', 'active')
            ->where('current_period_end', '<=', now())
            ->orderBy('id')
            ->chunkById((int) $this->option('chunk'), function ($subscriptions) {
                foreach ($subscriptions as $subscription) {
                    GenerateInvoiceJob::dispatch($subscription->id)->onQueue('billing');
                }
            });

        $this->info('Due invoice jobs queued.');
        return self::SUCCESS;
    }
}
