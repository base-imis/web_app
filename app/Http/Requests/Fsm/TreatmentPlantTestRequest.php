<?php
// Last Modified Date: 10-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)    
namespace App\Http\Requests\Fsm;

use Illuminate\Foundation\Http\FormRequest;

class TreatmentPlantTestRequest extends FormRequest
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


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */

    public function messages()
    {
        return [
            'treatment_plant_id.required' => 'Treatment plant is required.',
            'date.required' => ' Date is required.',
            'temperature.integer' => 'Temperature must be number',
            'ph.integer' => 'pH must be number',
            'cod.integer' => 'COD must be number',
            'bod.integer' => 'BOD must be number',
            'tss.integer' => 'TSS must be number',
            'ecoli.integer' => 'Ecoli must be number',
            'cod.required' => 'COD is required.',
            'ph.required' => 'pH is required.',
            'bod.required' => 'BOD is required.',
            'tss.required' => 'TSS is required.',
            'ecoli.required' => 'Ecoli is required.',
            'temperature.required' => 'Temperature is required.',
            'sample_location.required'  => 'Sample Location is required.',
        ];
    }

    public function rules()
    {

        $rules = ($this->isMethod('POST') ? $this->store() : $this->update());
        return $rules;
    }



    public function store()
    {
        $rules = [
            'treatment_plant_id' => 'required',
            'date' => 'required|date|before_or_equal:today',
            'temperature' => 'required|numeric|min:0',
            'ph' => 'required|numeric|between:0,14',
            'cod' => 'required|numeric|min:0',
            'bod' => 'required|numeric|min:0',
            'tss' => 'required|numeric|min:0',
            'ecoli' => 'required|integer|min:0',
            'sample_location' => 'required',
        ];

        return $rules;
    }




    public function update()
    {
        return [
            'treatment_plant_id' => 'required',
            'date' => 'required',
            'temperature' => 'required|numeric|min:0',
            'ph' => 'required|numeric|between:0,14',
            'cod' => 'required|numeric|min:0',
            'bod' => 'required|numeric|min:0',
            'tss' => 'required|numeric|min:0',
            'ecoli' => 'required|integer|min:0',
            'sample_location' => 'required',
        ];
    }
}
