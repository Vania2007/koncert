<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\SeatLock;
use Illuminate\Http\Request;

class EventController extends Controller
{
    // Главная страница (Афиша)
    public function index()
    {
        $events = Event::with(['hall', 'ticketTypes']) // Подгружаем цены и залы
            ->where('start_time', '>', now())
            ->orderBy('start_time', 'asc')
            ->get();
            
        return view('events.index', compact('events'));
    }

    // Страница события (Описание, Фото, Кнопка)
    public function show(Event $event)
    {
        $event->load(['hall', 'ticketTypes']);
        return view('events.show', compact('event'));
    }

    // Страница выбора мест (Схема зала)
    public function selectSeats(Event $event)
    {
        $event->load('hall.seats');

        // 1. Занятые места (купленные + забронированные другими)
        $soldSeatIds = Ticket::whereHas('ticketType', fn($q) => $q->where('event_id', $event->id))
            ->pluck('seat_id')->toArray();

        $lockedSeatIds = SeatLock::where('event_id', $event->id)
            ->where('expires_at', '>', now())
            ->where('session_id', '!=', session()->getId())
            ->pluck('seat_id')->toArray();

        $occupiedSeatIds = array_unique(array_merge($soldSeatIds, $lockedSeatIds));

        // 2. Мои места
        $myLocks = SeatLock::where('event_id', $event->id)
            ->where('expires_at', '>', now())
            ->where('session_id', session()->getId())
            ->get();
            
        $mySelectedSeats = $myLocks->pluck('seat_id')->toArray();
        $bookingExpiresAt = $myLocks->max('expires_at')?->timestamp;

        // ВАЖНО: Используем view 'events.select-seats'
        return view('events.select-seats', compact('event', 'occupiedSeatIds', 'mySelectedSeats', 'bookingExpiresAt'));
    }
}