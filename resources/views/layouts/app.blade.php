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

        <!-- Daterangepicker -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.45/moment-timezone.min.js"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.css">

        <!-- Datatables -->
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

        <!-- Select2 -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

        @stack('styles')
    </head>
    <body class="font-sans antialiased overflow-x-hidden">
        <div class="min-h-screen flex">
            @include('layouts.sidebar')
    
            <main class="p-4 w-full bg-gray-50 flex-1 overflow-hidden">
                @yield('content')
            </main>
        </div>
    </body>

    <script>
        moment.tz.setDefault("Asia/Kuala_Lumpur");
        
        $('body').on('keydown', '.phone-input', function(e) {
            let allowedKeys = ['backspace', 'arrowleft', 'arrowright', 'tab', '+', '-', ' ']
            let val = $(this).find('input').val()

            var evtobj = window.event? event : e
            if (['c', 'v', 'a'].includes(e.key) && (evtobj.ctrlKey || evtobj.metaKey)) {
                // console.debug( "Ctrl+z" )
            } else if (!/[0-9]/.test(e.key) && !allowedKeys.includes(e.key.toLowerCase())) {
                e.preventDefault()
            }
        })

        $('body').on('keydown', '.int-input', function(e) {
            let allowedKeys = ['backspace', 'arrowleft', 'arrowright', 'tab']
            let val = $(this).find('input').val()

            var evtobj = window.event? event : e
            if (['c', 'v', 'a'].includes(e.key) && (evtobj.ctrlKey || evtobj.metaKey)) {
                // console.debug( "Ctrl+z" )
            } else if (!/[0-9]/.test(e.key) && !allowedKeys.includes(e.key.toLowerCase())) {
                e.preventDefault()
            }
        })

        $('body').on('keydown', '.decimal-input', function(e) {
            let allowedKeys = ['backspace', '.', 'arrowleft', 'arrowright', 'tab']
            let val = $(this).find('input').val()

            if (val.includes('.')) {
                allowedKeys = ['backspace', 'arrowleft', 'arrowright', 'tab']
            }

            var evtobj = window.event? event : e
            if (['c', 'v', 'a'].includes(e.key) && (evtobj.ctrlKey || evtobj.metaKey)) {
                // console.debug( "Ctrl+z" )
            } else if (!/[0-9]/.test(e.key) && !allowedKeys.includes(e.key.toLowerCase())) {
                e.preventDefault()
            }
        })

        $('body').on('keyup', '.uppercase-input', function(e) {
            let selector = $(this).find('input')
            
            if (selector.length > 1) {
                selector = $(this).find("input[type='text']")
            }

            let val = selector.val()
            selector.val(val.toUpperCase())
        })
        
        function hasOnlyDigits(value) {
            return /^\d+$/.test(value);
        }

        function priceFormat(amount) {
            return parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')
        }

        function cleanPrice(amount) {
            return Number(numeral(amount).format('0.00'))
        }
    </script>

    @stack('scripts')
</html>
