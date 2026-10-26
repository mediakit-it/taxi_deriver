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

        // Вход в веб-панель - только для ролей панели (админка,
        // диспетчерская). Клиенты и водители входят через приложение:
        // для них веб-вход закрыт, даже если пароль верный.
        $user = User::where('email', $request->email)->first();

        if ($user && !$user->hasPanelAccess()) {
            return redirect()->back()->withInput($request->only('email'))->withErrors([
                'email' => 'Учётная запись не имеет доступа в панель управления',
            ]);
        }

        if (Auth::guard('web')->attempt($credentials, $remember)) {
            return redirect()->intended('/admin/dashboard');
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
