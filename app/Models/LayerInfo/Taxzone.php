<?php

namespace App\Models\LayerInfo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taxzone extends Model
{
    // use HasFactory;

    protected $table = 'layer_info.taxzones';
    protected $primaryKey = 'id';

    public static function getInAscOrder(){
        return Ward::orderBy('ward', 'asc')->pluck('ward', 'ward')->all();
    }
}
