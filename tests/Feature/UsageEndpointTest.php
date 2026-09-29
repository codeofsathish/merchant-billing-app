<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Merchant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class UsageEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_event_is_not_double_counted(): void
    {
        Queue::fake();

        $merchant = Merchant::create(['name'=>'Acme']);
        $customer = Customer::create(['merchant_id'=>$merchant->id,'name'=>'John']);

        $payload = [
            'merchant_id'=>$merchant->id,
            'customer_id'=>$customer->id,
            'event_key'=>'evt-123',
            'occurred_at'=>'2026-09-29 10:00:00',
            'units'=>10,
            'type'=>'api'
        ];

        $this->postJson('/api/usage', $payload)->assertStatus(201);
        $this->postJson('/api/usage', $payload)->assertStatus(200)->assertJson(['duplicate'=>true]);

        $this->assertDatabaseCount('usage_events', 1);
        Queue::assertPushed(\App\Jobs\AggregateDailyUsageJob::class, 2);
    }
}
