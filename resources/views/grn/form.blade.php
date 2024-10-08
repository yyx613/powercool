@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('grn.index') }}">{{ isset($sku) ? 'Edit GRN' : 'Create GRN' }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ route('grn.upsert') }}" method="POST" enctype="multipart/form-data" id="form">
        @csrf
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            @if (isset($sku))
                <x-app.input.input name="sku" id="sku" value="{{ $sku }}" class="hidden"/>
            @endif
            <div class="flex flex-col p-4">
                <x-app.input.label id="supplier" class="mb-1">Supplier <span class="text-sm text-red-500">*</span></x-app.input.label>
                <x-app.input.select2 name="supplier" id="supplier" :hasError="$errors->has('supplier')" placeholder="Select a supplier">
                    <option value="">Select a supplier</option>
                    @foreach ($suppliers as $supp)
                        <option value="{{ $supp->id }}">{{ $supp->name }}</option>
                    @endforeach
                </x-app.input.select2>
                <x-app.message.error id="supplier_err"/>
            </div>
            <!-- Template -->
            <div class="grid grid-cols-5 gap-6 w-full mb-8 p-4 rounded-md relative group hidden transition durtion-300 hover:bg-slate-50" id="item-template">
                <button type="button" class="bg-rose-400 p-2 rounded-full absolute top-[-5px] right-[-5px] hidden group-hover:block delete-item-btns" title="Delete Product">
                    <svg class="h-3 w-3 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M13.93,12L21.666,2.443c.521-.644,.422-1.588-.223-2.109-.645-.522-1.588-.421-2.109,.223l-7.334,9.06L4.666,.557c-1.241-1.519-3.56,.357-2.332,1.887l7.736,9.557L2.334,21.557c-.521,.644-.422,1.588,.223,2.109,.64,.519,1.586,.424,2.109-.223l7.334-9.06,7.334,9.06c.524,.647,1.47,.742,2.109,.223,.645-.521,.744-1.466,.223-2.109l-7.736-9.557Z"/></svg>
                </button>
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">Product <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="product_id[]">
                        <option value=""></option>
                    </x-app.input.select>
                    <x-app.message.error id="product_id_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="qty" class="mb-1">Quantity <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="qty" id="qty" :hasError="$errors->has('qty')" class="int-input" />
                    <x-app.message.error id="qty_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="uom" class="mb-1">UOM <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="uom" id="uom" :hasError="$errors->has('uom')" />
                    <x-app.message.error id="uom_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="unit_price" class="mb-1">Unit Price <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="unit_price" id="unit_price" :hasError="$errors->has('unit_price')" class="decimal-input"/>
                    <x-app.message.error id="unit_price_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="total_price" class="mb-1">Amount <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="total_price" id="total_price" :hasError="$errors->has('total_price')" class="decimal-input"/>
                    <x-app.message.error id="total_price_err"/>
                </div>
            </div>
            <div id="items-container"></div>
            <!-- Add Items -->
            <div class="flex justify-end px-4 gap-x-4">
                <button type="button" class="bg-yellow-400 rounded-md py-1.5 px-3 flex items-center gap-x-2 transition duration-300 hover:bg-yellow-300 hover:shadow" id="add-item-btn">
                    <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve" width="512" height="512">
                        <path d="M480,224H288V32c0-17.673-14.327-32-32-32s-32,14.327-32,32v192H32c-17.673,0-32,14.327-32,32s14.327,32,32,32h192v192   c0,17.673,14.327,32,32,32s32-14.327,32-32V288h192c17.673,0,32-14.327,32-32S497.673,224,480,224z"/>
                    </svg>
                    <span class="text-sm">Add Item</span>
                </button>
                @if (isset($sku))
                    <button type="button" class="bg-yellow-400 rounded-md py-1.5 px-3 flex items-center gap-x-2 transition duration-300 hover:bg-yellow-300 hover:shadow" id="stock-in-btn">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M21,12h-3c-1.103,0-2,.897-2,2s-.897,2-2,2h-4c-1.103,0-2-.897-2-2s-.897-2-2-2H3c-1.654,0-3,1.346-3,3v4c0,2.757,2.243,5,5,5h14c2.757,0,5-2.243,5-5v-4c0-1.654-1.346-3-3-3Zm1,7c0,1.654-1.346,3-3,3H5c-1.654,0-3-1.346-3-3v-4c0-.552,.448-1,1-1l3-.002v.002c0,2.206,1.794,4,4,4h4c2.206,0,4-1.794,4-4h3c.552,0,1,.448,1,1v4ZM7.293,7.121c-.391-.391-.391-1.023,0-1.414s1.023-.391,1.414,0l2.293,2.293V1c0-.553,.447-1,1-1s1,.447,1,1v7l2.293-2.293c.391-.391,1.023-.391,1.414,0s.391,1.023,0,1.414l-3.293,3.293c-.387,.387-.896,.582-1.405,.584l-.009,.002-.009-.002c-.509-.002-1.018-.197-1.405-.584l-3.293-3.293Z"/></svg>
                        <span class="text-sm">Stock In</span>
                    </button>
                @endif
                <x-app.button.submit id="submit-btn">Save and Update</x-app.button.submit>
            </div>
        </div>
    </form>
    
    @if (isset($sku))
        @include('components.app.modal.grn-stock-in-modal', [
            'sku' => $sku,
            'grns' => $grns
        ])
    @endif
