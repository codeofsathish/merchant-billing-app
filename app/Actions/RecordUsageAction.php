<?php
namespace App\Actions;

use App\DTOs\UsageEventData;
use App\Jobs\AggregateDailyUsageJob;
use App\Models\Customer;
use App\Models\UsageEvent;
use Illuminate\Validation\ValidationException;

class RecordUsageAction
{
    public function execute(UsageEventData $data): UsageEvent
    {
        if (!Customer::whereKey($data->customerId)->where('merchant_id',$data->merchantId)->exists()) {
            throw ValidationException::withMessages(['customer_id' => 'Customer does not belong to the supplied merchant.']);
        }

        $event = UsageEvent::firstOrCreate(
            ['merchant_id'=>$data->merchantId,'customer_id'=>$data->customerId,'event_key'=>$data->eventKey],
            ['occurred_at'=>$data->occurredAt,'usage_date'=>$data->occurredAt->toDateString(),'units'=>$data->units,'type'=>$data->type,'metadata'=>$data->metadata]
        );

        AggregateDailyUsageJob::dispatch($data->merchantId,$data->customerId,$data->occurredAt->toDateString())->onQueue('usage');
        return $event;
    }
}
