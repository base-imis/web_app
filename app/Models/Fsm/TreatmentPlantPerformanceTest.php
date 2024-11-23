<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Models\Fsm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentPlantPerformanceTest extends Model
{
    use HasFactory;
    protected $table = 'public.treatment_plant_performance_efficiency_test_settings';
    protected $primaryKey = 'id';
}
