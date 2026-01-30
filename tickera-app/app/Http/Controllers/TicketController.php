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
        // Подгружаем билеты со всеми нужными связями (событие, место)
        $order->load(['tickets.ticketType.event', 'tickets.seat']);
        $tickets = $order->tickets;

        if ($tickets->isEmpty()) {
            abort(404);
        }

        $event = $tickets->first()->ticketType->event;
        $filename = $this->generateFilename($order->customer_name, $event);

        // Если билет ОДИН — отдаем PDF
        if ($tickets->count() === 1) {
            $pdf = $this->generatePdf($tickets->first());
            return $pdf->download("{$filename}.pdf");
        }

        // Если билетов МНОГО — делаем ZIP
        $zipFileName = "{$filename}.zip";
        $zipPath = storage_path("app/public/{$zipFileName}");
        
        if (!File::exists(dirname($zipPath))) {
            File::makeDirectory(dirname($zipPath), 0755, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($tickets as $index => $ticket) {
                $pdf = $this->generatePdf($ticket);
                // Имя внутри архива: Ivan-Ivanov-ticket-1.pdf
                $zip->addFromString("ticket-" . ($index + 1) . ".pdf", $pdf->output());
            }
            $zip->close();
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    private function generatePdf($ticket)
    {
        // Генерируем QR-код (строка SVG)
        $qrCode = QrCode::size(200)->format('svg')->generate($ticket->unique_code);
        
        // ВАЖНО: Передаем 'qrCode' в шаблон
        return Pdf::loadView('tickets.pdf', compact('ticket', 'qrCode'));
    }

    private function generateFilename($customerName, $event)
    {
        $transliteratedName = Str::slug(Str::transliterate($customerName));
        $date = \Carbon\Carbon::parse($event->start_time)->format('d-m');
        $eventId = sprintf('%04d', $event->id);

        return "{$transliteratedName}-{$date}-{$eventId}";
    }
}