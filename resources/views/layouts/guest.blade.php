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
    <body>
        <div class="min-h-screen flex">
            <div class="flex-1 bg-blue-800 flex flex-col items-center justify-center py-2 px-6">
                <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                <h1 class="text-white text-2xl text-center">POWER COOL EQUIPMENTS (M) SDN BHD</h1>
            </div>
            <div class="flex-[2] bg-grey-[50] flex items-center justify-center">
                <div class="max-w-sm w-full">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
