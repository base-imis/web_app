<?php
// Last Modified Date: 09-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Http\Requests\fsm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class CtptUserRequest extends FormRequest
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
        
       $rules= ($this->isMethod('POST')? $this->store() : $this->update());
      return $rules;   
     }
        
      
     
     /**
      * Get the validation rules that apply to the request.
      *
      * @return array
      */
     public function store()
     { 
         
         
         return [
             
            'toilet_id' => 'required',
            'date' => 'required|date|date_equals:today',
            'no_male_user' => 'required|numeric|min:0',
            'no_female_user' => 'required|numeric|min:0',
     ];
     }
 
     public function update()
     {
        return[
        'no_male_user' => 'required|numeric|min:0',
        'no_female_user' => 'required|numeric|min:0',

        ];
     }
 
     public function messages()
     {
         return[
             'toilet_id.required' => 'The Toilet Name is required.',
             'date.required' => 'The Date is required.',
             'date_equals' => 'The date must be todays.',
             'no_male_user.required' => 'The No. of Male Users is required.',
             'no_male_user.numeric' => 'The No. of Male Users  must be numeric.',
             'no_male_user.min' => 'The No. of Male Users  must be positive value.',
             'no_female_user.required' => 'The No. of Female Users is required.',
             'no_female_user.numeric' => 'The No. of Female  must be numeric.',
             'no_female_user.min' => 'The No. of Female  must be positive value.',
         ];
     }
 }

