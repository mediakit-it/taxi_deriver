<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Момент, когда заказ был предложен текущему водителю (диспетчер
// назначил конкретного водителя в админке). Нужен сторожу ответов
// водителей (orders:watch-driver-response): если водитель не
// отреагировал за driver_accept_seconds из настроек, диспетчеры
// получают сигнал в админку. null - заказ никому персонально не
// предлагался либо водитель уже снят с заказа.
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('driver_offered_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('driver_offered_at');
        });
    }
};
