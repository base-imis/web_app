<?php

namespace App\Http\Requests\Fsm;

use Illuminate\Foundation\Http\FormRequest;

class ContainmentRequest extends FormRequest
{


    /**
     * Determine if the user is authorized to make this request.
     *
     * @return boolCount
     */
    public function authorize()
    {
        return true;
    }

    public function messages()
    {
        return [
            'type_id.required' => "The Containment Type  required",
            'depth.min' => 'The Tank depth should be positive value.',
            'tank_length.min' => 'The Tank length should be positive value.',
            'tank_width.min' => 'The Tank width  should be positive value.',
            'toilet_count.min' => 'Total Number of Toilets  should be positive value.',
            'pit_depth.min' => 'The Pit Depth should be positive value.',
            'pit_diameter.min' => 'The Pit Diameter Count should be positive value.',
            'size.required' => 'The Containment Volume (m³) required.',
            'size.min' => 'The Containment Volume (m³) should be positive value.',
            'sewer_code.required_if'=> 'Sewer Code required',
            'drain_code.required_if'=> 'Drain Code required'

        ];
    }
    public function rules()
    {
        $rules = ($this->isMethod('POST') ? $this->store() : $this->update());
        return $rules;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function store()
    {
        return [
            'type_id' => 'required',
            'size' => 'required|numeric|min:0',
            'pit_diameter' => 'integer|nullable|min:0',
            'pit_depth' => 'numeric|nullable|min:0',
            'depth' => 'numeric|nullable|min:0',
            'tank_length' => 'numeric|nullable|min:0',
            'tank_width' => 'numeric|nullable|min:0',
            'sewer_code' => [
                'required_if:type_id,1,13'
            ],
            'drain_code' => [
                'required_if:type_id,2,14'
            ],

        ];
    }

    public function update()
    {
        return [
            'type_id' => 'required',
            'size' => 'required|numeric|min:0',
            'pit_diameter' => 'integer|nullable|min:0',
            'pit_depth' => 'numeric|nullable|min:0',
            'depth' => 'numeric|nullable|min:0',
            'tank_length' => 'numeric|nullable|min:0',
            'tank_width' => 'numeric|nullable|min :0',
            'sewer_code' => [
                'required_if:type_id,1,13'
            ],
            'drain_code' => [
                'required_if:type_id,2,14'
            ],

        ];
    }
}
