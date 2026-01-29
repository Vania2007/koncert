<div 
    x-data="hallBuilder()"
    x-init="init()"
    class="border border-gray-700 rounded-xl bg-[#1a1a1a] overflow-hidden shadow-2xl select-none"
>
    <div class="bg-gray-800 px-4 py-3 border-b border-gray-700 flex justify-between items-center text-gray-300">
        <div class="flex items-center gap-4">
            <span class="font-bold text-white">🖥 СХЕМА ЗАЛА</span>
            <span class="text-xs bg-gray-700 px-2 py-1 rounded">Холст: 800x600px</span>
        </div>
        <div class="text-xs text-yellow-500 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11"></path></svg>
            Зажмите сектор мышкой, чтобы переместить
        </div>
    </div>

    <div 
        x-ref="canvas"
        class="relative w-full h-[600px] bg-[#121212] overflow-hidden cursor-crosshair group"
        @mousedown.self="deselectAll()"
        style="background-image: radial-gradient(#333 1px, transparent 1px); background-size: 40px 40px;"
    >
        <div class="absolute top-5 left-1/2 transform -translate-x-1/2 w-[600px] pointer-events-none opacity-90 z-0">
            <svg viewBox="0 0 800 40" fill="none" class="w-full drop-shadow-[0_0_25px_rgba(255,255,255,0.15)]">
                <path d="M10,35 Q400,-15 790,35" stroke="#444" stroke-width="4" fill="none" />
                <path d="M10,35 Q400,-15 790,35" stroke="url(#screenGlow)" stroke-width="2" fill="none" />
                
                <defs>
                    <linearGradient id="screenGlow" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="transparent" />
                        <stop offset="50%" stop-color="#00d4ff" /> <stop offset="100%" stop-color="transparent" />
                    </linearGradient>
                </defs>
            </svg>
            <div class="text-center text-[10px] text-gray-500 mt-2 font-bold tracking-[1em] uppercase">ЭКРАН СЦЕНЫ</div>
        </div>

        <template x-for="(uuid, index) in Object.keys(generators)" :key="uuid">
            <div
                class="absolute flex flex-col items-center transition-shadow duration-150 rounded"
                :class="draggingUuid === uuid ? 'z-50 cursor-grabbing' : 'z-10 cursor-grab hover:z-40'"
                :style="getStyle(uuid)"
                @mousedown.prevent="startDrag($event, uuid)"
            >
                <div 
                    class="mb-1 px-2 py-0.5 text-[10px] font-bold text-white rounded shadow-sm whitespace-nowrap border border-white/10"
                    :class="draggingUuid === uuid ? 'bg-indigo-500' : 'bg-gray-700 group-hover:bg-gray-600'"
                >
                    <span x-text="generators[uuid].section_name || 'Сектор ' + (index + 1)"></span>
                    <span class="opacity-60 font-normal ml-1" x-text="generators[uuid].rows + 'x' + generators[uuid].seats_per_row"></span>
                </div>

                <div 
                    class="grid gap-1 p-2 rounded border border-dashed transition-colors bg-black/20"
                    :class="draggingUuid === uuid ? 'border-indigo-500 bg-indigo-500/10' : 'border-gray-600 hover:border-gray-500'"
                    :style="`grid-template-columns: repeat(${generators[uuid].seats_per_row}, 1fr);`"
                >
                    <template x-for="i in (parseInt(generators[uuid].rows) * parseInt(generators[uuid].seats_per_row))">
                        <div class="w-5 h-5 bg-gray-600 rounded-t-sm rounded-b-lg shadow-sm border-t border-gray-500"></div>
                    </template>
                </div>
            </div>
        </template>
        
        <div x-show="Object.keys(generators).length === 0" class="absolute inset-0 flex flex-col items-center justify-center text-gray-600 pointer-events-none">
            <svg class="w-16 h-16 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            <p class="text-lg font-medium">Зал пуст</p>
            <p class="text-sm">Добавьте сектор в панели ниже 👇</p>
        </div>
    </div>
</div>

<script>
function hallBuilder() {
    return {
        generators: @entangle('data.seat_generators'),
        draggingUuid: null,
        startX: 0,
        startY: 0,
        initialLeft: 0,
        initialTop: 0,

        // Размеры для расчета (должны совпадать с CSS: w-5 + gap-1 = 20px + 4px = 24px)
        seatWidth: 20, 
        seatGap: 4, 
        padding: 16, // p-2 с двух сторон

        init() {
            // Глобальные слушатели, чтобы мышь не срывалась
            window.addEventListener('mousemove', (e) => this.onMouseMove(e));
            window.addEventListener('mouseup', () => this.onMouseUp());
        },

        getStyle(uuid) {
            let block = this.generators[uuid];
            let x = parseInt(block.x) || 100; // По умолчанию отступ 100px
            let y = parseInt(block.y) || 150; // По умолчанию ниже сцены

            return `left: ${x}px; top: ${y}px; user-select: none;`;
        },

        startDrag(e, uuid) {
            // Если кликнули, начинаем тянуть
            this.draggingUuid = uuid;
            this.startX = e.clientX;
            this.startY = e.clientY;
            
            let block = this.generators[uuid];
            this.initialLeft = parseInt(block.x) || 100;
            this.initialTop = parseInt(block.y) || 150;
        },

        onMouseMove(e) {
            if (!this.draggingUuid) return;
            e.preventDefault(); // Чтобы не выделялся текст

            // На сколько сдвинулась мышь
            const dx = e.clientX - this.startX;
            const dy = e.clientY - this.startY;

            // Новые координаты
            let newX = this.initialLeft + dx;
            let newY = this.initialTop + dy;

            // Сетка 10px (Snap to grid)
            newX = Math.round(newX / 10) * 10;
            newY = Math.round(newY / 10) * 10;

            // Ограничения (чтобы не улетел за экран)
            if (newX < 0) newX = 0;
            if (newY < 0) newY = 0;
            if (newX > 750) newX = 750;

            // Сохраняем в Livewire
            this.generators[this.draggingUuid].x = newX;
            this.generators[this.draggingUuid].y = newY;
        },

        onMouseUp() {
            this.draggingUuid = null;
        },

        deselectAll() {
            this.draggingUuid = null;
        }
    }
}
</script>