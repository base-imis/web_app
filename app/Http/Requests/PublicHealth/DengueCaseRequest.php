<?php

namespace App\Http\Requests\PublicHealth;

use App\Http\Requests\Request;
use App\Models\PublicHealth\DengueCaseRequest;

class DengueCaseRequest extends Request
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
                        'name' => 'required|max:255',
                        'width' => 'required|numeric',
                        'length' => 'required|numeric',
                        'carrying_width' => 'required|numeric',
                    ];
                }
            case 'PUT':
            case 'PATCH':
                {
                    return [
                        'name' => 'required|max:255',
                        'width' => 'required|numeric',
                        'length' => 'required|numeric',
                        'carrying_width' => 'required|numeric',
                    ];
                }
            default:break;
        }
    }
     public function messages()
    {
        return [
            'name.regex' => 'The name field should contain only contain letters and spaces.'
            
            ];
    }
}
