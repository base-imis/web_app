<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Http\Requests\Fsm;

use Illuminate\Foundation\Http\FormRequest;

class TreatmentplantPerformanceTestRequest extends FormRequest
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
    }
    
    public function store()
    {
        return [
            'tss_standard' => 'nullable|integer',
            'ecoli_standard' => 'nullable|integer',
            'ph_min' => 'nullable|integer|between:0,14',
            'ph_max' => 'nullable|integer|between:0,14',
            'bod_standard' => 'nullable|integer',
        ];
    }
    public function update()
    {
        return [
            'tss_standard' => 'nullable|integer',
            'ecoli_standard' => 'nullable|integer',
            'ph_min' => 'nullable|integer|between:0,14',
            'ph_max' => 'nullable|integer|between:0,14',
            'bod_standard' => 'nullable|integer',
          
        ];
    }

    /**
     * Get the messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'tss_standard.integer' => 'The TSS Standard is integer.',
            'ecoli_standard.integer' => 'The ECOLI Standard is integer.',
            'ph_min.integer' => 'The pH Minimum is integer.',
            'ph_min.between' => 'The pH Minimum between or equal to 0 and 14.',
            'ph_max.integer' => 'The pH Maximum is integer.',
            'ph_max.between' => 'The pH Maximum between or equal to 0 and 14.',
            'bod_standard.integer' => 'The BOD Standard is integer.',

        ];
    }
}
