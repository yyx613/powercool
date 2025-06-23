@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title
            url="{{ route('material_use.index') }}">{{ isset($material) && !isset($for_pid) ? __('Edit B.O.M Material Use') : __('Create B.O.M Material Use') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="w-full md:w-1/3">
                <!-- Product -->
                <div class="flex flex-col mb-4">
                    <x-app.input.label id="product" class="mb-1">{{ __('Product Name') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="product" id="product" :hasError="$errors->has('product')"
                        placeholder="{{ __('Select a product') }}">
                        <option value="">{{ __('Select a product') }}</option>
                        @foreach ($products as $pro)
                            <option value="{{ $pro->id }}">{{ $pro->model_name }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-app.message.error id="product_err" />
                </div>
            </div>
            <div class="w-full md:w-1/2">
                <!-- Material -->
                <div class="flex flex-col">
                    <x-app.input.label id="material" class="mb-1">{{ __('Raw Material Use') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <div id="material-container"></div>
                    <!-- Template -->
                    <div class="mb-4 hidden" id="material-template">
                        <div class="flex items-center gap-4 w-full rounded-md">
                            <div class="flex flex-col flex-1">
                                <x-app.input.select name="material[]" placeholder="{{ __('Select a material') }}">
                                    <option value="">{{ __('Select a material') }}</option>
                                    @foreach ($materials as $m)
                                        <option value="{{ $m->id }}">({{ $m->sku }}) {{ $m->model_name }}</option>
                                    @endforeach
                                </x-app.input.select>
                            </div>
                            <div class="flex flex-col flex-1">
                                <x-app.input.input name="qty[]" id="qty" :hasError="$errors->has('qty')" class="int-input"
                                    placeholder="{{ __('Enter quantity') }}" />
                            </div>
                            <button type="button"
                                class="data-[active=false]:bg-slate-100 data-[active=true]:bg-emerald-300 p-2 rounded-full h-8 w-8 flex items-center justify-center toggle-status-btns"
                                title="{{ __('Toggle Status') }}">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                                    viewBox="0 0 24 24" width="512" height="512">
                                    <path
                                        d="m16,4h-8C3.589,4,0,7.589,0,12s3.589,8,8,8h8c4.411,0,8-3.589,8-8s-3.589-8-8-8Zm0,14h-8c-3.309,0-6-2.691-6-6s2.691-6,6-6h8c3.309,0,6,2.691,6,6s-2.691,6-6,6Zm-8-10c-2.206,0-4,1.794-4,4s1.794,4,4,4,4-1.794,4-4-1.794-4-4-4Zm0,6c-1.103,0-2-.897-2-2s.897-2,2-2,2,.897,2,2-.897,2-2,2Z" />
                                </svg>
                            </button>
                            <button type="button"
                                class="bg-rose-400 p-2 rounded-full h-8 w-8 flex items-center justify-center delete-item-btns"
                                title="{{ __('Delete Product') }}">
                                <svg class="h-3 w-3 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1"
                                    data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512">
                                    <path
                                        d="M13.93,12L21.666,2.443c.521-.644,.422-1.588-.223-2.109-.645-.522-1.588-.421-2.109,.223l-7.334,9.06L4.666,.557c-1.241-1.519-3.56,.357-2.332,1.887l7.736,9.557L2.334,21.557c-.521,.644-.422,1.588,.223,2.109,.64,.519,1.586,.424,2.109-.223l7.334-9.06,7.334,9.06c.524,.647,1.47,.742,2.109,.223,.645-.521,.744-1.466,.223-2.109l-7.736-9.557Z" />
                                </svg>
                            </button>
                        </div>
                        <x-app.message.error id="material_err" />
                        <x-app.message.error id="qty_err" />
                    </div>
                    <!-- Add Items -->
                    <div class="flex justify-end mt-8">
                        <button type="button"
                            class="bg-yellow-400 rounded-md py-1.5 px-3 flex items-center gap-x-2 transition duration-300 hover:bg-yellow-300 hover:shadow"
                            id="add-material-btn">
                            <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px"
                                viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"
                                width="512" height="512">
                                <path
                                    d="M480,224H288V32c0-17.673-14.327-32-32-32s-32,14.327-32,32v192H32c-17.673,0-32,14.327-32,32s14.327,32,32,32h192v192   c0,17.673,14.327,32,32,32s32-14.327,32-32V288h192c17.673,0,32-14.327,32-32S497.673,224,480,224z" />
                            </svg>
                            <span class="text-sm">{{ __('Add Material') }}</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="mt-8 flex justify-end">
                <x-app.button.submit id="submit-btn">{{ __('Save and Update') }}</x-app.button.submit>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        SPR_ID = @json($spr_id ?? null); // Sale Production Request Id
        FOR_PID = @json($for_pid ?? null);
        MATERIAL = @json($material ?? null);
        FORM_CAN_SUBMIT = true
        ITEMS_COUNT = 0

        $(document).ready(function() {
            if (MATERIAL != null) {
                $('select[name="product"]').val(FOR_PID ?? MATERIAL.product_id).trigger('change')

                for (let i = 0; i < MATERIAL.materials.length; i++) {
                    const m = MATERIAL.materials[i];

                    $('#add-material-btn').click()

                    $(`.items[data-id="${i+1}"]`).attr('data-order-idx', m.id)
                    $(`.items[data-id="${i+1}"] select[name="material[]"]`).val(m.product_id).trigger('change')
                    $(`.items[data-id="${i+1}"] input[name="qty[]"]`).val(m.qty)
                    $(`.items[data-id="${i+1}"] .toggle-status-btns`).attr('data-active', m.status == 1 ? false :
                        true)
                }
                if (MATERIAL.materials.length <= 0) $('#add-item-btn').click()
            } else {
                $('#add-material-btn').click()
            }
        })
        $('#add-material-btn').on('click', function() {
            let clone = $('#material-template')[0].cloneNode(true);

            ITEMS_COUNT++
            $(clone).attr('data-id', ITEMS_COUNT)
            $(clone).find('.delete-item-btns').attr('data-id', ITEMS_COUNT)
            $(clone).find('.toggle-status-btns').attr('data-id', ITEMS_COUNT)
            $(clone).find('.toggle-status-btns').attr('data-active', true)
            $(clone).addClass('items')
            $(clone).removeClass('hidden')
            $(clone).removeAttr('id')

            $('#material-container').append(clone)

            // Build material select2
            buildMaterialSelect2(ITEMS_COUNT)
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
        $('body').on('click', '.toggle-status-btns', function() {
            let id = $(this).data('id')
            let active = $(this).attr('data-active')

            if (active == 'true') {
                $(this).attr('data-active', false)
            } else {
                $(this).attr('data-active', true)
            }
        })
        $('form').on('submit', function(e) {
            e.preventDefault()

            if (!FORM_CAN_SUBMIT) return

            FORM_CAN_SUBMIT = false

            $('form #submit-btn').text('Updating')
            $('form #submit-btn').removeClass('bg-yellow-400 shadow')
            $('.err_msg').addClass('hidden') // Remove error messages
            // Submit
            let url = '{{ route('material_use.upsert') }}'
            url = `${url}`

            let orderIdx = []
            let material = []
            let qty = []
            let active = []
            $('form .items').each(function(i, obj) {
                orderIdx.push($(this).data('order-idx') ?? null)
                material.push($(this).find('select[name="material[]"]').val())
                qty.push($(this).find('input[name="qty[]"]').val())
                active.push($(this).find('.toggle-status-btns').data('active'))
            })

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'POST',
                data: {
                    'spr_id': SPR_ID,
                    'material_use_id': FOR_PID != null || MATERIAL == null ? null : MATERIAL.id,
                    'order_idx': FOR_PID != null ? [] : orderIdx,
                    'product': $('form select[name="product"]').val(),
                    'material': material,
                    'qty': qty,
                    'active': active,
                },
                success: function(res) {
                    MATERIAL = res.material

                    let material_use_ids = res.material_use_ids
                    $('form .items').each(function(i, obj) {
                        $(this).attr('data-order-idx', material_use_ids[i])
                    })

                    setTimeout(() => {
                        $('form #submit-btn').text('Updated')
                        $('form #submit-btn').addClass('bg-green-400 shadow')

                        window.location.href = '{{ route('material_use.index') }}'

                        // setTimeout(() => {
                        //     $('form #submit-btn').text('Save and Update')
                        //     $('form #submit-btn').removeClass('bg-green-400')
                        //     $('form #submit-btn').addClass('bg-yellow-400 shadow')

                        //     FORM_CAN_SUBMIT = true
                        // }, 2000);
                    }, 300);
                },
                error: function(err) {
                    setTimeout(() => {
                        if (err.status == StatusCodes.UNPROCESSABLE_ENTITY) {
                            let errors = err.responseJSON.errors

                            for (const key in errors) {
                                if (key == 'product') {
                                    $(`form #${key}_err`).find('p').text(errors[key])
                                    $(`form #${key}_err`).removeClass('hidden')
                                } else {
                                    let field = key.split('.')[0]
                                    let idx = key.split('.')[1]
                                    idx++

                                    $(`form .items[data-id="${idx}"] #${field}_err`).find('p')
                                        .text(errors[key])
                                    $(`form .items[data-id="${idx}"] #${field}_err`)
                                        .removeClass('hidden')
                                }
                            }
                        } else if (err.status == StatusCodes.BAD_REQUEST) {
                            if (err.responseJSON.product !== undefined) {
                                $(`form #product_err`).find('p').text(err.responseJSON.product)
                                $(`form #product_err`).removeClass('hidden')
                            } else if (err.responseJSON.material !== undefined) {
                                $(`form #material_err`).find('p').text(err.responseJSON
                                    .material)
                                $(`form #material_err`).removeClass('hidden')
                            }
                        }
                        $('form #submit-btn').text('Save and Update')
                        $('form #submit-btn').addClass('bg-yellow-400 shadow')

                        FORM_CAN_SUBMIT = true
                    }, 300);
                },
            });
        })

        function buildMaterialSelect2(item_id) {
            $(`.items[data-id="${item_id}"] select[name="material[]"]`).select2({
                placeholder: "{!! __('Select a material') !!}"
            })
            $(`.items[data-id="${ITEMS_COUNT}"] .select2`).addClass('border border-gray-300 rounded-md overflow-hidden')
        }
    </script>
@endpush
