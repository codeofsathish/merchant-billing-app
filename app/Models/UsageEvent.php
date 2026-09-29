<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class UsageEvent extends Model {
    protected $fillable = ['merchant_id','customer_id','event_key','occurred_at','usage_date','units','type','metadata'];
    protected $casts = ['occurred_at'=>'datetime','metadata'=>'array','units'=>'integer'];
}
