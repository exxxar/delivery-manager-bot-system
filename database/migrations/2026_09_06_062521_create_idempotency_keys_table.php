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
        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique()->index();     // сам токен
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('endpoint', 255);                  // какой метод вызван
            $table->unsignedSmallInteger('status_code');      // код ответа
            $table->longText('response_body')->nullable();    // сериализованный ответ
            $table->timestamp('expires_at');                  // когда токен истечёт
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idempotency_keys');
    }
};
