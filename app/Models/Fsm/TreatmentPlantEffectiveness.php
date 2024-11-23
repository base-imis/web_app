<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Models\Fsm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentPlantEffectiveness extends Model
{
    use HasFactory;
    protected $table = 'fsm.treatmentplant_effects';
    protected $primaryKey = 'id';


    public function treatmentplants()
    {
        return $this->belongsTo('App\Models\Fsm\TreatmentPlant','treatment_plant_id','id');
    }
}