<?php

namespace Database\Seeders;

use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    /**
     * Уведомления от администрации: 12 штук -
     * 9 случайным клиентам и 3 случайным водителям.
     */
    public function run(): void
    {
        $clients = User::where('role', 'user')->orderBy('id')->get();
        $drivers = User::where('role', 'driving')->orderBy('id')->get();

        for ($i = 0; $i < 9; $i++) {
            Message::factory()->create([
                'user_id' => $clients->random()->id,
            ]);
        }

        for ($i = 0; $i < 3; $i++) {
            Message::factory()->create([
                'user_id' => $drivers->random()->id,
            ]);
        }
    }
}
