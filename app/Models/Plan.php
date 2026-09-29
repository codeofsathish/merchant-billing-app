<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Plan extends Model {
    protected $fillable = ['merchant_id','name','base_price','billing_cycle','included_units','overage_rate_per_unit'];
    protected $casts = ['base_price'=>'decimal:2','included_units'=>'integer','overage_rate_per_unit'=>'decimal:6'];
    public function merchant(): BelongsTo { return $this->belongsTo(Merchant::class); }
}
