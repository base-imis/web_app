<?php

namespace App\Models\BuildingInfo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WmsLink extends Model
{
    use HasFactory;
    protected $table = "building_info.wms_links";
    public $timestamps = false; // Disable automatic timestamps
}
