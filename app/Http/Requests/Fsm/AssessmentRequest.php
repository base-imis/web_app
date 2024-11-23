<?php

namespace App\Http\Requests\Fsm;

use Illuminate\Foundation\Http\FormRequest;

class AssessmentRequest extends FormRequest
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
            "application_id" => 'required|integer',
            "road_width" => "required|numeric",
            "road_distance" => "required|numeric",
            "sludge_assessed" => "required|numeric",
            "vacutug_accessibility" => "required|boolean",
            "vacutug_width" => "numeric",
            "vacutug_size" => "numeric",
            "required_trips" => "required|numeric",
            "comments" => "string",
            "proposed_emptying_date" => "required|date_format:Y-m-d",
            "estimated_cost" => "required|numeric",
            "tank_length" => ["required_if:pit_number,null","numeric"],
            "tank_width" => ["required_if:pit_diameter,null","numeric"],
            "tank_depth" => ["required_if:pit_depth,null","numeric"],
            "pit_number" => ["required_if:tank_length,null","numeric"],
            "pit_diameter" => ["required_if:tank_width,null","numeric"],
            "pit_depth" => ["required_if:tank_depth,null","numeric"],
            "house_image" => 'required|mimetypes:jpg,png,jpeg',
            "customer_name" => 'required',
            "customer_gender" => 'required'
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
            "application_id.required" => 'Application ID is required.',
            "application_id.integer" => 'Application ID should be an integer.',
            "road_width.required" => 'Road width is required.',
            "road_width.numeric" => 'Road width should be numeric.',
            "road_distance.required" => 'Distance from road is required. ',
            "road_distance.numeric" => 'Distance from road should be numeric.',
            "sludge_assessed.required" => 'Vol. of sludge accessed is required.',
            "sludge_assessed.numeric" => 'Vol. of sludge accecssed should be numeric.',
            "vacutug_accessibility.required" => 'Vacutug Accessibility is required.',
            "vacutug_accessibility.boolean" => 'Vacutug Accessibility should be boolean.',
            "vacutug_width.numeric" => 'Vacutug width should be numeric.',
            "vacutug_size.numeric" => 'Vacutug size should be numeric.',
            "required_trips.required" => 'Required trips is required.',
            "required_trips.numeric" => 'Required trips should be numeric.',
            "comments.string" => 'Comments should be a string.',
            "proposed_emptying_date.required" => 'Proposed emptying date is required.',
            "proposed_emptying_date.date_format" => 'Proposed emptying date should be in YYYY-MM-DD date format.',
            "estimated_cost.required" => 'Estimated Cost is required.',
            "estimated_cost.numeric" => 'Estimated Cost should be numeric.',
            "tank_length.numeric" => 'Tank Length should be numeric.',
            "tank_length.required_if" => 'Pit number is required.',
            "tank_width.numeric" => 'Pit diameter should be numeric.',
            "tank_depth.numeric" => 'Tank Depth should be numeric.',
            "pit_number.numeric" => 'Pit Number should be numeric.',
            "pit_number.required_if" => 'Tank Length is required.',
            "pit_diameter.numeric" => 'Pit Diameter should be numeric.',
            "pit_diameter.required_if" => 'Tank Width is required.',
            "pit_depth.numeric" => 'Pit Depth should be numeric.',
            "pit_depth.required_if" => 'Tank Depth is required.',
            "customer_name.required" => 'Customer Name is required.',
            "customer_gender.required" => 'Customer Gender is required.',
        ];
    }
}
