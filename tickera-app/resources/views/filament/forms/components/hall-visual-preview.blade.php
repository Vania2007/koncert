<div 
    x-data="{ 
        generators: @entangle('data.seat_generators'),
        colors: ['bg-blue-500', 'bg-red-500', 'bg-green-500', 'bg-yellow-500', 'bg-purple-500']
    }"
    class="border border-gray-700 rounded-lg p-4 bg-gray-900 overflow-hidden"
>
    <p class="text-sm text-gray-400 mb-4">👀 Предпросмотр схемы:</p>
    
    <div class="relative min-h-[300px] w-full bg-gray-800 rounded flex flex-col gap-4 p-4 overflow-auto">
        <template x-for="(block, index) in generators" :key="index">
            <div class="mb-4">
                <div class="text-xs font-bold text-white mb-1" x-text="block.section_name || 'Сектор ' + (index + 1)"></div>
                
                <div class="flex flex-col gap-1">
                    <template x-for="r in parseInt(block.rows) || 0">
                        <div class="flex gap-1">
                            <template x-for="s in parseInt(block.seats_per_row) || 0">
                                <div 
                                    class="w-6 h-6 rounded-sm text-[8px] flex items-center justify-center text-white opacity-80"
                                    :class="colors[index % colors.length]"
                                    :title="'Ряд ' + r + ', Место ' + s"
                                >
                                    <span x-text="s"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </template>
        
        <div x-show="!generators || generators.length === 0" class="text-center text-gray-500 py-10">
            Добавьте блоки мест, чтобы увидеть схему
        </div>
    </div>
</div>