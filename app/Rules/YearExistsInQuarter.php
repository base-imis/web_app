<?php
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class YearExistsInQuarter implements Rule
{
    protected $year;

    /**
     * Create a new rule instance.
     *
     * @param  int  $year
     * @return void
     */
    public function __construct($year)
    {
        $this->year = $year;
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
        // Check if the year exists in the quarters table
        return DB::table('fsm.quarters') 
            ->where('year', $this->year)
            ->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The selected year is not available.';
    }
}
