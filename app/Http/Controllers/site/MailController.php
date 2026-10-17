<?php

namespace App\Http\Controllers\site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Log;

class MailController extends Controller
{
    public function obratnayaMessage(Request $request){
       
        try {
            $name = $request->firstname; // имя
            $phone = $request->phone; // телефон
            $messages = $request->message; // содержание сообщения
        

            Mail::send('mails.obratnayaMessage', ['name' => $name, 'phone' => $phone, 'messages' => $messages], function($mailMessage) {
                $mailMessage->to(env('EMAIL_ADMIN'))->subject('Заказали обратную связь');
            });
            return true;
        } catch (Exception $e) {
            Log::error('почта не работает -- ' . $e->getMessage());
            return false;
        }
    }

    public function franshizaMessage(Request $request){
        try {
            $name = $request->firstname; // имя
            $city = $request->city;
            $phone = $request->telephone; // телефон
            $email = $request->email; // телефон
        

            Mail::send('mails.franshizaMessage', ['name' => $name, 'phone' => $phone, 'city' => $city, 'email' => $email], function($mailMessage) {
                $mailMessage->to(env('EMAIL_ADMIN'))->subject('Заявка на партнерство');
            });
            return true;
        } catch (Exception $e) {
            Log::error('почта не работает -- ' . $e->getMessage());
            return false;
        }
    }
}
