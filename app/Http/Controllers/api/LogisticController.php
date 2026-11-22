<?php

namespace App\Http\Controllers\api;

use App\Actions\Driver\SyncWorkStatusPeriod;
use App\Events\ClientDriverLocation;
use App\Events\DriverWorkStatusChanged;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DriverLocation;
use App\Models\Order;



class LogisticController extends Controller
{
    public function update(Request $request)
    {
        try {
            // Водитель определяется по токену, а не по user_id из тела
            // запроса: приложение может прислать 0 (профиль ещё не
            // сохранён локально) или устаревший id, и пинг тогда уходил
            // в никуда - диспетчер не видел машину на карте.
            $userId = $request->user()?->id ?? $request->input('user_id');

            // isZakaz и orderId - необязательные поля пинга: приложение
            // шлёт их всегда, но запрос без них не должен падать на
            // NOT NULL колонках driver_locations.
            $locationValues = [
                'lat' => $request->input('lat'),
                'lng' => $request->input('lng'),
                'is_zakaz' => $request->input('isZakaz', false),
            ];

            if ($request->filled('orderId')) {
                $locationValues['order_id'] = $request->input('orderId');
            }

            // is_online - уход с линии (пакет №160): приложение шлёт
            // флаг в каждом пинге. Поле необязательное: пинг без него
            // колонку не трогает, чтобы старые версии приложения не
            // сбрасывали статус водителя.
            if ($request->has('is_online')) {
                $locationValues['is_online'] = $request->boolean('is_online');
            }

            // Прежнее значение флага - ДО записи: переход статуса
            // (пакет №162) ловится сравнением с новым, а не каждым
            // пингом, иначе событие и периоды плодились бы каждые
            // 10 секунд.
            $previousIsOnline = null;
            if ($request->has('is_online')) {
                $previousFlag = DriverLocation::where('user_id', $userId)->value('is_online');
                $previousIsOnline = $previousFlag === null ? null : (bool) $previousFlag;
            }

            $location = DriverLocation::updateOrCreate(
                ['user_id' => $userId],
                $locationValues
            );

            if ($request->has('is_online')) {
                $newIsOnline = $request->boolean('is_online');
                if ($previousIsOnline === null || $previousIsOnline !== $newIsOnline) {
                    $this->syncWorkStatusAndNotify($request, $location, $newIsOnline);
                }
            }

            $this->broadcastToActiveClient($request);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            // Просто возвращаем ошибку, чтобы не сломать клиента
            return response()->json(['status' => 'failed'], 500);
        }
    }

    // Переход «на линии / не на линии» (пакет №162): период работы
    // в новом статусе открывается/закрывается синхронно с флагом,
    // а админка получает живой сигнал DriverWorkStatusChanged -
    // список водителей, список заказов и карта обновляются без
    // перезагрузки. Сбой учёта или рассылки пинг не роняет:
    // геопозиция к этому моменту уже сохранена выше.
    private function syncWorkStatusAndNotify(Request $request, DriverLocation $location, bool $isOnline): void
    {
        try {
            $driver = $request->user() ?? User::find($location->user_id);

            if (! $driver) {
                return;
            }

            $period = app(SyncWorkStatusPeriod::class)->sync($driver, $isOnline);

            event(new DriverWorkStatusChanged(
                userId: $driver->id,
                workStatus: $location->work_status,
                workStatusLabel: $location->work_status_label,
                statusSince: $period?->started_at?->toIso8601String()
                    ?? $location->updated_at?->toIso8601String(),
                isZakaz: (bool) $location->is_zakaz,
            ));
        } catch (\Exception $e) {
            // Учёт периодов и живой сигнал - не критичный путь пинга.
        }
    }

    // Клиент видит машину на карте вживую: если у водителя из
    // токена есть активный заказ (назначен, на месте или в
    // поездке), свежая геопозиция уходит его клиенту событием
    // driverLocation. Сбой рассылки не должен ронять сам пинг:
    // позиция для админки к этому моменту уже сохранена выше.
    private function broadcastToActiveClient(Request $request): void
    {
        $driver = $request->user();

        if (!$driver || $request->input('lat') === null || $request->input('lng') === null) {
            return;
        }

        $activeOrder = Order::where('driver_id', $driver->id)
            ->whereIn('status', [Order::STATUS_ASSIGNED, Order::STATUS_ON_PLACE, Order::STATUS_IN_PROGRESS])
            ->orderByDesc('id')
            ->first();

        if (!$activeOrder || !$activeOrder->client_id) {
            return;
        }

        try {
            event(new ClientDriverLocation(
                (int) $activeOrder->client_id,
                (int) $activeOrder->id,
                (float) $request->input('lat'),
                (float) $request->input('lng')
            ));
        } catch (\Exception $e) {
            // Рассылка клиенту - не критичный путь пинга.
        }
    }

}
