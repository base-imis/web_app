<?php

namespace App\Http\Requests\swm\services;

use Illuminate\Foundation\Http\FormRequest;

class TransferLogOutRequest extends FormRequest
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
            'transfer_station_id' => 'required',
            'landfill_site_id' => 'required',
            'type_of_waste' => 'required',
            'volume' => 'required|numeric',
            'date_time' => 'required|date',
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
            'transfer_station_id.required' => 'The Transfer station field is required.',
            'landfill_site_id.required' => 'The Landfill site field is required.',
            'type_of_waste.required' => 'The Type of waste field is required.',
            'volume.required' => 'The Volume field is required.',
            'volume.numeric' => 'The Volume field must be numeric.',
            'date_time.required' => 'The Date & Time field is required.',
            'date_time.date' => 'The Date & Time field must in some format.',
        ];
    }
}
