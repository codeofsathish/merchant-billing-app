<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class InvoiceLine extends Model {
    protected $fillable = ['invoice_id','subscription_period_id','description','base_amount','included_units','used_units','overage_units','overage_amount','total'];
    protected $casts = ['base_amount'=>'decimal:2','included_units'=>'decimal:4','used_units'=>'decimal:4','overage_units'=>'decimal:4','overage_amount'=>'decimal:2','total'=>'decimal:2'];
}
