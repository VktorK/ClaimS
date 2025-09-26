<x-layout>
    <!-- Hero Section -->
    <div class="relative overflow-hidden">
        <!-- Background with animated gradient -->
        <div class="absolute inset-0 bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-800 opacity-90"></div>
        <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/10 to-transparent"></div>
        
        <!-- Animated background elements -->
        <div class="absolute top-10 left-10 w-20 h-20 bg-white/10 rounded-full animate-pulse"></div>
        <div class="absolute top-32 right-20 w-16 h-16 bg-blue-300/20 rounded-full animate-bounce"></div>
        <div class="absolute bottom-20 left-1/4 w-12 h-12 bg-purple-300/20 rounded-full animate-ping"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="text-center">
                <!-- Main heading with gradient text -->
                <h1 class="text-5xl md:text-7xl font-bold mb-6 animate-fade-in">
                    <span class="bg-gradient-to-r from-white via-blue-100 to-purple-200 bg-clip-text text-transparent">
                        Добро пожаловать в
                    </span>
                    <br>
                    <span class="bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">
                        ClaimS
                    </span>
                </h1>
                
                <!-- Subtitle -->
                <p class="text-xl md:text-2xl text-white/90 mb-8 max-w-3xl mx-auto animate-slide-up">
                    Современная платформа для управления заявками с использованием Laravel, Livewire и JWT аутентификации
                </p>
                
                <!-- Action buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center animate-slide-up">
                    <a href="{{ route('livewire.demo') }}" 
                       class="group relative px-8 py-4 bg-white text-gray-900 rounded-xl font-semibold text-lg hover-lift transition-all duration-300 hover:shadow-2xl">
                        <span class="relative z-10">Livewire Demo</span>
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <span class="relative z-10 group-hover:text-white transition-colors duration-300">Livewire Demo</span>
                    </a>
                    
                    <a href="{{ route('jwt.demo') }}" 
                       class="group relative px-8 py-4 bg-transparent border-2 border-white text-white rounded-xl font-semibold text-lg hover-lift transition-all duration-300 hover:bg-white hover:text-gray-900">
                        <span class="relative z-10">JWT Auth Demo</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="py-20 bg-white dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Возможности платформы
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-300">
                    Инновационные технологии для современной веб-разработки
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="group p-8 bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-800 dark:to-gray-700 rounded-2xl hover-lift transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Livewire</h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        Интерактивные компоненты без написания JavaScript. Создавайте динамические интерфейсы с помощью PHP.
                    </p>
                </div>
                
                <!-- Feature 2 -->
                <div class="group p-8 bg-gradient-to-br from-purple-50 to-pink-100 dark:from-gray-800 dark:to-gray-700 rounded-2xl hover-lift transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">JWT Аутентификация</h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        Безопасная аутентификация с использованием JSON Web Tokens для API и веб-приложений.
                    </p>
                </div>
                
                <!-- Feature 3 -->
                <div class="group p-8 bg-gradient-to-br from-green-50 to-emerald-100 dark:from-gray-800 dark:to-gray-700 rounded-2xl hover-lift transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Laravel Framework</h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        Мощный PHP фреймворк с элегантным синтаксисом и богатой экосистемой пакетов.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Demo Section -->
    <div class="py-20 bg-gradient-to-br from-gray-50 to-blue-50 dark:from-gray-800 dark:to-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Попробуйте демо
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-300">
                    Интерактивные примеры работы с платформой
                </p>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <!-- Demo Cards -->
                <div class="space-y-6">
                    <!-- Livewire Demo Card -->
                    <div class="group relative p-8 bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover-lift transition-all duration-300">
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Livewire Counter</h3>
                                <p class="text-gray-600 dark:text-gray-300 mb-4">
                                    Интерактивный счетчик, демонстрирующий возможности Livewire без JavaScript
                                </p>
                                <a href="{{ route('livewire.demo') }}" 
                                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg font-medium hover:from-blue-600 hover:to-blue-700 transition-all duration-300">
                                    Попробовать
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- JWT Demo Card -->
                    <div class="group relative p-8 bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover-lift transition-all duration-300">
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-600 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">JWT Аутентификация</h3>
                                <p class="text-gray-600 dark:text-gray-300 mb-4">
                                    Полнофункциональная система регистрации и авторизации с JWT токенами
                                </p>
                                <a href="{{ route('jwt.demo') }}" 
                                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-pink-600 text-white rounded-lg font-medium hover:from-purple-600 hover:to-pink-700 transition-all duration-300">
                                    Попробовать
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Interactive Preview -->
                <div class="relative">
                    <div class="bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl p-8 text-white">
                        <h3 class="text-2xl font-bold mb-6">Интерактивная демонстрация</h3>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                                <span>Livewire компоненты работают в реальном времени</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-blue-400 rounded-full animate-pulse"></div>
                                <span>JWT токены обеспечивают безопасность</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-purple-400 rounded-full animate-pulse"></div>
                                <span>Адаптивный дизайн для всех устройств</span>
                            </div>
                        </div>
                        
                        <!-- Animated counter preview -->
                        <div class="mt-8 p-6 bg-white/10 rounded-xl backdrop-blur-sm">
                            <div class="text-center">
                                <div class="text-4xl font-bold mb-4 pulse-animation">42</div>
                                <p class="text-sm opacity-90">Интерактивный счетчик Livewire</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Technology Stack Section -->
    <div class="py-20 bg-white dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Технологический стек
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-300">
                    Современные технологии для создания лучших решений
                </p>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <!-- Laravel -->
                <div class="text-center group">
                    <div class="w-20 h-20 bg-gradient-to-r from-red-500 to-red-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                        <span class="text-white font-bold text-xl">L</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Laravel</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">PHP Framework</p>
                </div>
                
                <!-- Livewire -->
                <div class="text-center group">
                    <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                        <span class="text-white font-bold text-xl">⚡</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Livewire</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Full-stack Framework</p>
                </div>
                
                <!-- Tailwind CSS -->
                <div class="text-center group">
                    <div class="w-20 h-20 bg-gradient-to-r from-cyan-500 to-blue-500 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                        <span class="text-white font-bold text-xl">T</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tailwind CSS</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Utility-first CSS</p>
                </div>
                
                <!-- JWT -->
                <div class="text-center group">
                    <div class="w-20 h-20 bg-gradient-to-r from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                        <span class="text-white font-bold text-xl">🔐</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">JWT</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Authentication</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="py-20 bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h2 class="text-4xl font-bold text-white mb-6">
                Готовы начать?
            </h2>
            <p class="text-xl text-white/90 mb-8">
                Изучите возможности платформы и создайте что-то удивительное
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('livewire.demo') }}" 
                   class="px-8 py-4 bg-white text-gray-900 rounded-xl font-semibold text-lg hover-lift transition-all duration-300">
                    Начать с Livewire
                </a>
                <a href="{{ route('jwt.demo') }}" 
                   class="px-8 py-4 bg-transparent border-2 border-white text-white rounded-xl font-semibold text-lg hover-lift transition-all duration-300 hover:bg-white hover:text-gray-900">
                    Попробовать JWT Auth
                </a>
            </div>
        </div>
    </div>
</x-layout>
