<?php

namespace Database\Seeders;

use App\Models\CostOfTrip;
use Illuminate\Database\Seeder;

class CostOfTripSeeder extends Seeder
{
    /**
     * Тарифы с прода, значения - один в один.
     *
     * Цена в таблице хранится строкой (наследие прода, включая '1,70'
     * с запятой). Перевод цены в числовой тип - отдельная задача позже,
     * здесь тип и значения сознательно не трогаем.
     */
    public function run(): void
    {
        CostOfTrip::updateOrCreate(
            ['name' => 'City'],
            ['price' => '8']
        );

        CostOfTrip::updateOrCreate(
            ['name' => 'Outside_the_City'],
            ['price' => '1,70']
        );
    }
}
