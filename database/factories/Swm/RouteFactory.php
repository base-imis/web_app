<?php

namespace Database\Factories\Swm;

use App\Models\Swm\ServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

class RouteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "service_provider_id" => ServiceProvider::inRandomOrder()->first()->id,
            "name" => $this->faker->name(),
            "type" => $this->faker->creditCardType(),
        ];
    }
}
