<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Http\Requests\Fsm;

use Illuminate\Foundation\Http\FormRequest;

class EmptyingApiRequest extends FormRequest
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
            'application_id' => 'required|exists:fsm.applications,id',
            'volume_of_sludge' => 'required|numeric|gt:0',
            'desludging_vehicle_id' => 'required',
            'treatment_plant_id' => 'required',
            'driver' => 'required|integer',
            'emptier1' => 'required|integer',
            'emptier2' => 'nullable|integer',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'no_of_trips' => 'required|numeric|gt:0',
            'receipt_number' => 'required',
            'total_cost' => 'required|numeric|gt:0',
            'house_image' => 'required|mimes:jpeg,png,jpg,gif,svg',
            'receipt_image' => 'required|mimes:jpeg,png,jpg,gif,svg',
            'emptying_reason' => 'required',
            'service_receiver_contact' => 'required|integer',
            'service_receiver_gender' => 'required',
            'service_receiver_name' => 'required',
            'distance_closest_well' => 'numeric|nullable|gt:0',

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
            'application_id.required' => 'The application id is required.',
            'application_id.exists' => 'The application for this ID doesn\'t exist.',
            'volume_of_sludge.required' => 'Sludge Volume is required.',
            'volume_of_sludge.numeric' => 'Sludge Volume must be numeric.',
            'desludging_vehicle_id.required' => 'The desludging vehicle number plate  is required.',
            'treatment_plant_id.required' => 'The disposal place is required.',
            'driver.required' => 'The driver name is required.',
            'emptier1.required' => 'The emptier1 name is required.',
            'start_time.required' => 'The start time is required.',
            'end_time.required' => 'The end time is required.',
            'end_time.after' => 'The end time must be after start time.',
            'no_of_trips.required' => 'The number of trips is required.',
            'no_of_trips.numeric' => 'The number of trips must be numeric.',
            'receipt_number.required' => 'The receipt number is required.',
            'total_cost.required' => 'The total cost is required.',
            'total_cost.numeric' => 'The total cost must be numeric.',
            'house_image.required' => 'The house image is required.',
            'house_image.file' => 'The house image must be an image file.',
            'house_image.mimetypes' => 'The house image type is not supported.',
            'receipt_image.required' => 'The receipt image is required.',
            'receipt_image.file' => 'The receipt image must be an image file.',
            'receipt_image.mimetypes' => 'The receipt image type is not supported. ',
            'emptying_reason.required' => 'The reason for emptying is required',
            'service_receiver_contact.required' => 'The service receiver contact Number is required',
            'service_receiver_contact.integer' => 'The service receiver contact Number must be number',
            'service_receiver_gender.required' => 'The  service receiver gender is required',
            'service_receiver_name.required' => 'The  service receiver name is required',

        ];
    }
}
