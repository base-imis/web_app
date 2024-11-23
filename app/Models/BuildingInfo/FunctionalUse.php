<?php

namespace App\Models\BuildingInfo;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FunctionalUse extends Model
{
    protected $table = 'building_info.functional_uses';
    protected $primaryKey = 'id';

    public $timestamps = false; // Disable automatic timestamps
}
