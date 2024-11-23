<?php

namespace App\Models\Fsm;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class Hotspots extends Model
{
    use HasFactory;
    use RevisionableTrait;
    use SoftDeletes;
    protected $revisionCreationsEnabled = true;
    protected $table = 'public_health.waterborne_hotspots';
    protected $primaryKey = 'id';
}
