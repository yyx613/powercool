@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <x-app.page-title url="{{ route('delivery_order.index') }}">Convert Delivery Order to Billing</x-app.page-title>
    </div>
    <div class="bg-white p-4 border rounded-md flex gap-x-14">
        <!-- Steps -->
        <div class="flex flex-col gap-y-6 flex-1">
            <!-- Step 1 -->
            <div class="flex items-center bg-yellow-100 rounded overflow-hidden {{ $step != 1 ? 'opacity-25' : ''  }}">
                <div class="bg-yellow-300 p-2">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,24C5.383,24,0,18.617,0,12S5.383,0,12,0s12,5.383,12,12-5.383,12-12,12Zm0-22C6.486,2,2,6.486,2,12s4.486,10,10,10,10-4.486,10-10S17.514,2,12,2Zm1,15V7c0-.404-.244-.77-.617-.924-.375-.157-.805-.069-1.09,.217l-2.444,2.444c-.391,.391-.391,1.023,0,1.414s1.023,.391,1.414,0l.737-.737v7.586c0,.553,.448,1,1,1s1-.447,1-1Z"/></svg>
                </div>
                <h6 class="font-semibold mx-4">Delivery Order Selection</h6>
            </div>
            <!-- Step 2 -->
            <div class="flex items-center bg-yellow-100 rounded overflow-hidden {{ $step != 2 ? 'opacity-25' : ''  }}">
                <div class="bg-yellow-300 p-2">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,24C5.383,24,0,18.617,0,12S5.383,0,12,0s12,5.383,12,12-5.383,12-12,12Zm0-22C6.486,2,2,6.486,2,12s4.486,10,10,10,10-4.486,10-10S17.514,2,12,2Zm4,15c0-.553-.448-1-1-1h-4.781c.426-.37,1.069-.72,1.742-1.086,1.754-.956,4.156-2.265,4.035-5.131-.089-2.121-1.844-3.783-3.995-3.783-2.206,0-4,1.794-4,4,0,.553,.448,1,1,1s1-.447,1-1c0-1.103,.897-2,2-2,1.058,0,1.954,.838,1.997,1.867,.064,1.513-1.088,2.253-2.994,3.29-.99,.54-1.925,1.049-2.559,1.797-.475,.56-.58,1.319-.272,1.983,.304,.655,.942,1.062,1.666,1.062h5.162c.552,0,1-.447,1-1Z"/></svg>
                </div>
                <h6 class="font-semibold mx-4">Basic Info</h6>
            </div>
            <!-- Step 3 -->
            <div class="flex items-center bg-yellow-100 rounded overflow-hidden {{ $step != 3 ? 'opacity-25' : ''  }}">
                <div class="bg-yellow-300 p-2">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,0C5.383,0,0,5.383,0,12s5.383,12,12,12,12-5.383,12-12S18.617,0,12,0Zm0,22c-5.514,0-10-4.486-10-10S6.486,2,12,2s10,4.486,10,10-4.486,10-10,10Zm4-8c0,2.206-1.794,4-4,4h-3c-.552,0-1-.447-1-1s.448-1,1-1h3c1.103,0,2-.897,2-2s-.897-2-2-2h-2c-.552,0-1-.447-1-1s.448-1,1-1h2c.551,0,1-.448,1-1s-.449-1-1-1h-3c-.552,0-1-.447-1-1s.448-1,1-1h3c1.654,0,3,1.346,3,3,0,.68-.236,1.301-.619,1.805,.977,.73,1.619,1.885,1.619,3.195Z"/></svg>
                </div>
                <h6 class="font-semibold mx-4">Product Info</h6>
            </div>
        </div>
        <!-- Steps Content -->
        <div class="flex-[3]">
            <!-- Step 1 -->
            @if ($step == 1)
                <div>
                    <div class="mb-2">
                        <h5 class="text-md font-semibold">Select delivery order to proceed</h5>
                    </div>
                    @if (count($delivery_orders) > 0)
                        <ul class="flex flex-wrap gap-4">
                            @foreach ($delivery_orders as $do)
                                <li class="w-1/6 p-2 rounded-md cursor-pointer border border-slate-100 text-center do-selections" data-id="{{ $do->id }}">{{ $do->sku }}</li>
                            @endforeach
                        </ul>
                        <div class="flex justify-end mt-8">
                            <a href="{{ route('billing.to_delivery_order_billing') }}" class="w-1/6 bg-slate-100 rounded-md py-2 px-4 flex justify-center items-center gap-x-2" id="confirm-btn">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="m16.298,8.288l1.404,1.425-5.793,5.707c-.387.387-.896.58-1.407.58s-1.025-.195-1.416-.585l-2.782-2.696,1.393-1.437,2.793,2.707,5.809-5.701Zm7.702,3.712c0,6.617-5.383,12-12,12S0,18.617,0,12,5.383,0,12,0s12,5.383,12,12Zm-2,0c0-5.514-4.486-10-10-10S2,6.486,2,12s4.486,10,10,10,10-4.486,10-10Z"/></svg>
                                <span class="text-sm font-semibold">Confirm</span>
                            </a>
                        </div>
                    @else
                        @include('components.app.no-data')
                    @endif
                </div>
            @endif
            <!-- Step 2 -->
            @if ($step == 2)
                <div>
                    <div class="mb-4">
                        <h5 class="text-md font-semibold">Enter info to proceed</h5>
                    </div>
                    <div>
                        <form action="{{ route('billing.to_delivery_order_billing') }}">
                            @csrf
                            <input type="text" name="info" class="hidden">
                            <div class="grid grid-cols-3 gap-8 w-full mb-8">
                                <div class="flex flex-col">
                                    <x-app.input.label id="sale" class="mb-1">Salesperson</x-app.input.label>
                                    <x-app.input.select name="sale" id="sale">
                                        <option value="">Select a salesperson</option>
                                        @foreach ($sales as $sale)
                                            <option value="{{ $sale->id }}">{{ $sale->name }}</option>
                                        @endforeach
                                    </x-app.input.select>
                                    <x-input-error :messages="$errors->get('sale')" class="mt-1" />
                                </div>
                                <div class="flex flex-col">
                                    <x-app.input.label id="term" class="mb-1">Term</x-app.input.label>
                                    <x-app.input.select name="term" id="term">
                                        <option value="">Select a term</option>
                                        @foreach ($terms as $term)
                                            <option value="{{ $term->id }}">{{ $term->name }}</option>
                                        @endforeach
                                    </x-app.input.select>
                                    <x-input-error :messages="$errors->get('term')" class="mt-1" />
                                </div>
                                <div class="flex flex-col">
                                    <x-app.input.label id="your_po_no" class="mb-1">Your P/O No</x-app.input.label>
                                    <x-app.input.input name="your_po_no" id="your_po_no" value="{{ old('your_po_no') }}" />
                                    <x-input-error :messages="$errors->get('your_po_no')" class="mt-1" />
                                </div>
                                <div class="flex flex-col">
                                    <x-app.input.label id="your_so_no" class="mb-1">Your S/O No</x-app.input.label>
                                    <x-app.input.input name="your_so_no" id="your_so_no" value="{{ old('your_so_no') }}" />
                                    <x-input-error :messages="$errors->get('your_so_no')" class="mt-1" />
                                </div>
                            </div>
                            <div class="flex justify-end mt-8">
                                <x-app.button.submit>Submit</x-app.button.submit>
                            </div>
                        </form>
                    </div> 
                </div>
            @endif
            <!-- Step 3 -->
            @if ($step == 3)
                <div>
                    <div class="mb-4">
                        <h5 class="text-md font-semibold">Confirm products to convert</h5> 
                    </div>
                    @if (count($products) > 0)
                        <form action="{{ route('billing.convert_to_delivery_order_billing') }}">
                            @csrf
                            <div class="grid grid-cols-2 gap-4">
                                @foreach ($products as $pro)
                                    <div>
                                        <input type="text" name="sale_product_id[]" value="{{ $pro->id }}" class="hidden">
                                        <div class="w-full p-3 rounded-md border border-slate-100">
                                            <h6 class="leading-none">{{ $pro->product->model_name }}</h6>
                                            <p class="text-sm text-slate-400">{{ $pro->desc }}</p>
                                            <div class="flex justify-between">
                                                <span class="text-sm text-slate-600 mt-2 qty-label" data-id="{{ $pro->id }}" data-value="{{ $pro->qty }}">Qty: {{ $pro->qty }}</span>
                                                <span class="text-sm text-slate-600 mt-2 unit-price-label" data-id="{{ $pro->id }}">U/Price: RM {{ number_format($pro->unit_price, 2) }}</span>
                                                <span class="text-sm text-slate-600 mt-2 total-price-label" data-id="{{ $pro->id }}">T/Price: RM {{ number_format(($pro->qty * $pro->unit_price), 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="flex justify-end mt-8">
                                <x-app.button.submit>Confirm</x-app.button.submit>
                            </div>
                        </form>
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
    SELECTED_DOS = []

    $('.do-selections').on('click', function() {
        let id = $(this).data('id')

        const index = SELECTED_DOS.indexOf(id);
        if (index > -1) {
            SELECTED_DOS.splice(index, 1)

            $(`.do-selections[data-id="${id}"]`).removeClass('!border-black')
        } else {
            SELECTED_DOS.push(id)

            $(`.do-selections[data-id="${id}"]`).addClass('!border-black')
        }

        if (SELECTED_DOS.length <= 0) {
            $('#confirm-btn').removeClass('bg-green-200')
            $('#confirm-btn').addClass('bg-slate-100')
        } else {
            $('#confirm-btn').addClass('bg-green-200')
            $('#confirm-btn').removeClass('bg-slate-100')
        }
    })
    $('#confirm-btn').on('click', function(e) {
        e.preventDefault()

        if (SELECTED_DOS.length <= 0) return
        
        let url = $(this).attr('href')
        url = `${url}?do=${SELECTED_DOS}`

        window.location.href = url
    })
    $('.custom-unit-price input').on('keyup', function() {
        let id = $(this).parent().data('id')
        let qty = $(`.qty-label[data-id="${id}"]`).data('value')

        $(`.unit-price-label[data-id="${id}"]`).text(`U/Price: RM ${ priceFormat($(this).val()) }`)
        $(`.total-price-label[data-id="${id}"]`).text(`T/Price: RM ${ priceFormat(qty * $(this).val()) }`)
    })
</script>
@endpush