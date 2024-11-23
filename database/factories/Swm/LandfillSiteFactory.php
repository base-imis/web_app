<?php

namespace Database\Factories\Swm;

use Illuminate\Database\Eloquent\Factories\Factory;

class LandfillSiteFactory extends Factory
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
            "area" => $this->faker->numberBetween([0],[100]),
            "capacity" => $this->faker->numberBetween([0],[100]),
            "life_span" => $this->faker->date(),
            "status" => $this->faker->word(),
            "operated_by" => $this->faker->name(),
        ];
    }
}
