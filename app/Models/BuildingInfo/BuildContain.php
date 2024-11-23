<?php

namespace App\Models\BuildingInfo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BuildContain extends Model
{
    use SoftDeletes;


    protected $table = 'building_info.build_contains';
    protected $primaryKey = 'id';
}
