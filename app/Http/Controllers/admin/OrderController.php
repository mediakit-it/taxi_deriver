<?php

namespace App\Http\Controllers\admin;

use App\Actions\Admin\AssignOrderDriver;
use App\Exceptions\DriverAssignmentException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignOrderDriverRequest;
use App\Http\Requests\Admin\UpdateOrderPriceRequest;
use App\Queries\AdminOrdersQuery;
use Illuminate\Http\Request;

use App\Models\Car;
use App\Models\CostOfTrip;
use App\Models\DriverLocation;
use App\Models\User;
use App\Models\Order;
use App\Events\OrderSent;
use App\Services\FunctionalSettingsService;
use App\Events\OrderStatusChanged; // добавлены события статусов RAY
class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * Список строит AdminOrdersQuery (фильтры + сортировка), данные
     * для полной страницы и AJAX-фрагмента одни и те же. С параметром
     * fragment=1 отдаётся только admin.order.fragment (плашки +
     * таблица, без макета) - его подменяет admin-orders-live.js при
     * живом обновлении.
     */
    public function index(Request $request)
    {
        if ($request->boolean('fragment')) {
            return $this->renderListFragment($request);
        }

        return view('admin.order.index', $this->listViewData($request));
    }

    /**
     * Историческая точка AJAX-обновления таблицы: теперь отдаёт тот
     * же фрагмент, что и живой список (плашки + таблица).
     */
    public function indexAjax(Request $request)
    {
        return $this->renderListFragment($request);
    }

    private function renderListFragment(Request $request)
    {
        return view('admin.order.fragment', $this->listViewData($request));
    }

    /**
     * Данные списка заказов: отфильтрованная страница, нормализованные
     * фильтры для формы, подписи статусов, глобальные счётчики плашек
     * и список водителей для фильтров и быстрого назначения.
     */
    private function listViewData(Request $request): array
    {
        $query = AdminOrdersQuery::fromRequest($request);

        return [
            'orders' => $query->paginate(),
            'filters' => $query->filters(),
            'statusLabels' => Order::statusLabels(),
            'statusCounts' => AdminOrdersQuery::activeStatusCounts(),
            'drivers' => $this->driversWithAssignmentStates(),
        ];
    }

    /**
     * Водители для селектов назначения с пометкой доступности
     * (assignment_state): 'busy' - есть активный заказ, 'offline' -
     * не на линии, 'free' - можно назначать. Селекты в списке и в
     * карточке заказа отключают недоступных (пакет замечаний
     * 22.11.2026); сама защита назначения живёт в AssignOrderDriver,
     * пометка - только подсказка диспетчеру. Занятость важнее
     * линии: занятый водитель может одновременно быть офлайн по
     * свежести геопозиции, но причина отказа для диспетчера - заказ.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    private function driversWithAssignmentStates()
    {
        $drivers = User::where('role', 'driving')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $busyDriverIds = Order::query()
            ->whereIn('status', [
                Order::STATUS_ASSIGNED,
                Order::STATUS_ON_PLACE,
                Order::STATUS_IN_PROGRESS,
            ])
            ->whereNotNull('driver_id')
            ->pluck('driver_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        $locations = DriverLocation::query()
            ->whereIn('user_id', $drivers->pluck('id'))
            ->get()
            ->keyBy('user_id');

        foreach ($drivers as $driver) {
            if (in_array((int) $driver->id, $busyDriverIds, true)) {
                $driver->assignment_state = 'busy';
                continue;
            }

            $location = $locations->get($driver->id);
            $driver->assignment_state = $location
                && $location->work_status === DriverLocation::STATUS_ONLINE
                ? 'free'
                : 'offline';
        }

        return $drivers;
    }

    /**
     * Быстрое назначение водителя прямо из списка, без карточки
     * заказа. Сама операция - AssignOrderDriver, та же, что у формы
     * редактирования: метка предложения и события не расходятся.
     */
    public function assignDriver(AssignOrderDriverRequest $request, Order $order, AssignOrderDriver $assignOrderDriver)
    {
        try {
            $assignOrderDriver($order, $request->validated()['driver_id'] ?? null);
        } catch (DriverAssignmentException $e) {
            // Занятой или офлайн-водитель: назначение отклонено
            // действием, текст отказа показываем диспетчеру как есть.
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'driver_id' => $order->driver_id,
            'status' => $order->status,
        ]);
    }

    /**
     * Быстрая правка цены прямо из списка, без карточки заказа.
     */
    public function updatePrice(UpdateOrderPriceRequest $request, Order $order)
    {
        $order->update(['price' => $request->validated()['price']]);

        return response()->json([
            'ok' => true,
            'price' => $order->price,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::where('role','driving')->get();
        $tariffs = CostOfTrip::all();
        $sources = $this->orderSources();
        return view('admin.order.add',compact('users','tariffs','sources'));
        // return view('admin.order.add');
    }

    /**
     * Источники, доступные в форме заказа: подписи, ограниченные
     * разрешением из раздела «Функционал».
     *
     * @return array<string, string>
     */
    private function orderSources(): array
    {
        $allowed = app(FunctionalSettingsService::class)->allowedOrderSources();

        return array_intersect_key(Order::sourceLabels(), array_flip($allowed));
    }

    /**
     * Источник для сохранения: выбор из формы принимается только
     * если он разрешён настройками, иначе - телефон (или первый
     * разрешённый). У заказов из приложения источник не меняется.
     */
    private function resolveSource(Request $request, ?Order $order = null): string
    {
        if ($order && $order->source === Order::SOURCE_APP) {
            return Order::SOURCE_APP;
        }

        $allowed = array_keys($this->orderSources());
        $source = (string) $request->input('source', '');

        if (in_array($source, $allowed, true)) {
            return $source;
        }

        return in_array(Order::SOURCE_PHONE, $allowed, true) ? Order::SOURCE_PHONE : $allowed[0];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        // dd($request);
        // $order = Order::find(244);

        // dd($order);

        // поменяла тут метод RAY
        // $order = Order::create($request->all());
        // if($order){
        //     if($order->driver_id){
        //         event(new OrderSent($order,$order->driver_id));
        //     }
        //     else{
        //         event(new OrderSent($order, role: 'driving'));
        //     }
        //     return redirect(route('order.index'));
        // }else{
        //     return back();
        // }


        // Новый медот RAY
            $data = $request->all();
            $data['source'] = $this->resolveSource($request);
            if (!empty($data['driver_id'])) {
                $data['status'] = Order::STATUS_SEARCHING;
                $data['driver_offered_at'] = now();
                }
            $order = Order::create($data);
    if ($order) {
        if ($order->driver_id) {
            event(new OrderSent($order, $order->driver_id));
            event(new OrderStatusChanged($order, 'assigned_by_dispatcher'));
        } else {
            event(new OrderSent($order, role: 'driving'));
        }

        return redirect(route('order.index'));
    }

    return back();

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $users = $this->driversWithAssignmentStates();
        $tariffs = CostOfTrip::all();
        $sources = $this->orderSources();
        $order = Order::find($id);

        return view('admin.order.edit',compact('users','order','tariffs','sources'));
    }

    /**
     * Update the specified resource in storage.
     *
     * Назначение водителя вынесено в AssignOrderDriver - тот же
     * путь проходит быстрое назначение из списка заказов.
     */
    public function update(Request $request, string $id, AssignOrderDriver $assignOrderDriver)
    {
        $order = Order::findOrFail($id);

        $data = $request->all();
        $data['source'] = $this->resolveSource($request, $order);

        // Водителя из общих данных убираем: его ставит действие, со
        // всеми сопутствующими эффектами (метка предложения, статус,
        // события). Поля в запросе нет - действие получает текущего
        // водителя и повторяет историческое поведение update.
        $hasDriverField = array_key_exists('driver_id', $data);
        $driverId = $hasDriverField && $data['driver_id'] ? (int) $data['driver_id'] : null;
        unset($data['driver_id']);

        $order->update($data);

        try {
            $assignOrderDriver($order, $hasDriverField ? $driverId : ($order->driver_id !== null ? (int) $order->driver_id : null));
        } catch (DriverAssignmentException $e) {
            // Назначение отклонено (водитель занят или не на линии):
            // возвращаем диспетчера в карточку с текстом отказа,
            // остальные поля заказа при этом уже сохранены.
            return redirect()->route('order.edit', $order->id)->with('error', $e->getMessage());
        }

        return redirect(route('order.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $car = Order::find($id);
        if($car->delete()){
            return 'true';
        }
        else return 'false';
    }
}
