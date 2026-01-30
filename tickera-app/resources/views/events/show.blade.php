<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .screen-curve { filter: drop-shadow(0 0 15px rgba(255, 255, 255, 0.1)); }
        
        .seat {
            width: 28px;
            height: 28px;
            border-radius: 6px 6px 10px 10px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            user-select: none;
            transition: transform 0.1s;
        }
        
        /* Номера рядов (слева от ряда) */
        .row-label {
            position: absolute;
            font-size: 10px;
            color: #4b5563; /* gray-600 */
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            width: 30px; /* Ширина подписи */
            height: 28px; /* Высота как у места */
            transform: translateX(-35px); /* Сдвиг влево */
            pointer-events: none;
        }
    </style>
</head>
<body class="bg-[#0f0f0f] text-white min-h-screen flex flex-col">

    <div class="container mx-auto px-4 py-4 flex justify-between items-center border-b border-gray-800 bg-[#0f0f0f] z-10">
        <a href="{{ route('events.index') }}" class="text-gray-400 hover:text-white flex items-center gap-2 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Афиша</span>
        </a>
        <h1 class="text-lg font-bold truncate">{{ $event->title }}</h1>
    </div>

    <div class="container mx-auto px-4 mt-6">
        @if ($errors->any())
            <div class="bg-red-500/10 border border-red-500 text-red-400 p-4 rounded-xl mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-500/10 border border-red-500 text-red-400 p-4 rounded-xl mb-4">
                {{ session('error') }}
            </div>
        @endif
    </div>
    
    @if($event->hall && $event->hall->seats->count() > 0)
        @php
            // 1. Вычисляем реальные размеры схемы, чтобы она "влезла"
            $maxX = $event->hall->seats->max('x');
            $maxY = $event->hall->seats->max('y');
            
            // Добавляем отступы (padding), чтобы крайние места не обрезались
            $canvasWidth = $maxX + 100; 
            $canvasHeight = $maxY + 150; 

            // Центр сцены должен быть по центру холста
            $screenX = $canvasWidth / 2;
        @endphp

        <form action="{{ route('order.create') }}" method="POST" class="flex-grow flex flex-col lg:flex-row h-full overflow-hidden" id="main-form">
            @csrf
            <input type="hidden" name="event_id" value="{{ $event->id }}">

            <div class="flex-grow relative bg-[#18181b] overflow-hidden flex items-center justify-center" id="map-container">
                
                <div id="zoom-layer" class="origin-top transition-transform duration-200 ease-out" style="width: {{ $canvasWidth }}px; height: 93vh;">
                    
                    <div class="absolute top-5 left-1/2 transform -translate-x-1/2 w-[600px] pointer-events-none">
                        <svg viewBox="0 0 800 60" fill="none" class="w-full screen-curve">
                            <path d="M50,50 Q400,-10 750,50" stroke="#333" stroke-width="6" stroke-linecap="round" />
                            <path d="M50,50 Q400,-10 750,50" stroke="#6366f1" stroke-width="2" opacity="0.6" />
                        </svg>
                        <div class="text-center text-[10px] text-gray-500 mt-2 font-bold tracking-[0.4em] uppercase">СЦЕНА</div>
                    </div>

                    @php
                        $rows = $event->hall->seats->groupBy('row');
                    @endphp
                    @foreach($rows as $rowNum => $seatsInRow)
                        @php
                            // Ищем самое левое место в ряду
                            $firstSeat = $seatsInRow->sortBy('x')->first();
                        @endphp
                        @if($firstSeat)
                            <div class="row-label" style="left: {{ $firstSeat->x }}px; top: {{ $firstSeat->y }}px;">
                                {{ $rowNum }}
                            </div>
                        @endif
                    @endforeach

                    @foreach($event->hall->seats as $seat)
                        @php
                            $isOccupied = in_array($seat->id, $occupiedSeatIds);
                            
                            if ($isOccupied) {
                                // Занято
                                $colorClass = 'bg-gray-800 text-transparent border border-gray-700 cursor-not-allowed';
                            } else {
                                // Свободно (разные цвета для зон)
                                $colorClass = match($seat->section) {
                                    'VIP' => 'bg-yellow-600/80 border border-yellow-500 text-white/70 hover:bg-yellow-500 hover:scale-125 hover:z-50 hover:shadow-[0_0_15px_gold]',
                                    default => 'bg-indigo-600/80 border border-indigo-500 text-white/60 hover:bg-indigo-500 hover:scale-125 hover:z-50 hover:shadow-[0_0_15px_indigo]'
                                };
                                $colorClass .= ' cursor-pointer shadow-sm';
                            }
                        @endphp

                        <label 
                            class="absolute seat group {{ $colorClass }}"
                            style="left: {{ $seat->x }}px; top: {{ $seat->y }}px;"
                        >
                            @if(!$isOccupied)
                                <input type="checkbox" name="seats[]" value="{{ $seat->id }}" class="hidden peer" onchange="updateCart()">
                                <div class="absolute inset-0 border-2 border-white rounded-[5px] opacity-0 peer-checked:opacity-100 transition-opacity shadow-[0_0_10px_white]"></div>
                            @endif
                            
                            <span class="z-10 text-[9px] opacity-50 group-hover:opacity-100">{{ $seat->number }}</span>
                        </label>
                    @endforeach

                </div>
            </div>

            <div class="w-full lg:w-80 bg-[#121212] border-t lg:border-t-0 lg:border-l border-gray-800 p-6 flex flex-col z-20 shadow-2xl">
                <h2 class="text-xl font-bold mb-1">Билеты</h2>
                <p class="text-xs text-gray-500 mb-4">{{ $event->hall->name }}</p>

                <div id="cart-list" class="flex-grow overflow-auto custom-scrollbar space-y-2 mb-4 text-sm text-gray-500 italic text-center min-h-[100px]">
                    Выберите места на схеме
                </div>

                <div class="border-t border-gray-800 pt-4 mt-auto">
                    <div class="flex justify-between items-end mb-4">
                        <span class="text-gray-400 text-sm">К оплате:</span>
                        <span class="text-2xl font-bold text-white" id="total-price">0 ₴</span>
                    </div>

                    <button type="submit" id="submit-btn" class="w-full py-4 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl disabled:opacity-50 disabled:cursor-not-allowed transition flex justify-center items-center" disabled>
                        Оплатить
                    </button>
                </div>
            </div>
        </form>

    @else
        <div class="flex-grow flex flex-col items-center justify-center text-gray-500">
            <svg class="w-16 h-16 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
            <p class="text-xl">Схема зала недоступна</p>
        </div>
    @endif

    <script>
        // === ЛОГИКА МАСШТАБИРОВАНИЯ (ZOOM) ===
        function fitMap() {
            const container = document.getElementById('map-container');
            const layer = document.getElementById('zoom-layer');
            if (!container || !layer) return;

            // Реальные размеры контента (из PHP)
            const contentWidth = {{ $canvasWidth ?? 800 }};
            const contentHeight = {{ $canvasHeight ?? 600 }};

            // Доступное место
            const containerWidth = container.offsetWidth;
            const containerHeight = container.offsetHeight;

            // Отступы
            const padding = 20;

            // Считаем масштаб по ширине
            let scale = (containerWidth - padding * 2) / contentWidth;
            
            // Если схема слишком высокая, проверяем масштаб по высоте
            // (опционально, если хотите, чтобы всегда влезала целиком)
            // const scaleH = (containerHeight - padding * 2) / contentHeight;
            // if (scaleH < scale) scale = scaleH;

            // Ограничения: не увеличивать больше 100%
            if (scale > 1) scale = 1;
            // Не уменьшать слишком сильно (чтобы на мобилках не было микробов)
            if (window.innerWidth < 768 && scale < 0.4) scale = 0.4; 

            layer.style.transform = `scale(${scale})`;
            
            // Центрируем, если контент меньше контейнера
            if (scale === 1) {
                 layer.style.margin = '0 auto';
            } else {
                 // Компенсируем отступ снизу при уменьшении
                 const scaledHeight = contentHeight * scale;
                 const emptySpace = containerHeight - scaledHeight;
                 layer.style.transformOrigin = 'top center';
                 layer.style.marginBottom = `-${contentHeight - scaledHeight}px`;
            }
        }

        // Запускаем при загрузке и ресайзе
        window.addEventListener('load', fitMap);
        window.addEventListener('resize', fitMap);
        
        // === ЛОГИКА КОРЗИНЫ ===
        const price = {{ $event->ticketTypes->first()->price ?? 0 }};
        const form = document.getElementById('main-form');
        const submitBtn = document.getElementById('submit-btn');

        function updateCart() {
            const checked = document.querySelectorAll('input[name="seats[]"]:checked');
            const list = document.getElementById('cart-list');
            const total = document.getElementById('total-price');

            let sum = 0;
            let html = '';

            checked.forEach(cb => {
                sum += price;
                // Ищем родительский label, чтобы взять номер
                const label = cb.parentElement;
                const number = label.querySelector('span').innerText;
                // Ищем номер ряда (находим ближайший .row-label с такой же Y координатой - сложная логика,
                // упростим: просто выведем "Место N")
                
                html += `
                    <div class="flex justify-between items-center bg-gray-800 p-2 rounded border border-gray-700">
                        <span class="text-white font-bold">Место ${number}</span>
                        <span class="text-indigo-400">${price} ₴</span>
                    </div>
                `;
            });

            if (checked.length > 0) {
                list.innerHTML = html;
                list.classList.remove('text-center', 'italic');
                submitBtn.disabled = false;
                submitBtn.innerText = `Оплатить ${sum} ₴`;
            } else {
                list.innerHTML = 'Выберите места на схеме';
                list.classList.add('text-center', 'italic');
                submitBtn.disabled = true;
                submitBtn.innerText = 'Оплатить';
            }

            total.innerText = sum + ' ₴';
        }

        // Анимация загрузки при отправке
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
        });

    </script>
</body>
</html>