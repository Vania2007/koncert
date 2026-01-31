<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\SeatLock;
use App\Models\Ticket;

class EventController extends Controller
{
    // Главная: Афиша
    public function index()
    {
        $events = Event::with(['hall', 'ticketTypes'])
            ->where('start_time', '>', now())
            ->orderBy('start_time', 'asc')
            ->get();

        return view('events.index', compact('events'));
    }

    // Лендинг события
    public function show(Event $event)
    {
        $event->load(['hall', 'ticketTypes']);
        return view('events.show', compact('event'));
    }

    // Выбор мест (Схема)
    public function selectSeats(Event $event)
    {
        $event->load('hall.seats');
        $sessionId = session()->getId();

        // 1. Занятые места
        $soldSeatIds = Ticket::whereHas('ticketType', fn($q) => $q->where('event_id', $event->id))->pluck('seat_id')->toArray();
        $lockedSeatIds = SeatLock::where('event_id', $event->id)->where('expires_at', '>', now())->where('session_id', '!=', $sessionId)->pluck('seat_id')->toArray();
        $occupiedSeatIds = array_unique(array_merge($soldSeatIds, $lockedSeatIds));

        // 2. Мои брони
        $myLocks = SeatLock::where('event_id', $event->id)->where('expires_at', '>', now())->where('session_id', $sessionId)->get();
        $mySelectedSeats = $myLocks->pluck('seat_id')->toArray();
        $bookingExpiresAt = $myLocks->max('expires_at')?->timestamp;

        // ВАЖНО: view называется events.select-seats
        return view('events.select-seats', compact('event', 'occupiedSeatIds', 'mySelectedSeats', 'bookingExpiresAt'));
    }
}
