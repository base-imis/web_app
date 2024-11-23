<?php

namespace App\Models\SwmPaymentInfo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SwmPaymentStatus extends Model
{
    use HasFactory;

    protected $table = 'swm_info.swmservice_payment_status';
    protected $primaryKey = 'swm_customer_id';

    public static function selectAll(){
        return SwmPaymentStatus::select('swm_customer_id', 'customer_name', 'customer_contact', 'last_payment_date');
    }
}