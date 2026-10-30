<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use Illuminate\Http\Request;

use App\Models\Car;
use App\Models\User;
use App\Services\AssignDriverCar;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(private readonly AssignDriverCar $assignDriverCar)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Суперадмин нашей компании в списках у клиента не отображается
        $users = User::visibleInAdmin(auth('web')->user())->with('currentCar')->get();

        return view('admin.user.index',compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.user.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        

        $user = User::create([
            'last_name'=>$request->last_name,
            'first_name'=>$request->first_name,
            'email'=>$request->email,
            'phone'=>$request->phone,
            'password'=>Hash::make($request->password),
            'role'=>$request->role,
        ]);

        if($user)
            return redirect(route('user.index'));

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
        $user = User::find($id);

        // Чужие суперадмины недоступны даже для просмотра формы
        if ($user && $user->isSuperadmin() && !auth('web')->user()->isSuperadmin()) {
            abort(403);
        }

        $cars = Car::orderBy('brand')->orderBy('model')->get();

        return view('admin.user.edit', compact('user', 'cars'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, string $id)
    {
        // Суперадмина нашей компании может менять только суперадмин
        $target = User::find($id);
        if ($target && $target->isSuperadmin() && !$request->user('web')->isSuperadmin()) {
            abort(403);
        }

        $user = User::find($id)->update([
            'last_name'=>$request->last_name,
            'first_name'=>$request->first_name,
            'email'=>$request->email,
            'phone'=>$request->phone,
            // 'password'=> !empty($request->password) ? Hash::make($request->password) : ,
            'role'=>$request->role,
        ]);

        // Привязка автомобиля к устройству водителя: поле
        // current_car_id присылает только форма водителя. Одна
        // машина - один водитель: у прежнего владельца привязка
        // снимется внутри AssignDriverCar.
        if ($user && $request->has('current_car_id')) {
            $this->assignDriverCar->execute(
                $target,
                $request->filled('current_car_id') ? (int) $request->input('current_car_id') : null
            );
        }

        if($user)
            return redirect(route('user.index'));

        return back();
    }

    /**
     * Заблокировать / разблокировать пользователя. Заблокированный
     * не может войти ни в панель, ни в приложения, а выданные ему
     * API-токены перестают работать (middleware EnsureUserNotBlocked).
     * Защиты: собственную учётную запись заблокировать нельзя,
     * суперадмина нашей компании - тоже.
     */
    public function toggleBlock(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $actor = $request->user('web');

        if ($actor && $user->id === $actor->id) {
            return back()->with('error', 'Нельзя заблокировать собственную учётную запись');
        }

        if ($user->isSuperadmin()) {
            return back()->with('error', 'Суперадмин не может быть заблокирован');
        }

        $user->is_blocked = !$user->isBlocked();
        $user->save();

        return back()->with('success', $user->isBlocked()
            ? 'Пользователь заблокирован'
            : 'Пользователь разблокирован');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        if ($user && $user->isSuperadmin() && !auth('web')->user()->isSuperadmin()) {
            abort(403);
        }
        if($user->delete()){
            return 'true';
        }
        else return 'false';
    }
}
