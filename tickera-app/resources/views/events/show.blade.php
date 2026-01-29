<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>{{ $event->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .screen-curve { filter: drop-shadow(0 0 10px rgba(0, 212, 255, 0.3)); }
        .seat {
            width: 24px;
            height: 24px;
            border-radius: 4px 4px 8px 8px;
            transition: all 0.2s;
        }
    </style>
</head>
<body class="bg-[#121212] text-white min-h-screen">

    <div class="container mx-auto px-4 py-6">
        <a href="{{ route('events.index') }}" class="text-gray-500 hover:text-white mb-4 inline-block">&larr; Назад</a>
        
        <div class="flex flex-col lg:flex-row gap-6">
            <div class="flex-grow bg-[#050505] rounded-xl border border-gray-800 shadow-2xl relative overflow-hidden" style="min-height: 600px;">
                
                @if($event->hall) <div class="w-full h-full overflow-auto custom-scrollbar flex items-center justify-center bg-radial-dots">
                        <div class="relative w-[800px] h-[600px] flex-shrink-0">
                            
                            <div class="absolute top-5 left-1/2 transform -translate-x-1/2 w-[600px] pointer-events-none">
                                <svg viewBox="0 0 800 40" fill="none" class="w-full screen-curve">
                                    <path d="M10,35 Q400,-15 790,35" stroke="#333" stroke-width="4" fill="none" />
                                    <path d="M10,35 Q400,-15 790,35" stroke="#00d4ff" stroke-width="2" fill="none" opacity="0.7" />
                                </svg>
                                <div class="text-center text-[9px] text-gray-600 mt-1 uppercase tracking-widest font-bold">Экран</div>
                            </div>

                            <form action="{{ route('order.create') }}" method="POST" id="booking-form">
                                @csrf
                                <input type="hidden" name="event_id" value="{{ $event->id }}">
                                @if($event->ticketTypes->first())
                                    <input type="hidden" name="ticket_type_id" value="{{ $event->ticketTypes->first()->id }}">
                                @endif

                                @foreach($event->hall->seats as $seat)
                                    @php
                                        $isOccupied = in_array($seat->id, $occupiedSeatIds);
                                        if ($isOccupied) {
                                            $color = 'bg-gray-700 cursor-not-allowed opacity-40';
                                        } else {
                                            $color = match($seat->section) {
                                                'VIP' => 'bg-yellow-600 hover:bg-yellow-500 shadow-[0_0_5px_gold]',
                                                default => 'bg-indigo-600 hover:bg-indigo-500 hover:shadow-[0_0_5px_indigo]'
                                            };
                                            $color .= ' cursor-pointer hover:scale-125 hover:z-50';
                                        }
                                    @endphp

                                    <label 
                                        class="absolute seat flex items-center justify-center group {{ $color }}"
                                        style="left: {{ $seat->x }}px; top: {{ $seat->y }}px;"
                                        title="{{ $seat->section }} | Ряд {{ $seat->row }} Место {{ $seat->number }}"
                                    >
                                        @if(!$isOccupied)
                                            <input type="checkbox" name="seats[]" value="{{ $seat->id }}" class="hidden peer" onchange="updateTotal()">
                                            <div class="absolute inset-0 border-2 border-white rounded opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                        @endif
                                    </label>
                                @endforeach
                            </form>
                        </div>
                    </div>
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center text-gray-500">
                        <svg class="w-16 h-16 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <p class="text-xl font-bold">Зал не назначен</p>
                        <p class="text-sm">Администратор еще не выбрал зал для этого события.</p>
                    </div>
                @endif
            </div>

            <div class="w-full lg:w-80 flex-shrink-0">
                <h1 class="text-2xl font-bold mb-2">{{ $event->title }}</h1>
                <p class="text-sm text-gray-400 mb-6">{{ $event->hall->name ?? 'Место проведения уточняется' }}</p>

                <div class="bg-gray-800 p-5 rounded-lg border border-gray-700 sticky top-5">
                    <h3 class="text-lg font-bold mb-4 border-b border-gray-700 pb-2">Ваш билет</h3>
                    <div id="cart" class="text-sm text-gray-400 min-h-[50px] mb-4">
                        @if($event->hall)
                            Выберите места на схеме...
                        @else
                            Покупка временно недоступна.
                        @endif
                    </div>
                    <div class="flex justify-between items-center text-xl font-bold mb-4">
                        <span>Итого:</span>
                        <span id="total">0 ₴</span>
                    </div>
                    <button onclick="document.getElementById('booking-form').submit()" id="buy-btn" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded shadow-lg disabled:opacity-50 disabled:cursor-not-allowed transition" disabled>
                        Купить билет
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const price = {{ $event->ticketTypes->first()->price ?? 0 }};
        
        function updateTotal() {
            const checked = document.querySelectorAll('input[name="seats[]"]:checked');
            const cart = document.getElementById('cart');
            const totalEl = document.getElementById('total');
            const btn = document.getElementById('buy-btn');
            
            let total = 0;
            let html = '';

            checked.forEach(cb => {
                total += price;
                const title = cb.parentElement.getAttribute('title');
                html += `<div class="flex justify-between mb-1"><span class="text-white">${title}</span><span>${price}₴</span></div>`;
            });

            if (checked.length > 0) {
                cart.innerHTML = html;
                btn.disabled = false;
            } else {
                cart.innerHTML = 'Выберите места на схеме...';
                btn.disabled = true;
            }
            
            totalEl.innerText = total + ' ₴';
        }
    </script>
</body>
</html>