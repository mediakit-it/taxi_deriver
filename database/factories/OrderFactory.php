<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Улицы Быхова для фейковых адресов.
     *
     * @var array<int, string>
     */
    protected array $streets = [
        'ул. Ленина',
        'ул. Советская',
        'ул. Первомайская',
        'ул. Красноармейская',
        'ул. Гагарина',
        'ул. Пушкина',
        'ул. Молодёжная',
        'ул. Строителей',
        'ул. Войкова',
        'ул. Козлова',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => User::factory(),
            'client_first_name' => fake()->randomElement(['Иван', 'Анна', 'Пётр', 'Мария', 'Сергей', 'Ольга', 'Дмитрий', 'Елена']),
            'client_last_name' => fake()->randomElement(['Иванов', 'Иванова', 'Петров', 'Петрова', 'Козлов', 'Козлова', 'Соколов', 'Соколова']),
            'client_phone' => '+375 '.fake()->randomElement(['29', '33', '44', '25']).' '.fake()->numerify('###-##-##'),
            'driver_id' => null,
            'from_address' => $this->address(),
            'from_lat' => fake()->randomFloat(6, 53.512, 53.542),
            'from_lng' => fake()->randomFloat(6, 30.218, 30.256),
            'to_address' => $this->address(),
            'to_lat' => fake()->randomFloat(6, 53.512, 53.542),
            'to_lng' => fake()->randomFloat(6, 30.218, 30.256),
            'distance_to_client' => fake()->numberBetween(300, 6000),
            'time_to_client' => fake()->numberBetween(120, 900),
            'distance' => fake()->numberBetween(800, 25000),
            'duration' => fake()->numberBetween(300, 2400),
            'price' => fake()->randomFloat(2, 8, 45),
            'comment' => fake()->randomElement([null, null, null, 'Позвонить за 5 минут', 'Детское кресло', 'Крупный багаж', 'Буду с собакой']),
            'status' => Order::STATUS_PENDING,
        ];
    }

    /**
     * Случайный адрес в Быхове.
     */
    protected function address(): string
    {
        return 'г. Быхов, '.fake()->randomElement($this->streets).', '.fake()->numberBetween(1, 60);
    }

    /**
     * Заказ в ожидании.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_PENDING,
        ]);
    }

    /**
     * Идёт поиск водителя.
     */
    public function searching(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_SEARCHING,
        ]);
    }

    /**
     * Поиск водителя среди остальных.
     */
    public function searchingOther(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_SEARCHING_OTHER,
        ]);
    }

    /**
     * Водитель назначен.
     */
    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_ASSIGNED,
            'driver_id' => User::factory()->driver(),
        ]);
    }

    /**
     * Водитель на месте подачи.
     */
    public function onPlace(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_ON_PLACE,
            'driver_id' => User::factory()->driver(),
        ]);
    }

    /**
     * Поездка выполняется.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_IN_PROGRESS,
            'driver_id' => User::factory()->driver(),
        ]);
    }

    /**
     * Заказ завершён.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_COMPLETED,
            'driver_id' => User::factory()->driver(),
        ]);
    }

    /**
     * Заказ отменён.
     */
    public function canceled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_CANCELED,
        ]);
    }
}
