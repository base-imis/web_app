<?php

namespace App\Http\Requests\swm\registrations;

use Illuminate\Foundation\Http\FormRequest;

class CollectionPointRequest extends FormRequest
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
            'route_id' => 'required',
            'type' => 'required',
            'capacity' => 'required|numeric',
            'ward' => 'required',
            'service_type' => 'required',
            'household_served' => 'required|numeric',
            'status' => 'required',
            'collection_time' => 'required|date_format:H:i',
            'geom' => '',
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
            'route_id.required' => 'The Route field is required.',
            'type.required' => 'The Type field is required',
            'capacity.required' => 'The Capacity field is required',
            'capacity.numeric' => 'The Capacity field must be numeric',
            'ward.required' => 'The Ward field is required',
            'service_type.required' => 'The Service Type field is required',
            'household_served.required' => 'The household_served field is required',
            'household_served.numeric' => 'The household_served field must be numeric',
            'status.required' => 'The status field is required',
            'collection_time.required' => 'The collection_time field is required',
            'collection_time.date_format' => 'The collection_time field must be in H:i format',
            'geom' => '',
        ];
    }
}
