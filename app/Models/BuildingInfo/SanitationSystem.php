<?php

namespace App\Models\BuildingInfo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Venturecraft\Revisionable\RevisionableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;


class SanitationSystem extends Model
{
    protected $table = 'building_info.sanitation_systems';
    protected $primaryKey = 'id';

    public $timestamps = false; // Disable automatic timestamps

    public function buildings(){
        return $this->belongsTo(Building::class,'id','sanitation_system_id');
    }
}