@endsection

@push('scripts')
    <script>
        GRNS = @json($grns ?? []);
        PRODUCTS = @json($products ?? []);
        ITEMS_COUNT = 0
        FORM_CAN_SUBMIT = true

        $(document).ready(function(){
            if (GRNS.length > 0) {
                for (let i = 0; i < GRNS.length; i++) {
                    const grn = GRNS[i];

                    $('#add-item-btn').click()

                    $(`select[name="supplier"]`).val(grn.supplier_id).trigger('change')
                    $(`.items[data-id="${i+1}"] select[name="product_id[]"]`).val(grn.product_id).trigger('change')
                    $(`.items[data-id="${i+1}"] input[name="qty"]`).val(grn.qty)
                    $(`.items[data-id="${i+1}"] input[name="uom"]`).val(grn.uom)
                    $(`.items[data-id="${i+1}"] input[name="unit_price"]`).val(grn.unit_price)
                    $(`.items[data-id="${i+1}"] input[name="total_price"]`).val(grn.total_price)
                }
            } else {
                $('#add-item-btn').click()
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
            // Build product select2
            $(`.items[data-id="${ITEMS_COUNT}"] select[name="product_id[]"]`).select2({
                placeholder: 'Select a product'
            })
            for (let i = 0; i < PRODUCTS.length; i++) {
                const element = PRODUCTS[i];
                
                let opt = new Option(element.model_name, element.id)
                $(`.items[data-id="${ITEMS_COUNT}"] select[name="product_id[]"]`).append(opt)
            }
            $(`.items[data-id="${ITEMS_COUNT}"] .select2`).addClass('border border-gray-300 rounded-md overflow-hidden')
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

        $('#form').on('submit', function(e) {
            e.preventDefault()

            if (!FORM_CAN_SUBMIT) return

            FORM_CAN_SUBMIT = false

            $('#form #submit-btn').text('Updating')
            $('#form #submit-btn').removeClass('bg-yellow-400 shadow')
            $('.err_msg').addClass('hidden') // Remove error messages
            // Submit
            let url = '{{ route("grn.upsert") }}'
            url = `${url}`

            let prodId = []
            let qty = []
            let uom = []
            let unitPrice = []
            let totalPrice = []
            $('#form .items').each(function(i, obj) {
                prodId.push($(this).find('select[name="product_id[]"]').val())
                qty.push($(this).find('input[name="qty"]').val())
                uom.push($(this).find('input[name="uom"]').val())
                unitPrice.push($(this).find('input[name="unit_price"]').val())
                totalPrice.push($(this).find('input[name="total_price"]').val())
            })

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'POST',
                data: {
                    'sku': $('input[name="sku"]').val(),
                    'supplier': $('select[name="supplier"]').val(),
                    'product_id': prodId,
                    'qty': qty,
                    'uom': uom,
                    'unit_price': unitPrice,
                    'total_price': totalPrice,
                },
                success: function(res) {
                    setTimeout(() => {
                        $('#form #submit-btn').text('Updated')
                        $('#form #submit-btn').addClass('bg-green-400 shadow')

                        window.location.href = "{{ route('grn.index') }}"

                        // setTimeout(() => {
                        //     $('#form #submit-btn').text('Save and Update')
                        //     $('#form #submit-btn').removeClass('bg-green-400')
                        //     $('#form #submit-btn').addClass('bg-yellow-400 shadow')
                            
                        //     FORM_CAN_SUBMIT = true
                        // }, 2000);
                    }, 300);
                },
                error: function(err) {
                    setTimeout(() => {
                        if (err.status == StatusCodes.UNPROCESSABLE_ENTITY) {
                            let errors = err.responseJSON.errors

                            for (const key in errors) {
                                let field = key.split('.')[0]
                                let idx = key.split('.')[1]
                                idx++
                                $(`#form .items[data-id="${idx}"] #${field}_err`).find('p').text(errors[key])
                                $(`#form .items[data-id="${idx}"] #${field}_err`).removeClass('hidden')
                            }
                        }
                        $('#form #submit-btn').text('Save and Update')
                        $('#form #submit-btn').addClass('bg-yellow-400 shadow')

                        FORM_CAN_SUBMIT = true
                    }, 300);
                },
            });
        })

        $('#stock-in-btn').on('click', function() {
            $('#stock-in-modal').addClass('show-modal')
        })
    </script>
@endpush
