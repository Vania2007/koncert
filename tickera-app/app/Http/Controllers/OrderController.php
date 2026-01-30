<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // ... валидация ...
        $validated = $request->validate([/*...*/]);
        $sessionId = session()->getId();

        try {
            $order = DB::transaction(function () use ($validated, $totalAmount, $price, $ticketType, $event, $userEmail, $sessionId) {

                // 1. Финальная проверка: Эти места ДЕЙСТВИТЕЛЬНО забронированы МНОЙ?
                // Или они свободны (если вдруг успел проскочить), но НЕ заняты никем другим?

                // Проверяем, не купили ли их (Ticket)
                $isSold = Ticket::whereIn('seat_id', $validated['seats'])
                    ->whereHas('ticketType', fn($q) => $q->where('event_id', $event->id))
                    ->exists();

                if ($isSold) {
                    throw new \Exception('Кто-то уже купил эти билеты!');
                }

                // Проверяем, не заблокированы ли они ЧУЖИМИ людьми
                $lockedByOthers = \App\Models\SeatLock::where('event_id', $event->id)
                    ->whereIn('seat_id', $validated['seats'])
                    ->where('session_id', '!=', $sessionId) // Чужая сессия
                    ->where('expires_at', '>', now())
                    ->exists();

                if ($lockedByOthers) {
                    throw new \Exception('Места временно забронированы другим покупателем.');
                }

                // 2. Создаем заказ
                $order = Order::create([/*...*/]);

                // 3. Создаем билеты
                foreach ($validated['seats'] as $seatId) {
                    Ticket::create([/*...*/]);
                }

                // 4. ВАЖНО: Удаляем мои блокировки для этих мест, так как они теперь куплены
                \App\Models\SeatLock::where('event_id', $event->id)
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
