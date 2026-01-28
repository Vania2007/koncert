<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Билет</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .ticket { 
            border: 1px solid #ddd; 
            padding: 20px; 
            position: relative; 
            height: 280px; /* Фиксированная высота */
        }
        .header { 
            font-size: 20px; 
            font-weight: bold; 
            margin-bottom: 15px; 
            color: #333;
            text-transform: uppercase;
        }
        .row { margin-bottom: 8px; }
        .label { color: #777; font-size: 12px; }
        .value { font-size: 14px; font-weight: bold; color: #000; }
        
        /* QR код справа */
        .qr { 
            position: absolute; 
            right: 20px; 
            top: 20px; 
        }
        
        .footer { 
            position: absolute; 
            bottom: 20px; 
            left: 20px; 
            font-size: 10px; 
            color: #999; 
        }
        
        .divider { border-bottom: 2px dashed #eee; margin: 15px 0; width: 65%; }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            {{ $ticket->ticketType->event->title ?? 'Мероприятие' }}
        </div>
        
        <div style="width: 65%;">
            <div class="row">
                <span class="label">Владелец билета:</span><br>
                <span class="value">{{ $ticket->order->customer_name }}</span>
            </div>

            <div class="row">
                <span class="label">Место проведения:</span><br>
                <span class="value">{{ $ticket->ticketType->event->location ?? 'Не указано' }}</span>
            </div>

            <div class="row">
                <span class="label">Дата и время:</span><br>
                <span class="value">
                    {{ \Carbon\Carbon::parse($ticket->ticketType->event->start_time)->translatedFormat('d F Y в H:i') }}
                </span>
            </div>

            <div class="divider"></div>

            <div class="row">
                <span class="label">Категория билета:</span><br>
                <span class="value" style="font-size: 16px; color: #2563eb;">
                    {{ $ticket->ticketType->name }}
                </span>
            </div>
            
            @if(false) 
            <div class="row" style="margin-top: 10px;">
                <span class="value">Ряд: 5 | Место: 12</span>
            </div>
            @endif
        </div>

        <div class="qr">
            <img src="data:image/svg+xml;base64,{{ base64_encode($qrCode) }}" width="180" height="180">
        </div>

        <div class="footer">
            Заказ #{{ $ticket->order_id }} | Билет действителен для одного входа
        </div>
    </div>
</body>
</html>