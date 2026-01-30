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
    Schema::create('seat_locks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('event_id')->constrained()->cascadeOnDelete();
        $table->foreignId('seat_id')->constrained()->cascadeOnDelete();
        $table->string('session_id')->index(); // Идентификатор сессии пользователя
        $table->timestamp('expires_at'); // Когда бронь сгорает
        $table->timestamps();

        // Уникальность: одно место на событии не может быть заблокировано дважды одновременно
        // (но мы будем программно проверять время expires_at)
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seat_locks');
    }
};
