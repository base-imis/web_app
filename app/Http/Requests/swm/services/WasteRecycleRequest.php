<?php

namespace App\Http\Requests\swm\services;

use Illuminate\Foundation\Http\FormRequest;

class WasteRecycleRequest extends FormRequest
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
            'volume' => 'required|numeric',
            'waste_type' => 'required',
            'date_time' => 'required|date',
            'rate' => 'required|numeric',
            'total_price' => 'required|numeric',
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
            'volume.required' => 'The Volume field is required.',
            'volume.numeric' => 'The Volume field must be numeric.',
            'waste_type.required' => 'The Waste type is required.',
            'date_time.required' => 'The Date & Time field is required.',
            'date_time.date' => 'The Date & Time field must be in some format.',
            'rate.required' => 'The Rate field is required.',
            'rate.numeric' => 'The Rate field must be numeric.',
            'total_price.required' => 'The Total price field is required.',
            'total_price.numeric' => 'The Total price field must be numeric.',
        ];
    }
}
