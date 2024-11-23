<?php

namespace App\Models\UtilityInfo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;
class SewerConnection extends Model
{
    use HasFactory;

    use RevisionableTrait;

    protected $revisionCreationsEnabled = true;
    protected $table = 'utility_info.sewer_connections';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = ['bin','sewer_code'];
}
