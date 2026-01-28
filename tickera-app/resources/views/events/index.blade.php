<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Афиша событий</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-10">

    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6 text-center">Афиша событий</h1>

        <div class="grid gap-6">
            @foreach($events as $event)
                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition">
                    <h2 class="text-2xl font-bold mb-2">{{ $event->title }}</h2>
                    <p class="text-gray-600 mb-4">{{ $event->start_time }} | {{ $event->location }}</p>
                    <p class="mb-4 text-gray-800">{{ Str::limit($event->description, 100) }}</p>
                    
                    <a href="{{ route('events.show', $event->id) }}" class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Купить билет
                    </a>
                </div>
            @endforeach
        </div>
    </div>

</body>
</html>