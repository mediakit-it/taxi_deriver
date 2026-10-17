<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\YandexController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Broadcast;

use App\Http\Controllers\admin\DriverLocationController;
use App\Http\Controllers\admin\CostOfTripController;



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
    Route::get('/dashboard', function () {

        return view('admin.index');
    })->name('dashboard');

    Route::get('/cost-of-trip', [CostOfTripController::class, 'edit'])->name('cost-of-trip.edit');
    Route::put('/cost-of-trip', [CostOfTripController::class, 'update'])->name('cost-of-trip.update');

    Route::resource('user', 'App\Http\Controllers\admin\UserController');
    Route::resource('car', 'App\Http\Controllers\admin\CarController');
    Route::resource('order', 'App\Http\Controllers\admin\OrderController');
    Route::get('orderAjax', 'App\Http\Controllers\admin\OrderController@indexAjax')->name('orderAjax');
    Route::get('/yandex-suggest', [YandexController::class, 'suggest']);
    

    
    //     $params = [
    //         'text' => $query,
    //         'lang' => $lang,
    //         'results' => $results,
    //         'highlight' => $highlight,
    //         'apikey' => '871a8f7b-85ec-4bc7-b5cb-f336458187b6', // актуальный suggest API key
    //     ];
    
    //     if ($types) $params['types'] = $types;
    //     if ($print_address) $params['print_address'] = $print_address;
    //     if ($attrs) $params['attrs'] = $attrs;
    //     if ($ll) $params['ll'] = $ll;
    //     if ($spn) $params['spn'] = $spn;
    //     if ($bbox) $params['bbox'] = $bbox;
    
    //     $url = 'https://suggest-maps.yandex.ru/v1/suggest';
    
    //     $response = Http::get($url, $params);
    
    //     return response()->json($response->json());
    // })->name('ysndexSug');
    
    Route::get('proxy/yandex-suggest', function () {
        $query = request('text');
        // https://api-maps.yandex.ru/2.1/?lang=ru_RU&apikey=<ваш ключ для JS API>&suggest_apikey=<ваш ключ для Suggest API>
        // $url = 'https://suggest-maps.yandex.ru/v1/suggest';
        $url = 'https://api-maps.yandex.ru/2.1';
        $response = Http::get($url, [
            'text' => $query,
            'suggest_apikey'=>'871a8f7b-85ec-4bc7-b5cb-f336458187b6',
            'apikey' => 'ad38720a-e8c9-443c-b454-1f4d2499ad78',
            'lang'=>'ru_RU',
        ]);
        return response()->json($response->json());
    });
    

    //выход из аккаунта

    







    Route::get('mainPage', 'App\Http\Controllers\admin\MainPageController@index')->name('mainPage');


    Route::get('messenger', 'App\Http\Controllers\admin\MessengerController@index')->name('messenger');
    Route::post('getMessagesUser', 'App\Http\Controllers\admin\MessengerController@getMessagesUser');
    Route::post('addNewMessageUser', 'App\Http\Controllers\admin\MessengerController@addNewMessageUser');
    Route::post('readMessageUser', 'App\Http\Controllers\admin\MessengerController@readMessageUser');




    Route::get('/driver-locations', [DriverLocationController::class, 'index'])->name('admin.driver-locations.index');

    // API для получения всех локаций водителей (можно защитить middleware auth)
    Route::get('/data/driver-locations', [DriverLocationController::class, 'getAllDriverLocations'])->name('admin.driver-locations.api');





    //getMessagesUser



});

