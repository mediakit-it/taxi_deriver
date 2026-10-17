<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Прод-схема из дампа bihov содержит в orders.status восемь значений и
// константы модели Order им соответствуют, а исходная миграция создания
// таблицы orders создавала enum только из четырёх. Миграция приводит
// чистую установку к прод-схеме.
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `orders` MODIFY `status` ENUM('pending','assigned','completed','canceled','searching','searching_other','on_place','in_progress') NOT NULL DEFAULT 'pending'");
        }

        // Не MySQL (например, SQLite в тестах): enum там хранится строкой,
        // расширять нечего - сидеры рассчитаны на MySQL dev-базу.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Откат упадёт, если в таблице уже есть заказы со статусами
            // вне исходной четвёрки, - это ожидаемо и правильно.
            DB::statement("ALTER TABLE `orders` MODIFY `status` ENUM('pending','assigned','completed','canceled') NOT NULL DEFAULT 'pending'");
        }
    }
};
