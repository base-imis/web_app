<?php

namespace App\Http\Requests\BuildingInfo;

use Illuminate\Foundation\Http\FormRequest;

class BuildingSurveyRequest extends FormRequest
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
            "bin" => 'unique:buildingInfo.building_surveys|required|string',
            "tax_code" => "required|string",
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
            "bin.unique" => 'The House Number is already registered.',
            "bin.required" => 'The House Number is required.',
            "bin.string" => 'The House Number should be string.',
            "tax_code" => 'The tax code should be string.',
            "tax_code.required" => "The tax code is required.",
            "collected_date.required" => "Collected date is required.",
            "collected_date.date_format" => "Collected date should be in YYYY-MM-DD date format."
        ];
    }
}
