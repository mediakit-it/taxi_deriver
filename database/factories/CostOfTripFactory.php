<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CostOfTrip>
 */
class CostOfTripFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['City', 'Outside_the_City']),
            'display_name' => null,
            // Цена - decimal, как на проде после конвертации.
            'price' => fake()->randomFloat(2, 2, 15),
        ];
    }
}
