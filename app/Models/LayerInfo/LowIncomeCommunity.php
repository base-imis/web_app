<?php

namespace App\Models\LayerInfo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class LowIncomeCommunity extends Model
{
    use SoftDeletes;
    use RevisionableTrait;
    protected $table = 'layer_info.low_income_communities';
    protected $primaryKey = 'id';
}
