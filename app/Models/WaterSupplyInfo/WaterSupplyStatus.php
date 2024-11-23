<?php

namespace App\Models\WaterSupplyInfo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaterSupplyStatus extends Model
{
    use HasFactory;

    protected $table = 'watersupply_info.watersupply_payment_status';
    protected $primaryKey = 'water_customer_id';

    public static function selectAll(){
        return WaterSupplyStatus::select('water_customer_id', 'customer_name', 'customer_contact', 'last_payment_date');
    }
}
