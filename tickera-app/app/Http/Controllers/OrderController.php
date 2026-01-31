<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Order;
use App\Models\SeatLock;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // 1. Валидация данных покупателя
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'seats' => 'required|array|min:1',
            'seats.*' => 'exists:seats,id',
            'customer_name' => 'required|string|min:2',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string|min:10', // Для Телеграма
        ]);

        $event = Event::findOrFail($validated['event_id']);
        $ticketType = $event->ticketTypes->first();

        if (!$ticketType) {
            return back()->with('error', 'Цены не настроены');
        }

        $price = $ticketType->price;
        $totalAmount = $price * count($validated['seats']);
        $sessionId = session()->getId();

        // 2. Имитация процесса оплаты (Эквайринг)
        // В реальном проекте тут был бы запрос к API банка
        sleep(2); // Задержка 2 секунды для реализма

        try {
            $order = DB::transaction(function () use ($validated, $totalAmount, $price, $ticketType, $event, $sessionId) {

                // Проверки (не занято ли)
                $isSold = Ticket::whereIn('seat_id', $validated['seats'])
                    ->whereHas('ticketType', fn($q) => $q->where('event_id', $event->id))
                    ->exists();

                if ($isSold) {
                    throw new \Exception('Билеты уже куплены кем-то другим.');
                }

                $lockedByOthers = SeatLock::where('event_id', $event->id)
                    ->whereIn('seat_id', $validated['seats'])
                    ->where('session_id', '!=', $sessionId)
                    ->where('expires_at', '>', now())
                    ->exists();

                if ($lockedByOthers) {
                    throw new \Exception('Места заняты другим покупателем.');
                }

                // Создаем заказ с реальными данными
                $order = Order::create([
                    'customer_email' => $validated['customer_email'],
                    'customer_name' => $validated['customer_name'],
                    'customer_phone' => $validated['customer_phone'], // Записываем телефон
                    'total_amount' => $totalAmount,
                    'status' => 'paid',
                    'payment_id' => 'TEST-' . strtoupper(Str::random(10)), // Фейковый ID транзакции
                ]);

                // Создаем билеты
                foreach ($validated['seats'] as $seatId) {
                    Ticket::create([
                        'order_id' => $order->id,
                        'ticket_type_id' => $ticketType->id,
                        'seat_id' => $seatId,
                        'unique_code' => (string) Str::uuid(),
                        'is_checked_in' => false,
                    ]);
                }

                // Удаляем блокировки
                SeatLock::where('event_id', $event->id)
                    ->whereIn('seat_id', $validated['seats'])
                    ->where('session_id', $sessionId)
                    ->delete();

                return $order;
            });

            return redirect()->route('order.success', $order);

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function success(Order $order)
    {
        return view('orders.success', compact('order'));
    }
}
