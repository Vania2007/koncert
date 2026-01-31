<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickera</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { background: #0a0a0a; color: white; font-family: sans-serif; }</style>
</head>
<body>
    <nav class="p-6 border-b border-white/10 flex justify-between items-center bg-black/50 backdrop-blur-md fixed w-full z-50 top-0">
        <a href="/" class="text-2xl font-bold tracking-tighter">TICKERA</a>
        <a href="/admin" class="text-sm font-medium text-gray-400 hover:text-white transition">Войти</a>
    </nav>
    <main class="pt-24 min-h-screen">
        {{ $slot }}
    </main>
</body>
</html>