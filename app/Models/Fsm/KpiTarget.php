<?php

namespace App\Models\Fsm;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class KpiTarget extends Model
{
    use HasFactory;
    use RevisionableTrait;
    use SoftDeletes;
    protected $revisionCreationsEnabled = true;
    protected $table = 'fsm.kpi_targets';
    protected $primaryKey = 'id';
}
