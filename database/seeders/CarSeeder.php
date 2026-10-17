<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\User;
use Illuminate\Database\Seeder;

class CarSeeder extends Seeder
{
    /**
     * Автомобили для первых восьми водителей.
     * Первые четыре - BYD e2, как основной автопарк на проде.
     */
    public function run(): void
    {
        $drivers = User::where('role', 'driving')->orderBy('id')->get();

        foreach ($drivers->take(8) as $index => $driver) {
            $attributes = ['user_id' => $driver->id];

            if ($index < 4) {
                $attributes['brand'] = 'BYD';
                $attributes['model'] = 'e2';
            }

            Car::factory()->create($attributes);
        }
    }
}
