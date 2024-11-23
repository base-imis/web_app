<?php

namespace App\Models\WaterSupplyInfo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaterSupply extends Model
{
    use HasFactory;

    protected $table = 'watersupply_info.watersupply_payments';
     protected $fillable = [
        'water_customer_id', 'customer_name', 'customer_contact', 'last_payment_date'
        
    ];
    public static function selectAll(){
        return WaterSupply::select('water_customer_id', 'customer_name', 'customer_contact', 'last_payment_date');
    }
}
