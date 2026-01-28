<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заказ успешно создан!</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-10">
    <div class="bg-white p-8 md:p-12 rounded-2xl shadow-xl text-center max-w-2xl w-full">
        
        <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-6">
            <span class="text-4xl">🎉</span>
        </div>

        <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Билеты успешно оплачены!</h1>
        <p class="text-gray-500 mb-8">
            Спасибо, <b>{{ $order->customer_name }}</b>. Мы отправили копии на <u>{{ $order->customer_email }}</u>
        </p>

        <div class="mb-10">
            <a href="{{ route('order.download', $order->id) }}" class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 px-6 rounded-xl shadow-lg shadow-indigo-200 text-lg transition duration-200 flex items-center justify-center gap-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Скачать билеты (@if($order->tickets->count() > 1) ZIP-архив @else PDF @endif)
            </a>
            <p class="text-xs text-gray-400 mt-2">Нажмите, чтобы сохранить билеты на устройство</p>
        </div>
        
        <div class="text-left bg-gray-50 rounded-xl border border-gray-100 p-6 mb-8">
            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Состав заказа ({{ $order->tickets->count() }} шт.)</h3>
            
            <div class="space-y-4">
                @foreach($order->tickets as $ticket)
                    <div class="flex items-start gap-4 pb-4 border-b border-gray-100 last:border-0 last:pb-0">
                        <div class="bg-white p-2 rounded-lg border text-2xl shadow-sm">
                            🎫
                        </div>
                        
                        <div>
                            <h4 class="font-bold text-gray-800 text-lg leading-tight">
                                {{ $ticket->ticketType->event->title }}
                            </h4>
                            
                            <p class="text-gray-600 mt-1">
                                <span class="font-medium text-gray-900">{{ $order->customer_name }}</span>
                            </p>

                            <div class="flex flex-wrap gap-2 mt-2 text-xs font-medium text-gray-500">
                                <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded">
                                    {{ $ticket->ticketType->name }}
                                </span>
                                
                                {{-- 
                                <span class="bg-gray-200 px-2 py-1 rounded">Ряд 5</span>
                                <span class="bg-gray-200 px-2 py-1 rounded">Место 12</span> 
                                --}}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-center gap-4 text-sm font-medium">
            <a href="{{ route('events.index') }}" class="text-gray-500 hover:text-gray-900">
                Вернуться на главную
            </a>
        </div>
    </div>
</body>
</html>