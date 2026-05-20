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
        Schema::table('carts', function (Blueprint $table) {
            // Додаємо session_id після user_id
            $table->string('session_id')->nullable()->after('user_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            // Безпечний відкат: видаляємо індекс і колонку, якщо будемо робити migrate:rollback
            $table->dropIndex(['session_id']);
            $table->dropColumn('session_id');
        });
    }
};