<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Invoice extends Model {
    protected $fillable = ['merchant_id','customer_id','subscription_id','period_start','period_end','subtotal','total','status'];
    protected $casts = ['period_start'=>'datetime','period_end'=>'datetime','subtotal'=>'decimal:2','total'=>'decimal:2'];
    public function lines(): HasMany { return $this->hasMany(InvoiceLine::class); }
}
