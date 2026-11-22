<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Уход водителя с линии (пакет №160): приложение уже шлёт в каждом
// пинге геопозиции флаг is_online, но хранить его было негде, и бэк
// его игнорировал - статус работы выводился только из свежести
// пинга. Default true: существующие строки и старые версии
// приложения поведение не меняют.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_locations', function (Blueprint $table) {
            $table->boolean('is_online')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('driver_locations', function (Blueprint $table) {
            $table->dropColumn('is_online');
        });
    }
};
