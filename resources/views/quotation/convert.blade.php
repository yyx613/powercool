@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <x-app.page-title url="{{ route('quotation.index') }}">Convert Quotation to Sale Order</x-app.page-title>
    </div>
    @if ($step > 1)
        <div class="flex gap-x-4 mb-3">
            @if ($step > 1 && isset($selected_customer) && $selected_customer != null)
                <div class="flex items-center gap-x-2">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                        viewBox="0 0 24 24" width="512" height="512">
                        <path
                            d="M12,24C5.383,24,0,18.617,0,12S5.383,0,12,0s12,5.383,12,12-5.383,12-12,12Zm0-22C6.486,2,2,6.486,2,12s4.486,10,10,10,10-4.486,10-10S17.514,2,12,2Zm1,15V7c0-.404-.244-.77-.617-.924-.375-.157-.805-.069-1.09,.217l-2.444,2.444c-.391,.391-.391,1.023,0,1.414s1.023,.391,1.414,0l.737-.737v7.586c0,.553,.448,1,1,1s1-.447,1-1Z" />
                    </svg>
                    <p class="text-xs">{{ $selected_customer->company_name }} - {{ $selected_customer->name }}</p>
                </div>
            @endif
            @if ($step > 2 && isset($selected_salesperson) && $selected_salesperson != null)
                <div class="flex items-center gap-x-2">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                        viewBox="0 0 24 24" width="512" height="512">
                        <path
                            d="M12,24C5.383,24,0,18.617,0,12S5.383,0,12,0s12,5.383,12,12-5.383,12-12,12Zm0-22C6.486,2,2,6.486,2,12s4.486,10,10,10,10-4.486,10-10S17.514,2,12,2Zm4,15c0-.553-.448-1-1-1h-4.781c.426-.37,1.069-.72,1.742-1.086,1.754-.956,4.156-2.265,4.035-5.131-.089-2.121-1.844-3.783-3.995-3.783-2.206,0-4,1.794-4,4,0,.553,.448,1,1,1s1-.447,1-1c0-1.103,.897-2,2-2,1.058,0,1.954,.838,1.997,1.867,.064,1.513-1.088,2.253-2.994,3.29-.99,.54-1.925,1.049-2.559,1.797-.475,.56-.58,1.319-.272,1.983,.304,.655,.942,1.062,1.666,1.062h5.162c.552,0,1-.447,1-1Z" />
                    </svg>
                    <p class="text-xs">{{ $selected_salesperson->name }}</p>
                </div>
            @endif
        </div>
    @endif
    <div class="bg-white p-4 border rounded-md flex flex-col lg:flex-row gap-8 lg:gap-x-12">
        <!-- Steps -->
        <div class="flex flex-wrap lg:flex-col gap-4 flex-1">
            @if ($step != 1)
                <div>
                    <button
                        class="flex items-center gap-x-1 hover:bg-slate-200 px-2 py-1 transition-all duration-200 rounded"
                        id="previous-page-btn">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24"
                            width="512" height="512">
                            <path
                                d="M10.48,19a1,1,0,0,1-.7-.29L5.19,14.12a3,3,0,0,1,0-4.24L9.78,5.29a1,1,0,0,1,1.41,0,1,1,0,0,1,0,1.42L6.6,11.29a1,1,0,0,0,0,1.42l4.59,4.58a1,1,0,0,1,0,1.42A1,1,0,0,1,10.48,19Z" />
                            <path
                                d="M17.48,19a1,1,0,0,1-.7-.29l-6-6a1,1,0,0,1,0-1.42l6-6a1,1,0,0,1,1.41,0,1,1,0,0,1,0,1.42L12.9,12l5.29,5.29a1,1,0,0,1,0,1.42A1,1,0,0,1,17.48,19Z" />
                        </svg>
                        <span class="text-xs font-medium">{{ __('Previous Step') }}</span>
                    </button>
                </div>
            @endif
            <!-- Step 1 -->
            <div
                class="min-w-[250px] flex-1 lg:flex-none flex items-center bg-yellow-100 rounded overflow-hidden {{ $step != 1 ? 'opacity-25' : '' }}">
                <div class="bg-yellow-300 p-2">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                        viewBox="0 0 24 24" width="512" height="512">
                        <path
                            d="M12,24C5.383,24,0,18.617,0,12S5.383,0,12,0s12,5.383,12,12-5.383,12-12,12Zm0-22C6.486,2,2,6.486,2,12s4.486,10,10,10,10-4.486,10-10S17.514,2,12,2Zm1,15V7c0-.404-.244-.77-.617-.924-.375-.157-.805-.069-1.09,.217l-2.444,2.444c-.391,.391-.391,1.023,0,1.414s1.023,.391,1.414,0l.737-.737v7.586c0,.553,.448,1,1,1s1-.447,1-1Z" />
                    </svg>
                </div>
                <h6 class="font-semibold mx-4">Customer Selection</h6>
            </div>
            <!-- Step 2 -->
            <div
                class="min-w-[250px] flex-1 lg:flex-none flex items-center bg-yellow-100 rounded overflow-hidden {{ $step != 2 ? 'opacity-25' : '' }}">
                <div class="bg-yellow-300 p-2">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                        viewBox="0 0 24 24" width="512" height="512">
                        <path
                            d="M12,24C5.383,24,0,18.617,0,12S5.383,0,12,0s12,5.383,12,12-5.383,12-12,12Zm0-22C6.486,2,2,6.486,2,12s4.486,10,10,10,10-4.486,10-10S17.514,2,12,2Zm4,15c0-.553-.448-1-1-1h-4.781c.426-.37,1.069-.72,1.742-1.086,1.754-.956,4.156-2.265,4.035-5.131-.089-2.121-1.844-3.783-3.995-3.783-2.206,0-4,1.794-4,4,0,.553,.448,1,1,1s1-.447,1-1c0-1.103,.897-2,2-2,1.058,0,1.954,.838,1.997,1.867,.064,1.513-1.088,2.253-2.994,3.29-.99,.54-1.925,1.049-2.559,1.797-.475,.56-.58,1.319-.272,1.983,.304,.655,.942,1.062,1.666,1.062h5.162c.552,0,1-.447,1-1Z" />
                    </svg>
                </div>
                <h6 class="font-semibold mx-4">Salesperson Selection</h6>
            </div>
            <!-- Step 3 -->
            <div
                class="min-w-[250px] flex-1 lg:flex-none flex items-center bg-yellow-100 rounded overflow-hidden {{ $step != 3 ? 'opacity-25' : '' }}">
                <div class="bg-yellow-300 p-2">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                        viewBox="0 0 24 24" width="512" height="512">
                        <path
                            d="M12,0C5.383,0,0,5.383,0,12s5.383,12,12,12,12-5.383,12-12S18.617,0,12,0Zm0,22c-5.514,0-10-4.486-10-10S6.486,2,12,2s10,4.486,10,10-4.486,10-10,10Zm4-8c0,2.206-1.794,4-4,4h-3c-.552,0-1-.447-1-1s.448-1,1-1h3c1.103,0,2-.897,2-2s-.897-2-2-2h-2c-.552,0-1-.447-1-1s.448-1,1-1h2c.551,0,1-.448,1-1s-.449-1-1-1h-3c-.552,0-1-.447-1-1s.448-1,1-1h3c1.654,0,3,1.346,3,3,0,.68-.236,1.301-.619,1.805,.977,.73,1.619,1.885,1.619,3.195Z" />
                    </svg>
                </div>
                <h6 class="font-semibold mx-4">Quotation Selection</h6>
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
                                <li class="mb-4 rounded-md cursor-pointer transition duration-300 border border-slate-100 hover:border-black customer-selections"
                                    data-id="{{ $cus->id }}">
                                    <a href="{{ route('quotation.to_sale_order') }}?cus={{ $cus->id }}"
                                        class="text-sm flex items-center justify-between p-2 font-semibold">
                                        {{ $cus->company_name }} - {{ $cus->name }}
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="arrow-circle-down"
                                            viewBox="0 0 24 24" width="512" height="512">
                                            <path
                                                d="M0,12A12,12,0,1,0,12,0,12.013,12.013,0,0,0,0,12Zm22,0A10,10,0,1,1,12,2,10.011,10.011,0,0,1,22,12Z" />
                                            <path
                                                d="M16,12a2.993,2.993,0,0,1-.752,1.987c-.291.327-.574.637-.777.84L11.647,17.7a1,1,0,1,1-1.426-1.4L13.05,13.42c.187-.188.441-.468.7-.759a1,1,0,0,0,0-1.323c-.258-.29-.512-.57-.693-.752L10.221,7.7a1,1,0,1,1,1.426-1.4l2.829,2.879c.2.2.48.507.769.833A2.99,2.99,0,0,1,16,12Z" />
                                        </svg>
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
                                <li class="mb-4 rounded-md cursor-pointer transition duration-300 border border-slate-100 hover:border-black salesperson-selections"
                                    data-id="{{ $sp->id }}">
                                    <a href="{{ route('quotation.to_sale_order') }}?sp={{ $sp->id }}"
                                        class="text-sm flex items-center justify-between p-2 font-semibold">
                                        {{ $sp->name }}
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="arrow-circle-down"
                                            viewBox="0 0 24 24" width="512" height="512">
                                            <path
                                                d="M0,12A12,12,0,1,0,12,0,12.013,12.013,0,0,0,0,12Zm22,0A10,10,0,1,1,12,2,10.011,10.011,0,0,1,22,12Z" />
                                            <path
                                                d="M16,12a2.993,2.993,0,0,1-.752,1.987c-.291.327-.574.637-.777.84L11.647,17.7a1,1,0,1,1-1.426-1.4L13.05,13.42c.187-.188.441-.468.7-.759a1,1,0,0,0,0-1.323c-.258-.29-.512-.57-.693-.752L10.221,7.7a1,1,0,1,1,1.426-1.4l2.829,2.879c.2.2.48.507.769.833A2.99,2.99,0,0,1,16,12Z" />
                                        </svg>
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
                        <h5 class="text-md font-semibold">{{ __('Select quotation to convert') }}</h5>
                    </div>
                    @if (count($quotations) > 0)
                        <!-- Report type selection -->
                        <div class="flex justify-between mb-6">
                            <x-app.input.select2 name="report_type" id="report_type" :hasError="$errors->has('report_type')" class="w-1/2"
                                placeholder="{{ __('Select a report type') }}">
                                <option value="">{{ __('Select a report type') }}</option>
                                @foreach ($report_types as $type)
                                    <option value="{{ $type->id }}" @selected(old('report_type', isset($sale) ? $sale->report_type : null) == $type->id)>{{ $type->name }}
                                    </option>
                                @endforeach
                            </x-app.input.select2>
                        </div>
                        <!-- Quotations -->
                        <ul class="flex flex-wrap gap-4">
                            @foreach ($quotations as $quo)
                                <li class="w-1/6 p-2 rounded-md cursor-pointer border border-slate-100 text-center hidden quotation-selections"
                                    data-id="{{ $quo->id }}">{{ $quo->sku }}</li>
                            @endforeach
                        </ul>
                        <!-- Convert  -->
                        <div class="flex justify-end">
                            <a href="{{ route('quotation.convert_to_sale_order') }}"
                                class="bg-slate-100 rounded-md py-2 px-4 flex justify-center items-center gap-x-2"
                                id="convert-btn">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="arrow-circle-down"
                                    viewBox="0 0 24 24" width="512" height="512">
                                    <g>
                                        <path
                                            d="M23,16H2.681l.014-.015L4.939,13.7a1,1,0,1,0-1.426-1.4L1.274,14.577c-.163.163-.391.413-.624.676a2.588,2.588,0,0,0,0,3.429c.233.262.461.512.618.67l2.245,2.284a1,1,0,0,0,1.426-1.4L2.744,18H23a1,1,0,0,0,0-2Z" />
                                        <path
                                            d="M1,8H21.255l-2.194,2.233a1,1,0,1,0,1.426,1.4l2.239-2.279c.163-.163.391-.413.624-.675a2.588,2.588,0,0,0,0-3.429c-.233-.263-.461-.513-.618-.67L20.487,2.3a1,1,0,0,0-1.426,1.4l2.251,2.29L21.32,6H1A1,1,0,0,0,1,8Z" />
                                    </g>
                                </svg>
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
        QUOTATIONS = @json($quotations ?? null);
        SELECTED_QUOS = []

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
        $('.quotation-selections').on('click', function() {
            let id = $(this).data('id')

            const index = SELECTED_QUOS.indexOf(id);
            if (index > -1) {
                SELECTED_QUOS.splice(index, 1)

                $(`.quotation-selections[data-id="${id}"]`).removeClass('!border-black')
            } else {
                SELECTED_QUOS.push(id)

                $(`.quotation-selections[data-id="${id}"]`).addClass('!border-black')
            }

            canConvert()
        })
        $('select[name="report_type"]').on('change', function() {
            SELECTED_QUOS = []
            $(`.quotation-selections`).removeClass('!border-black')
            $(`.quotation-selections`).addClass('hidden')

            let val = $(this).val()

            for (let i = 0; i < QUOTATIONS.length; i++) {
                if (QUOTATIONS[i].report_type == val) {
                    $(`.quotation-selections[data-id="${QUOTATIONS[i].id}"]`).removeClass('hidden')
                }
            }

            canConvert()
        })
        $('#convert-btn').on('click', function(e) {
            e.preventDefault()

            if (!canConvert()) return

            let url = $(this).attr('href')
            url = `${url}?quo=${SELECTED_QUOS}&report_type=${ $('select[name="report_type"]').val() }`

            window.location.href = url
        })
        $('#previous-page-btn').on('click', function() {
            history.back()
        })

        function canConvert() {
            if (SELECTED_QUOS.length <= 0 || $('select[name="report_type"]').val() == '') {
                $('#convert-btn').removeClass('bg-green-200')
                $('#convert-btn').addClass('bg-slate-100')

                return false
            }
            $('#convert-btn').addClass('bg-green-200')
            $('#convert-btn').removeClass('bg-slate-100')

            return true
        }
    </script>
@endpush
