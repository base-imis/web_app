<?php
// Last Modified Date: 07-05-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Models\PublicHealth;

use Venturecraft\Revisionable\RevisionableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaterSamples extends Model
{
    use HasFactory;
    use RevisionableTrait;
    use SoftDeletes;
    protected $revisionCreationsEnabled = true;
    protected $table = 'public_health.water_samples';
    protected $primaryKey = 'id';
}
