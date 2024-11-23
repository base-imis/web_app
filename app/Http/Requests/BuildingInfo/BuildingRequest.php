<?php

namespace App\Http\Requests\BuildingInfo;

use Illuminate\Foundation\Http\FormRequest;
use Validator, Input, Redirect;

class BuildingRequest extends FormRequest
{
    /**
     * Determine if the user  authorized to make this request.
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
    public function messages()
    {
        return [
            //owner part
            'owner_name.required' => 'Owner Name  required.',
            'owner_contact.integer' => 'Owner Contact should be numeric value',
            'owner_gender.required' => 'Owner Gender  required',
            'owner_contact.required' => 'Owner Contact  required',


            //Building Information
            'main_building.required' => "The Main Building status  required.",
            'building_associated_to.required_if' => "The Main Building House Number  required.",
            'ward.required' => 'Ward  required.',
            'floor_count.required' => 'Number of Floors required',
            'tax_code.required' => 'Tax Code  required',
            'road_code.required' => 'Road Name  required.',
            'structure_type_id.required' => 'Structure Type  required.',
            'functional_use_id.required' => 'Functional Use of Building  required.',
            'house_number.unique' => 'The House Number is Taken',

            // 'household_served.integer' => 'Total Number of Households(Family) should be integer value.',
            // 'population_served.integer' => 'Total Population of Building Served should be integer value.',
            'construction_year.required' => 'Building Construction Date  required.',
            //lic
            'low_income_hh.required' => 'Is Low Income House required.',


            'lic_id.required_if' => 'LIC name required.',
            //Water Source Information
            'water_source_id.required' => "Main Drinking Water Source required",
            'watersupply_pipe_code.required_if' => 'Water Supply Pipe Line Code  required',

            //Sanitation System Information
            'toilet_status.required' => 'Toilet Presence Status  required',
            'sewer_code.required_if' => 'Sewer Code  required.',
            'drain_code.required_if' => 'Drain Code  required.',
            // 'ctpt_name.required_if' => 'Community Toilet Name  required.',

            'size.required_if' => 'Containment Volume (m3)  required if containment. Enter dimensions to auto generate.',

            'geom.required_if' => 'Building Footprint(KML)  required.',
            'floor_count.integer' => 'Total No. of Floors should be integer value.',


            'depth.numeric' => 'Tank Depth number should be numeric value.',
            'tank_length.numeric' => 'Tank Length number should be numeric value.',
            'tank_width.numeric' => 'Tank Width number should be numeric value.',
            'pit_depth.numeric' => 'Pit Depth should be numeric value.',
            'pit_diameter.integer' => 'Pit Diameter number should be integer value.',
            'size.numeric' => 'Containment Volume should be numeric value.',
            'floor_count.min' => 'Total No. of Floors should be positive value.',
            'estimated_area.min' => 'Estimated Area must be in float Data type',
            'toilet_count.min' => 'Total Number of toilets should be positive value.',
            'toilet_count.required_if' => 'Total Number of toilets required.',
            'household_served.required_if' => 'Total Household served is required',
            'population_served.required_if' => 'Total Population served is required',
            'distance_from_well.min' => 'Distance of containment from well should be integer value.',
            'distance_from_well.min' => 'Distance of containment from well should be positive value.',
            'depth.min' => 'Tank depth should be positive value.',
            'tank_length.min' => 'Tank length should be positive value.',
            'tank_width.min' => 'Tank width Count should be positive value.',
            'pit_depth.min' => 'Pit Depth should be positive value.',
            'pit_diameter.min' => 'Pit Diameter Count should be positive value.',
            'size.min' => 'Containment Volume should be positive value.',
            'construction_year' => 'Cant Select Future Value',
            'sanitation_system_id.required_if' => 'Toilet Connection  required.',
            //containment message for validation
            'type_id.required_if' => 'Containment Type field  required when Toilet Connection  Septic or Pit/Holding.',
            'defecation_place.required_if' => ' Defecation Place  required',
            'build_contain.required_if' => 'BIN of Pre-Connected Building required',
            // 'construction_date.required_if'=>'Containment Construction date  required.',
            'pit_shape.required_if' => 'Pit shape  required.',

        ];
    }

    public function rules()
    {
        $rules = ($this->isMethod('POST') ? $this->store() : $this->update());
        return $rules;
    }

    public function store()
    {
        Validator::extend(
            'file_extension',
            function ($attribute, $value, $parameters, $validator) {
                if (!in_array($value->getClientOriginalExtension(), $parameters)) {
                    return false;
                } else {
                    return true;
                }
            },
            'Building Footprint(KML) file must be kml format'
        );

        return [
            // // Owner Infomation
            'owner_name' => 'required',
            'owner_contact' => 'required',
            'owner_gender' => 'required',
            //Building Information
            'main_building' => 'required',
            'building_associated_to' => 'required_if:main_building,No',
            'ward' => 'required',
            'road_code' => 'required',
            'house_number' => 'nullable|unique:pgsql.building_info.buildings,house_number',

            'tax_code' => 'required',
            'structure_type_id' => 'required',
            'floor_count' => 'required|integer|min:0',
            'functional_use_id' => 'required',
            'household_served' => [
                'required_if:functional_use_id,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',
                'integer',
                'nullable',
                'min:0'
            ],
            'population_served' => [
                'required_if:functional_use_id,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',
                'integer',
                'nullable',
                'min:0'
            ],
            //year of building Construction
            'construction_year' => 'required|date|before_or_equal:today',
            //Lic Information
            'low_income_hh' => 'required',
            // 'lic_status' => 'required_if:low_income_hh,1',
            'lic_id' => 'required_if:lic_status,1',
            //water source Information
            'water_source_id' => 'required',
            'watersupply_pipe_code' => 'required_if:water_source_id,11',
            //sanitation system Information
            'toilet_status' => 'required',
            'toilet_count' => 'required_if:toilet_status,1',
            // 'defecation_place' => 'required_if:toilet_status,0',
            // 'ctpt_name' => 'required_if:defecation_place,9',
            'household_with_private_toilet' => 'nullable',
            'sanitation_system_id' => 'required_if:toilet_status,1',

            //   containment validation
            'type_id' => 'required_if:sanitation_system_id,3,4',
            'defecation_place' => 'required_if:toilet_status,0',
            'pit_shape' => 'required_if:type_id,8,9,10,11,12,13,14,15,16',
            'depth' => 'numeric|nullable|min:0',
            'tank_length' => 'numeric|nullable|min:0',
            'tank_width' => 'numeric|nullable|min:0',
            'pit_depth' => 'numeric|nullable|min:0',
            'pit_diameter' => 'integer|nullable|min:0',
            'build_contain' => 'required_if:sanitation_system_id,11',
            //draina and sewer code
            'sewer_code' => [
                'required_if:sanitation_system_id,1',
                'required_if:type_id,1,13'
            ],
            'drain_code' => [
                'required_if:sanitation_system_id,2',
                'required_if:type_id,2,14'
            ],
            'geom' => 'required_if:kml,null|file_extension:kml',

        ];
    }

    public function update()
    {
        Validator::extend('file_extension', function ($attribute, $value, $parameters, $validator) {
            if (!in_array($value->getClientOriginalExtension(), $parameters)) {
                return false;
            } else {
                return true;
            }
        }, 'File must be kml format');
        return [
            //  compulsory fields
            // Owner Information
             'owner_name' => 'required',
            'owner_contact' => 'integer|nullable|min:0',
            'owner_gender' => 'required',
            //Building Information
            'main_building' => 'required',
            // associated bin required only if not main building
            'building_associated_to' => 'required_if:main_building,No',
            'ward' => 'required',
            'road_code' => 'required',
            'tax_code' => 'required',
            'structure_type_id' => 'required',
            //year of building Construction
            'construction_year' => 'required|date|before_or_equal:today',
            'floor_count' => 'required|integer|min:0',
            'functional_use_id' => 'required',
            'household_served' => [
                'required_if:functional_use_id,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',
                'integer',
                'nullable',
                'min:0'
            ],
            'population_served' => [
                'required_if:functional_use_id,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15',
                'integer',
                'nullable',
                'min:0'
            ],



            // //water source Information
            // 'water_source_id' => 'required',
            'watersupply_pipe_code' => 'required_if:water_source_id,11',

            //sanitation system Information
            'toilet_status' => 'required',
            'toilet_count' => 'required_if:toilet_status,1',
            'defecation_place' => 'required_if:toilet_status,0',
            'ctpt_name' => 'required_if:defecation_place,9',
            'household_with_private_toilet' => 'nullable',
            'sanitation_system_id' => 'required_if:toilet_status,1',


            //draina and sewer code
            'sewer_code' => 'required_if:sanitation_system_id,1',
            'drain_code' => 'required_if:sanitation_system_id,2',
            'build_contain' => 'required_if:sanitation_system_id,11',

        ];
    }
}
