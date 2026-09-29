<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Merchant;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\BillingCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BillingCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_prorates_base_and_included_units_for_mid_cycle_change(): void
    {
        $merchant = Merchant::create(['name' => 'Acme']);
        $customer = Customer::create(['merchant_id' => $merchant->id, 'name' => 'John']);
        $plan = Plan::create([
            'merchant_id' => $merchant->id,
            'name' => 'Pro',
            'base_price' => 300,
            'billing_cycle' => 'monthly',
            'included_units' => 3000,
            'overage_rate_per_unit' => 0.10,
        ]);

        $subscription = Subscription::create([
            'merchant_id' => $merchant->id,
            'customer_id' => $customer->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'current_period_start' => '2026-09-01 00:00:00',
            'current_period_end' => '2026-09-30 23:59:59',
        ]);

        $subscription->periods()->create([
            'plan_id' => $plan->id,
            'starts_at' => '2026-09-01 00:00:00',
            'ends_at' => '2026-09-15 23:59:59',
            'base_price' => 300,
            'included_units' => 3000,
            'overage_rate_per_unit' => 0.10,
        ]);
        $subscription->periods()->create([
            'plan_id' => $plan->id,
            'starts_at' => '2026-09-16 00:00:00',
            'ends_at' => null,
            'base_price' => 600,
            'included_units' => 6000,
            'overage_rate_per_unit' => 0.20,
        ]);

        DB::table('usage_events')->insert([
            [
                'merchant_id'=>$merchant->id,'customer_id'=>$customer->id,'event_key'=>'a',
                'occurred_at'=>'2026-09-10 10:00:00','usage_date'=>'2026-09-10',
                'units'=>1000,'type'=>'api','metadata'=>null,'created_at'=>now(),'updated_at'=>now()
            ],
            [
                'merchant_id'=>$merchant->id,'customer_id'=>$customer->id,'event_key'=>'b',
                'occurred_at'=>'2026-09-20 10:00:00','usage_date'=>'2026-09-20',
                'units'=>4000,'type'=>'api','metadata'=>null,'created_at'=>now(),'updated_at'=>now()
            ],
        ]);

        $result = app(BillingCalculator::class)->calculate($subscription->load('periods.plan'));

        $this->assertCount(2, $result['lines']);
        $this->assertEquals(0, $result['lines'][0]['overage_units']);
        $this->assertEquals(0, $result['lines'][1]['overage_units']);
        $this->assertEquals(650.00, $result['total']);
    }

    public function test_overage_is_charged_only_above_included_allowance(): void
    {
        $merchant = Merchant::create(['name' => 'Acme']);
        $customer = Customer::create(['merchant_id' => $merchant->id, 'name' => 'John']);
        $plan = Plan::create([
            'merchant_id' => $merchant->id,
            'name' => 'Starter',
            'base_price' => 100,
            'billing_cycle' => 'monthly',
            'included_units' => 1000,
            'overage_rate_per_unit' => 0.25,
        ]);

        $subscription = Subscription::create([
            'merchant_id'=>$merchant->id,'customer_id'=>$customer->id,'plan_id'=>$plan->id,
            'status'=>'active','current_period_start'=>'2026-09-01 00:00:00',
            'current_period_end'=>'2026-09-30 23:59:59'
        ]);

        $subscription->periods()->create([
            'plan_id'=>$plan->id,'starts_at'=>'2026-09-01 00:00:00','ends_at'=>null,
            'base_price'=>100,'included_units'=>1000,'overage_rate_per_unit'=>0.25
        ]);

        DB::table('usage_events')->insert([
            'merchant_id'=>$merchant->id,'customer_id'=>$customer->id,'event_key'=>'x',
            'occurred_at'=>'2026-09-10 10:00:00','usage_date'=>'2026-09-10',
            'units'=>1200,'type'=>'api','metadata'=>null,'created_at'=>now(),'updated_at'=>now()
        ]);

        $result = app(BillingCalculator::class)->calculate($subscription->load('periods.plan'));

        $this->assertEquals(50.00, $result['lines'][0]['overage_amount']);
        $this->assertEquals(150.00, $result['total']);
    }
}
