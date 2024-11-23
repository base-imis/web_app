<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Http\Requests\Fsm;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class TreatmentPlantEffectivenessRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules= ($this->isMethod('POST')? $this->store() : $this->update());
        return $rules;   
        return[
            'treatment_plant_id' => 'required',
            'year' => [
                'required',
                'numeric',
                Rule::unique('fsm.treatmentplant_effects')->where(function ($query) {
                    return $query->where('treatment_plant_id', request()->input('treatment_plant_id'));
                }),
            ],
            'water_contamination_compliance' => 'required|numeric|max:100',
            'tp_effectiveness' => 'required|numeric|max:100',
            'treated_fecal_sludge' => 'required|numeric|max:100',
            'recovered_operational_cost' => 'required|numeric|max:100',
        ];
    }
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function store()
    { 
        return ['treatment_plant_id' => 'required',
            'year' => [
                'required',
                'numeric',
                Rule::unique('fsm.treatmentplant_effects')->where(function ($query) {
                    return $query->where('treatment_plant_id', request()->input('treatment_plant_id'));
                }),
            ],
            'water_contamination_compliance' => 'required|numeric|max:100',
            'tp_effectiveness' => 'required|numeric|max:100',
            'treated_fecal_sludge' => 'required|numeric|max:100',
            'recovered_operational_cost' => 'required|numeric|max:100',
        ];
    }

    public function update()
    {

     
        $id = request()->route('treatment_plant_effectiveness');
        return [
            'treatment_plant_id' => 'required',
            'year' => [
                'required',
                'numeric',
                Rule::unique('fsm.treatmentplant_effects')->where(function ($query) {
                    return $query->where('treatment_plant_id', request()->input('treatment_plant_id'));
                })->ignore(request()->route('treatment_plant_effectiveness'), 'id'),
            ],
            'water_contamination_compliance' => 'required|numeric|max:100',
            'tp_effectiveness' => 'required|numeric|max:100',
            'treated_fecal_sludge' => 'required|numeric|max:100',
            'recovered_operational_cost' => 'required|numeric|max:100',
    ];
    }

}
