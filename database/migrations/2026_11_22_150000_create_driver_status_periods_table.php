<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Периоды работы водителей (пакет №162): каждый переход «на линии /
// не на линии» открывает период, следующий переход его закрывает.
// По открытому периоду админка показывает, как давно водитель в
// текущем статусе, а по закрытым дальше собирается статистика смен.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_status_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'ended_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_status_periods');
    }
};
