<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket; // Не забудьте этот импорт!
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with('hall')->where('start_time', '>', now())->get();
        return view('events.index', compact('events'));
    }

    public function show(Event $event)
    {
        // Подгружаем зал и места
        $event->load(['hall.seats', 'ticketTypes']);

        // Находим ID занятых мест для этого события
        // (Ищем билеты на это событие, у которых прописан seat_id)
        $occupiedSeatIds = Ticket::query()
            ->whereHas('ticketType', function($query) use ($event) {
                $query->where('event_id', $event->id);
            })
            ->whereNotNull('seat_id')
            ->pluck('seat_id')
            ->toArray();

        return view('events.show', compact('event', 'occupiedSeatIds'));
    }
}