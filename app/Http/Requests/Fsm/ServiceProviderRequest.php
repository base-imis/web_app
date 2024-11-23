<?php
// Last Modified Date: 09-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Http\Requests\Fsm;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
class ServiceProviderRequest extends FormRequest
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
      $id = $this->route()->service_provider;

        switch ($this->method()) {
            case 'GET':
            case 'DELETE':
                {
                    return [];
                }
            case 'POST':
                {
                    return [
                        'company_name' => [
                            'required',
                            'string',
                            Rule::unique('pgsql.fsm.service_providers', 'company_name')
                                ->where(function ($query) use ($id) {
                                    $query->whereNull('deleted_at')
                                          ->where('id', '<>', $id);
                                }),],

                        'email' => ['required', 'email', 'max:255', 'unique:pgsql.auth.users' ,'regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix'],

                        'ward' => 'required|integer',
                        'company_location' => ['required', 'string', 'max:255'],
                        'contact_person' => ['required', 'string', 'max:255'],
                        'contact_number' => 'required|regex:/^[0-9]+$/',
                        'contact_gender' => 'required|string',
                        'status' => 'required|boolean',
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
                            'company_name' => [
                                'required',
                                'string',
                                Rule::unique('pgsql.fsm.service_providers', 'company_name')->where(function ($query) {
                                    return $query->whereNull('deleted_at');
                                })->ignore($id),
                            ],
                            'email' => [
                                'required',
                                'email',
                                'max:255',
                                Rule::unique('pgsql.auth.users', 'email')->ignore($id, 'service_provider_id'),
                                'regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix',
                            ],
                            'ward' => 'required|integer',
                            'company_location' => ['required', 'string', 'max:255'],
                            'contact_person' => ['required', 'string', 'max:255'],
                            'contact_number' => 'required|regex:/^[0-9]+$/',
                            'contact_gender' => 'required|string',
                            'status' => 'required|boolean',
                        ];
                    }


            default:break;
        }
    }

    public function messages()
    {
        return [
            'company_name.regex' => 'The service provider name field may only contain letters and spaces.',
            'company_name.required' => 'The Company Name is required.',
            'email.required' => 'The Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'The email has already been taken.',
            'ward.required' => 'The Ward Number is required.',
            'company_location.required' =>'The Address is required.',
            'company_location.string' =>'The Address must be string.',
            'contact_person.required' =>'The Contact Person Name is required.',
            'contact_person.string' =>'The Contact Person Name must be a string.',
            'contact_number.required'=>'The Contact Person Number is required.',
            'contact_number.integer'=>'The Contact Person Number must be an integer.',
            'contact_gender.required' => 'The Contact Person Gender is required.',
            'status.required' => 'The Status is required.',
            'password.required_if' => 'The Password is required when create user is on.'
        ];
    }
}
