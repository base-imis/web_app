<?php

namespace App\Http\Requests\UtilityInfo;

use App\Http\Requests\Request;
use App\Models\UtilityInfo\WaterSupplys;

class WaterSupplysRequest extends Request
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
        
        switch ($this->method()) 
        {
            case 'GET':
            case 'DELETE':
                {
                    return [];
                }
            case 'POST':
                {
                    return [
                        'diameter' => 'nullable|numeric',
                        'length' => 'nullable|numeric',
                        'project_name'=> 'nullable|string',
                        'type'=>'nullable|string',
                        'material_type'=>'nullable|string'
                    ];
                }
            case 'PUT':
            case 'PATCH':
                {
                    return [
                        'diameter' => 'nullable|numeric',
                        'length' => 'nullable|numeric',
                        'project_name'=> 'nullable|string',
                        'type'=>'nullable|string',
                        'material_type'=>'nullable|string'
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
            'name.string' => 'This Project Name must be a string.',
            ];
    }
}
