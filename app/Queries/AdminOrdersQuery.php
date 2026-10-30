<?php

namespace App\Queries;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

/**
 * Запрос списка заказов для админки: фильтры, сортировка и сводка
 * по статусам. Единственное место, где строится этот запрос: его
 * используют и полная страница списка, и AJAX-фрагмент живого
 * обновления - поведение у них общее по построению.
 */
class AdminOrdersQuery
{
    /**
     * Приоритет статусов для сортировки по умолчанию: новые заказы
     * (Ожидание) сверху, завершённые и отменённые - в самом низу.
     *
     * @var array<string, int>
     */
    public const STATUS_PRIORITY = [
        Order::STATUS_PENDING => 0,
        Order::STATUS_SEARCHING => 1,
        Order::STATUS_SEARCHING_OTHER => 2,
        Order::STATUS_ASSIGNED => 3,
        Order::STATUS_ON_PLACE => 4,
        Order::STATUS_IN_PROGRESS => 5,
        Order::STATUS_COMPLETED => 6,
        Order::STATUS_CANCELED => 7,
    ];

    /**
     * Активные статусы для плашек-сводки над списком: без
     * завершённых и отменённых (по слову Кати - «за исключением
     * завершённых»).
     *
     * @var array<int, string>
     */
    public const SUMMARY_STATUSES = [
        Order::STATUS_PENDING,
        Order::STATUS_SEARCHING,
        Order::STATUS_SEARCHING_OTHER,
        Order::STATUS_ASSIGNED,
        Order::STATUS_ON_PLACE,
        Order::STATUS_IN_PROGRESS,
    ];

    /**
     * @param  array{status: ?string, driver_id: int|string|null, date: ?string, search: ?string, sort: string}  $filters
     */
    private function __construct(private array $filters) {}

    /**
     * Нормализованные фильтры из GET-параметров списка. Невалидные
     * значения молча отбрасываются к «без фильтра», чтобы кривой
     * URL не ронял страницу.
     */
    public static function fromRequest(Request $request): self
    {
        $status = $request->query('status');
        $driver = $request->query('driver_id');
        $date = $request->query('date');

        return new self([
            'status' => in_array($status, array_keys(self::STATUS_PRIORITY), true) ? $status : null,
            'driver_id' => $driver === 'none'
                ? 'none'
                : (ctype_digit((string) $driver) ? (int) $driver : null),
            'date' => is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : null,
            'search' => trim((string) $request->query('search', '')) ?: null,
            'sort' => $request->query('sort') === 'old' ? 'old' : 'new',
        ]);
    }

    /**
     * @return array{status: ?string, driver_id: int|string|null, date: ?string, search: ?string, sort: string}
     */
    public function filters(): array
    {
        return $this->filters;
    }

    public function paginate(int $perPage = 30): LengthAwarePaginator
    {
        $f = $this->filters;

        $query = Order::query()->with(['driver', 'tariffClass']);

        if ($f['status'] !== null) {
            $query->where('status', $f['status']);
        }

        if ($f['driver_id'] === 'none') {
            $query->whereNull('driver_id');
        } elseif ($f['driver_id'] !== null) {
            $query->where('driver_id', $f['driver_id']);
        }

        if ($f['date'] !== null) {
            $query->whereDate('created_at', $f['date']);
        }

        if ($f['search'] !== null) {
            $term = $f['search'];
            $like = '%'.$term.'%';

            $query->where(function ($q) use ($term, $like) {
                $q->where('client_first_name', 'like', $like)
                    ->orWhere('client_last_name', 'like', $like)
                    ->orWhere('client_phone', 'like', $like)
                    ->orWhere('from_address', 'like', $like)
                    ->orWhere('to_address', 'like', $like);

                if (ctype_digit($term)) {
                    $q->orWhere('id', (int) $term);
                }
            });
        }

        // Приоритет статуса - первичный ключ всегда; внутри статуса -
        // по id (направление задаёт фильтр «Сначала новые/старые»).
        // Статусы берутся из констант модели, посторонних строк в CASE
        // попасть не может.
        $cases = collect(self::STATUS_PRIORITY)
            ->map(fn (int $priority, string $status) => "WHEN '{$status}' THEN {$priority}")
            ->implode(' ');

        $query->orderByRaw("CASE status {$cases} ELSE 99 END")
            ->orderBy('id', $f['sort'] === 'old' ? 'asc' : 'desc');

        return $query->paginate($perPage)
            ->appends(array_filter($f, fn ($value) => $value !== null && $value !== ''));
    }

    /**
     * Глобальные счётчики активных статусов для плашек-сводки
     * (фильтрами списка НЕ сужаются - сводка показывает всю картину).
     *
     * @return array<string, int>
     */
    public static function activeStatusCounts(): array
    {
        $counts = Order::query()
            ->selectRaw('status, COUNT(*) as total')
            ->whereIn('status', self::SUMMARY_STATUSES)
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect(self::SUMMARY_STATUSES)
            ->mapWithKeys(fn (string $status) => [$status => (int) ($counts[$status] ?? 0)])
            ->all();
    }
}
