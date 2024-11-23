<?php

namespace Database\Factories\Fsm;

use Illuminate\Database\Eloquent\Factories\Factory;

class TreatmentPlantFactory extends Factory
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
            "address" => $this->faker->address(),
            "capacity" => $this->faker->randomFloat(),
            "description" => $this->faker->word,
            "caretaker_name" => $this->faker->name(),
            "contact_person" =>  $this->faker->name(),
            "contact_number" => $this->faker->phoneNumber,
        ];
    }
}
