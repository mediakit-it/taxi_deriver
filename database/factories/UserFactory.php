<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Белорусские и русские имена для фейковых пользователей.
     *
     * @var array<string, array<int, string>>
     */
    protected array $lastNames = [
        'm' => ['Иванов', 'Петров', 'Сидоров', 'Козлов', 'Новиков', 'Морозов', 'Волков', 'Соколов', 'Лебедев', 'Ковалёв', 'Борисов', 'Тарасов'],
        'f' => ['Иванова', 'Петрова', 'Сидорова', 'Козлова', 'Новикова', 'Морозова', 'Волкова', 'Соколова', 'Лебедева', 'Ковалёва', 'Борисова', 'Тарасова'],
    ];

    /**
     * @var array<string, array<int, string>>
     */
    protected array $firstNames = [
        'm' => ['Иван', 'Пётр', 'Александр', 'Дмитрий', 'Сергей', 'Андрей', 'Михаил', 'Николай', 'Артём', 'Максим', 'Владимир', 'Олег'],
        'f' => ['Анна', 'Мария', 'Елена', 'Ольга', 'Наталья', 'Татьяна', 'Екатерина', 'Юлия', 'Светлана', 'Ирина', 'Дарья', 'Виктория'],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(['m', 'f']);

        return [
            'last_name' => fake()->randomElement($this->lastNames[$gender]),
            'first_name' => fake()->randomElement($this->firstNames[$gender]),
            'email' => fake()->unique()->numerify('user####@example.test'),
            'phone' => '+375 '.fake()->randomElement(['29', '33', '44', '25']).' '.fake()->numerify('###-##-##'),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'user',
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Администратор.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    /**
     * Водитель.
     */
    public function driver(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'driving',
        ]);
    }
}
