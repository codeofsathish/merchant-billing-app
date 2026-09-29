<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Subscription;
use App\Services\BillingCalculator;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class GenerateInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(public int $subscriptionId) {}

    public function handle(BillingCalculator $calculator): void
    {
        $subscription = Subscription::with('periods')->findOrFail($this->subscriptionId);

        $periodStart = Carbon::parse($subscription->current_period_start);
        $periodEnd = Carbon::parse($subscription->current_period_end);

        if ($periodEnd->isFuture()) {
            return;
        }

        $invoice = DB::transaction(function () use ($subscription, $periodStart, $periodEnd, $calculator) {
            if (Invoice::where('subscription_id', $subscription->id)
                ->where('period_start', $periodStart)
                ->exists()) {
                return null;
            }

            $result = $calculator->calculate($subscription);

            $invoice = Invoice::create([
                'merchant_id' => $subscription->merchant_id,
                'customer_id' => $subscription->customer_id,
                'subscription_id' => $subscription->id,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'subtotal' => $result['subtotal'],
                'total' => $result['total'],
                'status' => 'issued',
            ]);

            foreach ($result['lines'] as $line) {
                InvoiceLine::create([
                    'invoice_id' => $invoice->id,
                    'subscription_period_id' => $line['subscription_period_id'],
                    'description' => $line['description'],
                    'base_amount' => $line['base_amount'],
                    'included_units' => $line['included_units'],
                    'used_units' => $line['used_units'],
                    'overage_units' => $line['overage_units'],
                    'overage_amount' => $line['overage_amount'],
                    'total' => $line['total'],
                ]);
            }

            return $invoice;
        });

        if ($invoice) {
            // Advance the cycle. For yearly plans this starts the next year; otherwise next month.
            $plan = $subscription->periods->last()->plan;
            $nextStart = $periodEnd->copy()->addSecond();
            $nextEnd = $plan->billing_cycle === 'yearly'
                ? $nextStart->copy()->addYear()->subSecond()
                : $nextStart->copy()->addMonth()->subSecond();

            $subscription->update([
                'current_period_start' => $nextStart,
                'current_period_end' => $nextEnd,
            ]);
        }
    }
}
