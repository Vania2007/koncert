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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            // Связи: Билет принадлежит Заказу и Типу билета
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticket_type_id')->constrained();

            // QR и Проверка
            $table->string('unique_code')->unique(); // Уникальный код (хеш/UUID), который зашьем в QR

            $table->boolean('is_checked_in')->default(false); // Зашел ли человек внутрь?
            $table->dateTime('checked_in_at')->nullable(); // Когда именно зашел

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
