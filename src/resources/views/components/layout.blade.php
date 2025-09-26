<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Livewire Styles -->
        @livewireStyles
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] antialiased">
        <div class="min-h-screen">
            <!-- Navigation -->
            <nav class="bg-white dark:bg-[#161615] border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex items-center">
                            <h1 class="text-xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                                {{ config('app.name', 'Laravel') }}
                            </h1>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <a href="/" class="text-[#1b1b18] dark:text-[#EDEDEC] hover:text-[#f53003] dark:hover:text-[#FF4433] transition-colors">
                                Главная
                            </a>
                            <a href="{{ route('livewire.demo') }}" class="text-[#1b1b18] dark:text-[#EDEDEC] hover:text-[#f53003] dark:hover:text-[#FF4433] transition-colors">
                                Livewire Demo
                            </a>
                            <a href="{{ route('jwt.demo') }}" class="text-[#1b1b18] dark:text-[#EDEDEC] hover:text-[#f53003] dark:hover:text-[#FF4433] transition-colors">
                                JWT Auth Demo
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="py-12">
                {{ $slot }}
            </main>
        </div>

        <!-- Livewire Scripts -->
        @livewireScripts
    </body>
</html>