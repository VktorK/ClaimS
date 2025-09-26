<x-layout>
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto bg-white dark:bg-[#161615] p-8 rounded-lg shadow-lg">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">
                    Тест JWT Аутентификации
                </h2>
                <p class="text-lg text-[#706f6c] dark:text-[#A1A09A]">
                    Простой тест регистрации и входа
                </p>
            </div>

            <livewire:auth-demo />
            
            <div class="mt-8 text-center">
                <a href="/" class="inline-block px-6 py-3 bg-[#1b1b18] dark:bg-[#eeeeec] dark:text-[#1C1C1A] text-white rounded-md hover:bg-black dark:hover:bg-white transition-colors duration-200">
                    Назад на главную
                </a>
            </div>
        </div>
    </div>
</x-layout>
