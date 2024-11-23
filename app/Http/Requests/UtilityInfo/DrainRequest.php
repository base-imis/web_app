<?php

namespace App\Http\Requests\UtilityInfo;

use App\Http\Requests\Request;
use App\Models\UtilityInfo\Roadline;

class DrainRequest extends Request
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
        
        switch ($this->method()) {
            case 'GET':
            case 'DELETE':
                {
                    return [];
                }
            case 'POST':
                {
                    return [
                        'diameter' => 'nullable|numeric',
                        'type'=> 'nullable|string',
                        'length' => 'nullable|numeric',
                    ];
                }
            case 'PUT':
            case 'PATCH':
                {
                    return [
                         'diameter' => 'nullable|numeric',
                         'type'=> 'nullable|string',
                        'length' => 'nullable|numeric',
                    ];
                }
            default:break;
        }
    }
     public function messages()
    {
        return [
            'name.regex' => 'The name field should contain only contain letters and spaces.',
            'diameter.numeric' => 'The Diameter must be a number.',
            'length.numeric' => 'The Length(m) must be a number.',
            ];
    }
}
