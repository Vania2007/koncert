<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket; // Не забудьте этот импорт!

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with('hall')->where('start_time', '>', now())->get();
        return view('events.index', compact('events'));
    }

    public function show(Event $event)
    {
        $event->load('hall.seats');

        // 1. ID мест, на которые уже есть БИЛЕТЫ (продано)
        $soldSeatIds = Ticket::whereHas('ticketType', function ($query) use ($event) {
            $query->where('event_id', $event->id);
        })->pluck('seat_id')->toArray();

        // 2. ID мест, которые ЗАБЛОКИРОВАНЫ (другими людьми) прямо сейчас
        $lockedSeatIds = \App\Models\SeatLock::where('event_id', $event->id)
            ->where('expires_at', '>', now()) // Бронь еще действует
            ->where('session_id', '!=', session()->getId()) // И это НЕ моя бронь
            ->pluck('seat_id')
            ->toArray();

        // Объединяем оба массива — эти места рисовать серым
        $occupiedSeatIds = array_unique(array_merge($soldSeatIds, $lockedSeatIds));

        // 3. Места, которые выбрал ТЕКУЩИЙ пользователь (чтобы при F5 они остались зелеными)
        $mySelectedSeats = \App\Models\SeatLock::where('event_id', $event->id)
            ->where('expires_at', '>', now())
            ->where('session_id', session()->getId())
            ->pluck('seat_id')
            ->toArray();

        return view('events.show', compact('event', 'occupiedSeatIds', 'mySelectedSeats'));
    }
}
