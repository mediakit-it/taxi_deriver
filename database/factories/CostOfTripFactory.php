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
            // Цена в таблице хранится строкой - как на проде.
            'price' => (string) fake()->numberBetween(2, 15),
        ];
    }
}
