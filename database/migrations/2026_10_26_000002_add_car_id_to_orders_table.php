<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Автомобиль, с чьего устройства выполняется заказ. Водительское
// приложение привязывается к конкретному авто при установке (пароль
// привязки в настройках), дальше устройство всегда работает как это
// авто и шлёт его car_id при принятии заказа и старте поездки.
// null - заказ едет без привязки к авто (создан вручную, приложение
// без привязки и т.п.).
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('car_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['car_id']);
            $table->dropColumn('car_id');
        });
    }
};
