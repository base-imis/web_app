<?php
// Last Modified Date: 10-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Http\Requests\Fsm;

use Illuminate\Foundation\Http\FormRequest;

class EmptyingRequest extends FormRequest
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
      $rules= ($this->isMethod('POST')? $this->store() : $this->update());
     return $rules;
    }



    public function store()
    {
        return [
            'service_receiver_name' => 'required',
            'service_receiver_gender' => 'required',
            'service_receiver_contact' => 'required|integer|min:1',
            'emptying_reason' => 'required',
            'volume_of_sludge' => 'required|numeric|min:0',
            'distance_closest_well' => 'nullable|numeric|min:0',
            'desludging_vehicle_id' => 'required|integer',
            'treatment_plant_id' => 'required|integer',
            'driver' => 'required|integer',
            'emptier1' => 'required|integer',
            'emptier2' => 'nullable|integer',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'no_of_trips' => 'required|integer|min:1',
            'receipt_number' => 'required',
            'total_cost' => 'required|numeric|min:0',
            'house_image' => 'required|mimes:jpeg,png,jpg|max:2048',
            'receipt_image' => 'required|mimes:jpeg,png,jpg|max:2048',
        ];
    }
    public function update()
    {
        return [
            'service_receiver_name' => 'required',
            'service_receiver_gender' => 'required',
            'service_receiver_contact' => 'required|integer|min:1',
            'emptying_reason' => 'required',
            'volume_of_sludge' => 'required|numeric|min:0',
            'distance_closest_well' => 'nullable|numeric|min:0',
            'desludging_vehicle_id' => 'required|integer',
            'treatment_plant_id' => 'required|integer',
            'driver' => 'required|integer',
            'emptier1' => 'required|integer',
            'emptier2' => 'nullable|integer',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'no_of_trips' => 'required|integer|min:1',
            'receipt_number' => 'required',
            'total_cost' => 'required|numeric|min:0',
            'house_image' => 'mimes:jpeg,png,jpg|max:2048',
            'receipt_image' => 'mimes:jpeg,png,jpg|max:2048',

        ];
    }

    /**
     * Get the messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'service_receiver_name.required' => 'The Service Receiver Name is required.',
            'service_receiver_gender.required' => 'The Service Receiver Gender is required.',
            'service_receiver_contact.required' => 'The Service Receiver Contact Number is required.',
            'service_receiver_contact.integer' => 'The Service Receiver Contact Number must be an integer.',
            'service_receiver_contact.min' => 'The Service Receiver Contact Number must be positive number.',
            'emptying_reason.required' => 'The Emptying Reason is required.',
            'volume_of_sludge.required' => 'The Sludge Volume is required.',
            'volume_of_sludge.numeric' => 'The Sludge Volume must be numeric.',
            'volume_of_sludge.min' => 'The Sludge Volume Contact Number must be at least 0.',
            'distance_closest_well.numeric' => 'The Distance to Closest Well must be numeric.',
            'distance_closest_well.min' => 'The Distance to Closest Well must be at least 0.',
            'desludging_vehicle_id.required' => 'The Desludging Vehicle Number Plate is required.',
            'desludging_vehicle_id.integer' => 'The Desludging Vehicle Number Plate must be an integer.',
            'treatment_plant_id.required' => 'The Disposal Place is required.',
            'treatment_plant_id.integer' => 'The Disposal Place must be an integer.',
            'driver.required' => 'The Driver Name is required.',
            'emptier1.required' => 'The Emptier 1 Name is required.',
            'emptier1.integer' => 'The Emptier 1 Name is invalid.',
            'emptier2.integer' => 'The Emptier 2 Name is invalid.',
            'start_time.required' => 'The Start Time is required.',
            'end_time.required' => 'The End Time is required.',
            'end_time.after' => 'The End Time must be after start time.',
            'no_of_trips.required' => 'The No. of Trips is required.',
            'no_of_trips.integer' => 'The No. of Trips must be an integer.',
            'receipt_number.required' => 'The Receipt Number is required.',
            'total_cost.required' => 'The Total Cost is required.',
            'total_cost.numeric' => 'The Total Cost must be numeric.',
            'house_image.required' => 'The House Image is required.',
            'house_image.file' => 'The House Image must be an image file.',
            'house_image.mimetypes' => 'The House Image type is not supported.',
            'receipt_image.required' => 'The Receipt Image is required.',
            'receipt_image.file' => 'The Receipt Image must be an image file.',
            'receipt_image.mimetypes' => 'The Receipt Image type is not supported. ',
        ];
    }
}
