<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" href="{{ asset('/favicon.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body>
    <div class="min-h-screen flex">
        <div class="flex-1 bg-blue-800 flex-col items-center justify-center py-2 px-6 hidden lg:flex">
            <div class="w-full max-w-40 mb-2">
                <x-application-logo class="fill-current text-gray-500" />
            </div>
            <h1 class="text-white text-2xl text-center">POWER COOL EQUIPMENTS (M) SDN BHD</h1>
        </div>
        <div class="flex-[2] p-4">
            <div class="flex flex-col h-full">
                <!-- Language -->
                <div class="flex justify-end">
                    <x-app.language-selector />
                </div>
                <!-- Auth  -->
                <div class="flex items-center justify-center h-full">
                    <div class="max-w-sm w-full">
                        <div class="flex lg:hidden flex-col items-center justify-center py-2 px-6 mb-6">
                            <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                            <h1 class="text-2xl text-center text-blue-800 font-medium">POWER COOL EQUIPMENTS (M) SDN BHD
                            </h1>
                        </div>
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

@stack('scripts')

</html>
