<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div class="flex flex-col items-center gap-4">
                <div class="h-16 w-16 rounded-2xl bg-indigo-600 flex items-center justify-center shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6zM2 9c-.553 0-1 .447-1 1v1h2v8a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3v-8h2v-1c0-.553-.447-1-1-1H2zm6 2h4v8a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-8z"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold text-indigo-600">HRMS</h1>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
