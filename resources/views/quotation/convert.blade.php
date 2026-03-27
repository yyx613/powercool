@extends('layouts.app')
@section('title', 'Quotation')

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
                    <p class="text-xs">{{ $selected_customer->company_name }} - {{ $selected_customer->company_group == 1 ? 'Power Cool' : 'Hi-Ten' }}</p>
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
                class="min-w-[250px] flex-1 lg:flex-none flex items-center bg-yellow-100 rounded overflow-hidden {{ $step != 3 ? 'opacity-25' : '' }} step-indicator" data-step="3"
                id="step-3-indicator">
                <div class="bg-yellow-300 p-2">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                        viewBox="0 0 24 24" width="512" height="512">
                        <path
                            d="M12,0C5.383,0,0,5.383,0,12s5.383,12,12,12,12-5.383,12-12S18.617,0,12,0Zm0,22c-5.514,0-10-4.486-10-10S6.486,2,12,2s10,4.486,10,10-4.486,10-10,10Zm4-8c0,2.206-1.794,4-4,4h-3c-.552,0-1-.447-1-1s.448-1,1-1h3c1.103,0,2-.897,2-2s-.897-2-2-2h-2c-.552,0-1-.447-1-1s.448-1,1-1h2c.551,0,1-.448,1-1s-.449-1-1-1h-3c-.552,0-1-.447-1-1s.448-1,1-1h3c1.654,0,3,1.346,3,3,0,.68-.236,1.301-.619,1.805,.977,.73,1.619,1.885,1.619,3.195Z" />
                    </svg>
                </div>
                <h6 class="font-semibold mx-4">Quotation Selection</h6>
            </div>
            <!-- Step 4 -->
            <div
                class="min-w-[250px] flex-1 lg:flex-none flex items-center bg-yellow-100 rounded overflow-hidden opacity-25 step-indicator" data-step="4"
                id="step-4-indicator">
                <div class="bg-yellow-300 p-2 flex items-center justify-center">
                    <span class="h-5 w-5 flex items-center justify-center font-bold text-sm border-2 border-black rounded-full">4</span>
                </div>
                <h6 class="font-semibold mx-4">Payment Details</h6>
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
                                        {{ $cus->company_name }} - {{ $cus->company_group == 1 ? 'Power Cool' : 'Hi-Ten' }}
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
                                        {{ $sp->name }} ({{ $sp->company_group == 2 ? 'Hi-Ten' : 'Power Cool' }})
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
            <!-- Step 3: Quotation Selection -->
            @if ($step == 3)
                <div id="step-3-content">
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
                        <!-- Next Step -->
                        <div class="flex justify-end">
                            <button type="button"
                                class="bg-slate-100 rounded-md py-2 px-4 flex justify-center items-center gap-x-2"
                                id="next-step-btn">
                                <span class="text-sm font-semibold">{{ __('Next') }}</span>
                            </button>
                        </div>
                    @else
                        @include('components.app.no-data')
                    @endif
                </div>
                <!-- Step 4: Payment Details -->
                <div id="step-4-content" class="hidden">
                    <div class="mb-4">
                        <h5 class="text-md font-semibold">{{ __('Payment Details') }}</h5>
                    </div>
                    <form action="{{ route('quotation.convert_to_sale_order') }}" method="POST" id="convert-form">
                        @csrf
                        <input type="hidden" name="quo" id="convert-quo-ids">
                        <input type="hidden" name="report_type" id="convert-report-type">
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="flex flex-col">
                                <x-app.input.label id="payment_method" class="mb-1">{{ __('Payment Method') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                                <x-app.input.select2 name="payment_method" id="payment_method" :hasError="false"
                                    placeholder="{{ __('Select a method') }}">
                                    <option value=""></option>
                                    @foreach ($payment_methods as $method)
                                        <option value="{{ $method->id }}">{{ $method->name }}</option>
                                    @endforeach
                                </x-app.input.select2>
                                <x-app.message.error id="payment_method_err" />
                            </div>
                            <div class="flex flex-col hidden" id="convert-payment-term-container">
                                <x-app.input.label id="payment_term" class="mb-1">{{ __('Payment Term') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                                <x-app.input.select2 name="payment_term" id="payment_term" :hasError="false"
                                    placeholder="{{ __('Select a term') }}">
                                    <option value=""></option>
                                    @foreach ($credit_terms as $term)
                                        <option value="{{ $term->id }}">{{ $term->name }}</option>
                                    @endforeach
                                </x-app.input.select2>
                                <x-app.message.error id="payment_term_err" />
                            </div>
                            <div class="flex flex-col">
                                <x-app.input.label id="payment_due_date" class="mb-1">{{ __('Payment Due Date') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                                <x-app.input.input name="payment_due_date" id="payment_due_date" :hasError="false" />
                                <x-app.message.error id="payment_due_date_err" />
                            </div>
                            <div class="flex flex-col">
                                <x-app.input.label id="payment_remark" class="mb-1">{{ __('Payment Remark') }}</x-app.input.label>
                                <x-app.input.input name="payment_remark" id="payment_remark" :hasError="false" />
                                <x-app.message.error id="payment_remark_err" />
                            </div>
                        </div>
                        <!-- Convert -->
                        <div class="flex justify-end">
                            <button type="submit"
                                class="bg-green-200 rounded-md py-2 px-4 flex justify-center items-center gap-x-2"
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
                                <span class="text-sm font-semibold">{{ __('Convert') }}</span>
                            </button>
                        </div>
                    </form>
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
        CREDIT_PAYMENT_METHOD_IDS = @json($credit_payment_method_ids ?? []);
        SELECTED_QUOS = [];
        CURRENT_SUBSTEP = 3;

        // Restore step 4 state from URL query params on page refresh
        @if ($step == 3)
        (function() {
            let params = new URLSearchParams(window.location.search)
            if (params.get('substep') == '4' && params.get('quos') && params.get('report_type')) {
                SELECTED_QUOS = params.get('quos').split(',').map(Number)
                CURRENT_SUBSTEP = 4

                // Restore report type and quotation selections
                $('select[name="report_type"]').val(params.get('report_type')).trigger('change')
                setTimeout(function() {
                    // Re-select quotations after report_type change shows them
                    for (let i = 0; i < SELECTED_QUOS.length; i++) {
                        $(`.quotation-selections[data-id="${SELECTED_QUOS[i]}"]`).addClass('!border-black')
                    }

                    // Toggle to step 4 view
                    $('#step-3-content').addClass('hidden')
                    $('#step-4-content').removeClass('hidden')
                    $('#step-3-indicator').addClass('opacity-25')
                    $('#step-4-indicator').removeClass('opacity-25')
                    $('#convert-quo-ids').val(SELECTED_QUOS.join(','))
                    $('#convert-report-type').val(params.get('report_type'))

                    // Restore payment fields
                    if (params.get('payment_method')) {
                        $('select[name="payment_method"]').val(params.get('payment_method')).trigger('change')
                    }
                    if (params.get('payment_term')) {
                        setTimeout(function() {
                            $('select[name="payment_term"]').val(params.get('payment_term')).trigger('change')
                        }, 100)
                    }
                    if (params.get('payment_due_date')) {
                        $('input[name="payment_due_date"]').val(params.get('payment_due_date'))
                    }
                    if (params.get('payment_remark')) {
                        $('input[name="payment_remark"]').val(params.get('payment_remark'))
                    }
                }, 100)
            }
        })();
        @endif

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

            canProceed()
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

            canProceed()
        })

        // Step 3 → Step 4
        $('#next-step-btn').on('click', function(e) {
            e.preventDefault()
            if (!canProceed()) return

            CURRENT_SUBSTEP = 4

            // Hide step 3 content, show step 4 content
            $('#step-3-content').addClass('hidden')
            $('#step-4-content').removeClass('hidden')

            // Update step indicators
            $('#step-3-indicator').addClass('opacity-25')
            $('#step-4-indicator').removeClass('opacity-25')

            // Set hidden form fields
            $('#convert-quo-ids').val(SELECTED_QUOS.join(','))
            $('#convert-report-type').val($('select[name="report_type"]').val())

            // Pre-fill payment method from first selected QUO
            if (SELECTED_QUOS.length > 0 && QUOTATIONS != null) {
                for (let i = 0; i < QUOTATIONS.length; i++) {
                    if (QUOTATIONS[i].id == SELECTED_QUOS[0] && QUOTATIONS[i].payment_method) {
                        $('select[name="payment_method"]').val(QUOTATIONS[i].payment_method).trigger('change')
                        break
                    }
                }
            }

            updateStep4Url()
        })

        // Payment method change → show/hide payment term
        $('select[name="payment_method"]').on('change', function() {
            let val = $(this).val()
            if (CREDIT_PAYMENT_METHOD_IDS.includes(parseInt(val))) {
                $('#convert-payment-term-container').removeClass('hidden')
            } else {
                $('#convert-payment-term-container').addClass('hidden')
                $('select[name="payment_term"]').val('').trigger('change')
            }
            if (CURRENT_SUBSTEP == 4) updateStep4Url()
        })
        $('select[name="payment_term"]').on('change', function() {
            if (CURRENT_SUBSTEP == 4) updateStep4Url()
        })
        $('input[name="payment_due_date"]').on('change', function() {
            if (CURRENT_SUBSTEP == 4) updateStep4Url()
        })
        $('input[name="payment_remark"]').on('change', function() {
            if (CURRENT_SUBSTEP == 4) updateStep4Url()
        })

        // Date picker for payment due date
        $('input[name="payment_due_date"]').daterangepicker(datepickerParam)
        $('input[name="payment_due_date"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
            if (CURRENT_SUBSTEP == 4) updateStep4Url()
        });
        $('input[name="payment_due_date"]').val('')

        // Validate payment details before convert
        $('#convert-form').on('submit', function(e) {
            let errors = []
            $('.err_msg').addClass('hidden')

            let paymentMethod = $('select[name="payment_method"]').val()
            if (!paymentMethod || paymentMethod == '') {
                errors.push('Payment method is required')
                $('#payment_method_err').find('p').text('Payment method is required')
                $('#payment_method_err').removeClass('hidden')
            }

            if (CREDIT_PAYMENT_METHOD_IDS.includes(parseInt(paymentMethod))) {
                let paymentTerm = $('select[name="payment_term"]').val()
                if (!paymentTerm || paymentTerm == '') {
                    errors.push('Payment term is required')
                    $('#payment_term_err').find('p').text('Payment term is required')
                    $('#payment_term_err').removeClass('hidden')
                }
            }

            let paymentDueDate = $('input[name="payment_due_date"]').val()
            if (!paymentDueDate || paymentDueDate == '') {
                errors.push('Payment due date is required')
                $('#payment_due_date_err').find('p').text('Payment due date is required')
                $('#payment_due_date_err').removeClass('hidden')
            }

            if (errors.length > 0) {
                e.preventDefault()
            }
        })

        $('#previous-page-btn').on('click', function() {
            if (CURRENT_SUBSTEP == 4) {
                // Go back to step 3
                CURRENT_SUBSTEP = 3
                // Remove step 4 params from URL
                let params = new URLSearchParams(window.location.search)
                params.delete('substep')
                params.delete('quos')
                params.delete('report_type')
                params.delete('payment_method')
                params.delete('payment_term')
                params.delete('payment_due_date')
                params.delete('payment_remark')
                history.replaceState(null, '', '?' + params.toString())

                $('#step-4-content').addClass('hidden')
                $('#step-3-content').removeClass('hidden')
                $('#step-4-indicator').addClass('opacity-25')
                $('#step-3-indicator').removeClass('opacity-25')
            } else {
                history.back()
            }
        })

        function canProceed() {
            if (SELECTED_QUOS.length <= 0 || $('select[name="report_type"]').val() == '') {
                $('#next-step-btn').removeClass('bg-green-200')
                $('#next-step-btn').addClass('bg-slate-100')

                return false
            }
            $('#next-step-btn').addClass('bg-green-200')
            $('#next-step-btn').removeClass('bg-slate-100')

            return true
        }

        function updateStep4Url() {
            let params = new URLSearchParams(window.location.search)
            params.set('substep', '4')
            params.set('quos', SELECTED_QUOS.join(','))
            params.set('report_type', $('select[name="report_type"]').val())

            let pm = $('select[name="payment_method"]').val()
            if (pm) params.set('payment_method', pm); else params.delete('payment_method')
            let pt = $('select[name="payment_term"]').val()
            if (pt) params.set('payment_term', pt); else params.delete('payment_term')
            let pdd = $('input[name="payment_due_date"]').val()
            if (pdd) params.set('payment_due_date', pdd); else params.delete('payment_due_date')
            let pr = $('input[name="payment_remark"]').val()
            if (pr) params.set('payment_remark', pr); else params.delete('payment_remark')

            history.replaceState(null, '', '?' + params.toString())
        }
    </script>
@endpush
