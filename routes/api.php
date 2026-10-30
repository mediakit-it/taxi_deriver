<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\api\OrderController;
use App\Http\Controllers\api\LogisticController;
use App\Http\Controllers\api\CostOfTripController;
use App\Http\Controllers\api\AppSettingsController;
use App\Http\Controllers\api\CarBindingController;
use App\Http\Controllers\api\ClientNotificationController;
use App\Http\Controllers\api\GeocodeController;
use App\Http\Controllers\api\EmailVerificationController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::prefix('v1')->group(function () {
    Route::get('/app-settings', [AppSettingsController::class, 'index']);
    Route::post('/auth/verify-email', [EmailVerificationController::class, 'verify']);
    Route::middleware(['auth:api', 'blocked'])->group(function () {
        Route::get('/notifications', [ClientNotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [ClientNotificationController::class, 'unreadCount']);
        Route::post('/notifications/read', [ClientNotificationController::class, 'markRead']);
        Route::get('/geocode', [GeocodeController::class, 'geocode']);
        Route::get('/reverse-geocode', [GeocodeController::class, 'reverse']);
        // Доклад водителя о привязанном к устройству авто после входа
        Route::post('/car-binding/report', [CarBindingController::class, 'report']);
    });
    // Привязка устройства водителя к авто при установке приложения:
    // без авторизации (экран входа), доступ по паролю привязки из
    // настроек, троттлинг от перебора пароля.
    Route::post('/car-binding/cars', [CarBindingController::class, 'cars'])->middleware('throttle:5,1');
    Route::get('/cost-of-trip', [CostOfTripController::class, 'index']);
    Route::get('/cost-of-trip/simple', [CostOfTripController::class, 'getSimple']);
    Route::get('/cost-of-trip/{name}', [CostOfTripController::class, 'getByName']);
});


Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::post('login', '\App\Http\Controllers\AuthController@login');
    Route::post('resetPassword', '\App\Http\Controllers\AuthController@resetPassword');
    Route::post('register', 'App\Http\Controllers\AuthController@register');

    Route::post('userSignIn', '\App\Http\Controllers\AuthController@login');
    Route::post('userResetPassword', '\App\Http\Controllers\AuthController@resetPassword');
    Route::post('userSignUp', 'App\Http\Controllers\AuthController@register');


      // Регистрация пользователя
    Route::post('verify2', 'App\Http\Controllers\AuthController@verify2');

     // Регистрация пользователя
    Route::post('newVerifyCode', 'App\Http\Controllers\AuthController@newVerifyCode');

    

    // Подтверждение email с помощью кода
    Route::post('email/verify', 'App\Http\Controllers\AuthController@verify')->name('verification.verify');

    Route::post('logout', '\App\Http\Controllers\AuthController@logout');
    Route::post('refresh', '\App\Http\Controllers\AuthController@refresh');
    Route::get('me', '\App\Http\Controllers\AuthController@me');
});

Route::group(['middleware' => ['auth:api', 'blocked']], function () {
    Route::post('/check-token-validity', '\App\Http\Controllers\AuthController@checkTokenValidity');








    Route::post('/updateProfile', '\App\Http\Controllers\ProfileController@updateProfile');

    Route::post('/updatePassword', '\App\Http\Controllers\ProfileController@updatePassword');

    Route::post('/deleteProfile', '\App\Http\Controllers\ProfileController@deleteProfile');





    // //получение брендов
    
    
    // //Работа с акциями


    // //Работа с адресами пользователя
    


    // //Создание заказа


    // //Получение баннеров на главной 


    // // работа с смс 
    Route::post('/addNewMessage', '\App\Http\Controllers\MassageController@addNewMessage');
    Route::get('/getMessageUser', '\App\Http\Controllers\MassageController@getMessageUser');
    Route::get('/getCountMessage', '\App\Http\Controllers\MassageController@getCountMessage');
    Route::post('/readMessageUser', '\App\Http\Controllers\MassageController@readMessageUser');



    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/newOrderUserIndex', [OrderController::class, 'newOrderUserIndex']);
    Route::post('/orders/add', [OrderController::class, 'addDriving']);
    Route::post('/orders/close', [OrderController::class, 'closeDriving']);
    Route::post('/orders/closePrice', [OrderController::class, 'closeDrivingPrice']);

    Route::post('/orders/newOrderUser', [OrderController::class, 'newOrderUser']);

    Route::post('/orders/closeOrderUser', [OrderController::class, 'closeOrderUser']);
    Route::post('/orders/closeOrderUserDriving', [OrderController::class, 'closeOrderUserDriving']);
    Route::post('/orders/acceptOrderByDriver', [OrderController::class, 'acceptOrderByDriver']);
    Route::post('/orders/declineOrderByDriver', [OrderController::class, 'declineOrderByDriver']);

    //два метода от RAY
    Route::post('/orders/onPlace',   [OrderController::class, 'onPlace']);
    Route::post('/orders/startRide', [OrderController::class, 'startRide']);
    
    Route::post('/location/update', [LogisticController::class, 'update']);


    
    
});




Route::get('/suggest', function (Request $request) {
    $query = $request->input('text');
    if (!$query) {
        return response()->json(['error' => 'Missing text parameter'], 400);
    }

    $apiKey = config('services.yandex.suggest_key');
    if (!$apiKey) {
        return response()->json(['error' => 'Suggest service is not configured'], 503);
    }

    $response = Http::get('https://suggest-maps.yandex.ru/v1/suggest', [
        'apikey' => $apiKey,
        'text' => $query,
        'lang' => 'ru_RU',
                'll' => '30.240320,53.518235',   // Долгота,Широта (именно в таком порядке!)
        'spn' => '0.54,0.27',            // Ширина (долгота), Высота (широта)
        
        // 👇 Включаем строгие границы
        'strict_bounds' => 1,
        
        'results' => 10,

    ]);

    \Log::info($response);

    if ($response->failed()) {
        return response()->json(['error' => 'Ошибка при получении подсказок', 'status' => $response->status()], $response->status());
    }

    return $response->json();
});





