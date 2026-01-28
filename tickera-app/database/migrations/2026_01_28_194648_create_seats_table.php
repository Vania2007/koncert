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
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained()->cascadeOnDelete();
            
            $table->string('section')->nullable(); // Сектор (Партер, Балкон, VIP)
            $table->integer('row')->nullable();    // Ряд
            $table->integer('number');             // Номер места
            
            // Координаты для карты (x, y). Пока 0, но пригодятся для отрисовки
            $table->integer('x')->default(0);
            $table->integer('y')->default(0);
            
            $table->timestamps();
            
            // Индекс, чтобы быстро искать места в зале
            $table->index(['hall_id', 'section', 'row']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
