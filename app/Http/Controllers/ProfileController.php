<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\Address;

class ProfileController extends Controller
{
    public function updateProfile(Request $request){

        $user = Auth::user();
        \Log::info($request->all());

        if(!$user){
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

         $user->update($request->all());   


        return response()->json([
            'status' => true,
            'user' => $user,
        ]);
        
    }

    public function updatePassword(Request $request){

        $user = Auth::user();

        if($user){
            
            $user->password = Hash::make($request->input('newPassword'));
            $user->save();

            return response()->json([
                'status' => true,
                'user' => $user,
            ]);
        }else{
            // Если пользователь не аутентифицирован, возвращаем сообщение об ошибке
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }

    public function deleteProfile(Request $request)
    {

        $user = Auth::user();

        if($user){
            try {
                // Удаление пользователя
                $user->delete();
        
                // Завершение сессии
                auth()->logout();
        
                return response()->json([
                    'status' => true,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                ]);
            }
        }else{
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }

    public function deleteAddress(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            try {
                 
                $address = Address::where([
                    ['id', $request->input('id_address')],
                    ['user_id', $user->id]
                ])->first();

                if ($address) {
                    // Удаление адреса
                    $address->delete();
                    return response()->json([
                        'status' => 'success',
                    ]);
                } else {
                    // Адрес не найден
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Address not found'
                    ]);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
            }
        } else {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }

}
