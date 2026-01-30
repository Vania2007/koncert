<?php

namespace App\Http\Controllers;

use App\Models\SeatLock;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SeatLockController extends Controller
{
    public function toggle(Request $request)
    {
        $request->validate([
            'seat_id' => 'required|integer',
            'event_id' => 'required|integer',
        ]);

        $seatId = $request->seat_id;
        $eventId = $request->event_id;
        $sessionId = session()->getId(); // Идентификатор текущего пользователя

        // 1. Проверяем, не куплено ли место уже (Hard check)
        $isSold = Ticket::whereHas('ticketType', fn($q) => $q->where('event_id', $eventId))
            ->where('seat_id', $seatId)
            ->exists();

        if ($isSold) {
            return response()->json(['status' => 'error', 'message' => 'Место уже выкуплено'], 409);
        }

        // 2. Ищем активную блокировку
        $existingLock = SeatLock::where('event_id', $eventId)
            ->where('seat_id', $seatId)
            ->where('expires_at', '>', now()) // Только актуальные
            ->first();

        // А. Если блокировка есть
        if ($existingLock) {
            // Если это моя блокировка — снимаем её (отменил выбор)
            if ($existingLock->session_id === $sessionId) {
                $existingLock->delete();
                return response()->json(['status' => 'unlocked']);
            } 
            
            // Если чужая — ошибка
            return response()->json(['status' => 'error', 'message' => 'Место занято другим пользователем'], 409);
        }

        // Б. Блокировки нет — создаем новую (на 10 минут)
        SeatLock::create([
            'event_id' => $eventId,
            'seat_id' => $seatId,
            'session_id' => $sessionId,
            'expires_at' => now()->addMinutes(10),
        ]);

        return response()->json(['status' => 'locked', 'expires_at' => now()->addMinutes(10)->timestamp]);
    }
}