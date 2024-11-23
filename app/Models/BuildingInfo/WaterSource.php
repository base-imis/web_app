<?php

namespace App\Models\BuildingInfo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaterSource extends Model
{
    
    protected $table = 'building_info.water_sources';
    protected $primaryKey = 'id';
    public $timestamps = false; // Disable automatic timestamps
}
