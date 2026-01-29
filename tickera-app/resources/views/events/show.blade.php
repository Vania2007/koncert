<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>{{ $event->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="bg-gray-900 text-white min-h-screen">

    <div class="container mx-auto px-4 py-8">
        <a href="{{ route('events.index') }}" class="text-gray-400 hover:text-white mb-6 inline-block">← Назад к афише</a>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1">
                <h1 class="text-4xl font-bold mb-4">{{ $event->title }}</h1>
                
                <div class="bg-gray-800 p-6 rounded-xl shadow-lg mb-6">
                    <p class="text-gray-400 text-sm uppercase tracking-wide">Где и когда</p>
                    <p class="text-xl font-semibold mt-1">{{ $event->hall->name ?? $event->location }}</p>
                    <p class="text-indigo-400 mt-1">
                        {{ \Carbon\Carbon::parse($event->start_time)->translatedFormat('d F Y в H:i') }}
                    </p>
                </div>

                <div class="prose prose-invert mb-8">
                    {{ $event->description }}
                </div>
            </div>

            <div class="lg:col-span-2">
                
                @if($event->hall)
                    <div class="bg-gray-800 p-6 rounded-xl shadow-lg overflow-hidden">
                        <h3 class="text-xl font-bold mb-6 text-center">Выберите места</h3>
                        
                        <div class="w-full bg-gray-700 h-12 mb-10 rounded-lg flex items-center justify-center text-gray-400 font-bold tracking-[0.5em] shadow-inner">
                            СЦЕНА
                        </div>

                        <div class="overflow-auto border border-gray-700 rounded-lg bg-gray-900 relative" style="height: 500px;">
                            
                            <div class="relative min-w-[800px] min-h-[600px] p-10 origin-top-left transform scale-90 md:scale-100 transition-transform">
                                
                                <form action="{{ route('order.create') }}" method="POST" id="booking-form">
                                    @csrf
                                    <input type="hidden" name="event_id" value="{{ $event->id }}">
                                    
                                    @if($event->ticketTypes->first())
                                        <input type="hidden" name="ticket_type_id" value="{{ $event->ticketTypes->first()->id }}">
                                    @endif

                                    @foreach($event->hall->seats as $seat)
                                        @php
                                            $isOccupied = in_array($seat->id, $occupiedSeatIds);
                                            // Цвета секторов (для примера)
                                            $colorClass = match($seat->section) {
                                                'VIP' => 'bg-yellow-500 hover:bg-yellow-400 border-yellow-700',
                                                'Партер' => 'bg-blue-600 hover:bg-blue-500 border-blue-800',
                                                default => 'bg-indigo-600 hover:bg-indigo-500 border-indigo-800'
                                            };
                                            
                                            if ($isOccupied) {
                                                $colorClass = 'bg-gray-600 cursor-not-allowed opacity-50';
                                            }
                                        @endphp

                                        <label 
                                            class="absolute w-8 h-8 rounded-t-lg border-b-4 shadow-sm flex items-center justify-center text-[10px] font-bold transition-all duration-200 group {{ $colorClass }}"
                                            style="left: {{ $seat->x }}px; top: {{ $seat->y }}px;"
                                            title="{{ $seat->section }} | Ряд {{ $seat->row }} Место {{ $seat->number }}"
                                        >
                                            @if(!$isOccupied)
                                                <input type="checkbox" name="seats[]" value="{{ $seat->id }}" class="hidden peer" onchange="updateTotal()">
                                                <div class="absolute inset-0 bg-white opacity-0 peer-checked:opacity-100 transition-opacity rounded-t-lg mix-blend-overlay"></div>
                                                <div class="absolute -top-2 left-1/2 transform -translate-x-1/2 w-1 h-1 bg-white rounded-full opacity-0 peer-checked:opacity-100 shadow-[0_0_10px_white]"></div>
                                            @endif
                                            
                                            {{ $seat->number }}
                                        </label>
                                    @endforeach

                                </form>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-between items-center bg-gray-900 p-4 rounded-lg border border-gray-700">
                            <div>
                                <p class="text-sm text-gray-400">Выбрано мест: <span id="count" class="text-white font-bold">0</span></p>
                                <p class="text-xs text-gray-500 mt-1">Цена за билет: {{ $event->ticketTypes->first()->price ?? 0 }} грн</p>
                            </div>
                            <button onclick="document.getElementById('booking-form').submit()" id="buy-btn" class="bg-green-600 hover:bg-green-500 text-white font-bold py-3 px-8 rounded-lg shadow-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all" disabled>
                                Купить билеты
                            </button>
                        </div>

                    </div>
                @else
                    <div class="bg-yellow-900/50 border border-yellow-600 p-6 rounded-xl text-yellow-200">
                        Внимание! Для этого события не указан зал или схема рассадки.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function updateTotal() {
            const checkboxes = document.querySelectorAll('input[name="seats[]"]:checked');
            const count = checkboxes.length;
            const btn = document.getElementById('buy-btn');
            
            document.getElementById('count').innerText = count;
            
            if (count > 0) {
                btn.disabled = false;
                btn.classList.remove('bg-gray-600');
                btn.classList.add('bg-green-600');
            } else {
                btn.disabled = true;
                btn.classList.add('bg-gray-600');
                btn.classList.remove('bg-green-600');
            }
        }
    </script>
</body>
</html>