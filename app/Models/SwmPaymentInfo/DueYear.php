<?php

namespace App\Models\SwmPaymentInfo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DueYear extends Model
{
    // use HasFactory;

    protected $table = 'swm_info.due_years';
    protected $primaryKey = 'id';

    public static function getInAscOrder(){
        return DueYear::where('value', '!=', '99')->orderBy('id', 'asc')->pluck('name', 'name')->all();
    }
}