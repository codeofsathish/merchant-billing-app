<?php
namespace App\Services;

use App\Models\Plan;
use Illuminate\Support\Facades\Cache;

class PlanCacheService
{
    public function key(int $merchantId, int $planId): string
    {
        return "merchant:{$merchantId}:plan:{$planId}";
    }

    public function get(int $merchantId, int $planId): ?Plan
    {
        return Cache::remember($this->key($merchantId, $planId), now()->addMinutes(10), fn () =>
            Plan::where('merchant_id', $merchantId)->whereKey($planId)->first()
        );
    }

    public function forget(int $merchantId, int $planId): void
    {
        Cache::forget($this->key($merchantId, $planId));
    }
}
