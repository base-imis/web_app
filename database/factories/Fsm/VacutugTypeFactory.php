<?php

namespace Database\Factories\Fsm;

use App\Models\Fsm\ServiceProvider;

use Illuminate\Database\Eloquent\Factories\Factory;

class VacutugTypeFactory extends Factory
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
            
            "name" => $this->faker->name(),
            "size" => $this->faker->numberBetween([0],[100]),
            "width" => $this->faker->numberBetween([0],[100]),
            "service_provider_id" => ServiceProvider::inRandomOrder()->first()->id,
            "status" => "Operational",
            "description" => $this->faker->word,
        ];
    }
}
