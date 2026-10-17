<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\MessageAdminSent;
use App\Models\Message;

class MassageController extends Controller
{
    public function addNewMessage(Request $request){

        $user = Auth::user();
        if ($user) {
            $message = Message::create([
                'user_id' => $user->id,
                'message' => $request->input('message'),
                'type' => 'user',
            ]);

            if($message){
                event(new MessageAdminSent($message));
                // broadcast(new MessageAdminSent());
                return response()->json([
                    'success' => 'true',
                    'message' => $message,
                ]);
            }else{
                return response()->json([
                    'success' => 'false',
                ]);
            }
        }else{
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    
       
    }

    public function getMessageUser(){
         $user = Auth::user();

         if ($user) {
            $messages2 = Message::where([['user_id',$user->id],['type','admin'],['is_active',0]])->update([
                'is_active' => 1,
            ]);
            
            $messages = Message::where('user_id',$user->id)->get();

            return response()->json([
                'status' => 'success',
                'messages' => $messages,
            ]);

         }else{
            return response()->json(['error' => 'Unauthenticated'], 401);
         }
    }

    public function getCountMessage(){
        $user = Auth::user();

        if ($user) {
           $messages = Message::where([['user_id',$user->id],['type','admin'],['is_active',0]])->count();

            if($messages>0){
                return response()->json([
                    'success' => 'true',
                ]);
            }else{
                return response()->json([
                    'success' => 'false',
                ]);
            }

        }else{
           return response()->json(['error' => 'Unauthenticated'], 401);
        }
   }

   public function readMessageUser(Request $request) {
    
        $user = Auth::user();

        if ($user) {
        $messages2 = Message::where([['user_id',$user->id],['type','admin'],['is_active',0]])->update([
            'is_active' => 1,
        ]);
        

        return response()->json([
            'status' => 'success',
        ]);

        }else{
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

    }
}
