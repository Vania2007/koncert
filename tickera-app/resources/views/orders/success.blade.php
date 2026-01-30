<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Заказ успешно оплачен</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0f0f0f] text-white flex items-center justify-center min-h-screen p-4">

    <div class="bg-[#18181b] p-8 rounded-2xl border border-gray-800 text-center max-w-md w-full shadow-2xl">
        <div class="w-20 h-20 bg-green-500/20 text-green-500 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        
        <h1 class="text-2xl font-bold mb-2">Билеты оплачены!</h1>
        <p class="text-gray-400 mb-6">Номер заказа: #{{ $order->id }}</p>

        <div class="bg-gray-800 rounded-xl p-4 mb-6 text-left space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">Сумма:</span>
                <span class="font-bold">{{ $order->total_amount }} ₴</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Билетов:</span>
                <span>{{ $order->tickets->count() }} шт.</span>
            </div>
        </div>

        <div class="space-y-3">
            <a href="{{ route('order.download', $order) }}" class="flex items-center justify-center gap-2 w-full py-4 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl transition shadow-lg shadow-indigo-500/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Скачать билеты (PDF/ZIP)</span>
            </a>

            <a href="{{ route('events.index') }}" class="block w-full py-3 bg-transparent border border-gray-700 text-gray-400 hover:text-white hover:border-white font-medium rounded-xl transition">
                Вернуться к афише
            </a>
        </div>
    </div>

</body>
</html>