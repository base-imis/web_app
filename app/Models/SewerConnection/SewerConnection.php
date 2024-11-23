<?php

namespace App\Models\SewerConnection;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
class SewerConnection extends Model
{
    use HasFactory;
    use SoftDeletes;
    use RevisionableTrait;

    protected $revisionCreationsEnabled = true;
    protected $table = 'sewer_connection.sewer_connections';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = ['bin','sewer_code'];
}
