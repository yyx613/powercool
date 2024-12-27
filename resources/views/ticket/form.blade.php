@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('ticket.index') }}">{{ isset($ticket) ? __('Edit Ticket - ') . $ticket->sku : __('Create Ticket') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ isset($ticket) ? route('ticket.update', ['ticket' => $ticket]) : route('ticket.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
        @csrf
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label id="customer" class="mb-1">{{ __('Customer') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="customer" id="customer" :hasError="$errors->has('customer')" placeholder="{{ __('Select a customer') }}">
                        <option value="">{{ __('Select a customer') }}</option>
                        @foreach ($customers as $cu)
                            <option value="{{ $cu->id }}" @selected(old('customer', isset($ticket) ? $ticket->customer_id : null) == $cu->id)>{{ $cu->name }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('customer')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="status" class="mb-1">{{ __('Status') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                        <option value="">{{ __('Select a Active/Inactive') }}</option>
                        <option value="1" @selected(old('status', isset($ticket) ? $ticket->is_active : null) == 1)>{{ __('Active') }}</option>
                        <option value="0" @selected(old('status', isset($ticket) ? $ticket->is_active : null) === 0)>{{ __('Inactive') }}</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">{{ __('Attachment') }}</x-app.input.label>
                    <x-app.input.file id="attachment[]" :hasError="$errors->has('attachment')" multiple="true"/>
                    <x-input-error :messages="$errors->get('attachment')" class="mt-1" />
                    <div class="uploaded-file-preview-container" data-id="attachment">
                        <div class="p-y.5 px-1.5 rounded bg-blue-50 mt-2 hidden" id="uploaded-file-template">
                            <a href="" target="_blank" class="text-blue-700 text-xs"></a>
                        </div>
                        @if (isset($ticket))
                            @foreach ($ticket->attachments as $att)
                                <div class="p-y.5 px-1.5 rounded bg-blue-50 mt-2 old-preview">
                                    <a href="{{ $att->url }}" target="_blank" class="text-blue-700 text-xs">{{ $att->src }}</a>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="subject" class="mb-1">{{ __('Subject') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="subject" id="subject" :hasError="$errors->has('subject')" value="{{ old('subject', isset($ticket) ? $ticket->subject : null) }}" />
                    <x-input-error :messages="$errors->get('subject')" class="mt-1" />
                </div>
                <div class="flex flex-col col-span-2 lg:col-span-3">
                    <x-app.input.label id="body" class="mb-1">{{ __('Body') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.textarea name="body" id="body" :hasError="$errors->has('body')" text="{{ old('body', isset($ticket) ? $ticket->body : null) }}" />
                    <x-input-error :messages="$errors->get('body')" class="mt-1" />
                </div>
            </div>
        </div>
        <!-- SO / INV -->
        <div class="bg-white p-4 rounded-md shadow">
            <!-- template -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 lg:gap-8 w-full mb-8 p-4 rounded-md relative group hidden transition durtion-300 hover:bg-slate-50" id="item-template">
                <button type="button" class="bg-rose-400 p-2 rounded-full absolute top-[-5px] right-[-5px] hidden group-hover:block delete-item-btns" title="Delete Product">
                    <svg class="h-3 w-3 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M13.93,12L21.666,2.443c.521-.644,.422-1.588-.223-2.109-.645-.522-1.588-.421-2.109,.223l-7.334,9.06L4.666,.557c-1.241-1.519-3.56,.357-2.332,1.887l7.736,9.557L2.334,21.557c-.521,.644-.422,1.588,.223,2.109,.64,.519,1.586,.424,2.109-.223l7.334-9.06,7.334,9.06c.524,.647,1.47,.742,2.109,.223,.645-.521,.744-1.466,.223-2.109l-7.736-9.557Z"/></svg>
                </button>

                <div class="flex flex-col">
                    <x-app.input.label id="so_inv" class="mb-1">{{ __('Sale Order / Invoice') }}</x-app.input.label>
                    <x-app.input.select name="so_inv[]" id="so_inv" :hasError="$errors->has('so_inv')" placeholder="{{ __('Select a sale order / invoice') }}">
                        <option value="">{{ __('Select a sale order / invoice') }}</option>
                        @foreach ($sale_orders as $so)
                            <option value="{{ $so->id }}" @selected(old('so_inv') == $so->id) data-type="so">{{ $so->sku }}</option>
                        @endforeach
                        @foreach ($invoices as $inv)
                            <option value="{{ $inv->id }}" @selected(old('so_inv') == $inv->id) data-type="inv">{{ $inv->sku }}</option>
                        @endforeach
                    </x-app.input.select>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="product" class="mb-1">{{ __('Product') }}</x-app.input.label>
                    <x-app.input.select name="product[]" id="product" :hasError="$errors->has('product')" placeholder="{{ __('Select a product') }}">
                        <option value="">{{ __('Select a product') }}</option>
                    </x-app.input.select>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="serial_no" class="mb-1">{{ __('Serial No') }}</x-app.input.label>
                    <x-app.input.select name="serial_no[]" id="serial_no" :hasError="$errors->has('serial_no')" placeholder="{{ __('Select a serial no') }}">
                        <option value="">{{ __('Select a serial no') }}</option>
                    </x-app.input.select>
                </div>
                <input type="hidden" name="so_inv_type[]" value="">
            </div>
            @if (old('so_inv') != null)
                @foreach (old('so_inv') as $key => $val)
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 lg:gap-8 w-full mb-8 p-4 rounded-md relative group transition durtion-300 hover:bg-slate-50 items" data-id={{ $key + 1 }}>
                        <button type="button" class="bg-rose-400 p-2 rounded-full absolute top-[-5px] right-[-5px] hidden group-hover:block delete-item-btns" title="Delete Product">
                            <svg class="h-3 w-3 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M13.93,12L21.666,2.443c.521-.644,.422-1.588-.223-2.109-.645-.522-1.588-.421-2.109,.223l-7.334,9.06L4.666,.557c-1.241-1.519-3.56,.357-2.332,1.887l7.736,9.557L2.334,21.557c-.521,.644-.422,1.588,.223,2.109,.64,.519,1.586,.424,2.109-.223l7.334-9.06,7.334,9.06c.524,.647,1.47,.742,2.109,.223,.645-.521,.744-1.466,.223-2.109l-7.736-9.557Z"/></svg>
                        </button>
                        <div class="flex flex-col">
                            <x-app.input.label id="so_inv" class="mb-1">{{ __('Sale Order / Invoice') }}</x-app.input.label>
                            <x-app.input.select name="so_inv[]" id="so_inv" :hasError="$errors->has('so_inv')" placeholder="{{ __('Select a sale order / invoice') }}">
                                <option value="">{{ __('Select a sale order / invoice') }}</option>
                                @foreach ($sale_orders as $so)
                                    <option value="{{ $so->id }}" @selected(old('so_inv_type.'. $key) == 'so' && old('so_inv.'. $key) == $so->id) data-type="so">{{ $so->sku }}</option>
                                @endforeach
                                @foreach ($invoices as $inv)
                                    <option value="{{ $inv->id }}" @selected(old('so_inv_type.'.$key) == 'inv' && old('so_inv.' . $key) == $inv->id) data-type="inv">{{ $inv->sku }}</option>
                                @endforeach
                            </x-app.input.select>
                            <x-input-error :messages="$errors->get('so_inv.'.$key)" class="mt-1" />
                        </div>
                        <div class="flex flex-col">
                            <x-app.input.label id="product" class="mb-1">{{ __('Product') }}</x-app.input.label>
                            <x-app.input.select name="product[]" id="product" :hasError="$errors->has('product')" placeholder="{{ __('Select a product') }}">
                                <option value="">{{ __('Select a product') }}</option>
                                @foreach ($products as $prod)
                                    @if (old('product.'.$key) == $prod->id)
                                        <option value="{{ $prod->id }}" @selected(old('product.' . $key) == $prod->id)>{{ $prod->sku }}</option>
                                    @endif
                                @endforeach
                            </x-app.input.select>
                            <x-input-error :messages="$errors->get('product.'.$key)" class="mt-1" />
                        </div>
                        <div class="flex flex-col">
                            <x-app.input.label id="serial_no" class="mb-1">{{ __('Serial No') }}</x-app.input.label>
                            <x-app.input.select name="serial_no[]" id="serial_no" :hasError="$errors->has('serial_no')" placeholder="{{ __('Select a serial no') }}">
                                <option value="">{{ __('Select a serial no') }}</option>
                                @foreach ($product_children as $pc)
                                    @if (old('serial_no.'.$key) == $pc->id)
                                        <option value="{{ $pc->id }}" @selected(old('serial_no.' . $key) == $pc->id)>{{ $pc->sku }}</option>
                                    @endif
                                @endforeach
                            </x-app.input.select>
                            <x-input-error :messages="$errors->get('serial_no.'.$key)" class="mt-1" />
                        </div>
                        <input type="hidden" name="so_inv_type[]" value="">
                    </div>
                @endforeach
            @elseif (isset($ticket))
                @php
                    $ticket_so_invs = explode(',', $ticket->so_inv) ?? null;
                    $ticket_so_inv_types = explode(',', $ticket->so_inv_type) ?? null;
                    $ticket_products = explode(',', $ticket->product_id) ?? null;
                    $ticket_product_children = explode(',', $ticket->product_child_id) ?? null;
                @endphp
                @foreach ($ticket_so_invs as $key => $val)
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 lg:gap-8 w-full mb-8 p-4 rounded-md relative group transition durtion-300 hover:bg-slate-50 items" data-id={{ $key + 1 }}>
                        <button type="button" class="bg-rose-400 p-2 rounded-full absolute top-[-5px] right-[-5px] hidden group-hover:block delete-item-btns" title="Delete Product">
                            <svg class="h-3 w-3 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M13.93,12L21.666,2.443c.521-.644,.422-1.588-.223-2.109-.645-.522-1.588-.421-2.109,.223l-7.334,9.06L4.666,.557c-1.241-1.519-3.56,.357-2.332,1.887l7.736,9.557L2.334,21.557c-.521,.644-.422,1.588,.223,2.109,.64,.519,1.586,.424,2.109-.223l7.334-9.06,7.334,9.06c.524,.647,1.47,.742,2.109,.223,.645-.521,.744-1.466,.223-2.109l-7.736-9.557Z"/></svg>
                        </button>
                        <div class="flex flex-col">
                            <x-app.input.label id="so_inv" class="mb-1">{{ __('Sale Order / Invoice') }}</x-app.input.label>
                            <x-app.input.select name="so_inv[]" id="so_inv" :hasError="$errors->has('so_inv')" placeholder="{{ __('Select a sale order / invoice') }}">
                                <option value="">{{ __('Select a sale order / invoice') }}</option>
                                @foreach ($sale_orders as $so)
                                    <option value="{{ $so->id }}" @selected($ticket_so_inv_types[$key] == 'so' && $ticket_so_invs[$key] == $so->id) data-type="so">{{ $so->sku }}</option>
                                @endforeach
                                @foreach ($invoices as $inv)
                                    <option value="{{ $inv->id }}" @selected($ticket_so_inv_types[$key] == 'inv' && $ticket_so_invs[$key] == $inv->id) data-type="inv">{{ $inv->sku }}</option>
                                @endforeach
                            </x-app.input.select>
                            <x-input-error :messages="$errors->get('so_inv.'.$key)" class="mt-1" />
                        </div>
                        <div class="flex flex-col">
                            <x-app.input.label id="product" class="mb-1">{{ __('Product') }}</x-app.input.label>
                            <x-app.input.select name="product[]" id="product" :hasError="$errors->has('product')" placeholder="{{ __('Select a product') }}">
                                <option value="">{{ __('Select a product') }}</option>
                                @foreach ($products as $prod)
                                    @if ($ticket_products[$key] == $prod->id)
                                        <option value="{{ $prod->id }}" @selected($ticket_products[$key] == $prod->id)>{{ $prod->sku }}</option>
                                    @endif
                                @endforeach
                            </x-app.input.select>
                            <x-input-error :messages="$errors->get('product.'.$key)" class="mt-1" />
                        </div>
                        <div class="flex flex-col">
                            <x-app.input.label id="serial_no" class="mb-1">{{ __('Serial No') }}</x-app.input.label>
                            <x-app.input.select name="serial_no[]" id="serial_no" :hasError="$errors->has('serial_no')" placeholder="{{ __('Select a serial no') }}">
                                <option value="">{{ __('Select a serial no') }}</option>
                                @foreach ($product_children as $pc)
                                    @if ($ticket_product_children[$key] == $pc->id)
                                        <option value="{{ $pc->id }}" @selected($ticket_product_children[$key] == $pc->id)>{{ $pc->sku }}</option>
                                    @endif
                                @endforeach
                            </x-app.input.select>
                            <x-input-error :messages="$errors->get('serial_no.'.$key)" class="mt-1" />
                        </div>
                        <input type="hidden" name="so_inv_type[]" value="">
                    </div>
                @endforeach
            @endif
        <div id="items-container" class="col-span-3"></div>
            <!-- Add Items -->
            <div class="flex justify-end px-4">
                <button type="button" class="bg-yellow-400 rounded-md py-1.5 px-3 flex items-center gap-x-2 transition duration-300 hover:bg-yellow-300 hover:shadow" id="add-item-btn">
                    <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve" width="512" height="512">
                        <path d="M480,224H288V32c0-17.673-14.327-32-32-32s-32,14.327-32,32v192H32c-17.673,0-32,14.327-32,32s14.327,32,32,32h192v192   c0,17.673,14.327,32,32,32s32-14.327,32-32V288h192c17.673,0,32-14.327,32-32S497.673,224,480,224z"/>
                    </svg>
                    <span class="text-sm">{{ __('Add Item') }}</span>
                </button>
            </div>
        </div>
        <div class="mt-8 flex justify-end">
            <x-app.button.submit>{{ isset($ticket) ? __('Update Ticket') : __('Create New Ticket') }}</x-app.button.submit>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        ITEMS_COUNT = 0

        $(document).ready(function() {
            ITEMS_COUNT = $('.items').length

            if (ITEMS_COUNT == 0) $('#add-item-btn').click()
        })

        $('input[name="attachment[]"]').on('change', function() {
            let files = $(this).prop('files');

            $('.uploaded-file-preview-container[data-id="attachment"]').find('.old-preview').remove()

            for (let i = 0; i < files.length; i++) {
                const file = files[i];

                let clone = $('#uploaded-file-template')[0].cloneNode(true);
                $(clone).find('a').text(file.name)
                $(clone).find('a').attr('href', URL.createObjectURL(file))
                $(clone).addClass('old-preview')
                $(clone).removeClass('hidden')
                $(clone).removeAttr('id')

                $('.uploaded-file-preview-container[data-id="attachment"]').append(clone)
                $('.uploaded-file-preview-container[data-id="attachment"]').removeClass('hidden')
            }
        })

        $('#add-item-btn').on('click', function() {
            let clone = $('#item-template')[0].cloneNode(true);

            ITEMS_COUNT++
            $(clone).attr('data-id', ITEMS_COUNT)
            $(clone).find('.delete-item-btns').attr('data-id', ITEMS_COUNT)
            $(clone).addClass('items')
            $(clone).removeClass('hidden')
            $(clone).removeAttr('id')

            $('#items-container').append(clone)
        })
        $('body').on('click', '.delete-item-btns', function() {
            let id = $(this).data('id')

            $(`.items[data-id="${id}"]`).remove()

            ITEMS_COUNT = 0
            $('.items').each(function(i, obj) {
                ITEMS_COUNT++
                $(this).attr('data-id', ITEMS_COUNT)
                $(this).find('.delete-item-btns').attr('data-id', ITEMS_COUNT)
            })
        })


        // SO / INV changed, get products
        $('body').on('change', 'select[name="so_inv[]"]', function() {
            let type = $(this).find('option:checked').data('type')
            let val = $(this).val()
            let id = $(this).parent().parent().data('id')

            $(`.items[data-id="${id}"] input[name="so_inv_type[]"]`).val(type)

            let url = "{{ config('app.url') }}"
            url = `${url}/ticket/get-products?type=${type}&val=${val}`

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                contentType: 'application/json',
                success: function(res) {
                    // Append products option
                    $(`.items[data-id="${id}"] select[name="product[]"] option:not(:first)`).remove()

                    for (let i = 0; i < res.products.length ;i++) {
                        let opt = new Option(res.products[i].sku, res.products[i].id)
                        $(`.items[data-id="${id}"] select[name="product[]"]`).append(opt)
                    }
                }
            });
        })
        // Product changed, get children
        $('body').on('change', 'select[name="product[]"]', function() {
            let val = $(this).val()
            let id = $(this).parent().parent().data('id')

            let url = "{{ config('app.url') }}"
            url = `${url}/ticket/get-product-children?product_id=${val}`

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                contentType: 'application/json',
                success: function(res) {
                    // Append product children option
                    $(`.items[data-id="${id}"] select[name="serial_no[]"] option:not(:first)`).remove()

                    for (let i = 0; i < res.product_children.length ;i++) {
                        let opt = new Option(res.product_children[i].sku, res.product_children[i].id)
                        $(`.items[data-id="${id}"] select[name="serial_no[]"]`).append(opt)
                    }
                }
            });
        })

        $('form').one('submit', function(e) {
            e.preventDefault()

            $('#item-template').remove();

            $(this).submit()
        })
    </script>
@endpush
