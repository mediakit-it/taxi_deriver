<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Суперадмин нашей компании в списках у клиента не отображается
        $users = User::visibleInAdmin(auth('web')->user())->get();

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

        return view('admin.user.edit',compact('user'));
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

        if($user)
            return redirect(route('user.index'));

        return back();
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
