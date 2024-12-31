@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <x-app.page-title url="{{ route('sale_order.index') }}">Convert Sale Order to Delivery Order</x-app.page-title>
    </div>
    <div class="bg-white p-4 border rounded-md flex flex-col lg:flex-row gap-8 lg:gap-x-12">
        <!-- Steps -->
        <div class="flex flex-wrap lg:flex-col gap-6 flex-1">
            <!-- Step 1 -->
            <div class="min-w-[250px] flex-1 lg:flex-0 flex items-center bg-yellow-100 rounded overflow-hidden {{ $step != 1 ? 'opacity-25' : ''  }}">
                <div class="bg-yellow-300 p-2">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,24C5.383,24,0,18.617,0,12S5.383,0,12,0s12,5.383,12,12-5.383,12-12,12Zm0-22C6.486,2,2,6.486,2,12s4.486,10,10,10,10-4.486,10-10S17.514,2,12,2Zm1,15V7c0-.404-.244-.77-.617-.924-.375-.157-.805-.069-1.09,.217l-2.444,2.444c-.391,.391-.391,1.023,0,1.414s1.023,.391,1.414,0l.737-.737v7.586c0,.553,.448,1,1,1s1-.447,1-1Z"/></svg>
                </div>
                <h6 class="font-semibold mx-4">Customer Selection</h6>
            </div>
            <!-- Step 2 -->
            <div class="min-w-[250px] flex-1 lg:flex-0 flex items-center bg-yellow-100 rounded overflow-hidden {{ $step != 2 ? 'opacity-25' : ''  }}">
                <div class="bg-yellow-300 p-2">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,24C5.383,24,0,18.617,0,12S5.383,0,12,0s12,5.383,12,12-5.383,12-12,12Zm0-22C6.486,2,2,6.486,2,12s4.486,10,10,10,10-4.486,10-10S17.514,2,12,2Zm4,15c0-.553-.448-1-1-1h-4.781c.426-.37,1.069-.72,1.742-1.086,1.754-.956,4.156-2.265,4.035-5.131-.089-2.121-1.844-3.783-3.995-3.783-2.206,0-4,1.794-4,4,0,.553,.448,1,1,1s1-.447,1-1c0-1.103,.897-2,2-2,1.058,0,1.954,.838,1.997,1.867,.064,1.513-1.088,2.253-2.994,3.29-.99,.54-1.925,1.049-2.559,1.797-.475,.56-.58,1.319-.272,1.983,.304,.655,.942,1.062,1.666,1.062h5.162c.552,0,1-.447,1-1Z"/></svg>
                </div>
                <h6 class="font-semibold mx-4">Salesperson Selection</h6>
            </div>
            <!-- Step 3 -->
            <div class="min-w-[250px] flex-1 lg:flex-0 flex items-center bg-yellow-100 rounded overflow-hidden {{ $step != 3 ? 'opacity-25' : ''  }}">
                <div class="bg-yellow-300 p-2">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,0C5.383,0,0,5.383,0,12s5.383,12,12,12,12-5.383,12-12S18.617,0,12,0Zm0,22c-5.514,0-10-4.486-10-10S6.486,2,12,2s10,4.486,10,10-4.486,10-10,10Zm4-8c0,2.206-1.794,4-4,4h-3c-.552,0-1-.447-1-1s.448-1,1-1h3c1.103,0,2-.897,2-2s-.897-2-2-2h-2c-.552,0-1-.447-1-1s.448-1,1-1h2c.551,0,1-.448,1-1s-.449-1-1-1h-3c-.552,0-1-.447-1-1s.448-1,1-1h3c1.654,0,3,1.346,3,3,0,.68-.236,1.301-.619,1.805,.977,.73,1.619,1.885,1.619,3.195Z"/></svg>
                </div>
                <h6 class="font-semibold mx-4">Term Selection</h6>
            </div>
            <!-- Step 4 -->
            <div class="min-w-[250px] flex-1 lg:flex-0 flex items-center bg-yellow-100 rounded overflow-hidden {{ $step != 4 ? 'opacity-25' : ''  }}">
                <div class="bg-yellow-300 p-2">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,0C5.383,0,0,5.383,0,12s5.383,12,12,12,12-5.383,12-12S18.617,0,12,0Zm0,22c-5.514,0-10-4.486-10-10S6.486,2,12,2s10,4.486,10,10-4.486,10-10,10Zm4-15v10c0,.553-.447,1-1,1s-1-.447-1-1v-3h-3c-1.654,0-3-1.346-3-3V7c0-.553,.447-1,1-1s1,.447,1,1v4c0,.552,.448,1,1,1h3V7c0-.553,.447-1,1-1s1,.447,1,1Z"/></svg>
                </div>
                <h6 class="font-semibold mx-4">Sale Order Selection</h6>
            </div>
            <!-- Step 5 -->
            <div class="min-w-[250px] flex-1 lg:flex-0 flex items-center bg-yellow-100 rounded overflow-hidden {{ $step != 5 ? 'opacity-25' : ''  }}">
                <div class="bg-yellow-300 p-2">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,24C5.383,24,0,18.617,0,12S5.383,0,12,0s12,5.383,12,12-5.383,12-12,12Zm0-22C6.486,2,2,6.486,2,12s4.486,10,10,10,10-4.486,10-10S17.514,2,12,2Zm2.901,14.754c.763-.804,1.151-1.857,1.094-2.968-.111-2.123-1.941-3.786-4.165-3.786h-1.83v-2h4c.553,0,1-.447,1-1s-.447-1-1-1h-4c-1.103,0-2,.897-2,2v3c0,.553,.447,1,1,1h2.83c1.141,0,2.112,.849,2.167,1.891,.029,.557-.165,1.084-.547,1.486-.382,.401-.896,.623-1.45,.623h-3c-.553,0-1,.447-1,1s.447,1,1,1h3c1.092,0,2.149-.454,2.901-1.246Z"/></svg>
                </div>
                <h6 class="font-semibold mx-4">Product Selection</h6>
            </div>
        </div>
        <!-- Steps Content -->
        <div class="flex-[3]">
            <!-- Step 1 -->
            @if ($step == 1)
                <div>
                    @can('sale.sale_order.by_pass_conversion')
                        <div class="pb-6 mb-6 border-b">
                            <form action="{{ route('sale_order.to_delivery_order') }}" method="POST">
                                @csrf
                                <x-app.input.label id="by_pass_conversion" class="mb-1">{{ __('By Pass Conversion') }} <span class="text-xs mt-1 text-slate-500">({{ __('Enter your login password') }})</span></x-app.input.label>
                                <div class="flex items-center gap-4">
                                    <x-app.input.input name="by_pass_conversion" id="by_pass_conversion" :hasError="$errors->has('by_pass_conversion')" type="password" class="flex-1" />
                                    <x-app.button.submit class="shadow-none h-full">{{ __('Enable') }}</x-app.button.submit>
                                </div>
                                <x-input-error :messages="$errors->get('by_pass_conversion')" class="mt-1" />
                            </form>
                        </div>
                    @endcan
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
                                    @php
                                        $url = $by_pass == null ? route('sale_order.to_delivery_order', ['cus' => $cus->id]) : route('sale_order.to_delivery_order', ['by_pass' => $by_pass, 'cus' => $cus->id]);
                                    @endphp
                                    <a href="{{ $url  }}" class="text-sm flex items-center justify-between p-2 font-semibold">
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
                                @php
                                    $url = $by_pass == null ? route('sale_order.to_delivery_order', ['sp' => $sp->id]) : route('sale_order.to_delivery_order', ['by_pass' => $by_pass, 'sp' => $sp->id]);
                                @endphp
                                <li class="mb-4 rounded-md cursor-pointer transition duration-300 border border-slate-100 hover:border-black salesperson-selections" data-id="{{ $sp->id }}">
                                    <a href="{{ $url }}" class="text-sm flex items-center justify-between p-2 font-semibold">
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
                        <h5 class="text-md font-semibold">Select term to proceed</h5>
                    </div>
                    @if (count($terms) > 0)
                        <ul>
                            @foreach ($terms as $term)
                                @php
                                    $url = $by_pass == null ? route('sale_order.to_delivery_order', ['term' => $term->id]) : route('sale_order.to_delivery_order', ['by_pass' => $by_pass, 'term' => $term->id]);
                                @endphp
                                <li class="mb-4 rounded-md cursor-pointer transition duration-300 border border-slate-100 hover:border-black">
                                    <a href="{{ $url }}" class="text-sm flex items-center justify-between p-2 font-semibold">
                                        {{ $term->name }}
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
            <!-- Step 4 -->
            @if ($step == 4)
                <div class="flex flex-col h-full">
                    <div class="mb-4">
                        <h5 class="text-md font-semibold">Select sale order to proceed</h5>
                    </div>
                    @if (count($sale_orders) > 0)
                        <ul class="flex items-start flex-wrap gap-4 flex-1">
                            @foreach ($sale_orders as $so)
                                <li class="w-1/6  p-2 rounded-md cursor-pointer border border-slate-100 text-center sale-order-selections" data-id="{{ $so->id }}">{{ $so->sku }}</li>
                            @endforeach
                        </ul>
                        <div class="flex justify-end mt-8">
                            <a href="{{ route('sale_order.to_delivery_order') }}" class="bg-slate-100 rounded-md py-2 px-4 flex justify-center items-center gap-x-2" id="confirm-btn">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="m16.298,8.288l1.404,1.425-5.793,5.707c-.387.387-.896.58-1.407.58s-1.025-.195-1.416-.585l-2.782-2.696,1.393-1.437,2.793,2.707,5.809-5.701Zm7.702,3.712c0,6.617-5.383,12-12,12S0,18.617,0,12,5.383,0,12,0s12,5.383,12,12Zm-2,0c0-5.514-4.486-10-10-10S2,6.486,2,12s4.486,10,10,10,10-4.486,10-10Z"/></svg>
                                <span class="text-sm font-semibold">Confirm</span>
                            </a>
                        </div>
                    @else
                        @include('components.app.no-data')
                    @endif
                </div>
            @endif
            <!-- Step 5 -->
            @if ($step == 5)
                <div class="flex flex-col h-full">
                    <div class="mb-4">
                        <h5 class="text-md font-semibold">Select product to convert</h5>
                    </div>
                    @if (count($products) > 0)
                        <div class="flex flex-col gap-4 flex-1">
                            @foreach ($products as $pro)
                                @if ($pro->remainingQty() > 0)
                                    <div class="p-2 rounded-md border border-slate-200 products">
                                        <div class="flex-1 flex justify-between items-start">
                                            <div>
                                                <h6 class="font-semibold leading-none mb-2">{{ $pro->product->model_name }}</h6>
                                                <p class="text-xs text-slate-500">{{ __('SKU') }}: {{ $pro->sku }}</p>
                                                <p class="text-xs text-slate-500">{{ __('Description') }}: {{ $pro->desc }}</p>
                                            </div>
                                            <div class="flex flex-col items-end">
                                                <span class="text-slate-500 text-xs">{{ __('From SO') }}</span>
                                                <span>{{ $pro->sale->sku }}</span>
                                            </div>
                                        </div>
                                        <div class="border-t pt-2 mt-2">
                                            @foreach ($pro->children as $pc)
                                                @php
                                                    if (!in_array($pc->product_children_id, $allowed_spc_ids)) {
                                                        continue;
                                                    }
                                                @endphp
                                                <label for="{{ $pc->id }}" class="w-full block my-1">
                                                    <input type="checkbox" name="product_children" id="{{ $pc->id }}" value="{{ $pc->id }}" class="rounded mr-1">
                                                    <span>{{ $pc->productChild->sku }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        <div class="flex justify-end mt-8">
                            <a href="{{ route('sale_order.convert_to_delivery_order') }}" class="bg-slate-100 rounded-md py-2 px-4 flex justify-center items-center gap-x-2" id="convert-btn">
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

            $(`.sale-order-selections[data-id="${id}"]`).removeClass('!border-black')
        } else {
            SELECTED_SOS.push(id)

            $(`.sale-order-selections[data-id="${id}"]`).addClass('!border-black')
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

        if (SELECTED_SOS.length <= 0) return

        let url = $(this).attr('href')
        url = `${url}?so=${SELECTED_SOS}`

        window.location.href = url
    })
    $('.products input[type="checkbox"]').on('change', function() {
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
    $('#convert-btn').on('click', function(e) {
        e.preventDefault()

        let productChildIds = []
        let canConvert = false
        $('.products input[type="checkbox"]').each(function() {
            if ($(this).is(':checked')) {
                productChildIds.push($(this).attr('value'))
                canConvert = true
            }
        })
        if (!canConvert) return

        let url = $(this).attr('href')
        url = `${url}?product_children=${productChildIds}`

        window.location.href = url
    })
</script>
@endpush
