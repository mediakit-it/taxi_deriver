<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Фотография автомобиля: загружается из админки («Машины») на
// диск public, в таблице - путь к файлу. Показывается в списке
// машин и в карточке автомобиля; дальше фото сможет подхватить
// клиентское приложение. null - фото не загружено.
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('number_plate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn('photo');
        });
    }
};
