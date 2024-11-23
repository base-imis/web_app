<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class AtLeastOneFieldRequired implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return request('male_cases') !== null || request('female_cases') !== null || request('other_cases') !== null;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'At least one of the fields (male cases, female cases, other cases) must be filled.';
    }
}
