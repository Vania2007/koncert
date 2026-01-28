<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // 1. Проверяем данные
        $data = $request->validate([
            'email' => 'required|email',
            'name' => 'required|string',
            'tickets' => 'required|array', // массив ID билетов и количества
        ]);

        // 2. Считаем общую сумму и проверяем, выбрал ли пользователь хоть что-то
        $totalAmount = 0;
        $ticketsToBuy = []; // Собираем здесь, какие билеты покупать

        foreach ($data['tickets'] as $typeId => $quantity) {
            $quantity = (int)$quantity;
            if ($quantity > 0) {
                $ticketType = TicketType::find($typeId);
                
                // Простая проверка наличия мест
                if ($ticketType->quantity < $quantity) {
                    return back()->withErrors(['msg' => "Недостаточно мест для {$ticketType->name}"]);
                }

                $totalAmount += $ticketType->price * $quantity;
                $ticketsToBuy[$typeId] = $quantity; // Запоминаем ID и кол-во
            }
        }

        if ($totalAmount == 0) {
            return back(); // Ничего не выбрали — просто обновляем страницу
        }

        // 3. Создаем Заказ
        $order = Order::create([
            'customer_email' => $data['email'],
            'customer_name' => $data['name'],
            'total_amount' => $totalAmount,
            'status' => 'paid', // Сразу ставим "Оплачено" для простоты
        ]);

        // 4. Генерируем сами Билеты и списываем количество
        foreach ($ticketsToBuy as $typeId => $quantity) {
            $ticketType = TicketType::find($typeId);
            
            for ($i = 0; $i < $quantity; $i++) {
                $order->tickets()->create([
                    'ticket_type_id' => $typeId,
                    'unique_code' => Str::uuid(), // Уникальный код билета
                    'is_checked_in' => false,
                ]);
            }
            
            // Уменьшаем доступное количество
            $ticketType->decrement('quantity', $quantity);
        }

        // 5. Показываем страницу успеха
        return view('orders.success', compact('order'));
    }
}