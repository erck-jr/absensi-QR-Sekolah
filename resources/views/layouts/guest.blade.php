<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' | ' : '' }}{{ settings('app_name') ?? config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        
        @if(settings('app_logo'))
        <link rel="icon" type="image/x-icon" href="{{ asset(settings('app_logo')) }}">
        @endif

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased h-screen">
        <div class="flex flex-col lg:flex-row min-h-screen">
            <!-- Left Side: App Branding (Top on Mobile) -->
            <div class="w-full lg:w-1/2 bg-navy-900 flex flex-col justify-center items-center p-12 text-center text-white relative overflow-hidden">
                <!-- Background Decoration -->
                <div class="absolute -top-24 -left-24 w-64 h-64 bg-navy-800 rounded-full opacity-50"></div>
                <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-orange-600 rounded-full opacity-10"></div>
                
                <div class="relative z-10 flex flex-col items-center">
                    <a href="/" class="mb-6 transform hover:scale-110 transition-transform duration-300">
                        @if(settings('app_logo'))
                            <img src="{{ asset(settings('app_logo')) }}" alt="Logo" class="w-32 h-32 object-contain drop-shadow-2xl">
                        @else
                            <x-application-logo class="w-32 h-32 fill-current text-white drop-shadow-2xl" />
                        @endif
                    </a>
                    
                    <h1 class="text-4xl font-extrabold tracking-tight mb-2">
                        {{ settings('app_name') ?? config('app.name', 'Absensi Siswa') }}
                    </h1>
                    <div class="h-1.5 w-24 bg-orange-500 rounded-full mx-auto"></div>
                    <p class="mt-4 text-navy-200 text-lg max-w-sm">
                        {{ settings('app_description') ?? 'Sistem Informasi Absensi Siswa berbasis QR-Code yang Efisien dan Terpercaya.' }}
                    </p>
                </div>
            </div>

            <!-- Right Side: Content (Bottom on Mobile) -->
            <div class="w-full lg:w-1/2 bg-white flex flex-col justify-center items-center p-8 lg:p-16">
                <div class="w-full max-w-md">
                    {{ $slot }}
                </div>
                
                <div class="mt-12 text-center">
                    <p class="text-gray-400 text-xs">
                        &copy; {{ date('Y') }} {{ settings('app_name') ?? config('app.name', 'Laravel') }}. All rights reserved.
                    </p>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <x-sweetalert />
    </body>
</html>
