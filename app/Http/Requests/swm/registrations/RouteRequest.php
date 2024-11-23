<?php

namespace App\Http\Requests\swm\registrations;

use Illuminate\Foundation\Http\FormRequest;

class RouteRequest extends FormRequest
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
            'service_provider_id' => 'required',
            'name' => 'required',
            'type' => 'required',
            'time' => 'date_format:H:i A',
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
            'service_provider_id.required' => 'The Service provider field is required.',
            'name.required' => 'The name field is required.',
            'type.required' => 'The type field is required.',
            'time.date_format' => 'The time field must be in H:i A format.',
        ];
    }
}
