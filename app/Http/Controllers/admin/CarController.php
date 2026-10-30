<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCarRequest;
use App\Http\Requests\Admin\UpdateCarRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;

use App\Models\Car;
use App\Models\User;
use App\Services\AssignDriverCar;

class CarController extends Controller
{
    public function __construct(private readonly AssignDriverCar $assignDriverCar)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cars = Car::with('boundDriver')->get();

        return view('admin.car.index', compact('cars'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::where('role', 'driving')->get();
        return view('admin.car.add', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCarRequest $request)
    {
        $data = $request->validated();

        $car = Car::create([
            'brand' => $data['brand'],
            'model' => $data['model'] ?? null,
            'number_plate' => $data['number_plate'],
            'photo' => $this->storePhoto($request),
        ]);

        $this->syncBinding($car, $data['driver_id'] ?? null);

        return redirect(route('car.index'));
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
        $car = Car::with('boundDriver')->find($id);
        $users = User::where('role', 'driving')->get();

        return view('admin.car.edit', compact('car', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCarRequest $request, string $id)
    {
        $data = $request->validated();
        $car = Car::findOrFail($id);

        // Новое фото заменяет прежнее: старый файл удаляем с диска
        $photo = $this->storePhoto($request);
        if ($photo !== null) {
            if ($car->photo) {
                Storage::disk('public')->delete($car->photo);
            }
            $car->photo = $photo;
        }

        $car->brand = $data['brand'];
        $car->model = $data['model'] ?? null;
        $car->number_plate = $data['number_plate'];
        $car->save();

        $this->syncBinding($car, $data['driver_id'] ?? null);

        return redirect(route('car.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $car = Car::find($id);
        if ($car->photo) {
            Storage::disk('public')->delete($car->photo);
        }
        if ($car->delete()) {
            return 'true';
        } else {
            return 'false';
        }
    }

    // Загруженное фото автомобиля: диск public, папка cars
    private function storePhoto(FormRequest $request): ?string
    {
        if (!$request->hasFile('photo')) {
            return null;
        }

        return $request->file('photo')->store('cars', 'public');
    }

    // Привязка устройства по выбору в форме: выбранный водитель
    // получает это авто в users.current_car_id (у прежнего
    // владельца привязка снимется), пустой выбор - снять привязку.
    private function syncBinding(Car $car, ?int $driverId): void
    {
        if ($driverId !== null) {
            $driver = User::find($driverId);
            if ($driver) {
                $this->assignDriverCar->execute($driver, $car->id);
            }
        } else {
            $this->assignDriverCar->releaseCar($car);
        }
    }
}
