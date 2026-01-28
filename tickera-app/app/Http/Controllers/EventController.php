<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    // Главная страница: список всех событий
    public function index()
    {
        // Берем все события, сортируем по дате начала
        $events = Event::orderBy('start_time', 'asc')->get();
        
        return view('events.index', compact('events'));
    }

    // Страница конкретного события (где покупка билетов)
    public function show($id)
    {
        // Ищем событие по ID и сразу подгружаем типы билетов
        $event = Event::with('ticketTypes')->findOrFail($id);
        
        return view('events.show', compact('event'));
    }
}