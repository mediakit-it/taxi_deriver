<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Таблица cost_of_trip существовала на проде без миграции (в дампе bihov
// она есть, а миграции для неё в проекте не было). Миграция восстанавливает
// её для чистой установки. Тип price (строка) сознательно сохранён как на
// проде, включая значения вида '1,70', - перевод в число отдельной задачей.
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cost_of_trip', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_of_trip');
    }
};
