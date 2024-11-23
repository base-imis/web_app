<?php

namespace App\Models\LayerInfo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    // use HasFactory;

    protected $table = 'layer_info.wards';
    protected $primaryKey = 'ward';

    public static function getInAscOrder(){
        return Ward::orderBy('ward', 'asc')->pluck('ward', 'ward')->all();
    }
}
