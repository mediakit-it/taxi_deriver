<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\YandexController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Broadcast;

use App\Http\Controllers\admin\DriverLocationController;
use App\Http\Controllers\admin\DispatcherAlertController;
use App\Http\Controllers\admin\CostOfTripController;
use App\Http\Controllers\admin\SettingController;
use App\Http\Controllers\admin\FunctionalSettingsController;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\DriverController;
use App\Http\Controllers\admin\PolicyDocumentController;
use App\Http\Controllers\admin\AppDeletionStepController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::post('/pusher/auth', function (Illuminate\Http\Request $request) {
    return Broadcast::auth($request);
});

Route::get('/', function () {
    return view('site.html.index');
});


Route::get('/user-agreement', function () {
    return view('site.html.user_agreement');
});

Route::get('/politika-konfidencialinosti', function () {
    return view('site.html.politika_konfidencialinosti');
});

Route::get('/manual-action', function () {
    return view('site.html.manual_action');
});




Route::middleware(['guest:web'])->group(function () {







    //страница входа
    Route::get('/authorization', 'App\Http\Controllers\admin\AuthController@index')->name('login');
    //страница авторизации
    Route::post('/login_auth', 'App\Http\Controllers\admin\AuthController@login')->name('login_auth');
});

Route::middleware(['auth:web'])->group(function () {




    Route::get('/logout', 'App\Http\Controllers\admin\AuthController@logout')->name('logout');




});

Route::middleware(['auth:web', 'admin'])->prefix('admin')->group(function () {
    // Рабочий набор всех ролей панели (admin, superadmin, manager,
    // dispatcher): дашборд, заказы, водители, мессенджер, карта,
    // список авто для просмотра. Middleware 'admin' пускает только
    // роли панели (User::PANEL_ROLES).
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/drivers', [DriverController::class, 'index'])->name('drivers.index');

    Route::get('mainPage', 'App\Http\Controllers\admin\MainPageController@index')->name('mainPage');

    Route::resource('order', 'App\Http\Controllers\admin\OrderController');
    Route::get('orderAjax', 'App\Http\Controllers\admin\OrderController@indexAjax')->name('orderAjax');

    Route::get('messenger', 'App\Http\Controllers\admin\MessengerController@index')->name('messenger');
    Route::post('getMessagesUser', 'App\Http\Controllers\admin\MessengerController@getMessagesUser');
    Route::post('addNewMessageUser', 'App\Http\Controllers\admin\MessengerController@addNewMessageUser');
    Route::post('readMessageUser', 'App\Http\Controllers\admin\MessengerController@readMessageUser');

    Route::get('/driver-locations', [DriverLocationController::class, 'index'])->name('admin.driver-locations.index');
    Route::get('/data/driver-locations', [DriverLocationController::class, 'getAllDriverLocations'])->name('admin.driver-locations.api');

    // API для панели диспетчерских сигналов: заказы без ответа водителя
    Route::get('/data/overdue-orders', [DispatcherAlertController::class, 'overdue'])->name('admin.dispatcher-alerts.overdue');

    Route::get('/yandex-suggest', [YandexController::class, 'suggest']);

    Route::get('proxy/yandex-suggest', function () {
        $suggestKey = config('services.yandex.suggest_key');
        if (!$suggestKey) {
            return response()->json(['error' => 'Suggest service is not configured'], 503);
        }

        $query = request('text');
        $url = 'https://api-maps.yandex.ru/2.1';
        $response = Http::get($url, [
            'text' => $query,
            'suggest_apikey' => $suggestKey,
            'apikey' => config('services.yandex.js_key'),
            'lang' => 'ru_RU',
        ]);
        return response()->json($response->json());
    });

    // Авто: диспетчеру - только просмотр списка
    Route::resource('car', 'App\Http\Controllers\admin\CarController')->only(['index']);

    // Управление (диспетчеру недоступно): пользователи, изменения
    // авто, тарифы и все настройки, включая документы и шаги удаления
    Route::middleware('role:admin,superadmin,manager')->group(function () {
        Route::resource('user', 'App\Http\Controllers\admin\UserController');
        Route::resource('car', 'App\Http\Controllers\admin\CarController')->except(['index']);

        Route::get('/cost-of-trip', [CostOfTripController::class, 'edit'])->name('cost-of-trip.edit');
        Route::put('/cost-of-trip', [CostOfTripController::class, 'update'])->name('cost-of-trip.update');

        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/documents', [PolicyDocumentController::class, 'store'])->name('settings.documents.store');
        Route::delete('/settings/documents/{document}', [PolicyDocumentController::class, 'destroy'])->name('settings.documents.destroy');
        Route::post('/settings/steps', [AppDeletionStepController::class, 'store'])->name('settings.steps.store');
        Route::put('/settings/steps/{step}', [AppDeletionStepController::class, 'update'])->name('settings.steps.update');
        Route::delete('/settings/steps/{step}', [AppDeletionStepController::class, 'destroy'])->name('settings.steps.destroy');
    });

    // Раздел «Функционал»: только суперадмин нашей компании.
    // Страница раздела - карточка на странице настроек, сохранение -
    // отдельный роут, закрытый на уровне маршрутов и FormRequest
    Route::middleware('role:superadmin')->group(function () {
        Route::put('/settings/functional', [FunctionalSettingsController::class, 'update'])->name('settings.functional.update');
    });
});

