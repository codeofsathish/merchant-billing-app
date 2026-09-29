<?php

namespace App\Jobs;

use App\Models\DailyUsage;
use App\Models\UsageEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class AggregateDailyUsageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(
        public int $merchantId,
        public int $customerId,
        public string $usageDate
    ) {}

    public function handle(): void
    {
        $units = UsageEvent::where('merchant_id', $this->merchantId)
            ->where('customer_id', $this->customerId)
            ->whereDate('occurred_at', $this->usageDate)
            ->sum('units');

        DB::table('daily_usages')->upsert(
            [[
                'merchant_id' => $this->merchantId,
                'customer_id' => $this->customerId,
                'usage_date' => $this->usageDate,
                'units' => $units,
                'updated_at' => now(),
                'created_at' => now(),
            ]],
            ['merchant_id', 'customer_id', 'usage_date'],
            ['units', 'updated_at']
        );
    }
}
