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
    Schema::create('events', function (Blueprint $table) {
        $table->id();
        $table->string('title');                    // Название концерта/события
        $table->text('description')->nullable();    // Описание (можно пустое)
        $table->string('location');                 // Локация (адрес)
        $table->dateTime('start_time');             // Время начала
        $table->dateTime('end_time')->nullable();   // Время конца
        $table->timestamps();                       // Поля created_at и updated_at
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
