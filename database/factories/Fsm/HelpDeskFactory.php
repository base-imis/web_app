<?php

namespace Database\Factories\Fsm;

use App\Models\User;
use App\Models\Fsm\ServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

class HelpDeskFactory extends Factory
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
            "description" => $this->faker->text(),
            "contact_no" => $this->faker->phoneNumber(),
            "email" => $this->faker->email(),
            "service_provider_id" => ServiceProvider::inRandomOrder()->first()->id,
            
        ];
    }
}
