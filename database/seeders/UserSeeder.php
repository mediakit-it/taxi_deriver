<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Фейковые пользователи для локальной разработки:
     * 1 администратор, 10 водителей и 30 клиентов.
     * Пароль у всех один - 'password', только для локали.
     */
    public function run(): void
    {
        // Администратор с фиксированными данными для входа в админку.
        // updateOrCreate - чтобы повторный сид не падал на уникальном email.
        User::updateOrCreate(
            ['email' => 'admin@example.test'],
            [
                'last_name' => 'ОкТакси',
                'first_name' => 'Админ',
                'phone' => '+375 29 000-00-01',
                'password' => 'password',
                'role' => 'admin',
            ]
        );

        User::factory()->driver()->count(10)->create();

        User::factory()->count(30)->create();
    }
}
