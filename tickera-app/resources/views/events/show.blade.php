<x-app-layout>
    <div class="container mx-auto px-6 py-10">
        <div class="flex flex-col lg:flex-row gap-10">
            <div class="w-full lg:w-1/3">
                @if($event->image)
                    <img src="{{ Storage::url($event->image) }}" class="rounded-xl shadow-2xl w-full">
                @else
                    <div class="aspect-[2/3] bg-gray-800 rounded-xl flex items-center justify-center">Нет фото</div>
                @endif
            </div>
            <div class="flex-1">
                <h1 class="text-5xl font-black mb-6">{{ $event->title }}</h1>
                <div class="flex flex-wrap gap-4 mb-8 text-sm font-medium text-gray-400">
                    <span class="px-3 py-1 bg-white/10 rounded-md">{{ \Carbon\Carbon::parse($event->start_time)->translatedFormat('d F Y, H:i') }}</span>
                    <span class="px-3 py-1 bg-white/10 rounded-md">{{ $event->hall->name ?? 'Зал' }}</span>
                    <span class="px-3 py-1 bg-white/10 rounded-md">{{ $event->city }}</span>
                </div>
                <div class="prose prose-invert max-w-none text-gray-300 mb-10">
                    {!! $event->description !!}
                </div>
                <a href="{{ route('events.tickets', $event) }}" class="inline-block px-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl transition">
                    Купить билеты
                </a>
            </div>
        </div>
    </div>
</x-app-layout>