<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Subscription extends Model {
    protected $fillable = ['merchant_id','customer_id','plan_id','status','current_period_start','current_period_end'];
    protected $casts = ['current_period_start'=>'datetime','current_period_end'=>'datetime'];
    public function merchant(): BelongsTo { return $this->belongsTo(Merchant::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function plan(): BelongsTo { return $this->belongsTo(Plan::class); }
    public function periods(): HasMany { return $this->hasMany(SubscriptionPeriod::class); }
}
