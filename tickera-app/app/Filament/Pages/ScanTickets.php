<?php

namespace App\Filament\Pages;

use App\Models\Ticket;
use Filament\Pages\Page;
use BackedEnum;

class ScanTickets extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $navigationLabel = 'Сканер билетов';
    protected static ?string $title = 'Контроль входа';

    // Обычное свойство, не статическое
    protected string $view = 'filament.pages.scan-tickets';

    public function checkTicket($code)
    {
        // 1. Поиск билета
        $ticket = Ticket::with('ticketType')->where('unique_code', $code)->first();

        if (!$ticket) {
            $this->sendResult('error', 'БИЛЕТ НЕ НАЙДЕН', "Код: $code");
            return;
        }

        // 2. Проверка статуса
        if ($ticket->is_checked_in) {
            $time = $ticket->checked_in_at ? $ticket->checked_in_at->format('H:i:s') : '??';
            $this->sendResult('error', 'УЖЕ ИСПОЛЬЗОВАН', "Вход был в $time");
            return;
        }

        // 3. Успешный вход
        $ticket->update([
            'is_checked_in' => true,
            'checked_in_at' => now(),
        ]);

        $this->sendResult(
            'success', 
            'УСПЕШНО', 
            "{$ticket->ticketType->name}"
        );
    }

    // Вспомогательный метод для отправки ответа в JS
    protected function sendResult($status, $title, $body)
    {
        $this->dispatch('scan-finished', 
            status: $status, 
            title: $title, 
            body: $body
        );
    }
}