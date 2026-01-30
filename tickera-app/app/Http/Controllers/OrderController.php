<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // 1. Валидация под нашу схему (ждем массив seats)
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'seats' => 'required|array|min:1',
            'seats.*' => 'exists:seats,id',
        ]);

        $event = Event::findOrFail($validated['event_id']);
        $ticketType = $event->ticketTypes->first();

        if (!$ticketType) {
            return back()->with('error', 'Цены для события не настроены');
        }

        $totalAmount = $ticketType->price * count($validated['seats']);
        $userEmail = auth()->user() ? auth()->user()->email : 'guest@example.com';

        try {
            $order = DB::transaction(function () use ($validated, $totalAmount, $ticketType, $event, $userEmail) {
                
                // Проверка: не заняли ли места, пока мы думали
                $isTaken = Ticket::whereIn('seat_id', $validated['seats'])
                    ->whereHas('ticketType', fn($q) => $q->where('event_id', $event->id))
                    ->exists();

                if ($isTaken) {
                    throw new \Exception('Место уже забронировано кем-то другим');
                }

                // Создаем заказ
                $order = Order::create([
                    'customer_email' => $userEmail,
                    'customer_name' => auth()->user() ? auth()->user()->name : 'Guest',
                    'total_amount' => $totalAmount,
                    'status' => 'paid',
                    'payment_id' => 'CARD-' . strtoupper(Str::random(8)),
                ]);

                // Создаем билеты
                foreach ($validated['seats'] as $seatId) {
                    Ticket::create([
                        'order_id' => $order->id,
                        'ticket_type_id' => $ticketType->id,
                        'seat_id' => $seatId, // Теперь это сработает
                        'unique_code' => (string) Str::uuid(), // ИСПРАВЛЕНО: unique_code вместо qr_code
                        'is_checked_in' => false,
                    ]);
                }

                return $order;
            });

            return redirect()->route('order.success', $order);

        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка: ' . $e->getMessage());
        }
    }

    public function success(Order $order)
    {
        return view('orders.success', compact('order'));
    }
}