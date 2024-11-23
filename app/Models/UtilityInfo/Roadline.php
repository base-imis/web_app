<?php

namespace App\Models\UtilityInfo;

use App\Models\BuildingInfo\Building;
use App\Models\Fsm\Application;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Venturecraft\Revisionable\RevisionableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class Roadline extends Model
{
    use HasFactory;
    use RevisionableTrait;
    use SoftDeletes;
    protected $revisionCreationsEnabled = true;
    protected $table = 'utility_info.roads';
    protected $primaryKey = 'code';
    public $incrementing = false;

    /**
     * Get the buildings associated with the road.
     *
     *
     * @return HasMany
     */
    public function buildings(){
        return $this->hasMany(Building::class,'road_code','code');
    }



}
