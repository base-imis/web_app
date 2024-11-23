<?php

namespace Database\Factories\Swm;

use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceProviderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "name" => $this->faker->name(),
            "start_date" => $this->faker->dateTime,
        ];
    }
}
