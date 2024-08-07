@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <x-app.page-title>Convert Sale Order to Delivery Order</x-app.page-title>
    </div>
    <div class="bg-white p-4 border rounded-md flex gap-x-14">
        <!-- Steps -->
        <div class="flex flex-col gap-y-6 flex-1">
            <!-- Step 1 -->
            <div class="flex items-center bg-yellow-100 rounded overflow-hidden {{ $step != 1 ? 'opacity-25' : ''  }}">
                <div class="bg-yellow-300 p-2">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,24C5.383,24,0,18.617,0,12S5.383,0,12,0s12,5.383,12,12-5.383,12-12,12Zm0-22C6.486,2,2,6.486,2,12s4.486,10,10,10,10-4.486,10-10S17.514,2,12,2Zm1,15V7c0-.404-.244-.77-.617-.924-.375-.157-.805-.069-1.09,.217l-2.444,2.444c-.391,.391-.391,1.023,0,1.414s1.023,.391,1.414,0l.737-.737v7.586c0,.553,.448,1,1,1s1-.447,1-1Z"/></svg>
                </div>
                <h6 class="font-semibold mx-4">Customer Selection</h6>
            </div>
            <!-- Step 2 -->
            <div class="flex items-center bg-yellow-100 rounded overflow-hidden {{ $step != 2 ? 'opacity-25' : ''  }}">
                <div class="bg-yellow-300 p-2">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,24C5.383,24,0,18.617,0,12S5.383,0,12,0s12,5.383,12,12-5.383,12-12,12Zm0-22C6.486,2,2,6.486,2,12s4.486,10,10,10,10-4.486,10-10S17.514,2,12,2Zm4,15c0-.553-.448-1-1-1h-4.781c.426-.37,1.069-.72,1.742-1.086,1.754-.956,4.156-2.265,4.035-5.131-.089-2.121-1.844-3.783-3.995-3.783-2.206,0-4,1.794-4,4,0,.553,.448,1,1,1s1-.447,1-1c0-1.103,.897-2,2-2,1.058,0,1.954,.838,1.997,1.867,.064,1.513-1.088,2.253-2.994,3.29-.99,.54-1.925,1.049-2.559,1.797-.475,.56-.58,1.319-.272,1.983,.304,.655,.942,1.062,1.666,1.062h5.162c.552,0,1-.447,1-1Z"/></svg>
                </div>
                <h6 class="font-semibold mx-4">Salesperson Selection</h6>
            </div>
            <!-- Step 3 -->
            <div class="flex items-center bg-yellow-100 rounded overflow-hidden {{ $step != 3 ? 'opacity-25' : ''  }}">
                <div class="bg-yellow-300 p-2">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,0C5.383,0,0,5.383,0,12s5.383,12,12,12,12-5.383,12-12S18.617,0,12,0Zm0,22c-5.514,0-10-4.486-10-10S6.486,2,12,2s10,4.486,10,10-4.486,10-10,10Zm4-8c0,2.206-1.794,4-4,4h-3c-.552,0-1-.447-1-1s.448-1,1-1h3c1.103,0,2-.897,2-2s-.897-2-2-2h-2c-.552,0-1-.447-1-1s.448-1,1-1h2c.551,0,1-.448,1-1s-.449-1-1-1h-3c-.552,0-1-.447-1-1s.448-1,1-1h3c1.654,0,3,1.346,3,3,0,.68-.236,1.301-.619,1.805,.977,.73,1.619,1.885,1.619,3.195Z"/></svg>
                </div>
                <h6 class="font-semibold mx-4">Sale Order Selection</h6>
            </div>
            <!-- Step 3 -->
            <div class="flex items-center bg-yellow-100 rounded overflow-hidden {{ $step != 4 ? 'opacity-25' : ''  }}">
                <div class="bg-yellow-300 p-2">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,0C5.383,0,0,5.383,0,12s5.383,12,12,12,12-5.383,12-12S18.617,0,12,0Zm0,22c-5.514,0-10-4.486-10-10S6.486,2,12,2s10,4.486,10,10-4.486,10-10,10Zm4-15v10c0,.553-.447,1-1,1s-1-.447-1-1v-3h-3c-1.654,0-3-1.346-3-3V7c0-.553,.447-1,1-1s1,.447,1,1v4c0,.552,.448,1,1,1h3V7c0-.553,.447-1,1-1s1,.447,1,1Z"/></svg>
                </div>
                <h6 class="font-semibold mx-4">Product Selection</h6>
            </div>
        </div>
        <!-- Steps Content -->
        <div class="flex-[3]">
            <!-- Step 1 -->
            @if ($step == 1)
                <div>
                    <div class="mb-2">
                        <h5 class="text-md font-semibold">Select a customer to proceed</h5>
                    </div>
                    <div class="mb-4">
                        <x-app.input.input name="search_customer" placeholder="Search customer" />
                    </div>
                    @if (count($customers) > 0)
                        <ul>
                            @foreach ($customers as $cus)
                                <li class="mb-4 rounded-md cursor-pointer transition duration-300 border border-slate-100 hover:border-black customer-selections" data-id="{{ $cus->id }}">
                                    <a href="{{ route('sale_order.to_delivery_order') }}?cus={{ $cus->id }}" class="text-sm flex items-center justify-between p-2 font-semibold">
                                        {{ $cus->name }}
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="arrow-circle-down" viewBox="0 0 24 24" width="512" height="512"><path d="M0,12A12,12,0,1,0,12,0,12.013,12.013,0,0,0,0,12Zm22,0A10,10,0,1,1,12,2,10.011,10.011,0,0,1,22,12Z"/><path d="M16,12a2.993,2.993,0,0,1-.752,1.987c-.291.327-.574.637-.777.84L11.647,17.7a1,1,0,1,1-1.426-1.4L13.05,13.42c.187-.188.441-.468.7-.759a1,1,0,0,0,0-1.323c-.258-.29-.512-.57-.693-.752L10.221,7.7a1,1,0,1,1,1.426-1.4l2.829,2.879c.2.2.48.507.769.833A2.99,2.99,0,0,1,16,12Z"/></svg>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        @include('components.app.no-data')
                    @endif
                </div>
            @endif
            <!-- Step 2 -->
            @if ($step == 2)
                <div>
                    <div class="mb-2">
                        <h5 class="text-md font-semibold">Select a salesperson to proceed</h5>
                    </div>
                    <div class="mb-4">
                        <x-app.input.input name="search_salesperson" placeholder="Search salesperson" />
                    </div>
                    @if (count($salespersons) > 0)
                        <ul>
                            @foreach ($salespersons as $sp)
                                <li class="mb-4 rounded-md cursor-pointer transition duration-300 border border-slate-100 hover:border-black salesperson-selections" data-id="{{ $sp->id }}">
                                    <a href="{{ route('sale_order.to_delivery_order') }}?sp={{ $sp->id }}" class="text-sm flex items-center justify-between p-2 font-semibold">
                                        {{ $sp->name }}
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="arrow-circle-down" viewBox="0 0 24 24" width="512" height="512"><path d="M0,12A12,12,0,1,0,12,0,12.013,12.013,0,0,0,0,12Zm22,0A10,10,0,1,1,12,2,10.011,10.011,0,0,1,22,12Z"/><path d="M16,12a2.993,2.993,0,0,1-.752,1.987c-.291.327-.574.637-.777.84L11.647,17.7a1,1,0,1,1-1.426-1.4L13.05,13.42c.187-.188.441-.468.7-.759a1,1,0,0,0,0-1.323c-.258-.29-.512-.57-.693-.752L10.221,7.7a1,1,0,1,1,1.426-1.4l2.829,2.879c.2.2.48.507.769.833A2.99,2.99,0,0,1,16,12Z"/></svg>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        @include('components.app.no-data')
                    @endif
                </div>
            @endif
            <!-- Step 3 -->
            @if ($step == 3)
                <div>
                    <div class="mb-4">
                        <h5 class="text-md font-semibold">Select quotation to proceed</h5>
                    </div>
                    @if (count($sale_orders) > 0)
                        <ul class="flex flex-wrap gap-4">
                            @foreach ($sale_orders as $so)
                                <li class="w-1/6 p-2 rounded-md cursor-pointer border border-slate-100 text-center sale-order-selections" data-id="{{ $so->id }}">{{ $so->sku }}</li>
                            @endforeach
                        </ul>
                        <div class="flex justify-end mt-8">
                            <a href="{{ route('sale_order.to_delivery_order') }}" class="w-1/6 bg-slate-100 rounded-md py-2 px-4 flex justify-center items-center gap-x-2" id="confirm-btn">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="m16.298,8.288l1.404,1.425-5.793,5.707c-.387.387-.896.58-1.407.58s-1.025-.195-1.416-.585l-2.782-2.696,1.393-1.437,2.793,2.707,5.809-5.701Zm7.702,3.712c0,6.617-5.383,12-12,12S0,18.617,0,12,5.383,0,12,0s12,5.383,12,12Zm-2,0c0-5.514-4.486-10-10-10S2,6.486,2,12s4.486,10,10,10,10-4.486,10-10Z"/></svg>
                                <span class="text-sm font-semibold">Confirm</span>
                            </a>
                        </div>
                    @else
                        @include('components.app.no-data')
                    @endif
                </div>
            @endif
            <!-- Step 4 -->
            @if ($step == 4)
                <div>
                    <div class="mb-4">
                        <h5 class="text-md font-semibold">Select product to convert</h5>
                    </div>
                    @if (count($products) > 0)
                        <div class="flex flex-col gap-4">
                            @foreach ($products as $pro)
                                @if ($pro->remainingQty() > 0)
                                    <div class="p-2 rounded-md border border-slate-100 flex items-center justify-between gap-x-4 products" data-id="{{ $pro->id }}">
                                        <div class="flex items-center">
                                            <input type="checkbox" name="product" value="{{ $pro->id }}" class="rounded-full border-slate-300 cursor-pointer">
                                        </div>
                                        <div class="flex-1">
                                            <h6 class="leading-none">{{ $pro->name }}</h6>
                                            <p class="text-sm text-slate-400">{{ $pro->desc }}</p>
                                            <p class="text-sm text-slate-600 mt-2">Remaining Qty: {{ $pro->remainingQty() }}</p>
                                        </div>
                                        <div class="flex items-center gap-x-4 qty-summary" data-max-qty="{{ $pro->remainingQty() }}" data-current-qty="1">
                                            <button type="button" class="p-3 rounded-full bg-slate-100 transition duration-300 hover:bg-yellow-200 add-btns" data-id="{{ $pro->id }}">
                                                <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve" width="512" height="512">
                                                    <path d="M480,224H288V32c0-17.673-14.327-32-32-32s-32,14.327-32,32v192H32c-17.673,0-32,14.327-32,32s14.327,32,32,32h192v192   c0,17.673,14.327,32,32,32s32-14.327,32-32V288h192c17.673,0,32-14.327,32-32S497.673,224,480,224z"/>
                                                </svg>
                                            </button>
                                            <span class="qty-to-convert" data-id="{{ $pro->id }}">1</span>
                                            <button type="button" class="p-3 rounded-full bg-slate-100 transition duration-300 hover:bg-yellow-200 sub-btns" data-id="{{ $pro->id }}">
                                                <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve" width="512" height="512">
                                                    <path d="M480,288H32c-17.673,0-32-14.327-32-32s14.327-32,32-32h448c17.673,0,32,14.327,32,32S497.673,288,480,288z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        <div class="flex justify-end mt-8">
                            <a href="{{ route('sale_order.convert_to_delivery_order') }}" class="w-1/6 bg-slate-100 rounded-md py-2 px-4 flex justify-center items-center gap-x-2" id="convert-btn">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="arrow-circle-down" viewBox="0 0 24 24" width="512" height="512"><g><path d="M23,16H2.681l.014-.015L4.939,13.7a1,1,0,1,0-1.426-1.4L1.274,14.577c-.163.163-.391.413-.624.676a2.588,2.588,0,0,0,0,3.429c.233.262.461.512.618.67l2.245,2.284a1,1,0,0,0,1.426-1.4L2.744,18H23a1,1,0,0,0,0-2Z"/><path d="M1,8H21.255l-2.194,2.233a1,1,0,1,0,1.426,1.4l2.239-2.279c.163-.163.391-.413.624-.675a2.588,2.588,0,0,0,0-3.429c-.233-.263-.461-.513-.618-.67L20.487,2.3a1,1,0,0,0-1.426,1.4l2.251,2.29L21.32,6H1A1,1,0,0,0,1,8Z"/></g></svg>
                                <span class="text-sm font-semibold">Convert</span>
                            </a>
                        </div>
                    @else
                        @include('components.app.no-data')
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    CUSTOMERS = @json($customers ?? null);
    SALESPERSONS = @json($salespersons ?? null);
    SELECTED_SOS = []

    $('input[name="search_customer"]').on('keyup', function() {
        let val = $(this).val()

        $('.customer-selections').addClass('hidden')

        for (let i = 0; i < CUSTOMERS.length; i++) {
            const element = CUSTOMERS[i];
            
            if (element.name.includes(val)) {
                $(`.customer-selections[data-id="${element.id}"]`).removeClass('hidden')
            }
        }
    })
    $('input[name="search_salesperson"]').on('keyup', function() {
        let val = $(this).val()

        $('.salesperson-selections').addClass('hidden')

        for (let i = 0; i < SALESPERSONS.length; i++) {
            const element = SALESPERSONS[i];
            
            if (element.name.includes(val)) {
                $(`.salesperson-selections[data-id="${element.id}"]`).removeClass('hidden')
            }
        }
    })
    $('.sale-order-selections').on('click', function() {
        let id = $(this).data('id')

        const index = SELECTED_SOS.indexOf(id);
        if (index > -1) {
            SELECTED_SOS.splice(index, 1)

            $(`.sale-order-selections[data-id="${id}"]`).removeClass('border-black')
        } else {
            SELECTED_SOS.push(id)

            $(`.sale-order-selections[data-id="${id}"]`).addClass('border-black')
        }

        if (SELECTED_SOS.length <= 0) {
            $('#confirm-btn').removeClass('bg-green-200')
            $('#confirm-btn').addClass('bg-slate-100')
        } else {
            $('#confirm-btn').addClass('bg-green-200')
            $('#confirm-btn').removeClass('bg-slate-100')
        }
    })
    $('#confirm-btn').on('click', function(e) {
        e.preventDefault()

        let url = $(this).attr('href')
        url = `${url}?so=${SELECTED_SOS}`

        window.location.href = url
    })
    $('.products input[type="checkbox"]').on('change', function() {
        let val = $(this).val()

        if ($(this).is(':checked')) {
            $(`.products[data-id="${val}"]`).addClass('border-black')
        } else {
            $(`.products[data-id="${val}"]`).removeClass('border-black')
        }

        let canConvert = false
        $('.products input[type="checkbox"]').each(function() {
            if ($(this).is(':checked')) {
                canConvert = true
            }
        })

        if (canConvert) {
            $('#convert-btn').addClass('bg-green-200')
            $('#convert-btn').removeClass('bg-slate-100')
        } else {
            $('#convert-btn').removeClass('bg-green-200')
            $('#convert-btn').addClass('bg-slate-100')
        }
    })
    $('.add-btns').on('click', function() {
        let id = $(this).data('id')
        let currentQty = $(this).parent().data('current-qty')
        let maxQty = $(this).parent().data('max-qty')

        currentQty++

        if (currentQty <= maxQty) {
            $(`.qty-to-convert[data-id="${id}"]`).text(currentQty)
            $(this).parent().data('current-qty', currentQty)
        }
    })
    $('.sub-btns').on('click', function() {
        let id = $(this).data('id')
        let currentQty = $(this).parent().data('current-qty')

        currentQty--

        if (currentQty >= 1) {
            $(`.qty-to-convert[data-id="${id}"]`).text(currentQty)
            $(this).parent().data('current-qty', currentQty)
        }
    })
    $('#convert-btn').on('click', function(e) {
        e.preventDefault()

        let canConvert = false
        $('.products input[type="checkbox"]').each(function() {
            if ($(this).is(':checked')) {
                canConvert = true
            }
        })
        if (!canConvert) return

        // Prepare data
        let prodIds = []
        let qty = []

        $('.products').each(function() {
            if ($(this).find('input[type="checkbox"]').is(':checked')) {
                prodIds.push($(this).data('id'))
                qty.push($(this).find('.qty-summary').data('current-qty'))
            }
        })

        let url = $(this).attr('href')
        url = `${url}?prod=${prodIds}&qty=${qty}`

        window.location.href = url
    })
</script>
@endpush