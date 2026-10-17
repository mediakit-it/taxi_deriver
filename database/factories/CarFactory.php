<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Car>
 */
class CarFactory extends Factory
{
    /**
     * Пары марка/модель, из которых собирается автомобиль.
     *
     * @var array<int, array{0: string, 1: string}>
     */
    protected array $models = [
        ['BYD', 'e2'],
        ['Volkswagen', 'Polo'],
        ['Skoda', 'Octavia'],
        ['Kia', 'Rio'],
        ['Hyundai', 'Solaris'],
        ['Geely', 'Emgrand'],
        ['Lada', 'Vesta'],
        ['Renault', 'Logan'],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        [$brand, $model] = fake()->randomElement($this->models);

        return [
            'user_id' => User::factory()->driver(),
            'brand' => $brand,
            'model' => $model,
            // Госномер: регион 6 - Могилёвская область.
            'number_plate' => fake()->numerify('####').' '.fake()->randomElement(['AB', 'AE', 'AM', 'BT', 'EK', 'KH', 'MP']).'-6',
        ];
    }
}
