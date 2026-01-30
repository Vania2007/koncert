<?php

namespace App\Filament\Pages;

use App\Models\Ticket;
use Filament\Pages\Page;
use Illuminate\Support\Str;
use BackedEnum; // <--- Важный импорт

class ScanTickets extends Page
{
    // 👇 ИСПРАВЛЕНО: Тип должен точно совпадать с родительским классом
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';
    
    protected static ?string $navigationLabel = 'Сканер билетов';
    protected static ?string $title = 'Контроль входа';

    protected string $view = 'filament.pages.scan-tickets';

    public function checkTicket($result)
    {
        // 1. Очистка кода
        $code = is_array($result) ? ($result['data'] ?? ($result[0] ?? '')) : $result;
        $code = trim((string) $code);

        if (filter_var($code, FILTER_VALIDATE_URL)) {
            $code = basename(parse_url($code, PHP_URL_PATH));
        }

        // 2. Поиск билета
        $ticket = Ticket::with(['ticketType.event', 'seat', 'order'])->where('unique_code', $code)->first();

        // Сценарий 1: Не найден
        if (!$ticket) {
            $this->sendResult('error', 'НЕ НАЙДЕН', 'Код не существует в базе', '❌');
            return;
        }

        // Данные для вывода
        $seatInfo = $ticket->seat 
            ? "Ряд: {$ticket->seat->row} | Место: {$ticket->seat->number}" 
            : "Входной билет";
        
        $clientName = Str::limit($ticket->order->customer_name ?? 'Гость', 20);
        $eventName = Str::limit($ticket->ticketType->event->title ?? '', 30);

        // Сценарий 2: Повторный вход
        if ($ticket->is_checked_in) {
            $time = $ticket->checked_in_at ? $ticket->checked_in_at->format('H:i') : '??';
            $this->sendResult(
                'warning', 
                'УЖЕ БЫЛ!', 
                "Вход выполнен в {$time}\n{$clientName}", 
                '⚠️'
            );
            return;
        }

        // Сценарий 3: Успех
        $ticket->update([
            'is_checked_in' => true,
            'checked_in_at' => now(),
        ]);

        $this->sendResult(
            'success', 
            'ВХОД РАЗРЕШЕН', 
            "{$clientName}\n{$seatInfo}", 
            '✅'
        );
    }

    protected function sendResult($status, $title, $body, $icon)
    {
        $this->dispatch('scan-finished', 
            status: $status, 
            title: $title, 
            body: $body,
            icon: $icon
        );
    }
}