<?php

namespace App\Models\UtilityInfo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class WaterSupplys extends Model
{
    use HasFactory;
    use RevisionableTrait;
    protected $revisionCreationsEnabled = true;
    protected $table = 'utility_info.water_supplys';
    protected $primaryKey = 'code';
    public $incrementing = false;
}
