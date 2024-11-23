<?php

namespace App\Models\BuildingInfo;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UseCategory extends Model
{
    protected $table = 'building_info.use_categorys';
    protected $primaryKey = 'id';
    public $timestamps = false; // Disable automatic timestamps
}
