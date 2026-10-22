<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Класс обслуживания заказа: диспетчер выбирает тариф при создании и
// редактировании заказа в админке. Хранится машинное имя класса
// (cost_of_trip.name: City, Outside_the_City, ...), человекочитаемая
// подпись берётся из cost_of_trip.display_name. Колонка nullable:
// у заказов из приложения и старых заказов класса может не быть.
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('tariff')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('tariff');
        });
    }
};
