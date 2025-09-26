<x-layout>
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto bg-white dark:bg-[#161615] p-8 rounded-lg shadow-lg">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">
                    API Тестирование
                </h2>
                <p class="text-lg text-[#706f6c] dark:text-[#A1A09A]">
                    Тестирование JWT API endpoints через cURL
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Регистрация -->
                <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-lg">
                    <h3 class="text-xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">
                        Тест регистрации
                    </h3>
                    <div class="bg-gray-900 text-green-400 p-4 rounded text-sm font-mono overflow-x-auto">
                        <div>curl -X POST http://127.0.0.1:8000/api/auth/register \</div>
                        <div class="ml-4">-H "Content-Type: application/json" \</div>
                        <div class="ml-4">-d '{</div>
                        <div class="ml-8">"name": "Test User",</div>
                        <div class="ml-8">"email": "test@example.com",</div>
                        <div class="ml-8">"password": "password123",</div>
                        <div class="ml-8">"password_confirmation": "password123"</div>
                        <div class="ml-4">}'</div>
                    </div>
                </div>

                <!-- Авторизация -->
                <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-lg">
                    <h3 class="text-xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">
                        Тест авторизации
                    </h3>
                    <div class="bg-gray-900 text-green-400 p-4 rounded text-sm font-mono overflow-x-auto">
                        <div>curl -X POST http://127.0.0.1:8000/api/auth/login \</div>
                        <div class="ml-4">-H "Content-Type: application/json" \</div>
                        <div class="ml-4">-d '{</div>
                        <div class="ml-8">"email": "test@example.com",</div>
                        <div class="ml-8">"password": "password123"</div>
                        <div class="ml-4">}'</div>
                    </div>
                </div>

                <!-- Защищенный запрос -->
                <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-lg">
                    <h3 class="text-xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">
                        Тест защищенного запроса
                    </h3>
                    <div class="bg-gray-900 text-green-400 p-4 rounded text-sm font-mono overflow-x-auto">
                        <div>curl -X GET http://127.0.0.1:8000/api/auth/me \</div>
                        <div class="ml-4">-H "Authorization: Bearer YOUR_JWT_TOKEN"</div>
                    </div>
                </div>

                <!-- Обновление токена -->
                <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-lg">
                    <h3 class="text-xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">
                        Тест обновления токена
                    </h3>
                    <div class="bg-gray-900 text-green-400 p-4 rounded text-sm font-mono overflow-x-auto">
                        <div>curl -X POST http://127.0.0.1:8000/api/auth/refresh \</div>
                        <div class="ml-4">-H "Authorization: Bearer YOUR_JWT_TOKEN"</div>
                    </div>
                </div>
            </div>

            <!-- Livewire Demo -->
            <div class="mt-8">
                <h3 class="text-xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">
                    Интерактивная демонстрация
                </h3>
                <livewire:auth-demo />
            </div>

            <!-- Навигация -->
            <div class="mt-8 text-center">
                <a href="/" class="inline-block px-6 py-3 bg-[#1b1b18] dark:bg-[#eeeeec] dark:text-[#1C1C1A] text-white rounded-md hover:bg-black dark:hover:bg-white transition-colors duration-200">
                    Назад на главную
                </a>
            </div>
        </div>
    </div>
</x-layout>