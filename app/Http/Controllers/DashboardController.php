<?php
namespace App\Http\Controllers;
use App\Models\Customer; use App\Models\Merchant; use App\Models\Subscription; use Carbon\CarbonImmutable; use Illuminate\Http\JsonResponse; use Illuminate\Support\Facades\DB;
class DashboardController extends Controller {
 public function show(Merchant $merchant): JsonResponse {
  $now=CarbonImmutable::now(); $monthStart=$now->startOfMonth(); $prevStart=$monthStart->subMonth(); $prevEnd=$monthStart->subSecond();
  $top=DB::table('customers as c')->leftJoin('daily_usages as du',function($j)use($monthStart,$now){$j->on('du.customer_id','=','c.id')->whereBetween('du.usage_date',[$monthStart->toDateString(),$now->toDateString()]);})->where('c.merchant_id',$merchant->id)->select('c.id','c.name',DB::raw('COALESCE(SUM(du.units),0) AS usage_units'))->groupBy('c.id','c.name')->orderByDesc('usage_units')->limit(5)->get();
  $current=DB::table('daily_usages')->where('merchant_id',$merchant->id)->whereBetween('usage_date',[$monthStart->toDateString(),$now->toDateString()])->select('customer_id',DB::raw('SUM(units) units'))->groupBy('customer_id')->pluck('units','customer_id');
  $previous=DB::table('daily_usages')->where('merchant_id',$merchant->id)->whereBetween('usage_date',[$prevStart->toDateString(),$prevEnd->toDateString()])->select('customer_id',DB::raw('SUM(units) units'))->groupBy('customer_id')->pluck('units','customer_id');
  $risk=Customer::where('merchant_id',$merchant->id)->whereIn('id',$current->keys()->merge($previous->keys())->unique())->get(['id','name'])->filter(fn($c)=>(float)($previous[$c->id]??0)>0&&(float)($current[$c->id]??0)<((float)($previous[$c->id]??0)*.5))->values();
  $projected=0.0;
  foreach(Subscription::with('periods')->where('merchant_id',$merchant->id)->where('status','active')->get() as $s){ $start=CarbonImmutable::parse($s->current_period_start); $end=CarbonImmutable::parse($s->current_period_end); if($start->gt($now)) continue; $elapsedEnd=$now->lt($end)?$now:$end; $days=max(1,$start->startOfDay()->diffInDays($elapsedEnd->startOfDay())+1); $cycleDays=max(1,$start->startOfDay()->diffInDays($end->startOfDay())+1); $used=(float)DB::table('daily_usages')->where('customer_id',$s->customer_id)->whereBetween('usage_date',[$start->toDateString(),$elapsedEnd->toDateString()])->sum('units'); $rate=(float)($s->periods->sortByDesc('starts_at')->first()?->overage_rate_per_unit??0); $included=$s->periods->sum('included_units'); $projectedUnits=$used/$days*$cycleDays; $projected+=max(0,$projectedUnits-$included)*$rate; }
  return response()->json(['merchant_id'=>$merchant->id,'month'=>$monthStart->format('Y-m'),'top_5_customers_by_usage'=>$top,'projected_overage_revenue'=>round($projected,2),'usage_drop_over_50_percent'=>$risk]);
 }
}
