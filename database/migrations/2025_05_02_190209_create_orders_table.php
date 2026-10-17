<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Клиент
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('client_first_name');
            $table->string('client_last_name');
            $table->string('client_phone');

            // Водитель (опционально при назначении)
            $table->unsignedBigInteger('driver_id')->nullable();

            // Адрес откуда и координаты
            $table->string('from_address');
            $table->decimal('from_lat', 10, 6)->nullable();
            $table->decimal('from_lng', 10, 6)->nullable();

            // Адрес куда и координаты
            $table->string('to_address');
            $table->decimal('to_lat', 10, 6)->nullable();
            $table->decimal('to_lng', 10, 6)->nullable();

            // Время и расстояние до клиента
            $table->integer('distance_to_client')->nullable(); // в метрах
            $table->integer('time_to_client')->nullable();     // в секундах

            // Время и расстояние маршрута
            $table->integer('distance')->nullable(); // в метрах
            $table->integer('duration')->nullable(); // в секундах маршрут сколько составил 

            // Цена и комментарий
            $table->decimal('price', 8, 2)->nullable();
            $table->text('comment')->nullable();

            // Статус заказа
            $table->enum('status', ['pending', 'assigned', 'completed', 'canceled'])->default('pending');

            $table->timestamps();

            // Внешние ключи (если таблицы users, drivers существуют)
            $table->foreign('client_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
