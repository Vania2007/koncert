<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $event->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/imask"></script>
    <style>
        .screen-curve { filter: drop-shadow(0 0 15px rgba(255, 255, 255, 0.1)); }
        .seat {
            width: 28px; height: 28px; border-radius: 6px 6px 10px 10px;
            font-size: 10px; font-weight: 700; display: flex; align-items: center; justify-content: center; user-select: none;
        }
        .row-label {
            position: absolute; font-size: 10px; color: #4b5563; font-weight: bold; width: 30px; height: 28px;
            display: flex; align-items: center; justify-content: flex-end; transform: translateX(-35px); pointer-events: none;
        }
        .timer-circle { transition: stroke-dashoffset 1s linear; transform: rotate(-90deg); transform-origin: 50% 50%; }
        
        /* Скроллбар */
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #18181b; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #3f3f46; border-radius: 2px; }

        /* === ИНТЕРАКТИВНАЯ КАРТА === */
        .card-wrapper {
            perspective: 1000px;
            width: 100%;
            max-width: 400px;
            height: 250px;
            margin: 0 auto 20px auto;
            font-family: 'Source Code Pro', monospace;
        }
        .card-inner {
            position: relative;
            width: 100%;
            height: 100%;
            transition: transform 0.8s cubic-bezier(0.71, 0.03, 0.56, 0.85);
            transform-style: preserve-3d;
        }
        .card-inner.flipped {
            transform: rotateY(180deg);
        }
        .card-front, .card-back {
            position: absolute;
            width: 100%;
            height: 100%;
            -webkit-backface-visibility: hidden;
            backface-visibility: hidden;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px 0 rgba(14, 42, 90, 0.55);
            background-image: url('https://raw.githubusercontent.com/muhammederdem/credit-card-form/master/src/assets/images/22.jpeg');
            background-size: cover;
            background-position: center;
        }
        
        
        /* ПЕРЕДНЯЯ СТОРОНА */
        .card-chip {
            width: 60px;
            position: absolute;
            top: 25px;
            left: 25px;
            z-index: 2;
        }
        .card-logo {
            height: 45px;
            position: absolute;
            top: 25px;
            right: 25px;
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 2;
        }
        .card-number {
            position: absolute;
            top: 100px;
            left: 25px;
            right: 25px;
            font-size: 26px;
            color: #fff;
            text-shadow: 0 2px 4px rgba(0,0,0,0.6);
            letter-spacing: 3px;
            z-index: 2;
            cursor: pointer;
        }
        .card-holder-group {
            position: absolute;
            bottom: 25px;
            left: 25px;
            max-width: 240px;
            z-index: 2;
        }
        .card-expires-group {
            position: absolute;
            bottom: 25px;
            right: 25px;
            text-align: right;
            z-index: 2;
        }
        .card-label {
            font-size: 11px;
            color: #ccc;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: block;
            margin-bottom: 4px;
        }
        .card-value {
            font-size: 18px;
            color: #fff;
            text-transform: uppercase;
            text-shadow: 0 1px 2px rgba(0,0,0,0.6);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ЗАДНЯЯ СТОРОНА */
        .card-back {
            transform: rotateY(180deg);
        }
        .card-strip {
            width: 100%;
            height: 50px;
            background: rgba(0, 0, 0, 0.9);
            margin-top: 30px;
            position: relative;
            z-index: 2;
        }
        .card-cvv-group {
            margin-top: 20px;
            padding-right: 20px;
            text-align: right;
            position: relative;
            z-index: 2;
        }
        .card-cvv-box {
            background: #fff;
            color: #333;
            width: 90%;
            height: 45px;
            border-radius: 4px;
            margin-left: auto;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 15px;
            font-size: 18px;
            letter-spacing: 2px;
        }
        .card-logo-back {
            position: absolute;
            bottom: 25px;
            right: 25px;
            height: 45px;
            opacity: 0.8;
            z-index: 2;
        }
    </style>
</head>
<body class="bg-[#0f0f0f] text-white h-screen flex flex-col overflow-hidden">

    <div id="toast-container" class="fixed top-5 left-1/2 transform -translate-x-1/2 z-[100] flex flex-col gap-2 pointer-events-none"></div>

    {{-- ТАЙМЕР БРОНИ --}}
    <div id="booking-timer" class="fixed bottom-6 right-6 z-[90] hidden flex-col items-center bg-gray-900/90 backdrop-blur-md p-3 rounded-2xl shadow-2xl border border-gray-700 transition-opacity duration-500">
        <div class="relative w-12 h-12 flex items-center justify-center">
            <svg class="w-full h-full transform -scale-x-100">
                <circle cx="24" cy="24" r="20" stroke="#374151" stroke-width="3" fill="none"></circle>
                <circle id="timer-progress" cx="24" cy="24" r="20" stroke="#6366f1" stroke-width="3" fill="none" stroke-dasharray="126" stroke-dashoffset="0" stroke-linecap="round" class="timer-circle"></circle>
            </svg>
            <span id="timer-text" class="absolute text-xs font-bold font-mono text-white">10:00</span>
        </div>
        <span class="text-[8px] text-gray-400 mt-1 uppercase tracking-wider font-bold">Бронь</span>
    </div>

    <div class="flex-none container mx-auto px-4 h-16 flex justify-between items-center border-b border-gray-800 bg-[#0f0f0f] z-10">
        <a href="{{ route('events.index') }}" class="text-gray-400 hover:text-white flex items-center gap-2 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Афиша</span>
        </a>
        <h1 class="text-lg font-bold truncate">{{ $event->title }}</h1>
    </div>

    @if($event->hall && $event->hall->seats->count() > 0)
        @php
            $maxX = $event->hall->seats->max('x'); $maxY = $event->hall->seats->max('y');
            $canvasWidth = $maxX + 100; $canvasHeight = $maxY + 150; 
        @endphp

        <form action="{{ route('order.create') }}" method="POST" class="flex-grow flex flex-col lg:flex-row overflow-hidden" id="main-form">
            @csrf
            <input type="hidden" name="event_id" value="{{ $event->id }}">

            <div class="flex-grow relative bg-[#18181b] overflow-hidden flex items-center justify-center" id="map-container">
                <div id="zoom-layer" class="origin-top transition-transform duration-300" style="width: {{ $canvasWidth }}px; height: {{ $canvasHeight }}px;">
                    <div class="absolute top-5 left-1/2 transform -translate-x-1/2 w-[600px] pointer-events-none">
                        <svg viewBox="0 0 800 60" fill="none" class="w-full screen-curve">
                            <path d="M50,50 Q400,-10 750,50" stroke="#333" stroke-width="6" stroke-linecap="round" />
                            <path d="M50,50 Q400,-10 750,50" stroke="#6366f1" stroke-width="2" opacity="0.6" />
                        </svg>
                        <div class="text-center text-[10px] text-gray-500 mt-2 font-bold tracking-[0.4em] uppercase">СЦЕНА</div>
                    </div>
                    @foreach($event->hall->seats->groupBy('row') as $rowNum => $seats)
                        @if($first = $seats->sortBy('x')->first()) <div class="row-label" style="left: {{ $first->x }}px; top: {{ $first->y }}px;">{{ $rowNum }}</div> @endif
                    @endforeach
                    @foreach($event->hall->seats as $seat)
                        @php
                            $isOccupied = in_array($seat->id, $occupiedSeatIds); $isMySeat = in_array($seat->id, $mySelectedSeats ?? []);
                            $baseClass = $isOccupied ? 'bg-gray-800 border-gray-700 cursor-not-allowed text-transparent' : ($seat->section == 'VIP' ? 'bg-yellow-600/80 border-yellow-500' : 'bg-indigo-600/80 border-indigo-500');
                        @endphp
                        <label class="absolute seat group {{ $baseClass }} {{ !$isOccupied ? 'cursor-pointer hover:scale-125 hover:z-50 hover:shadow-[0_0_15px_indigo]' : '' }}" style="left: {{ $seat->x }}px; top: {{ $seat->y }}px;">
                            @if(!$isOccupied)
                                <input type="checkbox" name="seats[]" value="{{ $seat->id }}" class="hidden peer seat-checkbox" data-seat-number="{{ $seat->number }}" @checked($isMySeat) onchange="handleSeatClick(this)">
                                <div class="absolute inset-0 border-2 border-white rounded-[5px] opacity-0 peer-checked:opacity-100 transition-opacity shadow-[0_0_10px_white]"></div>
                            @endif
                            <span class="z-10 text-[9px] opacity-50 group-hover:opacity-100 {{ $isOccupied ? 'hidden' : '' }}">{{ $seat->number }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex-none w-full lg:w-80 h-1/3 lg:h-full bg-[#121212] border-t lg:border-t-0 lg:border-l border-gray-800 flex flex-col z-20 shadow-2xl relative">
                <div class="p-4 flex-none">
                    <h2 class="text-xl font-bold mb-1">Билеты</h2>
                    <p class="text-xs text-gray-500">{{ $event->hall->name }}</p>
                </div>
                <div id="cart-list" class="flex-grow overflow-y-auto custom-scrollbar px-4 space-y-2 text-sm text-gray-500 italic text-center">Выберите места на схеме</div>
                <div class="p-4 border-t border-gray-800 bg-[#121212] flex-none">
                    <div class="flex justify-between items-end mb-3">
                        <span class="text-gray-400 text-sm">К оплате:</span>
                        <span class="text-2xl font-bold text-white" id="total-price">0 ₴</span>
                    </div>
                    <button type="button" id="checkout-btn" onclick="openCheckoutModal()" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl disabled:opacity-50 disabled:cursor-not-allowed transition flex justify-center items-center" disabled>Оформить заказ</button>
                </div>
            </div>

            <div id="checkout-modal" class="hidden fixed inset-0 z-[200] bg-black/90 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity">
                <div class="bg-[#18181b] border border-gray-800 w-full max-w-4xl rounded-2xl shadow-2xl flex flex-col max-h-[95vh] overflow-hidden animate-[fadeIn_0.2s]">
                    
                    <div class="flex justify-between items-center p-6 border-b border-gray-800 bg-[#121212]">
                        <h3 class="text-xl font-bold">Оформление заказа</h3>
                        <button type="button" onclick="closeCheckoutModal()" class="text-gray-400 hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="flex flex-col md:flex-row flex-grow overflow-hidden">
                        
                        <div class="w-full md:w-1/2 p-6 space-y-5 overflow-y-auto custom-scrollbar">
                            <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Контактные данные</h4>
                            
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Имя и Фамилия</label>
                                <input type="text" name="customer_name" required placeholder="Иван Иванов" class="w-full bg-[#0f0f0f] border border-gray-700 rounded-lg px-4 py-3 text-white focus:border-indigo-500 focus:outline-none transition" id="input-name">
                            </div>

                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Эл. почта</label>
                                <input type="email" name="customer_email" required placeholder="mail@example.com" class="w-full bg-[#0f0f0f] border border-gray-700 rounded-lg px-4 py-3 text-white focus:border-indigo-500 focus:outline-none transition">
                            </div>

                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Телефон (Telegram)</label>
                                <input type="tel" id="phone-input" name="customer_phone" required placeholder="+38 (0XX) XXX-XX-XX" class="w-full bg-[#0f0f0f] border border-gray-700 rounded-lg px-4 py-3 text-white focus:border-indigo-500 focus:outline-none transition">
                            </div>
                        </div>

                        <div class="w-full md:w-1/2 bg-[#121212] p-6 flex flex-col items-center justify-center border-l border-gray-800">
                            
                            <div class="card-wrapper mb-6">
                                <div class="card-inner" id="card-inner">
                                    
                                    <div class="card-front">
                                        <div class="card-bg-image"></div>
                                        
                                        <img src="https://raw.githubusercontent.com/muhammederdem/credit-card-form/master/src/assets/images/chip.png" class="card-chip">
                                        
                                        <img id="card-type-logo" src="" class="card-logo" alt="Logo">

                                        <div class="card-number" id="card-number-display">#### #### #### ####</div>
                                        
                                        <div class="card-holder-group">
                                            <span class="card-label">Card Holder</span>
                                            <div class="card-value" id="card-name-display">FULL NAME</div>
                                        </div>
                                        
                                        <div class="card-expires-group">
                                            <span class="card-label">Expires</span>
                                            <div class="card-value">
                                                <span id="card-month-display">MM</span>/<span id="card-year-display">YY</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-back">
                                        <div class="card-bg-image"></div>
                                        
                                        <div class="card-strip"></div>
                                        
                                        <div class="card-cvv-group">
                                            <span class="card-label">CVV</span>
                                            <div class="card-cvv-box" id="card-cvv-display"></div>
                                        </div>
                                        
                                        <img id="card-type-logo-back" src="" class="card-logo-back" alt="Logo">
                                    </div>
                                </div>
                            </div>

                            <div class="w-full max-w-[400px] space-y-4">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Номер карты</label>
                                    <input type="text" id="input-card-number" class="w-full bg-[#0f0f0f] border border-gray-700 rounded-lg px-4 py-2 text-white font-mono tracking-widest focus:border-indigo-500 focus:outline-none transition" maxlength="19">
                                </div>

                                <div class="flex gap-4">
                                    <div class="flex-1">
                                        <label class="block text-xs text-gray-500 mb-1">Срок (ММ/ГГ)</label>
                                        <input type="text" id="input-card-date" placeholder="MM/YY" maxlength="5" class="w-full bg-[#0f0f0f] border border-gray-700 rounded-lg px-4 py-2 text-white font-mono tracking-widest focus:border-indigo-500 focus:outline-none transition">
                                    </div>
                                    
                                    <div class="w-24">
                                        <label class="block text-xs text-gray-500 mb-1">CVV</label>
                                        <input type="password" id="input-card-cvv" class="w-full bg-[#0f0f0f] border border-gray-700 rounded-lg px-4 py-2 text-white font-mono text-center focus:border-indigo-500 focus:outline-none transition" maxlength="4">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="p-6 border-t border-gray-800 bg-[#121212] flex justify-between items-center">
                        <div class="text-sm">
                            <span class="text-gray-500">Итого:</span>
                            <span class="text-xl font-bold text-white ml-2" id="modal-total-price">0 ₴</span>
                        </div>
                        <button type="submit" id="final-pay-btn" class="px-8 py-3 bg-green-600 hover:bg-green-500 text-white font-bold rounded-xl transition flex items-center gap-2 shadow-[0_0_15px_rgba(22,163,74,0.4)]">
                            <span>Оплатить</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </button>
                    </div>
                </div>
            </div>

        </form>
    @else
        <div class="flex-grow flex items-center justify-center text-gray-500">Схема зала недоступна</div>
    @endif

    <script>
        // === ЛОГИКА БРОНИРОВАНИЯ И КОРЗИНЫ ===
        const price = {{ $event->ticketTypes->first()->price ?? 0 }};
        const eventId = {{ $event->id }};
        const csrfToken = '{{ csrf_token() }}';
        let serverExpiresAt = {{ $bookingExpiresAt ?? 'null' }}; 
        let bookingEndTime = null, timerInterval = null, currentTotal = 0;
        const TIMER_DURATION = 10 * 60;

        document.addEventListener('DOMContentLoaded', () => {
            const phoneEl = document.getElementById('phone-input');
            if(phoneEl) IMask(phoneEl, { mask: '+{38} (000) 000-00-00' });
            
            // Запуск логики карты
            initCreditCardLogic();
        });

        function openCheckoutModal() { document.getElementById('checkout-modal').classList.remove('hidden'); document.getElementById('modal-total-price').innerText = currentTotal + ' ₴'; }
        function closeCheckoutModal() { document.getElementById('checkout-modal').classList.add('hidden'); }

        async function handleSeatClick(checkbox) {
            checkbox.disabled = true;
            const seatNumber = checkbox.dataset.seatNumber;
            try {
                const response = await fetch("{{ route('seats.toggle_lock') }}", {
                    method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken },
                    body: JSON.stringify({ seat_id: checkbox.value, event_id: eventId })
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Ошибка');

                if (data.status === 'locked') {
                    updateCart(); showToast(`Место ${seatNumber} забронировано`, 'success'); startTimer(data.expires_at);
                } else if (data.status === 'unlocked') {
                    updateCart(); showToast(`Бронь места ${seatNumber} снята`, 'info');
                }
            } catch (error) { showToast(error.message, 'error'); checkbox.checked = !checkbox.checked; updateCart(); } finally { checkbox.disabled = false; }
        }

        function startTimer(expiresAtTimestamp) {
            bookingEndTime = expiresAtTimestamp;
            const timerDiv = document.getElementById('booking-timer');
            timerDiv.classList.remove('hidden'); timerDiv.classList.add('flex');
            if (timerInterval) clearInterval(timerInterval);
            const tick = () => {
                const now = Math.floor(Date.now() / 1000); const diff = bookingEndTime - now;
                if (diff <= 0) { clearInterval(timerInterval); handleTimeExpired(); return; }
                updateTimerUI(diff);
            };
            tick(); timerInterval = setInterval(tick, 1000);
        }
        function updateTimerUI(secondsLeft) {
            const minutes = Math.floor(secondsLeft / 60).toString().padStart(2, '0'); const seconds = (secondsLeft % 60).toString().padStart(2, '0');
            document.getElementById('timer-text').innerText = `${minutes}:${seconds}`;
            const circle = document.getElementById('timer-progress'); const circumference = 126;
            const percent = Math.min(secondsLeft / TIMER_DURATION, 1);
            circle.style.strokeDashoffset = circumference - (percent * circumference);
            circle.style.stroke = secondsLeft < 60 ? '#ef4444' : '#6366f1';
        }
        function handleTimeExpired() {
            showToast('Время бронирования истекло!', 'error');
            document.getElementById('booking-timer').classList.add('hidden');
            document.querySelectorAll('input[name="seats[]"]:checked').forEach(cb => { cb.checked = false; });
            updateCart();
        }
        function updateCart() {
            const checked = document.querySelectorAll('input[name="seats[]"]:checked');
            const list = document.getElementById('cart-list'); const total = document.getElementById('total-price'); const checkoutBtn = document.getElementById('checkout-btn');
            currentTotal = 0; let html = '';
            checked.forEach(cb => { currentTotal += price; html += `<div class="flex justify-between items-center bg-gray-800 p-2 rounded border border-gray-700 animate-[fadeIn_0.3s]"><span class="text-white font-bold">Место ${cb.dataset.seatNumber}</span><span class="text-indigo-400">${price} ₴</span></div>`; });
            if (checked.length > 0) { list.innerHTML = html; list.classList.remove('text-center', 'italic'); checkoutBtn.disabled = false; checkoutBtn.innerText = `Оформить на ${currentTotal} ₴`; } 
            else { list.innerHTML = 'Выберите места на схеме'; list.classList.add('text-center', 'italic'); checkoutBtn.disabled = true; checkoutBtn.innerText = 'Оформить заказ'; document.getElementById('booking-timer').classList.add('hidden'); document.getElementById('booking-timer').classList.remove('flex'); if (timerInterval) clearInterval(timerInterval); }
            total.innerText = currentTotal + ' ₴';
        }
        function showToast(message, type = 'info') {
            const container = document.getElementById('toast-container'); const toast = document.createElement('div');
            let bgClass = type === 'error' ? 'bg-red-500' : (type === 'success' ? 'bg-green-500' : 'bg-indigo-500');
            toast.className = `${bgClass} text-white px-6 py-3 rounded-full shadow-lg text-sm font-bold flex items-center gap-2 transform transition-all duration-300 translate-y-[-20px] opacity-0`;
            toast.innerHTML = `<span>${message}</span>`; container.appendChild(toast);
            requestAnimationFrame(() => { toast.classList.remove('translate-y-[-20px]', 'opacity-0'); });
            setTimeout(() => { toast.classList.add('opacity-0', 'translate-y-[-20px]'); setTimeout(() => toast.remove(), 300); }, 3000);
        }
        const container = document.getElementById('map-container'); const layer = document.getElementById('zoom-layer');
        function fitMap() {
            if (!container || !layer) return;
            const contentWidth = {{ $canvasWidth ?? 800 }}; const contentHeight = {{ $canvasHeight ?? 600 }}; const padding = 20;
            let scale = Math.min((container.offsetWidth - padding*2) / contentWidth, (container.offsetHeight - padding*2) / contentHeight, 1);
            layer.style.transformOrigin = 'center center'; layer.style.transform = `scale(${scale})`;
        }
        window.addEventListener('load', () => { fitMap(); updateCart(); if (serverExpiresAt) startTimer(serverExpiresAt); });
        window.addEventListener('resize', fitMap);
        document.getElementById('main-form').addEventListener('submit', () => { const btn = document.getElementById('final-pay-btn'); btn.disabled = true; btn.innerHTML = '<svg class="animate-spin h-5 w-5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Обработка...'; });

        // === ИНТЕРАКТИВНАЯ КАРТА: ЛОГИКА ===
        function initCreditCardLogic() {
            const cardInner = document.getElementById('card-inner');
            const inputNum = document.getElementById('input-card-number');
            const inputName = document.getElementById('input-name');
            const inputDate = document.getElementById('input-card-date'); // Единое поле даты
            const inputCvv = document.getElementById('input-card-cvv');

            const dispNum = document.getElementById('card-number-display');
            const dispName = document.getElementById('card-name-display');
            const dispMonth = document.getElementById('card-month-display');
            const dispYear = document.getElementById('card-year-display');
            const dispCvv = document.getElementById('card-cvv-display');
            const cardLogo = document.getElementById('card-type-logo');
            const cardLogoBack = document.getElementById('card-type-logo-back');

            // Регулярные выражения для типов карт
            const cardTypes = {
                visa: /^4/,
                mastercard: /^5[1-5]|^2[2-7]/,
                amex: /^3[47]/,
                discover: /^6011|^65|^64[4-9]|^622/,
                unionpay: /^62/,
                troy: /^9792/
            };

            // Цветные логотипы
            const logos = {
                visa: 'https://raw.githubusercontent.com/muhammederdem/credit-card-form/master/src/assets/images/visa.png',
                mastercard: 'https://upload.wikimedia.org/wikipedia/commons/a/a4/Mastercard_2019_logo.svg',
                amex: 'https://raw.githubusercontent.com/muhammederdem/credit-card-form/master/src/assets/images/amex.png',
                discover: 'https://raw.githubusercontent.com/muhammederdem/credit-card-form/master/src/assets/images/discover.png',
                unionpay: 'https://raw.githubusercontent.com/muhammederdem/credit-card-form/master/src/assets/images/unionpay.png',
                troy: 'https://raw.githubusercontent.com/muhammederdem/credit-card-form/master/src/assets/images/troy.png'
            };

            // 1. Форматирование номера (группы по 4 цифры)
            inputNum.addEventListener('input', (e) => {
                let value = e.target.value.replace(/\D/g, '').substring(0, 16);
                let formatted = value.match(/.{1,4}/g)?.join(' ') || '';
                e.target.value = formatted;
                
                dispNum.innerText = formatted || '#### #### #### ####';

                // Определение типа карты
                let type = null;
                for (const [key, regex] of Object.entries(cardTypes)) {
                    if (regex.test(value)) { type = key; break; }
                }
                
                // Смена логотипа
                if (type && logos[type]) {
                    cardLogo.src = logos[type];
                    cardLogoBack.src = logos[type];
                    cardLogo.style.opacity = '1';
                } else {
                    cardLogo.style.opacity = '0';
                    cardLogoBack.src = '';
                }
            });

            // 2. Имя владельца (передаем из основного поля формы)
            inputName.addEventListener('input', (e) => {
                let val = e.target.value.toUpperCase();
                dispName.innerText = val || 'FULL NAME';
            });

            // 3. ДАТА (ММ/ГГ) - Умный ввод
            inputDate.addEventListener('input', (e) => {
                let val = e.target.value.replace(/\D/g, ''); // Удаляем всё кроме цифр
                if (val.length > 4) val = val.substring(0, 4); // Максимум 4 цифры (MMYY)

                // Добавляем слэш после 2-го символа
                if (val.length >= 2) {
                    e.target.value = val.substring(0, 2) + '/' + val.substring(2);
                } else {
                    e.target.value = val;
                }

                // Обновляем карту
                if (val.length >= 2) {
                    dispMonth.innerText = val.substring(0, 2);
                    dispYear.innerText = val.substring(2) || 'YY';
                } else {
                    dispMonth.innerText = val || 'MM';
                    dispYear.innerText = 'YY';
                }
            });

            // Обработка Backspace (Удаление слэша и цифры перед ним)
            inputDate.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && e.target.value.length === 3) {
                    // Если значение "12/" и нажали Backspace -> удаляем "/" и "2" -> остается "1"
                    e.preventDefault();
                    let newVal = e.target.value.substring(0, 1);
                    e.target.value = newVal;
                    dispMonth.innerText = newVal;
                }
            });

            // 4. CVV и Переворот карты
            inputCvv.addEventListener('focus', () => {
                cardInner.classList.add('flipped');
            });
            inputCvv.addEventListener('blur', () => {
                cardInner.classList.remove('flipped');
            });
            inputCvv.addEventListener('input', (e) => {
                // Отображаем звездочки вместо цифр на карте
                dispCvv.innerText = '*'.repeat(e.target.value.length);
            });
        }
    </script>
</body>
</html>