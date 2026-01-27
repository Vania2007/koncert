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
    Schema::create('ticket_types', function (Blueprint $table) {
        $table->id();
        // Связь с событием. Если событие удалят, удалятся и типы билетов (cascade)
        $table->foreignId('event_id')->constrained()->onDelete('cascade');
        
        $table->string('name');                 // Название (VIP, Fan-zone)
        $table->decimal('price', 10, 2);        // Цена (10 цифр всего, 2 после запятой)
        $table->integer('quantity');            // Общее количество мест этого типа
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};
