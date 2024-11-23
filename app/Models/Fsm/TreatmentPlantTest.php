<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Models\Fsm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class TreatmentPlantTest extends Model
{
    use HasFactory;

    use SoftDeletes;
    use RevisionableTrait;
    protected $revisionCreationsEnabled = true;
    protected $table = 'fsm.treatmentplant_tests';
    protected $primaryKey = 'id';
    protected $with =['treatmentplants'];
    public function treatmentplants()
    {
        return $this->belongsTo('App\Models\Fsm\TreatmentPlant','treatment_plant_id','id');
    }


}
