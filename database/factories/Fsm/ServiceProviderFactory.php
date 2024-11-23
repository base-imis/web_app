<?php

namespace Database\Factories\Fsm;

use App\Models\LayerInfo\Ward;
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
            "ward" => Ward::inRandomOrder()->first()->id,
            "location" => $this->faker->address(),
            "email" => $this->faker->email(),
            "contact_person" => $this->faker->name(),
            "contact_no" => $this->faker->phoneNumber,
        ];
    }
}
