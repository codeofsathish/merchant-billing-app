<?php
namespace App\Providers;

use App\Models\Plan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
         Schema::defaultStringLength(191);
         RateLimiter::for('usage', function (Request $request) {
            $merchant = (string) $request->input('merchant_id', 'unknown');
            return Limit::perMinute(120)->by($merchant . '|' . $request->ip());
        });
    }

    public static function cachedPlan(int $planId): ?Plan
    {
        return Cache::remember("plan:{$planId}", now()->addMinutes(10), fn () => Plan::find($planId));
    }
}
