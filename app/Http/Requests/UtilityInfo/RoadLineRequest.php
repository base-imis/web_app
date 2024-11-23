<?php

namespace App\Http\Requests\UtilityInfo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class RoadLineRequest extends FormRequest
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

        $rules = [];

        switch ($this->method())
        {

            case 'GET':
            case 'DELETE':
                {
                    $rules = [];
                    break;
                }
            case 'POST':
                {
                    $rules = [
                        'name' => 'required|max:255',
                        'length' => 'required|numeric',
                        'carrying_width' => 'required|numeric',
                        'hierarchy' => 'nullable',
                        'surface_type' => 'nullable',
                        'right_of_way' => 'required|numeric',
                    ];
                    break;
                }
            case 'PUT':
            case 'PATCH':
                {
                    $rules = [
                        'name' => 'required|max:255',
                        'right_of_way' => 'required|numeric',
                        'length' => 'required|numeric',
                        'carrying_width' => 'required|numeric',
                        'hierarchy' => 'nullable',
                        'surface_type' => 'nullable',
                    ];
                    break;
                }
            default: break;
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->right_of_way < $this->carrying_width) {
                $validator->errors()->add('right_of_way', 'The right of way must be greater than or equal to the carrying width.');
            }
        });
    }

    public function messages()
    {
        return [
            'name.regex' => 'The name field should contain only contain letters and spaces.',
            'length.numeric' => 'The Road Length(m) must be a number.',
            'carrying_width.numeric' => 'The Carrying Width of the Road(m) must be a number.',
        ];
    }
}
