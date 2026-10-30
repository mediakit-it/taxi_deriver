<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;
use App\Http\Requests\Api\SendVerificationCodeRequest;
use App\Services\EmailDomainValidator;
use App\Services\EmailVerificationService;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;

use Illuminate\Support\Facades\Log;

use App\Models\User;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','resetPassword','refresh','me','register','verify','newVerifyCode','verify2']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            
        ]);

        Log::info($request->all());
        $credentials = $request->only('email', 'password');

        if(User::where([['email',$request->input('email')],['role',$request->input('user_type')]])->exists()){
            $token = auth()->attempt($credentials);
            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                ], 401);
            }
    
            $user = auth()->user();

            // Заблокированному пользователю токен не выдаётся: только
            // что созданный сразу инвалидируется.
            if ($user->isBlocked()) {
                auth()->logout();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Пользователь заблокирован',
                ], 403);
            }

            return response()->json([
                    'status' => 'success',
                    'user' => $user->profilePayload(),
                    'authorisation' => [
                        'token' => $token,
                        'type' => 'bearer',
                    ]
                ]);
        }else{
            return response()->json([
                'status' => 'false',
                'message' => 'Данного профиля нет в базе',
            ]);
        }
        
    }

    /**
     * Reset password and send it to the user's email.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
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

    // Регистрация
    public function register(Request $request, EmailVerificationService $verification)
    {

        \Log::info($request->all());

        // Если по этому адресу запрашивали код подтверждения (поток клиентского
        // приложения: verify2 -> v1/auth/verify-email -> userSignUp), регистрация
        // обязана дождаться подтверждения адреса. Потоки, где код не запрашивали,
        // идут как раньше: гейт касается только начатой проверки.
        $email = (string) $request->input('email');
        if ($email !== '' && $verification->flowInitiated($email) && ! $verification->isVerified($email)) {
            return response()->json([
                'status' => false,
                'message' => 'Сначала подтвердите адрес электронной почты кодом из письма.',
            ], 422);
        }

        $user = User::create([
            'last_name'=>$request->input('last_name'),
            'first_name'=>$request->input('first_name'),
            'email'=>$request->input('email'),
            'phone'=>$request->input('phone'),
            'password'=>Hash::make($request->input('password')),
            'role'=>'user',
        ]);

        if($user){
            $verification->consumeVerification($email);
            return response()->json([
                'status' => true
                ], 200);
        }else{
            return response()->json([
                'status' => false
                ], 200);
        }
    
        
    }

    

    // Верефикация
    public function verify(Request $request)
    {

        Log::error($request->email);

        Log::error($request->verification_code);
        // Проверка входных данных
        $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required|string|min:5',
        ]);

        // Поиск пользователя по email и коду верификации
        $user = User::where('email', $request->email)
                    ->where('email_verification_code', $request->verification_code)
                    ->first();

        // Если пользователь не найден или код неверный
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => $request->email,
            ], 401);
            // return response()->json(['error' => $request->email], 400);
        }

        // Помечаем email как подтвержденный
        $user->email_verified_at = now();
        $user->email_verification_code = null; // Очищаем код верификации
        $user->save();

        return response()->json([
            'status' => 'success',
            'user' => $user,
        ]);
    }

    public function newVerifyCode(Request $request)
    {

        Log::error($request->email);

        Log::error('----');
        // Проверка входных данных
        $request->validate([
            'email' => 'required|email',
        ]);

        // Поиск пользователя по email и коду верификации
        $user = User::where('email', $request->email)
                    ->first();

        $user->email_verification_code = Str::random(6);
        $user->save();

        

        // Если пользователь не найден или код неверный
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => $request->email,
            ], 401);
            // return response()->json(['error' => $request->email], 400);
        }

         // Отправка письма с кодом подтверждения
         Mail::to($user->email)->send(new VerificationCodeMail($user->email_verification_code));

        return response()->json([
            'status' => 'success',
        ]);
    }


    public function checkTokenValidity(Request $request)
    {
        try {
            // Получаем токен из запроса
            $token = $request->bearerToken();
            
            // Проверяем токен на действительность и получаем пользователя
            $user = JWTAuth::setToken($token)->authenticate();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Token is valid',
                'user' => $user->profilePayload() // Возвращаем пользователя вместе с ответом
            ], 200);
        } catch (TokenExpiredException $e) {
            // Если токен истек, возвращаем сообщение об ошибке
            return response()->json(['error' => 'Token has expired'], 401);
        } catch (TokenInvalidException $e) {
            // Если токен недействителен, возвращаем сообщение об ошибке
            return response()->json(['error' => 'Token is invalid'], 401);
        } catch (JWTException $e) {
            // Если возникла другая ошибка JWT, возвращаем сообщение об ошибке
            return response()->json(['error' => 'Error validating token'], 500);
        }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = Auth::user();

        if ($user) {
            return response()->json($user->profilePayload());
        } else {
            // Проверяем, истек ли срок действия токена
            $token = Auth::getToken();
            if ($token && Auth::payload()->get('exp') < time()) {
                return response()->json(['error' => 'Token has expired'], 401);
            } else {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function verify2(SendVerificationCodeRequest $request, EmailDomainValidator $validator, EmailVerificationService $verification){
        $email = $request->validated('email');

        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            return response()->json([
                'status' => true,
                'status_code' =>'not_verified',
            ]);
        }

        // До отправки письма убеждаемся, что домен вообще принимает почту:
        // письмо на несуществующий адрес - это bounce и потерянный клиент.
        if (! $validator->isDeliverable($email)) {
            return response()->json([
                'message' => 'Похоже, такой почты не существует. Проверьте адрес.',
                'suggestion' => $validator->suggestDomain($email),
            ], 422);
        }

        // Код хранится только на сервере (хеш в кэше) и наружу не отдаётся:
        // проверка - отдельным запросом POST /api/v1/auth/verify-email.
        $verification->sendCode($email);

        return response()->json([
            'status' => true,
            'status_code' =>'verified',
        ]);
    }
}

