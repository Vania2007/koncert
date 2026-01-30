<?php

namespace App\Filament\Pages;

use App\Models\Ticket;
use Filament\Pages\Page;
use BackedEnum;
use Illuminate\Support\Str;

class ScanTickets extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $navigationLabel = 'Сканер билетов';
    protected static ?string $title = 'Контроль входа';

    protected string $view = 'filament.pages.scan-tickets';

    public function checkTicket($result)
    {
        // 1. Извлекаем строку из результата (если это объект/массив от сканера)
        $code = is_array($result) ? ($result['data'] ?? ($result[0] ?? '')) : $result;
        $code = trim((string) $code);

        // 2. Если сканировали полную ссылку (например, сайт.ком/ticket/UUID)
        // Мы пытаемся взять только последнюю часть (сам ID)
        if (filter_var($code, FILTER_VALIDATE_URL)) {
            $code = basename(parse_url($code, PHP_URL_PATH));
        }

        // 3. Поиск билета
        $ticket = Ticket::with('ticketType.event')->where('unique_code', $code)->first();

        if (!$ticket) {
            // Теперь $code — это строка, и ошибки "Array to string" не будет
            $this->sendResult('error', 'БИЛЕТ НЕ НАЙДЕН', "Код: " . Str::limit($code, 20));
            return;
        }

        // 4. Проверка статуса
        if ($ticket->is_checked_in) {
            $time = $ticket->checked_in_at ? $ticket->checked_in_at->format('H:i') : '??';
            $this->sendResult('error', 'УЖЕ ИСПОЛЬЗОВАН', "Вход: $time. Место: " . ($ticket->seat?->number ?? 'н/д'));
            return;
        }

        // 5. Успешный вход
        $ticket->update([
            'is_checked_in' => true,
            'checked_in_at' => now(),
        ]);

        $this->sendResult(
            'success', 
            'УСПЕШНО', 
            "{$ticket->ticketType->name} | Ряд {$ticket->seat?->row} Место {$ticket->seat?->number}"
        );
    }

    protected function sendResult($status, $title, $body)
    {
        $this->dispatch('scan-finished', 
            status: $status, 
            title: $title, 
            body: $body
        );
    }
}