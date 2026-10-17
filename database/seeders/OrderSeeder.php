<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Фейковые заказы: всего 64, по всем статусам.
     *
     * Данные клиента в заказе (client_id, имя, фамилия, телефон) -
     * снимок из карточки клиента, поэтому берутся из модели клиента,
     * а не генерируются заново.
     */
    public function run(): void
    {
        $clients = User::where('role', 'user')->orderBy('id')->get();
        $drivers = User::where('role', 'driving')->orderBy('id')->get();

        // Статус => [метод состояния фабрики, количество, нужен ли водитель, свежий ли заказ]
        $plan = [
            Order::STATUS_COMPLETED => ['completed', 30, true, false],
            Order::STATUS_PENDING => ['pending', 10, false, true],
            Order::STATUS_ASSIGNED => ['assigned', 8, true, true],
            Order::STATUS_SEARCHING => ['searching', 4, false, true],
            Order::STATUS_SEARCHING_OTHER => ['searchingOther', 2, false, true],
            Order::STATUS_ON_PLACE => ['onPlace', 2, true, true],
            Order::STATUS_IN_PROGRESS => ['inProgress', 2, true, true],
            Order::STATUS_CANCELED => ['canceled', 6, false, false],
        ];

        foreach ($plan as [$state, $count, $withDriver, $fresh]) {
            for ($i = 0; $i < $count; $i++) {
                $client = $clients->random();
                $createdAt = $this->randomTime($fresh);

                $attributes = [
                    'client_id' => $client->id,
                    'client_first_name' => $client->first_name,
                    'client_last_name' => $client->last_name,
                    'client_phone' => $client->phone,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];

                if ($withDriver) {
                    $attributes['driver_id'] = $drivers->random()->id;
                }

                Order::factory()->{$state}()->create($attributes);
            }
        }
    }

    /**
     * Время создания заказа: свежие заказы - за последние 2 дня,
     * остальные - равномерно за последние 30 дней.
     */
    protected function randomTime(bool $fresh): Carbon
    {
        if ($fresh) {
            return now()->subMinutes(random_int(5, 2 * 24 * 60));
        }

        return now()
            ->subDays(random_int(0, 30))
            ->setTime(random_int(6, 23), random_int(0, 59));
    }
}
