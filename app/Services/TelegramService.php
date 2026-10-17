<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $token;
    protected $chatId;

    public function __construct()
    {
        $this->token = env('TELEGRAM_BOT_TOKEN');
        $this->chatId = env('TELEGRAM_CHAT_ID');
    }

    public function sendMessage($text)
    {
        if (!$this->token || !$this->chatId) {
            Log::error('Telegram: token или chat_id не настроены');
            return false;
        }

        $url = "https://api.telegram.org/bot{$this->token}/sendMessage";

        $response = Http::post($url, [
            'chat_id' => $this->chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);

        if ($response->failed()) {
            Log::error('Telegram: ошибка отправки', ['response' => $response->body()]);
            return false;
        }

        return true;
    }
}