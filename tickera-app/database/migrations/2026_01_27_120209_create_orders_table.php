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
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        // Данные покупателя (делаем простую версию без обязательной регистрации)
        $table->string('customer_email');
        $table->string('customer_name')->nullable();
        
        // Финансы
        $table->decimal('total_amount', 10, 2); // Итоговая сумма заказа
        $table->string('status')->default('pending'); // pending (ждет оплаты), paid (оплачен), canceled
        
        // Технические метки
        $table->string('payment_id')->nullable(); // ID транзакции от платежной системы
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
