<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Только фейковые данные для локальной разработки:
     * реальные пользователи, заказы и машины прода сюда не попадают.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CarSeeder::class,
            CostOfTripSeeder::class,
            OrderSeeder::class,
            MessageSeeder::class,
            DriverLocationSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
