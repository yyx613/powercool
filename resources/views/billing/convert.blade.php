@extends('layouts.app')
@section('title', 'Billing')

@section('content')
    <div class="mb-6">
        <x-app.page-title url="{{ route('invoice.index') }}">{{ __('Convert Invoice to Billing') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <div class="flex gap-4 flex-col lg:flex-row">
        <!-- Left -->
        <div class="bg-white p-4 border rounded-md flex flex-1 flex-col lg:flex-row gap-8 lg:gap-x-12">
            <!-- Steps -->
            <div class="flex flex-wrap lg:flex-col gap-6 flex-1">
                <!-- Step 1 -->
                <div class="min-w-[250px] flex-1 lg:flex-none flex items-center bg-yellow-100 rounded overflow-hidden {{ $step != 1 ? 'opacity-25' : ''  }}">
                    <div class="bg-yellow-300 p-2">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,24C5.383,24,0,18.617,0,12S5.383,0,12,0s12,5.383,12,12-5.383,12-12,12Zm0-22C6.486,2,2,6.486,2,12s4.486,10,10,10,10-4.486,10-10S17.514,2,12,2Zm1,15V7c0-.404-.244-.77-.617-.924-.375-.157-.805-.069-1.09,.217l-2.444,2.444c-.391,.391-.391,1.023,0,1.414s1.023,.391,1.414,0l.737-.737v7.586c0,.553,.448,1,1,1s1-.447,1-1Z"/></svg>
                    </div>
                    <h6 class="font-semibold mx-4">{{ __('Delivery Order Selection') }}</h6>
                </div>
                <!-- Step 2 -->
                <div class="min-w-[250px] flex-1 lg:flex-none flex items-center bg-yellow-100 rounded overflow-hidden {{ $step != 2 ? 'opacity-25' : ''  }}">
                    <div class="bg-yellow-300 p-2">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,24C5.383,24,0,18.617,0,12S5.383,0,12,0s12,5.383,12,12-5.383,12-12,12Zm0-22C6.486,2,2,6.486,2,12s4.486,10,10,10,10-4.486,10-10S17.514,2,12,2Zm4,15c0-.553-.448-1-1-1h-4.781c.426-.37,1.069-.72,1.742-1.086,1.754-.956,4.156-2.265,4.035-5.131-.089-2.121-1.844-3.783-3.995-3.783-2.206,0-4,1.794-4,4,0,.553,.448,1,1,1s1-.447,1-1c0-1.103,.897-2,2-2,1.058,0,1.954,.838,1.997,1.867,.064,1.513-1.088,2.253-2.994,3.29-.99,.54-1.925,1.049-2.559,1.797-.475,.56-.58,1.319-.272,1.983,.304,.655,.942,1.062,1.666,1.062h5.162c.552,0,1-.447,1-1Z"/></svg>
                    </div>
                    <h6 class="font-semibold mx-4">{{ __('Basic Info') }}</h6>
                </div>
                <!-- Step 3 -->
                <div class="min-w-[250px] flex-1 lg:flex-none flex items-center bg-yellow-100 rounded overflow-hidden {{ $step != 3 ? 'opacity-25' : ''  }}">
                    <div class="bg-yellow-300 p-2">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,0C5.383,0,0,5.383,0,12s5.383,12,12,12,12-5.383,12-12S18.617,0,12,0Zm0,22c-5.514,0-10-4.486-10-10S6.486,2,12,2s10,4.486,10,10-4.486,10-10,10Zm4-8c0,2.206-1.794,4-4,4h-3c-.552,0-1-.447-1-1s.448-1,1-1h3c1.103,0,2-.897,2-2s-.897-2-2-2h-2c-.552,0-1-.447-1-1s.448-1,1-1h2c.551,0,1-.448,1-1s-.449-1-1-1h-3c-.552,0-1-.447-1-1s.448-1,1-1h3c1.654,0,3,1.346,3,3,0,.68-.236,1.301-.619,1.805,.977,.73,1.619,1.885,1.619,3.195Z"/></svg>
                    </div>
                    <h6 class="font-semibold mx-4">{{ __('Product Info') }}</h6>
                </div>
            </div>
            <!-- Steps Content -->
            <div class="flex-[3]">
                <!-- Step 1 -->
                @if ($step == 1)
                    <div class="flex flex-col h-full">
                        <div class="mb-2">
                            <h5 class="text-md font-semibold">{{ __('Select invoice to proceed') }}</h5>
                        </div>
                        @if (count($invoices) > 0)
                            <ul class="flex flex-wrap gap-4 flex-1 items-start">
                                @foreach ($invoices as $inv)
                                    <li class="w-1/4 p-2 rounded-md cursor-pointer border border-slate-100 text-center inv-selections" data-id="{{ $inv->id }}">{{ $inv->sku }}</li>
                                @endforeach
                            </ul>
                            <div class="flex justify-end mt-8">
                                <a href="{{ route('billing.to_billing') }}" class="bg-slate-100 rounded-md py-2 px-4 flex justify-center items-center gap-x-2" id="confirm-btn">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="m16.298,8.288l1.404,1.425-5.793,5.707c-.387.387-.896.58-1.407.58s-1.025-.195-1.416-.585l-2.782-2.696,1.393-1.437,2.793,2.707,5.809-5.701Zm7.702,3.712c0,6.617-5.383,12-12,12S0,18.617,0,12,5.383,0,12,0s12,5.383,12,12Zm-2,0c0-5.514-4.486-10-10-10S2,6.486,2,12s4.486,10,10,10,10-4.486,10-10Z"/></svg>
                                    <span class="text-sm font-semibold">{{ __('Confirm') }}</span>
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
                            <h5 class="text-md font-semibold">{{ __('Enter info to proceed') }}</h5>
                        </div>
                        <div>
                            <form action="{{ route('billing.to_billing') }}">
                                @csrf
                                <input type="text" name="info" class="hidden">
                                <div class="grid grid-cols-3 gap-8 w-full mb-8">
                                    <div class="flex flex-col">
                                        <x-app.input.label id="term" class="mb-1">{{ __('Term') }}</x-app.input.label>
                                        <x-app.input.select name="term" id="term">
                                            <option value="">{{ __('Select a term') }}</option>
                                            @foreach ($terms as $term)
                                                <option value="{{ $term->id }}">{{ $term->name }}</option>
                                            @endforeach
                                        </x-app.input.select>
                                        <x-input-error :messages="$errors->get('term')" class="mt-1" />
                                    </div>
                                    <div class="flex flex-col">
                                        <x-app.input.label id="our_do_no" class="mb-1">{{ __('Our D/O No') }}</x-app.input.label>
                                        <x-app.input.input name="our_do_no" id="our_do_no" value="{{ old('our_do_no') }}" />
                                        <x-input-error :messages="$errors->get('our_do_no')" class="mt-1" />
                                    </div>
                                    <div class="flex flex-col">
                                        <x-app.input.label id="your_ref" class="mb-1">{{ __('Your Ref') }}</x-app.input.label>
                                        <x-app.input.input name="your_ref" id="your_ref" value="{{ old('your_ref') }}" />
                                        <x-input-error :messages="$errors->get('your_ref')" class="mt-1" />
                                    </div>
                                </div>
                                <div class="flex justify-end mt-8">
                                    <x-app.button.submit>{{ __('Submit') }}</x-app.button.submit>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
                <!-- Step 3 -->
                @if ($step == 3)
                    <div>
                        <div class="mb-4">
                            <h5 class="text-md font-semibold">{{ __('Confirm products to convert') }}</h5>
                        </div>
                        @if (count($products) > 0)
                            <form action="{{ route('billing.convert_to_billing') }}" id="product-info-form">
                                @csrf
                                <div class="flex flex-col gap-4">
                                    @foreach ($products as $key => $pro)
                                        <div class="w-full p-3 rounded-md border border-slate-200">
                                            <div class="flex justify-between">
                                                <h6 class="leading-none">{{ $pro->product->model_desc }}</h6>
                                                <p class="text-sm">SKU: {{ $pro->product->sku }}</p>
                                            </div>
                                            <p class="text-sm text-slate-400">{{ $pro->product->model_desc }}</p>
                                            <div class="border-t pt-4 mt-4">
                                                <div class="flex items-center justify-between mb-2">
                                                    <p class="text-sm font-medium mb-2">Total Qty: {{ $pro->qty }}</p>
                                                    <button type="button" class="add-item-btns bg-blue-500 rounded-md aspect-square w-7 flex items-center justify-center" data-product-id="{{ $pro->product->id }}">
                                                        <svg class="h-4 w-4 fill-white" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve" width="512" height="512">
                                                            <path d="M480,224H288V32c0-17.673-14.327-32-32-32s-32,14.327-32,32v192H32c-17.673,0-32,14.327-32,32s14.327,32,32,32h192v192   c0,17.673,14.327,32,32,32s32-14.327,32-32V288h192c17.673,0,32-14.327,32-32S497.673,224,480,224z"/>
                                                        </svg>
                                                    </button>
                                                </div>

                                                <div class="item-row-template items flex items-center gap-4 hidden" data-product-id="{{ $pro->product->id }}">
                                                    <x-app.input.input name="qty_{{ $pro->product->id }}[]" placeholder="Enter quantity" class="int-input text-sm flex-1" />
                                                    <x-app.input.input name="price_{{ $pro->product->id }}[]" placeholder="Enter price" class="decimal-input text-sm flex-1" />
                                                    <button type="button" class="bg-red-500 rounded-md aspect-square w-7 flex items-center justify-center">
                                                        <svg class="h-4 w-4 fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M21,4H17.9A5.009,5.009,0,0,0,13,0H11A5.009,5.009,0,0,0,6.1,4H3A1,1,0,0,0,3,6H4V19a5.006,5.006,0,0,0,5,5h6a5.006,5.006,0,0,0,5-5V6h1a1,1,0,0,0,0-2ZM11,2h2a3.006,3.006,0,0,1,2.829,2H8.171A3.006,3.006,0,0,1,11,2Zm7,17a3,3,0,0,1-3,3H9a3,3,0,0,1-3-3V6H18Z"/><path d="M10,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,10,18Z"/><path d="M14,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,14,18Z"/></svg>
                                                    </button>
                                                </div>

                                                <div class="item-container flex flex-col gap-4" data-product-id="{{ $pro->product->id }}">
                                                </div>

                                                <x-app.message.error id="row_{{ $pro->product->id }}_err"/>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="flex justify-end mt-8">
                                    <x-app.button.submit>{{ __('Confirm') }}</x-app.button.submit>
                                </div>
                            </form>
                        @else
                            @include('components.app.no-data')
                        @endif
                    </div>
                @endif
            </div>
        </div>
        <!-- Right -->
        <div class="bg-white p-4 border rounded-md">
            <div class="max-h-44 overflow-y-auto">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="text-left px-2 py-1 text-sm border-b">{{ __('Product') }}</th>
                            <th class="text-left px-2 py-1 text-sm border-b">{{ __('Qty/SKU') }}</th>
                            <th class="text-left px-2 py-1 text-sm border-b">{{ __('U/Price') }}</th>
                            <th class="text-left px-2 py-1 text-sm border-b">{{ __('T/Price') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($costs as $co)
                            <tr>
                                <td class="px-2 py-0.5 text-sm">{{ $co->product()->withTrashed()->first()->model_desc }}</td>
                                <td class="px-2 py-0.5 text-sm">{{ $co->qty ?? $co->sku }}</td>
                                <td class="px-2 py-0.5 text-sm">{{ number_format($co->unit_price, 2) }}</td>
                                <td class="px-2 py-0.5 text-sm">{{ number_format($co->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    PRODUCTS = @json($products ?? []);
    SELECTED_INVS = []

    $('.inv-selections').on('click', function() {
        let id = $(this).data('id')

        const index = SELECTED_INVS.indexOf(id);
        if (index > -1) {
            SELECTED_INVS.splice(index, 1)

            $(`.inv-selections[data-id="${id}"]`).removeClass('!border-black')
        } else {
            SELECTED_INVS.push(id)

            $(`.inv-selections[data-id="${id}"]`).addClass('!border-black')
        }

        if (SELECTED_INVS.length <= 0) {
            $('#confirm-btn').removeClass('bg-green-200')
            $('#confirm-btn').addClass('bg-slate-100')
        } else {
            $('#confirm-btn').addClass('bg-green-200')
            $('#confirm-btn').removeClass('bg-slate-100')
        }
    })
    $('#confirm-btn').on('click', function(e) {
        e.preventDefault()

        if (SELECTED_INVS.length <= 0) return

        let url = $(this).attr('href')
        url = `${url}?inv=${SELECTED_INVS}`

        window.location.href = url
    })
    $('.custom-unit-price input').on('keyup', function() {
        let id = $(this).parent().data('id')

        $(`.unit-price-label[data-id="${id}"]`).text(`U/Price: RM ${ priceFormat($(this).val()) }`)
    })
    // Step 3
    $('#product-info-form').on('submit', function(e) {
        e.preventDefault()

        $('.err_msg').addClass('hidden')

        let data = {}
        let productIds = []
        for (i = 0; i < PRODUCTS.length; i++) {
            productIds.push(PRODUCTS[i].product.id)
            $(`.item-row-template[data-product-id="${PRODUCTS[i].product.id}"]`).remove()

            let qty = []
            let price = []
            $(`.item-container[data-product-id="${PRODUCTS[i].product.id}"] .items`).each(function(idx, obj) {
                let qtyVal = $(this).find(`input[name="qty_${PRODUCTS[i].product.id}[]"]`).val()
                let priceVal = $(this).find(`input[name="price_${PRODUCTS[i].product.id}[]"]`).val()

                qty.push( qtyVal == '' ? null : qtyVal )
                price.push( priceVal == '' ? null : priceVal )
            })
            data[`qty_${PRODUCTS[i].product.id}`] = qty
            data[`price_${PRODUCTS[i].product.id}`] = price
        }
        data['product_ids'] = productIds.join(',')

        // Submit form
        let url = $(this).attr('action')
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: url,
            type: 'POST',
            data: data,
            success: function(res) {
                window.location.href = "{{ route('billing.index') }}";
            },
            error: function(xhr, status, error) {
                let err = JSON.parse(xhr.responseText)

                for (key in err) {
                    $(`#${key}_err`).find('p').text(err[key])
                    $(`#${key}_err`).removeClass('hidden')
                }
            }
        });
    })
    $('.add-item-btns').on('click', function() {
        let productId = $(this).data('product-id')
        let clone = $(`.item-row-template[data-product-id="${productId}"]`)[0].cloneNode(true)

        $(clone).removeClass('item-row-template hidden')
        $(`.item-container[data-product-id="${productId}"]`).append(clone)
    })

    $(document).ready(function() {
        for (i = 0; i < PRODUCTS.length; i++) {
            $(`.add-item-btns[data-product-id="${PRODUCTS[i].product.id}"]`).click()
        }
    })
</script>
@endpush
