<?php

namespace App\Models\BuildingInfo;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use App\Models\BuildingInfo\SanitationSystem;

class BuildingSurvey extends Model
{
    use SoftDeletes;

    use RevisionableTrait;

    protected $revisionCreationsEnabled = true;
    protected $table = 'building_info.building_surveys';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = ['bin','tax_code','collected_date'];
}
