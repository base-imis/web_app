<?php

namespace Database\Factories\Swm;

use App\Models\Swm\Route;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollectionPointFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "route_id" => Route::inRandomOrder()->first()->id,
            "type" => $this->faker->creditCardType(),
            "capacity" => $this->faker->numberBetween([0],[100]),
            "ward" => $this->faker->numberBetween([0],[100]),
            "service_type" => $this->faker->creditCardType(),
            "household_served" => $this->faker->numberBetween([0],[100]),
            "status" => $this->faker->boolean,
            "collection_time" => $this->faker->time,
        ];
    }
}
