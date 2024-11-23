<?php

namespace Database\Factories\Fsm;

use App\Models\Fsm\ServiceProvider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeInfoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $types = ["Management","Driver","Cleaner/Emptier"];
        $typek = array_rand($types);
        $typev = $types[$typek];
        $sex=array("Male","Female");
        $sexk = array_rand($sex);
        $sexv = $sex[$sexk];

        return [
            "employee_type" => $typev,
            "service_provider_id" => ServiceProvider::inRandomOrder()->first()->id,
            "name" => $this->faker->name(),
            "local_name" => $this->faker->name(),
            "address" => $this->faker->creditCardType(),
            "age" => $this->faker->numberBetween([0],[100]),
            "dob" => $this->faker->date,
            "sex" => $sexv,
            "year_of_experience" => $this->faker->numberBetween([0],[100]),
            "wage" => $this->faker->numberBetween([0],[100]),
            "driving_license" => $this->faker->word,
            "training_status" => $this->faker->word,
            "employment_start" => $this->faker->date,
            "employment_end" => $this->faker->date,
            "added_by" => User::inRandomOrder()->first()->id,
        ];
    }
}
