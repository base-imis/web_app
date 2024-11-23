<?php

namespace App\Http\Requests\Fsm;

use Illuminate\Foundation\Http\FormRequest;

class ContainmentSurveyRequest extends FormRequest
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
            "containment_code" => 'unique:containmentsurveys|required|string',
            "longitude" => ["required",'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d{1,8}))|180(\.0+)?)$/'],
            "latitude" => ["required","regex:/^[-]?(([0-8]?[0-9])\.(\d{1,8}))|(90(\.0+)?)$/"],
            "collected_date" => "required|date_format:Y-m-d"
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
            "containment_code.unique" => 'The containment code is already registered',
            "containment_code.required" => 'The containment code is required.',
            "containment_code.string" => 'The Containment code should be string',
            "longitude.required" => 'The Longitude is required.',
            "longitude.regex" => "The Longitude must be valid, with a limit of 8 digits after a decimal point.",
            "latitude.required" => 'The Latitude is required.',
            "latitude.regex" => "The Latitude must be valid, with a limit of 8 digits after a decimal point.",
            "collected_date.required" => "Collected date is required.",
            "collected_date.date_format" => "Collected date should be in YYYY-MM-DD date format."
        ];
    }
}
