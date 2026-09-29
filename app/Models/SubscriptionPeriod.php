<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SubscriptionPeriod extends Model {
    protected $fillable = ['subscription_id','plan_id','starts_at','ends_at','base_price','included_units','overage_rate_per_unit'];
    protected $casts = ['starts_at'=>'datetime','ends_at'=>'datetime','base_price'=>'decimal:2','included_units'=>'integer','overage_rate_per_unit'=>'decimal:6'];
    public function subscription(): BelongsTo { return $this->belongsTo(Subscription::class); }
    public function plan(): BelongsTo { return $this->belongsTo(Plan::class); }
}
