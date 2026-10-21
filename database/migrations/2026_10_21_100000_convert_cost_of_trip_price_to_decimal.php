<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Тарифы становятся классами обслуживания. Цена в cost_of_trip исторически
// хранилась строкой с запятой ('8', '1,70'), потому что так было на проде.
// Миграция переводит price в decimal(8,2) и добавляет display_name -
// человекочитаемое имя класса («По городу», «Стандарт»...), которое видит
// диспетчер. Машинное имя (name: City, Outside_the_City) НЕ трогаем -
// на него завязано мобильное приложение.
//
// Конвертация - через временную колонку и перенос данных в PHP: так она
// одинаково работает на MySQL и на SQLite и не зависит от того, как СУБД
// кастует строки вида '1,70'.
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cost_of_trip', function (Blueprint $table) {
            $table->string('display_name')->nullable();
            $table->decimal('price_decimal', 8, 2)->nullable();
        });

        foreach (DB::table('cost_of_trip')->get() as $row) {
            DB::table('cost_of_trip')
                ->where('id', $row->id)
                ->update(['price_decimal' => $this->parsePrice($row->price)]);
        }

        Schema::table('cost_of_trip', function (Blueprint $table) {
            $table->dropColumn('price');
        });

        Schema::table('cost_of_trip', function (Blueprint $table) {
            $table->renameColumn('price_decimal', 'price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Восстанавливает price как строку, но значения - с точкой
     * ('1.70' вместо исходного '1,70'): запятую назад не возвращаем,
     * откат нужен для схемы, а не для побайтового восстановления прода.
     */
    public function down(): void
    {
        Schema::table('cost_of_trip', function (Blueprint $table) {
            $table->string('price_varchar')->nullable();
        });

        foreach (DB::table('cost_of_trip')->get() as $row) {
            DB::table('cost_of_trip')
                ->where('id', $row->id)
                ->update(['price_varchar' => $row->price === null
                    ? null
                    : number_format((float) $row->price, 2, '.', '')]);
        }

        Schema::table('cost_of_trip', function (Blueprint $table) {
            $table->dropColumn('price');
        });

        Schema::table('cost_of_trip', function (Blueprint $table) {
            $table->dropColumn('display_name');
        });

        Schema::table('cost_of_trip', function (Blueprint $table) {
            $table->renameColumn('price_varchar', 'price');
        });
    }

    /**
     * Разбор legacy-цены: '1,70' -> 1.70, '8' -> 8.0, '1.70' -> 1.70,
     * пустая строка и нечисловой мусор -> null.
     */
    private function parsePrice(?string $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $normalized = str_replace(',', '.', trim($value));

        return is_numeric($normalized) ? (float) $normalized : null;
    }
};
