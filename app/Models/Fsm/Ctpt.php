<?php

namespace App\Models\Fsm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use App\Models\Fsm\CtptUsers;
class Ctpt extends Model
{
    use SoftDeletes;
    use RevisionableTrait;

    protected $revisionCreationsEnabled = true;
    protected $table = 'fsm.toilets';
    protected $primaryKey = 'id';



    public function building()
    {
        return $this->belongsTo('App\Models\BuildingInfo\Building','bin','bin');

    }


    public function ctptuser()
    {
        return $this->hasMany(CtptUsers::class);
    }
}
