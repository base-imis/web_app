<?php
// Last Modified Date: 19-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Http\Requests\Fsm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class CtptRequest extends FormRequest
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
    public function rules()
    {
        $rules = ($this->isMethod('POST') ? $this->store() : $this->update());
        return $rules;
    }

    public function messages()
    {
        return[
            'type.required' => 'The Toilet Type is required.',
            'name.unique' => 'The Toilet Name already in use.',
            'name.string' => 'The Toilet Name must be a string.',
            'ward.required' => 'The Ward is required.',
            'location_name.string' => 'The Location must be a string.',
            'bin.required' => 'The House Number / BIN is required.',
            'access_frm_nearest_road.numeric' => 'The Distance from Nearest Road must be numeric.',
            'access_frm_nearest_road.min' => 'The Distance from Nearest Road should be positive value.',
            'status.required' => 'The Status is required.',
            'caretaker_name.required' => 'The Caretaker Name is required.',
            'caretaker_name.string' => 'The Caretaker Name must be a string.',
            'caretaker_gender.required' => 'The Caretaker Gender is required.',
            'caretaker_contact_number.required' => 'The Caretaker Contact is required.',
            'caretaker_contact_number.integer' => 'The Caretaker Contact must be an integer.',
            'caretaker_contact_number.digit' => 'The Caretaker Contact must be at least 10 digits.',
            'caretaker_contact_number.min' => 'The Caretaker Contact must be a positive value.',
            'owner.required' => 'The Owning Institution is required.',
            'owner.string' => 'The Owning Institution must be a string.',
            'owning_institution_name.string' => 'The Name of Owning Institution must be a string.',
            'operator_or_maintainer.required' => 'The Operate and Maintained by is required.',
            'operator_or_maintainer_name.string' => 'The Name of Operate and Maintained by must be a string.',
            'total_no_of_toilets.integer' => 'The Total Number of Seats must be an integer.',
            'total_no_of_toilets.min' => 'The Total Number of Seats should be positive value.',
            'total_no_of_urinals.integer' => 'The Total Number of Urinals must be an integer.',
            'total_no_of_urinals.min' => 'The Total Number of Urinals should be positive value.',
            'male_seats.integer' => 'The No. of Seats for Male Users must be integer.',
            'male_seats.min' => 'The No. of Seats for Male Users should be positive value.',
            'female_seats.integer' => 'The No. of Seats for Female Users must be integer.',
            'female_seats.min' => 'The No. of Seats for Female Users should be positive value.',
            'pwd_seats.integer' => 'The No. of seats for People with Disability must be an integer.',
            'pwd_seats.min' => 'The No. of seats for People with Disability  should be positive value.',
            'amount_of_fee_collected.numeric' => 'The Uses Fee Rate must be numeric.',
            'amount_of_fee_collected.min' => 'The Uses Fee Rate should be positive value.',

        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function store()
    {

        return [
            'type' => 'required| string',
            'name' => 'nullable|string|unique:pgsql.fsm.toilets,name,NULL,id',
            'ward' => 'required',
            'location_name' => 'nullable| string',
            'bin' => 'required',
            'access_frm_nearest_road' => 'nullable|numeric|min:0',
            'status' => 'required',
            'caretaker_name' => 'required|string',
            'caretaker_gender' => 'required',
            'caretaker_contact_number' => 'required|integer|min:1',
            'owner' => 'required| string',
            'owning_institution_name' => 'nullable| string',
            'operator_or_maintainer' => 'required',
            'operator_or_maintainer_name' => 'nullable| string',
            'total_no_of_toilets' => 'nullable|integer|min:0',
            'total_no_of_urinals' => 'nullable|integer|min:0',
            'male_or_female_facility' => 'nullable|boolean',
            'male_seats' => 'nullable|integer|min:0',
            'female_seats' => 'nullable|integer|min:0',
            'handicap_facility' => 'nullable|boolean',
            'pwd_seats' => 'nullable|integer|min:0',
            'children_facility' => 'nullable|boolean',
            'separate_facility_with_universal_design'=>'nullable|boolean',
            'indicative_sign' =>'nullable|boolean',
            'sanitary_supplies_disposal_facility' =>'nullable|boolean',
            'fee_collected' => 'nullable|boolean',
            'amount_of_fee_collected' => 'nullable|numeric|min:0',
            'frequency_of_fee_collected' => 'nullable'

        ];
    }

    public function update()
    {
        $id = request()->route('ctpt');
        return [
            'type' => 'required| string',
            'name' => [
                'nullable', 
                'string',   
                Rule::unique('pgsql.fsm.toilets', 'name') 
                    ->where(function ($query) {
                        return $query->whereNull('deleted_at'); 
                    })
                    ->ignore($id), 
            ],
            'ward' => 'required',
            'location_name' => 'nullable| string',
            'bin' => 'required',
            'owner' => 'required| string',
            'operator_or_maintainer' => 'required',
            'caretaker_name' => 'required|string',
            'caretaker_gender' => 'required',
            'caretaker_contact_number' => 'required| numeric|digits:10',
            'operator_or_maintainer_name' => 'nullable| string',
            'owning_institution_name' => 'nullable| string',
            'total_no_of_toilets' => 'nullable|integer|min:0',
            'access_frm_nearest_road' => 'nullable|numeric|min:0',
            'male_or_female_facility' => 'nullable|boolean',
            'separate_facility_with_universal_design'=>'nullable|boolean',
            'total_no_of_urinals' => 'nullable|integer|min:0',
            'handicap_facility' => 'nullable|boolean',
            'children_facility' => 'nullable|boolean',
            'male_seats' => 'nullable|integer|min:0',
            'female_seats' => 'nullable|integer|min:0',
            'pwd_seats' => 'nullable|integer|min:0',
            'sanitary_supplies_disposal_facility' =>'nullable|boolean',
            'status' => 'required',
            'indicative_sign' =>'nullable|boolean',
            'fee_collected' => 'nullable|boolean',
            'amount_of_fee_collected' => 'nullable|numeric|min:0',
            'frequency_of_fee_collected' => 'nullable'
        ];
    }
}
