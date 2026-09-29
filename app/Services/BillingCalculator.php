<?php

namespace App\Services;

use App\Models\DailyUsage;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BillingCalculator
{
    /**
     * Each subscription period is billed independently.
     * Base price and included units are prorated by calendar-day coverage
     * inside the subscription's billing cycle.
     *
     * Usage is attributed to the plan segment by event timestamp, so a
     * mid-cycle upgrade/downgrade never re-prices historical usage.
     */
    public function calculate(Subscription $subscription): array
    {
        $cycleStart = Carbon::parse($subscription->current_period_start)->startOfDay();
        $cycleEnd = Carbon::parse($subscription->current_period_end)->endOfDay();
        $cycleDays = max(1, $cycleStart->diffInDays($cycleEnd) + 1);

        $lines = [];
        $subtotal = 0.0;

        foreach ($subscription->periods->sortBy('starts_at') as $period) {
            // Ignore subscription-period rows that belong to an older/newer billing cycle.
            $periodStartRaw = Carbon::parse($period->starts_at);
            $periodEndRaw = $period->ends_at ? Carbon::parse($period->ends_at) : null;
            if ($periodStartRaw->gt($cycleEnd) || ($periodEndRaw && $periodEndRaw->lt($cycleStart))) {
                continue;
            }
            $segmentStart = Carbon::parse($period->starts_at)->max($cycleStart);
            $segmentEnd = $period->ends_at
                ? Carbon::parse($period->ends_at)->min($cycleEnd)
                : $cycleEnd;

            if ($segmentEnd->lt($segmentStart)) {
                continue;
            }

            $segmentDays = $segmentStart->startOfDay()->diffInDays($segmentEnd->copy()->startOfDay()) + 1;
            $baseAmount = round((float) $period->base_price * ($segmentDays / $cycleDays), 2);
            $includedUnits = (float) $period->included_units * ($segmentDays / $cycleDays);

            // Use raw events for exact attribution around a plan change.
            $usedUnits = (float) DB::table('usage_events')
                ->where('customer_id', $subscription->customer_id)
                ->whereBetween('occurred_at', [$segmentStart, $segmentEnd])
                ->sum('units');

            $overageUnits = max(0, $usedUnits - $includedUnits);
            $overageAmount = round($overageUnits * (float) $period->overage_rate_per_unit, 2);
            $total = round($baseAmount + $overageAmount, 2);

            $lines[] = [
                'subscription_period_id' => $period->id,
                'description' => 'Plan: ' . $period->plan->name,
                'base_amount' => $baseAmount,
                'included_units' => round($includedUnits, 4),
                'used_units' => $usedUnits,
                'overage_units' => round($overageUnits, 4),
                'overage_amount' => $overageAmount,
                'total' => $total,
            ];

            $subtotal += $total;
        }

        return [
            'subtotal' => round($subtotal, 2),
            'total' => round($subtotal, 2),
            'lines' => $lines,
        ];
    }
}
