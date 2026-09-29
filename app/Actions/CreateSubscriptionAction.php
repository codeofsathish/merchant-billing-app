<?php
namespace App\Actions;
use App\Models\Customer; use App\Models\Subscription; use App\Services\PlanCacheService; use Carbon\CarbonImmutable; use Illuminate\Support\Facades\DB; use Illuminate\Validation\ValidationException;
class CreateSubscriptionAction {
 public function __construct(private readonly PlanCacheService $plans) {}
 public function execute(int $merchantId,int $customerId,int $planId,?string $startsAt): Subscription {
  $customer=Customer::whereKey($customerId)->where('merchant_id',$merchantId)->first(); $plan=$this->plans->get($merchantId,$planId);
  if(!$customer||!$plan) throw ValidationException::withMessages(['subscription'=>'Customer and plan must belong to the merchant.']);
  $start=CarbonImmutable::parse($startsAt ?: now()); $end=$plan->billing_cycle==='yearly'?$start->addYear()->subSecond():$start->addMonth()->subSecond();
  return DB::transaction(function() use($merchantId,$customerId,$plan,$start,$end){ $s=Subscription::create(['merchant_id'=>$merchantId,'customer_id'=>$customerId,'plan_id'=>$plan->id,'status'=>'active','current_period_start'=>$start,'current_period_end'=>$end]); $s->periods()->create(['plan_id'=>$plan->id,'starts_at'=>$start,'ends_at'=>null,'base_price'=>$plan->base_price,'included_units'=>$plan->included_units,'overage_rate_per_unit'=>$plan->overage_rate_per_unit]); return $s; });
 }
}
