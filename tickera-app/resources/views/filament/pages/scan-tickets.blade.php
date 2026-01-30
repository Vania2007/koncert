<x-filament-panels::page>

    <div id="scan-overlay" class="hidden fixed inset-0 z-[9999] flex-col items-center justify-center text-center p-4 transition-all duration-300">
        <div id="overlay-icon" class="text-9xl mb-8 drop-shadow-md"></div>
        <h2 id="overlay-title" class="text-5xl md:text-7xl font-black mb-4 uppercase tracking-wider drop-shadow-sm"></h2>
        <p id="overlay-body" class="text-2xl md:text-3xl font-bold opacity-90"></p>
    </div>

    <div class="flex flex-col items-center justify-center min-h-[60vh]">

        <div class="mb-4 text-center">
            <h2 class="text-xl font-bold">Быстрый сканер</h2>
        </div>

        <div wire:ignore class="relative w-full max-w-md mx-auto">
            <div class="bg-black rounded-3xl overflow-hidden shadow-2xl border-4 border-gray-800 relative aspect-square">

                <video id="qr-video" class="w-full h-full object-cover"></video>

                <div class="absolute inset-0 border-[30px] border-black/30 pointer-events-none">
                    <div class="border-2 border-white/50 w-full h-full rounded-lg"></div>
                </div>

                <div id="status-msg" class="absolute inset-0 flex flex-col items-center justify-center text-white bg-black/80 p-4 text-center">
                    <div id="loading-spinner" class="animate-spin rounded-full h-10 w-10 border-b-2 border-white mb-4"></div>
                    <span id="status-text" class="text-sm font-mono">Запуск камеры...</span>
                    <button id="btn-retry" class="hidden mt-4 px-4 py-2 bg-white text-black rounded font-bold text-xs">
                        Попробовать снова
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <button id="switch-cam" class="hidden px-4 py-2 bg-gray-200 rounded-lg text-sm hover:bg-gray-300">
                🔄 Сменить камеру
            </button>
        </div>

    </div>

    <script src="https://unpkg.com/qr-scanner@1.4.2/qr-scanner.legacy.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Элементы
            const videoElem = document.getElementById('qr-video');
            const statusMsg = document.getElementById('status-msg');
            const statusText = document.getElementById('status-text');
            const spinner = document.getElementById('loading-spinner');
            const btnRetry = document.getElementById('btn-retry');
            const btnSwitch = document.getElementById('switch-cam');

            // Оверлей
            const overlay = document.getElementById('scan-overlay');
            const overlayTitle = document.getElementById('overlay-title');
            const overlayBody = document.getElementById('overlay-body');
            const overlayIcon = document.getElementById('overlay-icon');

            // Звуки
            const audioSuccess = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
            const audioError = new Audio('https://assets.mixkit.co/active_storage/sfx/257/257-preview.mp3');

            let scanner = null;
            let isBlocked = false;

            // --- ФУНКЦИИ ИНТЕРФЕЙСА ---
            function showError(msg) {
                spinner.style.display = 'none';
                statusText.innerHTML = `<span class="text-red-400">ОШИБКА:</span><br>${msg}`;
                btnRetry.classList.remove('hidden');
            }

            function showOverlay(type, title, message) {
                overlay.classList.remove('hidden');
                overlay.className = 'fixed inset-0 z-[9999] flex flex-col items-center justify-center text-center p-4 transition-all duration-300 backdrop-blur-md';

                if (type === 'success') {
                    overlay.classList.add('bg-green-600/95', 'text-white');
                    overlayIcon.innerHTML = '✅';
                    audioSuccess.play().catch(()=>{});
                } else if (type === 'error') {
                    overlay.classList.add('bg-red-600/95', 'text-white');
                    overlayIcon.innerHTML = '⛔';
                    audioError.play().catch(()=>{});
                } else {
                    overlay.classList.add('bg-yellow-500/95', 'text-black');
                    overlayIcon.innerHTML = '⚠️';
                    audioError.play().catch(()=>{});
                }
                overlayTitle.innerText = title;
                overlayBody.innerText = message;
            }

            function hideOverlay() {
                overlay.classList.add('hidden');
                isBlocked = false;
            }

            // --- ИНИЦИАЛИЗАЦИЯ СКАНЕРА ---
            function startScanner() {
                // Сброс UI
                statusText.innerText = 'Запуск камеры...';
                spinner.style.display = 'block';
                btnRetry.classList.add('hidden');
                statusMsg.style.display = 'flex'; // Показываем экран загрузки

                // Создаем сканер
                if (!scanner) {
                    scanner = new QrScanner(videoElem, result => {
                        if (isBlocked) return;

                        console.log('Scanned:', result);
                        isBlocked = true;

                        // Проверка билета
                        @this.checkTicket(result).catch(err => {
                            console.error(err);
                            showOverlay('warning', 'ОШИБКА', 'Нет связи с сервером');
                            setTimeout(hideOverlay, 3000);
                        });

                    }, {
                        // Опции для лучшей совместимости
                        onDecodeError: error => {},
                        highlightScanRegion: true,
                        highlightCodeOutline: true,
                    });
                }

                // Запускаем
                scanner.start()
                    .then(() => {
                        // Успех! Скрываем экран загрузки
                        statusMsg.style.display = 'none';

                        // Проверяем наличие других камер
                        QrScanner.listCameras(true).then(cameras => {
                            if (cameras.length > 1) btnSwitch.classList.remove('hidden');
                        });
                    })
                    .catch(err => {
                        console.error("Camera start error:", err);
                        // Выводим понятную ошибку на экран
                        if (err.toString().includes('Permission denied')) {
                            showError('Доступ к камере запрещен.<br>Разрешите доступ в настройках браузера.');
                        } else if (err.toString().includes('Secure Context')) {
                            showError('Камера работает только по HTTPS или на localhost!<br>На телефоне нужен https://.');
                        } else {
                            showError(err.toString());
                        }
                    });
                    if (!scanner) {
    scanner = new QrScanner(videoElem, result => {
        if (isBlocked) return;

        isBlocked = true;

        // Передаем только текстовые данные (result.data или сам result)
        const codeValue = (typeof result === 'object') ? result.data : result;

        console.log('Scanned Code:', codeValue);

        @this.checkTicket(codeValue).catch(err => {
            console.error(err);
            showOverlay('warning', 'ОШИБКА', 'Нет связи с сервером');
            setTimeout(hideOverlay, 3000);
        });

    }, {
        highlightScanRegion: true,
        highlightCodeOutline: true,
    });
}
            }

            // --- КНОПКИ ---
            btnRetry.addEventListener('click', startScanner);

            btnSwitch.addEventListener('click', () => {
                QrScanner.listCameras(true).then(cameras => {
                    // Простая переключалка (циклическая)
                    // Для реального продакшена лучше делать выпадающий список
                    // но пока просто пересоздадим сканер с новой камерой.
                    alert('Функция переключения пока упрощена. Попробуйте обновить страницу.');
                });
            });

            // --- СЛУШАЕМ PHP ---
            window.addEventListener('scan-finished', event => {
                const data = event.detail;
                showOverlay(data.status, data.title, data.body);
                setTimeout(hideOverlay, 2500);
            });

            // Старт при загрузке
            startScanner();
        });
    </script>

    <style>
        video { transform: scaleX(-1); } /* Зеркало */
    </style>

</x-filament-panels::page>
