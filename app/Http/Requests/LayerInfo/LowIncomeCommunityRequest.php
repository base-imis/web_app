<?php

namespace App\Http\Requests\LayerInfo;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\AtLeastOneFieldRequired;
use App\Rules\FatalitiesLessThanCases;
class LowIncomeCommunityRequest extends FormRequest
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
    return [
        'community_name.required' => 'The Community Name is required.',
        'no_of_buildings.required' => 'The No. of Buildings is required.',
        'no_of_buildings.integer' => 'The No. of Buildings must be an integer.',
        'no_of_buildings.min' => 'The No. of Buildings must be at least 0.',

        'population_total.required' => 'The Population is required.',
        'population_total.integer' => 'The Population must be an integer.',
        'population_total.min' => 'The Population must be at least 0.',

        'number_of_households.required' => 'The No. of Households is required.',
        'number_of_households.integer' => 'The No. of Households must be an integer.',
        'number_of_households.min' => 'The No. of Households must be at least 0.',

        'population_male.integer' => 'The Male Population must be an integer.',
        'population_male.min' => 'The Male Population must be at least 0.',

        'population_female.integer' => 'The Female Population must be an integer.',
        'population_female.min' => 'The Female Population must be at least 0.',

        'population_others.integer' => 'The Other Population must be an integer.',
        'population_others.min' => 'The Other Population must be at least 0.',

        'no_of_septic_tank.integer' => 'The No. of Septic Tanks must be an integer.',
        'no_of_septic_tank.min' => 'The No. of Septic Tanks must be at least 0.',

        'no_of_holding_tank.integer' => 'The No. of Holding Tanks must be an integer.',
        'no_of_holding_tank.min' => 'The No. of Holding Tanks must be at least 0.',

        'no_of_pit.integer' => 'The No. of Pits must be an integer.',
        'no_of_pit.min' => 'The No. of Pits must be at least 0.',

        'no_of_sewer_connection.integer' => 'The No. of Sewer Connections must be an integer.',
        'no_of_sewer_connection.min' => 'The No. of Sewer Connections must be at least 0.',

        'no_of_community_toilets.integer' => 'The No. of Community Toilets must be an integer.',
        'no_of_community_toilets.min' => 'The No. of Community Toilets must be at least 0.',

        'geom.required' => 'The Area is required.',
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
            'community_name' => 'required',
            'no_of_buildings' => 'required|integer|min:0',
            'population_total' => 'required|integer|min:0',
            'number_of_households' => 'required|integer|min:0',
            'population_male' => 'nullable|integer|min:0',
            'population_female' => 'nullable|integer|min:0',
            'population_others' => 'nullable|integer|min:0',
            'no_of_septic_tank' => 'nullable|integer|min:0',
            'no_of_holding_tank' => 'nullable|integer|min:0',
            'no_of_pit' => 'nullable|integer|min:0',
            'no_of_sewer_connection' => 'nullable|integer|min:0',
            'no_of_community_toilets' => 'nullable|integer|min:0',
            'geom' => 'required',

    ];
}


    public function update()
    {
        return [
            'community_name' => 'required',
            'no_of_buildings' => 'required|integer|min:0',
            'population_total' => 'required|integer|min:0',
            'number_of_households' => 'required|integer|min:0',
            'population_male' => 'nullable|integer|min:0',
            'population_female' => 'nullable|integer|min:0',
            'population_others' => 'nullable|integer|min:0',
            'no_of_septic_tank' => 'nullable|integer|min:0',
            'no_of_holding_tank' => 'nullable|integer|min:0',
            'no_of_pit' => 'nullable|integer|min:0',
            'no_of_sewer_connection' => 'nullable|integer|min:0',
            'no_of_community_toilets' => 'nullable|integer|min:0',
            'geom' => 'required',

        ];
    }

}
// Last Modified Date: 10-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)    for .php files
