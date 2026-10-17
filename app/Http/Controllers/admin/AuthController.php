<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Mail\VerificationCodeMail;

use App\Http\Requests\LoginRequest;

use App\Models\User;

class AuthController extends Controller
{
    public function index(){
        return view('site.auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        // Проверяем роль пользователя
        $user = User::where('email', $request->email)->first();

        if ($user) {
            if (in_array($user->role, ["admin", "superadmin"])) {
                if (Auth::guard('web')->attempt($credentials, $remember)) {
                    return redirect()->intended('/admin/dashboard');
                }
            } else {
                if (Auth::guard('web')->attempt($credentials, $remember)) {
                    return redirect()->intended('/');
                }
            }
        }

        return redirect()->back()->withInput($request->only('email'))->withErrors([
            'email' => 'Неверный email или пароль',
        ]);
    }

    public function logout(Request $request) {
        Auth::guard('web')->logout();
        //session()->forget('cart');
        // Если вы хотите перенаправить пользователя после выхода, вы можете использовать метод redirect()
        return redirect('/');
    }

}
