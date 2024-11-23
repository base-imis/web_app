<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Models\User;
use Illuminate\Validation\Rules\Password;

class UserRequest extends Request
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
        $user = User::find($this->user);
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
                        'gender' => 'required',
                        'username' => 'required|max:255|unique:pgsql.auth.users',
                        'email' => 'required|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix|max:255|unique:pgsql.auth.users',
                        'password' => ['required',Password::min(8)
                            ->letters()
                            ->mixedCase()
                            ->numbers()
                            ->symbols()
                            ->uncompromised(),'confirmed'],
                        'roles' => 'required',
                        'user_type' => 'required',
                        'service_provider_id' => 'required_if:user_type,Service Provider',
                        'help_desk_id' => 'required_if:user_type,Help Desk',

                        //'help_desk_id' => ['integer',$this->service_provider_id?'required':''],
                        'status' => 'required',
                    ];
                }
            case 'PUT':
            case 'PATCH':
                {
                    return [
                        'name' => 'required|max:255',
                        'gender' => 'required',
                        'username' => 'required|max:255|unique:pgsql.auth.users,username,'.$user->id,
                        'email' => 'required|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix|unique:pgsql.auth.users,email,'.$user->id,
                        'password' => ['nullable','required_with:password_confirmation',Password::min(8)
                            ->letters()
                            ->mixedCase()
                            ->numbers()
                            ->symbols()
                            ->uncompromised(),'confirmed'],
                        'roles' => 'required',
                        'ward' => 'integer',
                        'user_type' => 'required',
                        'service_provider_id' => 'required_if:user_type,Service Provider',
                        'help_desk_id' => 'required_if:user_type,Help Desk',

                        //'help_desk_id' => ['integer',$user->service_provider_id?'required':''],
                        'status' => 'required',
                    ];
                }
            default:break;
        }
    }
     public function messages()
    {
        return [
            'name.regex' => 'The name field should contain only contain letters and spaces.',
            'email.regex' => 'The email must be a valid email address.',
            ];
    }
}
