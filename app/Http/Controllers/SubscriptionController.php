<?php
namespace App\Http\Controllers;
use App\Actions\ChangeSubscriptionPlanAction; use App\Actions\CreateSubscriptionAction; use App\Http\Requests\ChangePlanRequest; use App\Http\Requests\CreateSubscriptionRequest; use App\Models\Subscription; use Illuminate\Http\JsonResponse;
class SubscriptionController extends Controller {
 public function store(CreateSubscriptionRequest $request,CreateSubscriptionAction $action): JsonResponse { $d=$request->validated(); $s=$action->execute((int)$d['merchant_id'],(int)$d['customer_id'],(int)$d['plan_id'],$d['starts_at']??null); return response()->json($s->load('periods.plan'),201); }
 public function changePlan(ChangePlanRequest $request,Subscription $subscription,ChangeSubscriptionPlanAction $action): JsonResponse { $d=$request->validated(); return response()->json($action->execute($subscription,(int)$d['plan_id'],$d['effective_at']??null)); }
}
