<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Билет</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .ticket { 
            border: 2px solid #333; 
            padding: 30px; 
            position: relative; 
            height: 320px;
        }
        .header { 
            font-size: 24px; 
            font-weight: bold; 
            margin-bottom: 20px; 
            color: #000;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .row-info { margin-bottom: 10px; }
        .label { color: #666; font-size: 11px; text-transform: uppercase; }
        .value { font-size: 16px; font-weight: bold; color: #000; }
        
        .qr { 
            position: absolute; 
            right: 30px; 
            top: 80px; 
        }
        
        .seat-info {
            margin-top: 15px;
            padding: 10px;
            background: #f3f4f6;
            width: fit-content;
            border: 1px solid #ddd;
        }

        .footer { 
            position: absolute; 
            bottom: 30px; 
            left: 30px; 
            font-size: 10px; 
            color: #777; 
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            {{ $ticket->ticketType->event->title ?? 'Мероприятие' }}
        </div>
        
        <div style="width: 60%;">
            <div class="row-info">
                <span class="label">Владелец:</span><br>
                <span class="value">{{ $ticket->order->customer_name }}</span>
            </div>

            <div class="row-info">
                <span class="label">Место проведения:</span><br>
                <span class="value">{{ $ticket->ticketType->event->location ?? 'Не указано' }}</span>
            </div>

            <div class="row-info">
                <span class="label">Дата и время:</span><br>
                <span class="value">
                    {{ \Carbon\Carbon::parse($ticket->ticketType->event->start_time)->translatedFormat('d F Y, H:i') }}
                </span>
            </div>

            @if($ticket->seat)
                <div class="seat-info">
                    <span class="label">Сектор:</span> <strong>{{ $ticket->seat->section }}</strong> | 
                    <span class="label">Ряд:</span> <strong>{{ $ticket->seat->row }}</strong> | 
                    <span class="label">Место:</span> <strong>{{ $ticket->seat->number }}</strong>
                </div>
            @else
                <div class="seat-info">
                    <span class="label">Тип билета:</span> <strong>{{ $ticket->ticketType->name }}</strong>
                </div>
            @endif
        </div>

        <div class="qr">
            <img src="data:image/svg+xml;base64,{{ base64_encode($qrCode) }}" width="160" height="160">
            <div style="text-align: center; font-size: 8px; margin-top: 5px; font-family: monospace;">
                {{ $ticket->unique_code }}
            </div>
        </div>

        <div class="footer">
            Заказ #{{ $ticket->order_id }} | Сгенерировано {{ now()->format('d.m.Y H:i') }}
        </div>
    </div>
</body>
</html>