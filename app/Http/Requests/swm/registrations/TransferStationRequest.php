<?php

namespace App\Http\Requests\swm\registrations;

use Illuminate\Foundation\Http\FormRequest;

class TransferStationRequest extends FormRequest
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
        return [
            'name' => 'required',
            'ward' => 'required',
            'separation_facility' => 'required',
            'area' => 'required|numeric',
            'capacity' => 'required|numeric',
        ];
    }

    /**
     * Get the error messages to display if validation fails.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'The Name field is required.',
            'ward.required' => 'The Ward field is required.',
            'separation_facility.required' => 'The Separation facility field is required.',
            'area.required' => 'The Area field is required.',
            'area.numeric' => 'The Area field must be numeric.',
            'capacity.required' => 'The Capacity field is required.',
            'capacity.numeric' => 'The Capacity field must be numeric.',
        ];
    }
}
