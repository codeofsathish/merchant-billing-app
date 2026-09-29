<?php
namespace App\Actions;
use App\Models\Subscription; use App\Services\PlanCacheService; use Carbon\CarbonImmutable; use Illuminate\Support\Facades\DB; use Illuminate\Validation\ValidationException;
class ChangeSubscriptionPlanAction {
 public function __construct(private readonly PlanCacheService $plans) {}
 public function execute(Subscription $subscription,int $planId,?string $effectiveAt): Subscription {
  $plan=$this->plans->get($subscription->merchant_id,$planId); if(!$plan) throw ValidationException::withMessages(['plan_id'=>'Plan does not belong to the merchant.']);
  $effective=CarbonImmutable::parse($effectiveAt ?: now()); $start=CarbonImmutable::parse($subscription->current_period_start); $end=CarbonImmutable::parse($subscription->current_period_end);
  if($effective->lt($start)||$effective->gt($end)) throw ValidationException::withMessages(['effective_at'=>'Plan change must be inside the current billing period.']);
  DB::transaction(function() use($subscription,$plan,$effective){ $current=$subscription->periods()->whereNull('ends_at')->lockForUpdate()->latest('starts_at')->firstOrFail(); if($effective->lte(CarbonImmutable::parse($current->starts_at))) throw ValidationException::withMessages(['effective_at'=>'Effective time must be after the current segment start.']); $current->update(['ends_at'=>$effective->subSecond()]); $subscription->update(['plan_id'=>$plan->id]); $subscription->periods()->create(['plan_id'=>$plan->id,'starts_at'=>$effective,'ends_at'=>null,'base_price'=>$plan->base_price,'included_units'=>$plan->included_units,'overage_rate_per_unit'=>$plan->overage_rate_per_unit]); });
  return $subscription->fresh()->load('periods.plan');
 }
}
