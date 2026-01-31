<x-app-layout title="Афиша мероприятий">
    
    <div class="relative container mx-auto px-6 mb-16 mt-8">
        <div class="flex flex-col items-center text-center space-y-4">
            <span class="px-3 py-1 rounded-full border border-indigo-500/30 bg-indigo-500/10 text-indigo-400 text-xs font-bold uppercase tracking-widest">
                Премьеры сезона
            </span>
            <h1 class="text-5xl md:text-7xl font-black text-transparent bg-clip-text bg-gradient-to-r from-white via-gray-200 to-gray-500">
                АФИША
            </h1>
            <p class="text-gray-400 max-w-2xl text-lg">
                Лучшие концерты, театральные постановки и шоу вашего города. Бронируйте лучшие места онлайн.
            </p>
        </div>
    </div>

    <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-x-8 gap-y-12">
            
            @foreach($events as $event)
            <div class="group relative flex flex-col">
                
                <div class="relative w-full aspect-[2/3] rounded-2xl overflow-hidden mb-6 bg-gray-800 shadow-2xl transition-all duration-500 group-hover:shadow-[0_0_30px_rgba(99,102,241,0.3)] group-hover:-translate-y-2">
                    
                    @if($event->image)
                        <img src="{{ Storage::url($event->image) }}" alt="{{ $event->title }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-gray-800 to-gray-900 text-gray-700">
                            <svg class="w-16 h-16 mb-2 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="text-xs uppercase font-bold tracking-widest opacity-30">Нет постера</span>
                        </div>
                    @endif

                    <div class="absolute top-4 left-4 bg-white/10 backdrop-blur-md border border-white/20 text-white px-3 py-1.5 rounded-lg flex flex-col items-center text-xs font-bold leading-tight">
                        <span class="text-lg">{{ \Carbon\Carbon::parse($event->start_time)->format('d') }}</span>
                        <span class="uppercase text-[10px]">{{ \Carbon\Carbon::parse($event->start_time)->translatedFormat('M') }}</span>
                    </div>

                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                        <a href="{{ route('events.show', $event) }}" class="transform translate-y-4 group-hover:translate-y-0 transition duration-300 px-6 py-3 bg-white text-black font-bold rounded-full hover:bg-indigo-500 hover:text-white">
                            Купить билет
                        </a>
                    </div>
                </div>

                <div class="flex flex-col flex-grow">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-xl font-bold text-white leading-tight group-hover:text-indigo-400 transition">
                            <a href="{{ route('events.show', $event) }}">{{ $event->title }}</a>
                        </h3>
                    </div>

                    <div class="text-sm text-gray-400 mb-4 flex flex-col gap-1">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span>{{ $event->city ?? 'Киев' }}, {{ Str::limit($event->location, 20) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>{{ \Carbon\Carbon::parse($event->start_time)->format('H:i') }}</span>
                        </div>
                    </div>

                    <div class="mt-auto pt-4 border-t border-gray-800">
                        <a href="{{ route('events.show', $event) }}" class="block w-full py-3 rounded-xl border border-gray-700 hover:border-indigo-500 hover:bg-indigo-600/10 text-center text-sm font-bold text-gray-300 hover:text-white transition group-active:scale-95">
                            Выбрать билет
                        </a>
                    </div>
                </div>

            </div>
            @endforeach

        </div>
    </div>

</x-app-layout>