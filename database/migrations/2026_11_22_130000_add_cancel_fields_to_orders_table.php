<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Отмена заказа в поездке с причиной (пакет замечаний 22.11.2026):
// водитель обязан указать причину (client_refusal / tech_problems /
// other, подписи - Order::CANCEL_REASON_LABELS) и комментарий.
// Причина и комментарий хранятся в самом заказе при любом исходе
// отмены: и когда заказ закрывается отменённым, и когда
// возвращается в поиск другого водителя. Диспетчер видит их в
// карточке заказа, а не ищет по логам.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('cancel_reason')->nullable();
            $table->text('cancel_comment')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['cancel_reason', 'cancel_comment']);
        });
    }
};
