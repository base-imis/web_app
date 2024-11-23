<?php

namespace App\Models\LayerInfo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandUse extends Model
{
    use HasFactory;
    protected $table = 'layer_info.landuses';
    protected $primaryKey = 'id';
}
