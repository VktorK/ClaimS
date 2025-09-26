<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Auth Demo - {{ config('app.name', 'Laravel') }}</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app"></div>
    
    <!-- Дополнительная информация для демонстрации -->
    <div class="fixed bottom-4 right-4 bg-blue-600 text-white p-4 rounded-lg shadow-lg max-w-sm">
        <h3 class="font-bold mb-2">🔐 Демо авторизации</h3>
        <p class="text-sm mb-2">Попробуйте:</p>
        <ul class="text-xs space-y-1">
            <li>• Регистрацию нового пользователя</li>
            <li>• Вход в систему</li>
            <li>• Просмотр профиля</li>
            <li>• Выход из системы</li>
        </ul>
        <p class="text-xs mt-2 opacity-75">
            API: <a href="/api/info" target="_blank" class="underline">/api/info</a>
        </p>
    </div>
</body>
</html>
