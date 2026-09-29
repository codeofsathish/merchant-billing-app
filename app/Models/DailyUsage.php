<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DailyUsage extends Model {
    protected $fillable = ['merchant_id','customer_id','usage_date','units'];
    protected $casts = ['usage_date'=>'date','units'=>'integer'];
}
