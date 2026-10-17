<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DriverLocation>
 */
class DriverLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->driver(),
            // Координаты в пределах Быхова.
            'lat' => fake()->randomFloat(7, 53.512, 53.542),
            'lng' => fake()->randomFloat(7, 30.218, 30.256),
            'is_zakaz' => false,
            'order_id' => null,
        ];
    }

    /**
     * Водитель занят заказом (сам заказ передаётся вызывающим кодом).
     */
    public function busy(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_zakaz' => true,
        ]);
    }
}
