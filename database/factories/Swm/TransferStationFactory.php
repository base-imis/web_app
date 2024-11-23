<?php

namespace Database\Factories\Swm;

use Illuminate\Database\Eloquent\Factories\Factory;

class TransferStationFactory extends Factory
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
            "ward" => $this->faker->numberBetween([0],[100]),
            "separation_facility" => $this->faker->boolean(),
            "area" => $this->faker->numberBetween([0],[100]),
            "capacity" => $this->faker->numberBetween([0],[100]),
        ];
    }
}
