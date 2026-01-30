<x-filament-panels::page>

    {{-- 
        ОВЕРЛЕЙ (ВСПЛЫВАЮЩЕЕ ОКНО) 
        Используем inline-стили для гарантии перекрытия всего интерфейса.
        Добавлен transition для плавности.
    --}}
    <div id="scan-overlay" 
         style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 2147483647; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 20px;">
        
        {{-- Иконка --}}
        <div id="overlay-icon" class="text-[150px] mb-8 leading-none drop-shadow-lg animate-bounce"></div>

        {{-- Заголовок --}}
        <h2 id="overlay-title" class="text-6xl md:text-8xl font-black uppercase tracking-widest text-white drop-shadow-md mb-6" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.5);"></h2>

        {{-- Текст --}}
        <div class="bg-black/30 backdrop-blur-md rounded-xl p-6 border border-white/20">
             <p id="overlay-body" class="text-3xl md:text-5xl font-bold text-white whitespace-pre-line leading-tight" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.8);"></p>
        </div>
    </div>

    {{-- ОСНОВНОЙ ЭКРАН СКАНЕРА --}}
    <div class="flex flex-col items-center justify-center min-h-[70vh]">

        <div class="mb-6 text-center">
            <h2 class="text-3xl font-black uppercase text-gray-400 tracking-widest">Scan Point</h2>
            <p class="text-gray-500 text-sm mt-2">Наведите камеру на QR-код</p>
        </div>

        {{-- ВИДЕО --}}
        <div wire:ignore class="relative w-full max-w-md aspect-square mx-auto">
            <div class="w-full h-full bg-black rounded-[2rem] overflow-hidden shadow-2xl border-8 border-gray-800 relative">
                
                <video id="qr-video" class="w-full h-full object-cover"></video>

                {{-- Прицел --}}
                <div class="absolute inset-0 border-[50px] border-black/40 pointer-events-none">
                    <div class="w-full h-full border-4 border-white/40 rounded-xl relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-2 bg-red-500 shadow-[0_0_20px_red] animate-[scan_1.5s_infinite]"></div>
                    </div>
                </div>

                {{-- Сообщение о статусе камеры --}}
                <div id="status-msg" class="absolute inset-0 flex flex-col items-center justify-center bg-black/90 z-10">
                    <div id="loading-spinner" class="animate-spin rounded-full h-16 w-16 border-4 border-gray-600 border-t-white mb-6"></div>
                    <span id="status-text" class="text-white font-bold text-lg">Запуск камеры...</span>
                    <button id="btn-retry" class="hidden mt-6 px-6 py-3 bg-white text-black rounded-full font-bold hover:bg-gray-200 transition">
                        Перезапуск
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Библиотека --}}
    <script src="https://unpkg.com/qr-scanner@1.4.2/qr-scanner.legacy.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            const videoElem = document.getElementById('qr-video');
            const statusMsg = document.getElementById('status-msg');
            const statusText = document.getElementById('status-text');
            const spinner = document.getElementById('loading-spinner');
            const btnRetry = document.getElementById('btn-retry');

            // Элементы оверлея
            const overlay = document.getElementById('scan-overlay');
            const oTitle = document.getElementById('overlay-title');
            const oBody = document.getElementById('overlay-body');
            const oIcon = document.getElementById('overlay-icon');

            // Звуки
            const audioOk = new Audio('https://assets.mixkit.co/active_storage/sfx/1435/1435-preview.mp3');
            const audioErr = new Audio('https://assets.mixkit.co/active_storage/sfx/940/940-preview.mp3');
            const audioWarn = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');

            let scanner = null;
            let isBlocked = false;

            // --- ПОКАЗ ОКНА НА ВЕСЬ ЭКРАН ---
            function showFullVerdict(status, title, body, icon) {
                
                // 👇 ДОБАВЛЕНО: ВИБРАЦИЯ (Haptic Feedback)
                if (navigator.vibrate) {
                    if (status === 'success') {
                        navigator.vibrate(200); // Короткая, уверенная
                    } else {
                        navigator.vibrate([100, 50, 100, 50, 100]); // Длинная, прерывистая (ошибка)
                    }
                }

                // 1. Показываем блок
                overlay.style.display = 'flex';
                
                // 2. Красим фон через style
                if (status === 'success') {
                    overlay.style.backgroundColor = 'rgba(22, 163, 74, 1)'; // Зеленый
                    oIcon.innerText = '✅';
                    audioOk.currentTime = 0;
                    audioOk.play().catch(()=>{});
                } 
                else if (status === 'warning') {
                    overlay.style.backgroundColor = 'rgba(234, 179, 8, 1)'; // Желтый
                    oIcon.innerText = '⚠️';
                    audioWarn.currentTime = 0;
                    audioWarn.play().catch(()=>{});
                } 
                else {
                    overlay.style.backgroundColor = 'rgba(220, 38, 38, 1)'; // Красный
                    oIcon.innerText = '⛔';
                    audioErr.currentTime = 0;
                    audioErr.play().catch(()=>{});
                }

                // 3. Заполняем текст
                oTitle.innerText = title;
                oBody.innerText = body;
                if(icon) oIcon.innerText = icon;

                // 4. Скрываем через 2.5 сек
                setTimeout(() => {
                    overlay.style.display = 'none';
                    setTimeout(() => { isBlocked = false; }, 500);
                }, 2500);
            }

            function showCamError(msg) {
                spinner.style.display = 'none';
                statusText.innerHTML = `<span class="text-red-500 font-bold">ОШИБКА</span><br><span class="text-xs text-gray-300">${msg}</span>`;
                btnRetry.classList.remove('hidden');
            }

            // --- СТАРТ СКАНЕРА ---
            function startScanner() {
                statusText.innerText = 'Запуск камеры...';
                spinner.style.display = 'block';
                btnRetry.classList.add('hidden');
                statusMsg.style.display = 'flex';

                if (scanner) {
                    scanner.destroy();
                    scanner = null;
                }

                scanner = new QrScanner(videoElem, result => {
                    if (isBlocked) return;

                    let code = (typeof result === 'object' && result.data) ? result.data : result;
                    if (!code) return;

                    console.log('Scan:', code);
                    isBlocked = true;

                    // Вызов PHP
                    @this.checkTicket(code).catch(err => {
                        console.error(err);
                        showFullVerdict('error', 'СБОЙ', 'Ошибка связи с сервером', '📡');
                    });

                }, {
                    highlightScanRegion: true,
                    highlightCodeOutline: true,
                    preferredCamera: 'environment',
                    maxScansPerSecond: 5,
                });

                scanner.start().then(() => {
                    statusMsg.style.display = 'none';
                }).catch(err => {
                    console.error(err);
                    showCamError(err);
                });
            }

            btnRetry.addEventListener('click', startScanner);

            // Слушаем событие от Livewire
            window.addEventListener('scan-finished', event => {
                const data = event.detail;
                const payload = data.status ? data : (data[0] || {});
                showFullVerdict(payload.status, payload.title, payload.body, payload.icon);
            });

            startScanner();
        });
    </script>

    <style>
        @keyframes scan {
            0% { top: 0%; opacity: 0; }
            50% { opacity: 1; }
            100% { top: 100%; opacity: 0; }
        }
    </style>

</x-filament-panels::page>