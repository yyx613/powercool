<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - {{ config('app.name', 'Laravel') }}</title>
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
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.css">

    {{-- Debounce --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-throttle-debounce/1.1/jquery.ba-throttle-debounce.min.js">
    </script>

    <!-- Datatables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <!-- Big Decimal -->
    <script src="https://unpkg.com/js-big-decimal@1.3.1/dist/web/js-big-decimal.min.js"></script>

    <!-- Chartjs -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

    <!-- QR Code -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    {{-- Sortable --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    @stack('styles')
</head>

<body class="font-sans antialiased overflow-x-hidden">
    <div class="min-h-screen flex">
        @if (!isCreateLink())
            @include('layouts.sidebar')
        @endif

        <main class="flex-1 overflow-hidden bg-gray-50">
            @if (!isCreateLink())
                @include('layouts.navbar')
            @endif
            <div class="p-4 h-full overflow-x-auto">
                @yield('content')
            </div>
        </main>
    </div>
</body>

<script>
    moment.tz.setDefault("Asia/Kuala_Lumpur")
    var datepickerParam = {
        singleDatePicker: true,
        showDropdowns: true,
        autoUpdateInput: false,
        locale: {
            format: 'YYYY-MM-DD'
        }
    }
    const DEBOUNCE_DURATION = 300 // milliseconds
    const CAN_VIEW_APPROVAL = @json($can_view_approval ?? null);

    $(document).ready(function() {
        if (CAN_VIEW_APPROVAL) {
            setInterval(() => {
                hasPendingApproval() // Update every 30 seconds
            }, 1000 * 30);
        }
    })

    $('body').on('keydown', '.phone-input', function(e) {
        let allowedKeys = ['backspace', 'arrowleft', 'arrowright', 'tab', '+', '-', ' ']
        let val = $(this).find('input').val()

        var evtobj = window.event ? event : e
        if (['c', 'v', 'a'].includes(e.key) && (evtobj.ctrlKey || evtobj.metaKey)) {
            // console.debug( "Ctrl+z" )
        } else if (!/[0-9]/.test(e.key) && !allowedKeys.includes(e.key.toLowerCase())) {
            e.preventDefault()
        }
    })

    $('body').on('keydown', '.int-input', function(e) {
        let allowedKeys = ['backspace', 'arrowleft', 'arrowright', 'tab']
        let val = $(this).find('input').val()

        var evtobj = window.event ? event : e
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

        var evtobj = window.event ? event : e
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

    function priceFormat(val) {
        return new bigDecimal(val).round(2).getPrettyValue()
    }

    function decimalPlace2(val) {
        return new bigDecimal(val).round(2).getValue()
    }

    function hasPendingApproval() {
        let url = '{{ route('approval.has_pending') }}'

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: url,
            type: 'GET',
            success: function(res) {
                if (res.has_pending) {
                    $('.approval-red-dots').removeClass('hidden')
                } else {
                    $('.approval-red-dots').addClass('hidden')
                }
            },
        });
    }
</script>

@stack('scripts')

</html>
