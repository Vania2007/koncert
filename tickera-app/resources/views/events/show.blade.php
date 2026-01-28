<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>{{ $event->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-10">

    <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow-lg">
        <a href="{{ route('events.index') }}" class="text-blue-600 hover:underline mb-6 inline-block">
            &larr; Назад к афише
        </a>

        <h1 class="text-4xl font-bold mb-4">{{ $event->title }}</h1>
        <div class="flex text-gray-600 mb-6 text-lg">
            <span class="mr-6">📅 {{ \Carbon\Carbon::parse($event->start_time)->format('d.m.Y H:i') }}</span>
            <span>📍 {{ $event->location }}</span>
        </div>

        <div class="mb-8 text-gray-800 leading-relaxed text-lg">
            {{ $event->description }}
        </div>

        <hr class="my-8 border-gray-200">

        <h3 class="text-2xl font-bold mb-6">Выберите билеты</h3>

        <form action="{{ route('order.store') }}" method="POST">
            @csrf
            
            <div class="bg-gray-50 p-6 rounded-lg mb-6 border">
                <h4 class="font-bold text-lg mb-4">Ваши данные</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-600 mb-1">Ваше имя</label>
                        <input type="text" name="name" required placeholder="Иван Иванов" class="w-full border rounded p-2">
                    </div>
                    <div>
                        <label class="block text-gray-600 mb-1">Email</label>
                        <input type="email" name="email" required placeholder="ivan@example.com" class="w-full border rounded p-2">
                    </div>
                </div>
            </div>

            <div class="space-y-4 mb-8">
                @foreach($event->ticketTypes as $type)
                    <div class="flex items-center justify-between border p-4 rounded-lg hover:bg-gray-50 transition">
                        <div>
                            <h4 class="font-bold text-xl">{{ $type->name }}</h4>
                            <p class="text-gray-600 text-lg">{{ number_format($type->price, 0, '.', ' ') }} ₴</p>
                            <p class="text-sm text-gray-400">Доступно: {{ $type->quantity }}</p>
                        </div>
                        <div class="flex items-center">
                            <label class="mr-3 text-gray-500">Кол-во:</label>
                            <input type="number" 
                                   name="tickets[{{ $type->id }}]" 
                                   min="0" 
                                   max="{{ $type->quantity }}" 
                                   value="0" 
                                   class="border border-gray-300 rounded p-2 w-20 text-center text-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="submit" class="w-full bg-green-600 text-white font-bold py-4 rounded-lg hover:bg-green-700 transition text-xl shadow-md">
                Оформить заказ
            </button>
        </form>
    </div>

</body>
</html>