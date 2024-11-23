<?php

namespace App\Http\Requests\swm\registrations;

use Illuminate\Foundation\Http\FormRequest;

class LandfillSiteRequest extends FormRequest
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
            'area' => 'required|numeric',
            'capacity' => 'required|numeric',
            'life_span' => 'required|integer',
            'status' => 'required',
            'operated_by' => 'required',
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
            'area.required' => 'The Area field is required.',
            'area.numeric' => 'The Area field must be numeric.',
            'capacity.required' => 'The Capacity field is required.',
            'capacity.numeric' => 'The Capacity field must be numeric.',
            'life_span.required' => 'The Life Span field is required.',
            'life_span.date_format' => 'The Life Span field must be in yyyy format.',
            'status.required' => 'The Status field is required.',
            'operated_by.required' => 'The Operated by field is required.',
        ];
    }
}
