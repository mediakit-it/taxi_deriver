<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Заказ «от борта» заводится водителем при посадке пассажира с
// улицы: зарегистрированного клиента у него нет. client_id уже
// nullable, а вот снимок клиента (имя, фамилия, телефон) и адрес
// подачи в исходной схеме NOT NULL - без клиента их честно хранить
// пустыми, а не выдумывать фиктивного пассажира. Админка эти поля
// выводит напрямую и пустое значение переносит спокойно.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('client_first_name')->nullable()->change();
            $table->string('client_last_name')->nullable()->change();
            $table->string('client_phone')->nullable()->change();
            $table->string('from_address')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('client_first_name')->nullable(false)->change();
            $table->string('client_last_name')->nullable(false)->change();
            $table->string('client_phone')->nullable(false)->change();
            $table->string('from_address')->nullable(false)->change();
        });
    }
};
