<x-layout>
    <div class="container mx-auto px-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">
                Демонстрация Livewire
            </h1>
            <p class="text-lg text-[#706f6c] dark:text-[#A1A09A]">
                Пример интерактивного компонента на Livewire
            </p>
        </div>

        <livewire:counter />
        
        <div class="mt-8 text-center">
            <a href="/" class="inline-block px-6 py-3 bg-[#1b1b18] dark:bg-[#eeeeec] dark:text-[#1C1C1A] text-white rounded-md hover:bg-black dark:hover:bg-white transition-colors duration-200">
                Назад на главную
            </a>
        </div>
    </div>
</x-layout>
