<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\api\OrderController;
use App\Http\Controllers\api\LogisticController;
use App\Http\Controllers\api\CostOfTripController;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::prefix('v1')->group(function () {
    Route::get('/cost-of-trip', [CostOfTripController::class, 'index']);
    Route::get('/cost-of-trip/{name}', [CostOfTripController::class, 'getByName']);
    Route::get('/cost-of-trip/simple', [CostOfTripController::class, 'getSimple']);
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

Route::group(['middleware' => ['auth:api']], function () {
    Route::post('/check-token-validity', '\App\Http\Controllers\AuthController@checkTokenValidity');
    // Route::get('/shops', '\App\Http\Controllers\ShopController@index');

    // Route::get('/categoryes', '\App\Http\Controllers\CategoryController@index');


    // Route::get('/products-main', '\App\Http\Controllers\ProductController@indexMain');
    // Route::get('/product/{slug}', '\App\Http\Controllers\ProductController@getProduct');//получение одного продукта





    Route::post('/updateProfile', '\App\Http\Controllers\ProfileController@updateProfile');

    Route::post('/updatePassword', '\App\Http\Controllers\ProfileController@updatePassword');

    Route::post('/deleteProfile', '\App\Http\Controllers\ProfileController@deleteProfile');
    // Route::post('/deleteAddress', '\App\Http\Controllers\ProfileController@deleteAddress');//---

    // Route::post('/newReviews', '\App\Http\Controllers\ReviewController@newReviews');


    // Route::get('/getAllProduct', '\App\Http\Controllers\ProductController@getAllProduct');
    // Route::get('/getAllProductCategory/{slug}', '\App\Http\Controllers\ProductController@getAllProductCategory');
    // Route::get('/searchProduct', '\App\Http\Controllers\ProductController@searchProduct');
    // Route::get('/searchViewProduct', '\App\Http\Controllers\ProductController@searchViewProduct');

    // Route::get('/productsAndBrandInCategory', '\App\Http\Controllers\ProductController@productsAndBrandInCategory');
    // Route::get('/getMetkasInProduct', '\App\Http\Controllers\ProductController@metkasInProduct');//Получение на главной странице меток с продуктами

    // //получение брендов
    // Route::get('/getBrandsCount', '\App\Http\Controllers\BrandController@getBrandsCount');
    // Route::get('/getBrandsAll', '\App\Http\Controllers\BrandController@getBrandsAll');
    // Route::get('/getAllProductBrand/{slug}', '\App\Http\Controllers\BrandController@getAllProductBrand');
    
    
    // //Работа с акциями
    // Route::get('/getActionAll', '\App\Http\Controllers\DiscountController@getActionAll');//Получение всех акций


    // //Работа с адресами пользователя
    // Route::get('/getUserAddressAll', '\App\Http\Controllers\AddressController@getUserAddressAll');//получение всех адресов пользователя
    // Route::get('/getUserAddressCountry', '\App\Http\Controllers\AddressController@getUserAddressCountry');//получение всех адресов пользователя его выбранной страны
    // Route::post('/addNewUserAddres', '\App\Http\Controllers\AddressController@addNewUserAddres');//создание нового адреса пользователя
    // Route::post('/editUserAddress', '\App\Http\Controllers\AddressController@editUserAddress');//изменение адреса пользователя
    

    // Route::post('/addCartUser', '\App\Http\Controllers\CartController@addCartUser');
    // Route::get('/getCartsUser', '\App\Http\Controllers\CartController@getCartsUser');
    // Route::get('/getCountCartUser', '\App\Http\Controllers\CartController@getCountCartUser');
    // Route::post('/cartUserPlus', '\App\Http\Controllers\CartController@cartUserPlus');//Увеличение количества
    // Route::post('/cartUserMinus', '\App\Http\Controllers\CartController@cartUserMinus');//Уменьшение количества

    // //Создание заказа
    // Route::post('/newOrder', '\App\Http\Controllers\BasketController@newOrder');
    // Route::get('/getOrdersUser', '\App\Http\Controllers\BasketController@getOrdersUser');


    // //Получение баннеров на главной 
    // Route::get('/getBanners', '\App\Http\Controllers\BanerController@getBanners');


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

    $response = Http::get('https://suggest-maps.yandex.ru/v1/suggest', [
        'apikey' => '97973d7d-a7b7-4674-8334-531a0dddb696',
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




// Route::apiResource('institutions','\App\Http\Controllers\API\InstitutionController')->middleware('auth:api');
