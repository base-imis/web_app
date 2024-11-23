<?php

namespace App\Http\Requests\swm\registrations;

use Illuminate\Foundation\Http\FormRequest;

class ServiceAreaRequest extends FormRequest
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
        ];
    }
}
