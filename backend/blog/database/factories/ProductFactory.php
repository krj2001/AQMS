<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->text(maxNbChars:5),
            'description' => $this->faker->text(),
            'price' => $this->faker->numberBetween(10,10000),
            'quantity' => $this->faker->randomDigit(), // password            
        ];
    }
}
