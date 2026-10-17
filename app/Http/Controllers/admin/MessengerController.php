<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Message;
use App\Models\User;
use App\Events\MessageSent;

class MessengerController extends Controller
{
    public function index() {
    
        $users = User::where('role','driving')->get();

        $usersWithMessages = User::where('role', 'driving')->whereHas('messages')
            ->with(['messages' => function ($query) {
                $query->latest()->first();
            }])
            ->get();

        $sortedUsersWithMessages = $usersWithMessages->sortByDesc(function ($user) {
            return optional($user->messages->last())->created_at;
        });

        $usersWithoutMessages = $users->diff($usersWithMessages);

        $sortedUsers = $sortedUsersWithMessages->merge($usersWithoutMessages);

        return view('messenger.index', ['users' => $sortedUsers]);
    }

    public function readMessageUser(Request $request) {
    
        $user = User::find($request->userId);

        $messages2 = Message::where([['user_id',$user->id],['type','user'],['is_active',0]])->update([
            'is_active' => 1,
        ]);
        
        return response()->json([
            'status' =>'true',
        ]);

    }

    

    public function getMessagesUser(Request $request) {
    
        $user = User::find($request->userId);

        $messages2 = Message::where([['user_id',$user->id],['type','user'],['is_active',0]])->update([
            'is_active' => 1,
        ]);
        
        $messages = Message::where('user_id',$user->id)->orderBy('created_at', 'asc')->get();

        $htmlMessage = view('messenger.dialog', ['userMessage' => $user,'messagesUser' => $messages])->render();

        return response()->json([
            'htmlMessage' =>$htmlMessage,
        ]);

    }

    public function addNewMessageUser(Request $request) {
    
        $user = User::find($request->userId);

        $message = Message::create([
            'user_id' => $user->id,
            'message' => $request->message,
            'type' => 'admin',
        ]);

        if($message){
            broadcast(new MessageSent($message));

            $htmlMessage = view('messenger.messageUser', ['message' => $message])->render();
    
            return response()->json([
                'status'=>'true',
                'htmlMessage' =>$htmlMessage,
            ]);
        }else{
            return response()->json([
                'status'=>'false',
            ]);
        }
    }


    
}
