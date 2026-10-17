<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

use App\Mail\VerificationCodeMail;

use App\Http\Requests\LoginRequest;

use App\Models\User;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;

class AuthController extends Controller
{
    public function index(){
        return view('site.auth.login');
    }

    public function registration(){
        return view('site.auth.register');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::guard('web')->user();

        // Валидация данных
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'otchestvo' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'select_country' => 'required|string|max:255',
        ]);

        $ishodCountry = $user->country;

        $user->name = $validatedData['name'];
        $user->birthdate = $request->birth_date;
        $user->otchestvo = $validatedData['otchestvo'];
        $user->first_name = $validatedData['first_name'];
        $user->phone = $validatedData['phone'];
        $user->country = $validatedData['select_country'];
        $user->save();

        if($ishodCountry != $validatedData['select_country']){
            $user->whishlists()->delete();
        }

        // Возвращаем ответ
        return response()->json([
            'success' => true,
            'message' => 'Профиль успешно обновлен',
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'password_old' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::guard('web')->user();

        // Проверка старого пароля
        if (!Hash::check($request->password_old, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Старый пароль введен неверно.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['success' => true, 'message' => 'Пароль успешно изменен.']);
    }

    public function registrationUser(Request $request){
        // Валидация входящих данных
        $request->validate([
            'first_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'email' => 'required|string|email|max:255|unique:users',
            'select_country' => 'required|string|max:255',
        ]);

        // Проверка на существование пользователя с данным email
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' =>'Такой пользователь уже существует.',
            ]);
        }

        // Генерация нового пароля
        $newPassword = Str::random(10);

        // Создание пользователя
        $user = User::create([
            'first_name'=> $request->first_name,
            'name' => $request->name,
            'otchestvo' => $request->otchestvo,
            'phone'=> $request->phone,
            'birthdate' =>$request->birth_date,
            'email' => $request->email,
            'gender'=>'Мужской',
            'country' => $request->select_country,
            'password' => Hash::make($newPassword),
            'email_verification_code' => Str::random(6),
            'email_verified_at' => now(),
        ]);

        try{
            Mail::raw("Ваш новый пароль: $newPassword", function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Это новый пароль');
            });
        }catch(Exception $e){

        }

        Excel::store(new UsersExport, 'users_export.xlsx', 'uploads');

        return response()->json([
            'success' => true,
        ]);
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

    public function forgoutPasswordIndex(Request $request) {
        return view('site.auth.forgot-password');
    }

    public function forgoutPassword(Request $request) {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => true,
            ]);
        }

        $newPassword = Str::random(10);

        $user->password = Hash::make($newPassword);
        $user->save();

        Mail::raw("Ваш новый пароль: $newPassword", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Это новый пароль');
        });

        return response()->json([
            'status' => true,
        ]);
    }
}
