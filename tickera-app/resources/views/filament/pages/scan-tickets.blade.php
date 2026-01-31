<x-filament-panels::page>

    {{-- ОВЕРЛЕЙ (ВЕРДИКТ) --}}
    <div id="scan-overlay" 
         style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 2147483647; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 20px;">
        
        <div id="overlay-icon" class="text-[100px] md:text-[150px] mb-6 leading-none drop-shadow-lg animate-bounce"></div>

        <h2 id="overlay-title" class="text-5xl md:text-8xl font-black uppercase tracking-widest text-white drop-shadow-md mb-4" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.5);"></h2>

        <div class="bg-black/30 backdrop-blur-md rounded-xl p-6 border border-white/20 max-w-sm w-full mx-auto">
             <p id="overlay-body" class="text-xl md:text-4xl font-bold text-white whitespace-pre-line leading-tight" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.8);"></p>
        </div>
    </div>

    {{-- ОСНОВНОЙ ЭКРАН --}}
    <div class="flex flex-col items-center min-h-[85vh] py-2">

        <div class="mb-4 text-center shrink-0">
            <h2 class="text-xl font-black uppercase text-gray-400 tracking-widest">Scan Point</h2>
        </div>

        {{-- 
            ГЛАВНЫЙ КОНТЕЙНЕР
            Mobile: h-[70vh] w-full (Высокий портретный режим)
            Desktop: h-[500px] w-[500px] (Квадрат)
        --}}
        <div wire:ignore class="relative w-full max-w-[350px] md:max-w-[500px] h-[70vh] md:h-[500px] mx-auto">
            
            {{-- Черная подложка и рамка --}}
            <div class="w-full h-full bg-black rounded-[30px] overflow-hidden shadow-2xl border-4 border-gray-800 relative flex flex-col justify-center">
                
                {{-- 
                    ВИДЕО
                    style="... !important" - критически важно, чтобы перебить стили библиотеки
                --}}
                <video id="qr-video" 
                       playsinline muted 
                       style="width: 100% !important; height: 100% !important; object-fit: cover !important; display: block;">
                </video>

                {{-- ПРИЦЕЛ (Визуальный) --}}
                {{-- На мобильном делаем его явно вертикальным прямоугольником --}}
                <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                    <div class="absolute inset-0 border-[40px] border-black/50"></div>
                    
                    <div class="relative w-full h-full border-2 border-white/50 rounded-lg overflow-hidden box-border z-10">
                        {{-- Уголки --}}
                        <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-white rounded-tl-lg"></div>
                        <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-white rounded-tr-lg"></div>
                        <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-white rounded-bl-lg"></div>
                        <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-white rounded-br-lg"></div>

                        {{-- Лазер --}}
                        <div class="absolute top-0 left-0 w-full h-0.5 bg-red-500 shadow-[0_0_15px_red] animate-[scan_2s_infinite]"></div>
                    </div>
                </div>

                {{-- СТАТУС / ЗАГРУЗКА --}}
                <div id="status-msg" class="absolute inset-0 flex flex-col items-center justify-center bg-black/95 z-20">
                    <div id="loading-spinner" class="animate-spin rounded-full h-12 w-12 border-4 border-gray-600 border-t-indigo-500 mb-4"></div>
                    <span id="status-text" class="text-white font-bold text-sm tracking-wider uppercase">Запуск камеры...</span>
                    <button id="btn-retry" class="hidden mt-6 px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-full font-bold transition text-xs uppercase shadow-lg shadow-indigo-500/30">
                        Повторить
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Подключаем библиотеку --}}
    <script src="https://unpkg.com/qr-scanner@1.4.2/qr-scanner.legacy.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            const videoElem = document.getElementById('qr-video');
            const statusMsg = document.getElementById('status-msg');
            const statusText = document.getElementById('status-text');
            const spinner = document.getElementById('loading-spinner');
            const btnRetry = document.getElementById('btn-retry');

            const overlay = document.getElementById('scan-overlay');
            const oTitle = document.getElementById('overlay-title');
            const oBody = document.getElementById('overlay-body');
            const oIcon = document.getElementById('overlay-icon');

            const audioOk = new Audio('https://assets.mixkit.co/active_storage/sfx/1435/1435-preview.mp3');
            const audioErr = new Audio('https://assets.mixkit.co/active_storage/sfx/940/940-preview.mp3');
            const audioWarn = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');

            let scanner = null;
            let isBlocked = false;

            // === ФУНКЦИИ ИНТЕРФЕЙСА ===
            function showFullVerdict(status, title, body, icon) {
                if (navigator.vibrate) {
                    if (status === 'success') navigator.vibrate(200);
                    else navigator.vibrate([100, 50, 100, 50, 100]);
                }

                overlay.style.display = 'flex';
                
                if (status === 'success') {
                    overlay.style.backgroundColor = 'rgba(22, 163, 74, 1)'; // Зеленый
                    oIcon.innerText = '✅';
                    audioOk.currentTime = 0; audioOk.play().catch(()=>{});
                } 
                else if (status === 'warning') {
                    overlay.style.backgroundColor = 'rgba(234, 179, 8, 1)'; // Желтый
                    oIcon.innerText = '⚠️';
                    audioWarn.currentTime = 0; audioWarn.play().catch(()=>{});
                } 
                else {
                    overlay.style.backgroundColor = 'rgba(220, 38, 38, 1)'; // Красный
                    oIcon.innerText = '⛔';
                    audioErr.currentTime = 0; audioErr.play().catch(()=>{});
                }

                oTitle.innerText = title;
                oBody.innerText = body;
                if(icon) oIcon.innerText = icon;

                setTimeout(() => {
                    overlay.style.display = 'none';
                    setTimeout(() => { isBlocked = false; }, 500);
                }, 2500);
            }

            function showCamError(msg) {
                spinner.style.display = 'none';
                statusText.innerHTML = `<span class="text-red-500 font-bold">ОШИБКА</span><br><span class="text-[10px] text-gray-400 normal-case">${msg}</span>`;
                btnRetry.classList.remove('hidden');
            }

            // === СТАРТ СКАНЕРА ===
            function startScanner() {
                statusText.innerText = 'ЗАПУСК...';
                spinner.style.display = 'block';
                btnRetry.classList.add('hidden');
                statusMsg.style.display = 'flex';

                if (scanner) { scanner.destroy(); scanner = null; }

                scanner = new QrScanner(videoElem, result => {
                    if (isBlocked) return;

                    let code = (typeof result === 'object' && result.data) ? result.data : result;
                    if (!code) return;

                    console.log('Scan:', code);
                    isBlocked = true;

                    @this.checkTicket(code).catch(err => {
                        console.error(err);
                        showFullVerdict('error', 'СБОЙ', 'Ошибка сервера', '📡');
                    });

                }, {
                    highlightScanRegion: true,
                    highlightCodeOutline: true,
                    preferredCamera: 'environment',
                    maxScansPerSecond: 5,
                    // 👇 ВАЖНО: Принудительно задаем вертикальную область сканирования
                    calculateScanRegion: (video) => {
                        const w = video.videoWidth;
                        const h = video.videoHeight;
                        // Сканируем центральную часть (по сути весь экран, если обрезан CSS)
                        // Делаем регион 70% от меньшей стороны, но центрируем
                        const size = Math.min(w, h) * 0.8;
                        return {
                            x: Math.round((w - size) / 2),
                            y: Math.round((h - size) / 2),
                            width: size,
                            height: size
                        };
                    }
                });

                scanner.start().then(() => {
                    statusMsg.style.display = 'none';
                }).catch(err => {
                    console.error(err);
                    showCamError(err);
                });
            }

            btnRetry.addEventListener('click', startScanner);

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