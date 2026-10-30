<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'message','type','is_active'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Непрочитанные админом сообщения пользователей. Признак
    // прочтения - is_active: 0, пока диспетчер не открыл диалог
    // (MessengerController помечает сообщения прочитанными);
    // у сообщений админа та же колонка работает в обратную
    // сторону - прочтение пользователем в приложениях.
    public function scopeUnreadForAdmin($query)
    {
        return $query->where('type', 'user')->where('is_active', 0);
    }
}
