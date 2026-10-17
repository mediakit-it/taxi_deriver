<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Address;
use App\Models\User;

class AddressController extends Controller
{
    public function getUserAddressAll(Request $request){
        // Получаем аутентифицированного пользователя по токену
        $user = $request->user();
                
        // Если пользователь аутентифицирован, используем его страну для запроса магазинов
        if ($user) {
            $addresses = $user->addresses->makeHidden(['created_at', 'updated_at']);
            return response()->json([
                'status' => 'success',
                'addresses' => $addresses,
            ]);
        } else {
            // Если пользователь не аутентифицирован, возвращаем сообщение об ошибке
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }

    public function getUserAddressCountry(Request $request){
        // Получаем аутентифицированного пользователя по токену
        $user = $request->user();
                
        // Если пользователь аутентифицирован, используем его страну для запроса магазинов
        if ($user) {
            $addresses = $user->addresses->makeHidden(['created_at', 'updated_at']);
            return response()->json([
                'success' => 'true',
                'addresses' => $addresses,
            ]);
        } else {
            // Если пользователь не аутентифицирован, возвращаем сообщение об ошибке
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }

    public function addNewUserAddres(Request $request){
        // Получаем аутентифицированного пользователя по токену
        $user = $request->user();
                
        // Если пользователь аутентифицирован, используем его страну для запроса магазинов
        if ($user) {
            
            $address = Address::create([
                'first_name'=>$request->input('first_name'),
                'name'=>$request->input('name'),
                'phone'=>$request->input('phone'),
                'country'=>$request->input('country'),
                'region'=>$request->input('region'),
                'city'=>$request->input('city'),
                'postCode'=>$request->input('postCode'),
                'street'=>$request->input('street'),
                'home'=>$request->input('home'),
                'corpus'=>$request->input('corpus'),
                'flat'=>$request->input('flat'),
                'user_id'=>$user->id,
            ]);


            return response()->json([
                'status' => 'success',
            ]);
        } else {
            // Если пользователь не аутентифицирован, возвращаем сообщение об ошибке
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }

    public function editUserAddress(Request $request){
        // Получаем аутентифицированного пользователя по токену
        $user = $request->user();
                
        // Если пользователь аутентифицирован, используем его страну для запроса магазинов
        if ($user) {
            
            $address = Address::find($request->input('idAddress'))->update([
                'first_name'=>$request->input('first_name'),
                'name'=>$request->input('name'),
                'phone'=>$request->input('phone'),
                'country'=>$request->input('country'),
                'region'=>$request->input('region'),
                'city'=>$request->input('city'),
                'postCode'=>$request->input('postCode'),
                'street'=>$request->input('street'),
                'home'=>$request->input('home'),
                'corpus'=>$request->input('corpus'),
                'flat'=>$request->input('flat'),
            ]);


            return response()->json([
                'status' => 'success',
            ]);
        } else {
            // Если пользователь не аутентифицирован, возвращаем сообщение об ошибке
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }
}
