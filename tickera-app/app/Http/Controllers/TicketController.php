<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use ZipArchive;
use Illuminate\Support\Facades\File;

class TicketController extends Controller
{
    public function downloadOrder(Order $order)
    {
        // Подгружаем билеты с событиями
        $order->load(['tickets.ticketType.event']);
        $tickets = $order->tickets;

        if ($tickets->isEmpty()) {
            abort(404);
        }

        // 1. Генерируем правильное имя файла
        // Берем первое событие (предполагаем, что в заказе билеты на одно событие)
        $event = $tickets->first()->ticketType->event;
        $filename = $this->generateFilename($order->customer_name, $event);

        // 2. Если билет ОДИН — просто отдаем PDF
        if ($tickets->count() === 1) {
            $pdf = $this->generatePdf($tickets->first());
            return $pdf->download("{$filename}.pdf");
        }

        // 3. Если билетов МНОГО — делаем ZIP
        $zipFileName = "{$filename}.zip";
        $zipPath = storage_path("app/public/{$zipFileName}");
        
        // Убедимся, что папка существует
        if (!File::exists(dirname($zipPath))) {
            File::makeDirectory(dirname($zipPath), 0755, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($tickets as $index => $ticket) {
                $pdf = $this->generatePdf($ticket);
                // Имя файла внутри архива: bilet-1.pdf, bilet-2.pdf
                $zip->addFromString("ticket-" . ($index + 1) . ".pdf", $pdf->output());
            }
            $zip->close();
        }

        // Отдаем архив и удаляем его после отправки
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    // Вспомогательный метод генерации PDF
    private function generatePdf($ticket)
    {
        $qrCode = QrCode::size(200)->generate($ticket->unique_code);
        return Pdf::loadView('tickets.pdf', compact('ticket', 'qrCode'));
    }

    // Вспомогательный метод для имени файла
    private function generateFilename($customerName, $event)
    {
        // Транслитерация имени (Иван Иванов -> Ivan-Ivanov)
        $transliteratedName = Str::slug(Str::transliterate($customerName));

        // Дата: День-Месяц (28-01)
        $date = \Carbon\Carbon::parse($event->start_time)->format('d-m');

        // ID концерта: 4 цифры (0001)
        $eventId = sprintf('%04d', $event->id);

        // Собираем: Ivan-Ivanov-28-01-0001
        return "{$transliteratedName}-{$date}-{$eventId}";
    }
}