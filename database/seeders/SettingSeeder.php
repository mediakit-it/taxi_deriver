<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Стартовые значения настроек коробки.
     * updateOrCreate - чтобы повторный сид не падал на уникальном ключе.
     */
    public function run(): void
    {
        $defaults = [
            'company_name' => 'СитиТакси',
            'city' => 'Ваш город',
            'timezone' => 'Europe/Minsk',
            'currency_label' => 'руб.',
            'driver_accept_seconds' => '60',
            'color_primary' => '',
            'color_accent' => '',
        ];

        foreach ($defaults as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
