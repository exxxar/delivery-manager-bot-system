<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->boolean("in_learning")->default(false)->after("region");
            $table->foreignId('mentor_id')->nullable()
                ->after("user_id")
                ->constrained("users");
            $table->date("start_learning_date")->nullable()->after("mentor_id")
                ->comment("начало обучения");
            $table->date("end_learning_date")->nullable()->after("start_learning_date")
                ->comment("окончание обучение");
        });

        Schema::table('users', function (Blueprint $table) {
            $table->float('mentor_percent')
                ->default(0)->after("percent");
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('work_phone')->nullable()->after("phone");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            //
        });
    }
};
