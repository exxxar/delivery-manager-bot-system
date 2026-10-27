<?php
// database/migrations/xxxx_make_percentage_polymorphic.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('percentages', function (Blueprint $table) {
            // 🔹 Добавляем полиморфные поля
            $table->string('percentable_type')->nullable()->after('id');
            $table->unsignedBigInteger('percentable_id')->nullable()->after('percentable_type');

            // Индекс для быстрого поиска
            $table->index(['percentable_type', 'percentable_id'], 'percentages_percentable_index');
        });

        // 🔹 Переносим существующие данные: agent_id → polymorphic
        DB::table('percentages')
            ->whereNotNull('agent_id')
            ->update([
                'percentable_type' => 'App\\Models\\Agent',
                'percentable_id' => DB::raw('agent_id'),
            ]);

        // 🔹 Делаем agent_id nullable и удаляем внешний ключ (если был)
        Schema::table('percentages', function (Blueprint $table) {
            // Удаляем внешний ключ, если он есть
            $table->dropForeign(['agent_id']);
            $table->unsignedBigInteger('agent_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('percentages', function (Blueprint $table) {
            $table->dropIndex('percentages_percentable_index');
            $table->dropColumn(['percentable_type', 'percentable_id']);
        });
    }
};
