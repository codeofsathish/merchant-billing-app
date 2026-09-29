<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Merchant;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $merchant = Merchant::create(['name' => 'Demo Merchant']);

        $starter = Plan::create([
            'merchant_id' => $merchant->id,
            'name' => 'Starter',
            'base_price' => 99.00,
            'billing_cycle' => 'monthly',
            'included_units' => 1000,
            'overage_rate_per_unit' => 0.10,
        ]);

        Customer::create([
            'merchant_id' => $merchant->id,
            'name' => 'Demo Customer',
            'email' => 'customer@example.com',
        ]);

        $this->command?->info("Demo merchant ID: {$merchant->id}");
        $this->command?->info("Demo plan ID: {$starter->id}");
    }
}
