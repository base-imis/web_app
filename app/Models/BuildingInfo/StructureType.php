<?php

namespace App\Models\BuildingInfo;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StructureType extends Model
{
    protected $table = 'building_info.structure_types';
    protected $primaryKey = 'id';
    public $timestamps = false; // Disable automatic timestamps
}
