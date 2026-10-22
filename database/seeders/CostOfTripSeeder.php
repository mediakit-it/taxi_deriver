<?php

namespace Database\Seeders;

use App\Models\CostOfTrip;
use Illuminate\Database\Seeder;

class CostOfTripSeeder extends Seeder
{
    /**
     * Классы обслуживания с прода, значения - один в один.
     *
     * City - цена поездки по городу, Outside_the_City - цена за километр
     * за городом. Цена хранится decimal; мобильное приложение по-прежнему
     * получает её строкой вида '8' / '1,70' (см. CostOfTrip::formatPrice).
     * display_name - человекочитаемое имя класса для диспетчера и сайта.
     */
    public function run(): void
    {
        CostOfTrip::updateOrCreate(
            ['name' => 'City'],
            ['price' => 8, 'display_name' => 'По городу']
        );

        CostOfTrip::updateOrCreate(
            ['name' => 'Outside_the_City'],
            ['price' => 1.70, 'display_name' => 'За городом']
        );
    }
}
