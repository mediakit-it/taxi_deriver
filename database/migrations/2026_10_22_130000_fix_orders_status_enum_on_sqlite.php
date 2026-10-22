<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The 2026_10_17 enum extension only alters MySQL, assuming SQLite
     * stores enums as plain strings. It does not: Laravel creates a
     * CHECK constraint there, so on SQLite (the test database) orders
     * cannot be saved in searching / searching_other / on_place /
     * in_progress at all. Rebuild the column on SQLite with the full
     * status list; MySQL keeps its production ENUM untouched.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'searching',
                'searching_other',
                'assigned',
                'on_place',
                'in_progress',
                'completed',
                'canceled',
            ])->default('pending')->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'assigned',
                'completed',
                'canceled',
            ])->default('pending')->change();
        });
    }
};
