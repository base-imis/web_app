<?php

namespace App\Models\BuildingInfo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Venturecraft\Revisionable\RevisionableTrait;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Owner extends Model
{
    use RevisionableTrait;
    protected $table = 'building_info.owners';
    protected $primaryKey = 'id';
    protected $fillable = ["bin","owner_name", "owner_gender", "owner_contact" ];
    protected $revisionCreationsEnabled = true;

    public function buildings()
    {
        return $this->hasMany('App\models\BuildingInfo\Building', 'bin', 'bin');

    }
    /**
     * Get the owner associated with the building.
     *
     *
     * @return HasMany
     */

}
