<?php
// Last Modified Date: 10-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)    
namespace App\Http\Requests\Fsm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;



class HelpDeskRequest extends FormRequest
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


    public function messages()
{
    return [
        'name.required' => 'The Help Desk Name is required.',
        'email.required' => 'The Email is required.',
        'email.email' => 'Please enter a valid Email Address.',
        'email.required' => 'The Email Address is required.',
        'email.unique' => 'The Email Address has already been taken.',
        'description.required' => 'The Description is required.',
        'contact_number.required'=>'The Contact Number is required.',
        'contact_number.integer'=>'The Contact Number must be an integer.',
        'password.required_if' => 'The Password is required when create user is on.'
    ];
}

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = $this->route()->help_desk;
        switch ($this->method()) {
        case 'GET':
        case 'DELETE':
            {
                return [];
            }
        case 'POST':
            {
                return [
                    'name' => 'required',
                    'description' => 'required',
                    'contact_number' => 'required|integer|min:1',
                    'email' => ['required', 'email', 'max:255', 'unique:pgsql.auth.users' ,'regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix'],
                    'password' => ['required_if:create_user,on', 'nullable',Password::min(8)
                        ->letters()
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                        ->uncompromised(),'confirmed'],
                ];
            }
        case 'PUT':
        case 'PATCH':
            {
                return [
                    'name' => 'required',
                    'description' => 'required',
                    'contact_number' => 'required|integer|min:1',
                    'email' => ['required_if:create_user,on', 'email', 'max:255', 'unique:pgsql.auth.users' ,'regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix'],

                ];
            }
        default:break;
    }
}


}
