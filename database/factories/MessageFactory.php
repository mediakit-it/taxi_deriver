<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Тексты уведомлений от администрации.
     *
     * @var array<int, string>
     */
    protected array $notices = [
        'С 1 ноября действуют обновлённые тарифы на поездки за город. Подробности у диспетчера.',
        'В ночь с субботы на воскресенье с 02:00 до 04:00 возможны перерывы в работе приложения: технические работы.',
        'Вышло обновление приложения. Пожалуйста, обновите его перед следующей поездкой.',
        'Акция: каждая пятая поездка по городу со скидкой 20%.',
        'На дорогах гололёд. Будьте осторожны, время подачи автомобиля может увеличиться.',
        'Новый номер диспетчера: +375 29 000-00-00. Заказы также принимаются в приложении.',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => 'admin',
            'message' => fake()->randomElement($this->notices),
            'is_active' => 0,
        ];
    }
}
