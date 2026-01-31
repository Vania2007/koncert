<x-app-layout :title="$event->title">
    
    <div class="absolute top-0 left-0 w-full h-[60vh] overflow-hidden -z-10">
        @if($event->image)
            <img src="{{ Storage::url($event->image) }}" class="w-full h-full object-cover opacity-20 blur-3xl scale-110">
        @endif
        <div class="absolute inset-0 bg-gradient-to-b from-[#050505]/50 via-[#050505]/80 to-[#050505]"></div>
    </div>

    <div class="container mx-auto px-6 mt-10">
        <div class="flex flex-col lg:flex-row gap-12">
            
            <div class="w-full lg:w-1/3 flex-shrink-0">
                <div class="aspect-[2/3] rounded-2xl overflow-hidden shadow-2xl border border-white/10 sticky top-24">
                    @if($event->image)
                        <img src="{{ Storage::url($event->image) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full bg-gray-800 flex items-center justify-center">Нет фото</div>
                    @endif
                </div>
            </div>

            <div class="w-full lg:w-2/3 flex flex-col justify-center">
                
                <div class="mb-6">
                    <span class="inline-block px-3 py-1 bg-indigo-500/20 text-indigo-400 rounded-full text-xs font-bold uppercase tracking-widest mb-4">
                        Концерт
                    </span>
                    <h1 class="text-4xl md:text-6xl font-black text-white leading-tight mb-2">
                        {{ $event->title }}
                    </h1>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-white/5 rounded-2xl border border-white/5 backdrop-blur-sm mb-8">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-indigo-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase">Дата и время</p>
                            <p class="text-white font-bold">{{ \Carbon\Carbon::parse($event->start_time)->translatedFormat('d F Y, H:i') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-indigo-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase">Место проведения</p>
                            <p class="text-white font-bold">{{ $event->city }}, {{ $event->hall->name ?? 'Зал не указан' }}</p>
                            <p class="text-xs text-gray-500">{{ $event->location }}</p>
                        </div>
                    </div>
                </div>

                <div class="prose prose-invert max-w-none text-gray-300 mb-10">
                    {!! $event->description !!}
                </div>

                <div class="border-t border-white/10 pt-8">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <p class="text-sm text-gray-400">Стоимость билетов:</p>
                            @php
                                $min = $event->ticketTypes->min('price');
                                $max = $event->ticketTypes->max('price');
                            @endphp
                            <p class="text-2xl font-bold text-white">
                                {{ $min ? ($min == $max ? $min . ' ₴' : "$min - $max ₴") : 'Нет в продаже' }}
                            </p>
                        </div>
                        
                        <a href="{{ route('events.tickets', $event) }}" class="px-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-lg shadow-lg shadow-indigo-500/30 transition transform hover:scale-105">
                            Выбрать место
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>