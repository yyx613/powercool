@inject('prodMs', 'App\Models\ProductionMilestone')
@inject('prodMsMaterialPreview', 'App\Models\ProductionMilestoneMaterialPreview')

@extends('layouts.app')
@section('title', 'Production')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title
            url="{{ route('production.index') }}">{{ !isset($is_duplicate) && isset($production) ? __('Edit Production - ') . $production->sku : __('Create Production') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form
        action="{{ isset($production) && !isset($is_duplicate) ? route('production.upsert', ['production' => $production->id]) : route('production.upsert') }}"
        method="POST" enctype="multipart/form-data" id="info-form">
        @csrf
        @if (isset($modify_from))
            <input type="hidden" name="modify_from" value="{{ $production->id }}">
        @endif
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label id="name" class="mb-1">{{ __('Name') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="name" id="name" class="uppercase-input" :hasError="$errors->has('name')"
                        value="{{ old('name', isset($from_ticket) ? $from_ticket->subject : (isset($production) ? $production->name : (isset($customer_name) ? $customer_name : null))) }}" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
                <div class="flex flex-col col-span-1 md:col-span-2">
                    <x-app.input.label id="desc" class="mb-1">{{ __('Description') }} </x-app.input.label>
                    <x-app.input.input name="desc" id="desc" :hasError="$errors->has('desc')"
                        value="{{ old('desc', isset($from_ticket) ? $from_ticket->body : (isset($production) ? $production->desc : null)) }}" />
                    <x-input-error :messages="$errors->get('desc')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="start_date" class="mb-1">{{ __('Start Date') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="start_date" id="start_date" :hasError="$errors->has('start_date')"
                        value="{{ old('start_date', isset($production) ? $production->start_date : (isset($default_start_date) ? $default_start_date : null)) }}" />
                    <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="due_date" class="mb-1">{{ __('Due Date') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="due_date" id="due_date" :hasError="$errors->has('due_date')"
                        value="{{ old('due_date', isset($production) ? $production->due_date : (isset($default_due_date) ? $default_due_date : null)) }}" />
                    <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="status" class="mb-1">{{ __('Status') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                        <option value="">{{ __('Select a status') }}</option>
                        <option value="1" @selected(old('status', isset($production) ? $production->status : (isset($customer_name) ? 1 : null)) == 1)>{{ __('New') }}</option>
                        <option value="2" @selected(old('status', isset($production) ? $production->status : null) == 2)>{{ __('Doing') }}</option>
                        <option value="3" @selected(old('status', isset($production) ? $production->status : null) == 3)>{{ __('Completed') }}</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                </div>
                <div class="flex flex-col" id="product-select-container">
                    <x-app.input.label class="mb-1">{{ __('Product') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="product" id="product" :hasError="$errors->has('product')">
                        <option value="">{{ __('Select a product') }}</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('product')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">{{ __('Assigned Order ID') }}</x-app.input.label>
                    <x-app.input.select2 name="order" id="order" placeholder="{{ __('Select a order') }}"
                        :hasError="$errors->has('order')">
                        <option value="">{{ __('Select a order') }}</option>
                        @foreach ($sales as $sale)
                            <option value="{{ $sale->id }}" @selected(old('order', isset($production) ? $production->sale_id : (isset($default_sale) ? $default_sale->id : null)) == $sale->id)>{{ $sale->sku }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('order')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">{{ __('Priority') }}</x-app.input.label>
                    <x-app.input.select2 name="priority" id="priority" placeholder="{{ __('Select a priority') }}"
                        :hasError="$errors->has('priority')">
                        <option value="">{{ __('Select a priority') }}</option>
                        @foreach ($priorities as $priority)
                            <option value="{{ $priority->id }}" @selected(old('priority', isset($production) ? $production->priority_id : null) == $priority->id)>{{ $priority->name }}
                            </option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('priority')" class="mt-2" />
                </div>
                <div class="flex flex-col col-span-2 md:col-span-3">
                    <x-app.input.label id="remark" class="mb-1">{{ __('Remark') }}</x-app.input.label>
                    <textarea name="remark" id="remark" class="hidden">{!! old('remark', isset($production) ? $production->remark : null) !!}</textarea>
                    <x-input-error :messages="$errors->get('remark')" class="mt-2" />
                </div>
                <div class="flex flex-col col-span-2 md:col-span-3">
                    <x-app.input.label id="assign" class="mb-1">{{ __('Assigned Staff') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="assign[]" id="assign" :hasError="$errors->has('assign')" multiple>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(in_array($user->id, old('assign', isset($production) ? $production->users()->pluck('user_id')->toArray() : [])))>{{ $user->name }}</option>
                        @endforeach
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('assign')" class="mt-2" />
                </div>
                {{-- Milestones --}}
                <div class="flex flex-col col-span-2 md:col-span-3">
                    <x-app.input.label class="mb-2">{{ __('Milestones') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="custom_milestone" class="mb-2"
                        placeholder="{{ __('Enter custom milestone here') }}" />
                    <p class="text-xs text-end mb-2 text-slate-500">
                        {{ __("'Yes' represent the milestone is required to fill up material use.") }}</p>
                    <div id="milestone-list-container">
                        {{-- Template --}}
                        <div class="flex justify-between mb-2 hidden cursor-grab hover:bg-slate-50"
                            id="milestone-template">
                            <div class="flex items-center gap-2 first-half">
                                <input type="checkbox" class="rounded-sm">
                                <label class="text-sm ms-name"></label>
                            </div>
                            <div class="flex items-center second-half">
                                <button type="button" class="mr-3 view-material-use-selection-btns hidden"
                                    title="View Material Use Selection">
                                    <svg class="h-4 w-4 fill-slate-400 hover:fill-black"
                                        xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                                        viewBox="0 0 24 24">
                                        <path
                                            d="M23.707,22.293l-5.969-5.969c1.412-1.725,2.262-3.927,2.262-6.324C20,4.486,15.514,0,10,0S0,4.486,0,10s4.486,10,10,10c2.397,0,4.599-.85,6.324-2.262l5.969,5.969c.195,.195,.451,.293,.707,.293s.512-.098,.707-.293c.391-.391,.391-1.023,0-1.414ZM2,10C2,5.589,5.589,2,10,2s8,3.589,8,8-3.589,8-8,8S2,14.411,2,10Zm13.933-1.261c-.825-1.21-2.691-3.239-5.933-3.239s-5.108,2.03-5.933,3.239c-.522,.766-.522,1.755,0,2.521,.825,1.21,2.692,3.24,5.933,3.24s5.108-2.03,5.933-3.239c.522-.766,.522-1.755,0-2.521Zm-1.652,1.395c-.735,1.08-2.075,2.366-4.28,2.366s-3.544-1.287-4.28-2.367c-.056-.081-.056-.185,0-.267,.735-1.08,2.075-2.366,4.28-2.366s3.545,1.287,4.28,2.366h0c.056,.082,.056,.186,0,.268Zm-2.78-.134c0,.829-.671,1.5-1.5,1.5s-1.5-.671-1.5-1.5,.671-1.5,1.5-1.5,1.5,.671,1.5,1.5Z" />
                                    </svg>
                                </button>
                                <label
                                    class="flex items-center rounded-full overflow-hidden relative cursor-pointer select-none border border-grey-200 w-24 h-7">
                                    <input type="checkbox" class="hidden peer" name="required_serial_no[]" />
                                    <div class="flex items-center w-full">
                                        <span
                                            class="flex-1 font-medium uppercase z-20 text-center text-xs">{{ __('No') }}</span>
                                        <span
                                            class="flex-1 font-medium uppercase z-20 text-center text-xs">{{ __('Yes') }}</span>
                                    </div>
                                    <span
                                        class="w-1/2 h-6 peer-checked:translate-x-full absolute rounded-full transition-all bg-blue-200 border border-black" />
                                </label>
                            </div>
                        </div>
                    </div>
                    @if ($errors->has('material_use_product'))
                        <x-input-error :messages="$errors->get('material_use_product')" class="mt-2" />
                    @endif
                    <input type="hidden" name="material_use_product">
                </div>
            </div>
            @if (!isset($production) || (isset($is_duplicate) && $is_duplicate == true))
                <div class="mt-8 flex justify-end">
                    <x-app.button.submit type="button" id="submit-btn">{{ __('Save and Update') }}</x-app.button.submit>
                </div>
            @endif
        </div>
    </form>

    <x-app.modal.production-material-use-modal />
@endsection

@push('scripts')
    <script>
        CAN_SUBMIT = false
        INIT_EDIT = false
        PRODUCTS = @json($products ?? []);
        DEFAULT_PRODUCT = @json($default_product ?? null);
        SALES = @json($sales ?? null);
        PRODUCTION = @json($production ?? null);
        PRODUCTION_MILESTONE_MATERIAL_PREVIEW = @json($production_milestone_material_previews ?? null);
        MILESTONES = {} // milestone id: material use id
        SELECTED_MILESTONE_ID = null
        MATERIAL_USE = []
        CUSTOM_MILESTONE_IDX = 0
        IS_DUPLICATE = @json($is_duplicate ?? null);
        SELECTED_PRODUCT = @json($selected_product ?? null);

        $(document).ready(function() {
            // Build product select2 ajax
            bulidSelect2Ajax({
                selector: '#product',
                placeholder: '{{ __('Search a product') }}',
                url: '{{ route('production.search_product') }}',
                processResults: function(data) {
                    PRODUCTS = data.products
                    return {
                        results: $.map(data.products, function(item) {
                            return {
                                id: item.id,
                                text: `${item.sku} - ${item.model_name}`
                            };
                        })
                    }
                }
            })
            $(`#product-select-container .select2`).addClass('border border-gray-300 rounded-md overflow-hidden')

            buildRemarkQuillEditor()

            // Start Init
            if (PRODUCTION == null) {
                var elem = document.getElementById('milestone-list-container')
                var sortable = Sortable.create(elem, {
                    onEnd: function(evt) {
                        sortMilestone()
                    },
                })
            } else if (PRODUCTION != null) {
                INIT_EDIT = true

                if (SELECTED_PRODUCT != null) {
                    let opt = new Option(SELECTED_PRODUCT.model_name, SELECTED_PRODUCT.id, true, true)
                    $('select[name="product"]').append(opt)
                }

                for (let i = 0; i < PRODUCTION.milestones.length; i++) {
                    const element = PRODUCTION.milestones[i];

                    let clone = $('#milestone-template')[0].cloneNode(true);
                    $(clone).find('.ms-name').text(element.name)
                    $(clone).find('.ms-name').attr('for', `ms-${element.id}`)
                    $(clone).find('.first-half input').attr('id', `ms-${element.id}`)
                    $(clone).find('.first-half input').attr('checked', true)
                    $(clone).find('.second-half input').attr('data-milestone-id',
                        `ms-${element.id}`)
                    $(clone).attr('data-milestone-id', `ms-${element.id}`)
                    $(clone).removeClass('hidden')
                    $(clone).addClass('milestones')
                    $(clone).removeAttr('id')

                    $('#milestone-list-container').append(clone)

                    material_use_product_ids = []
                    if (PRODUCTION_MILESTONE_MATERIAL_PREVIEW != null) {
                        for (let j = 0; j < PRODUCTION_MILESTONE_MATERIAL_PREVIEW.length; j++) {
                            if (element.pivot.id == PRODUCTION_MILESTONE_MATERIAL_PREVIEW[j]
                                .production_milestone_id) {
                                material_use_product_ids.push(PRODUCTION_MILESTONE_MATERIAL_PREVIEW[j].product_id)
                            }
                        }
                        if (material_use_product_ids.length > 0) {
                            $(`.milestones[data-milestone-id="ms-${element.id}"] .first-half input`)
                                .attr('checked', true)
                            $(`input[name="required_serial_no[]"][data-milestone-id="ms-${element.id}"`)
                                .attr('checked', true)
                            $(`.milestones[data-milestone-id="ms-${element.id}"] .view-material-use-selection-btns`)
                                .removeClass('hidden')
                        }

                        MILESTONES[`ms-${element.id}`] = {
                            material_use_product_ids: material_use_product_ids,
                            sequence: i + 1,
                            is_checked: true,
                            title: element.name,
                        }
                    }
                }
                INIT_EDIT = false
            }
            if (DEFAULT_PRODUCT != null) {
                let opt = new Option(DEFAULT_PRODUCT.model_name, DEFAULT_PRODUCT.id, true, true)
                $('select[name="product"]').append(opt)
            }
            if (PRODUCTION != null || DEFAULT_PRODUCT != true) {
                $('select[name="product"]').trigger('change')
            }
        })

        $('input[name="start_date"]').daterangepicker(datepickerParam)
        $('input[name="start_date"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });
        $('input[name="due_date"]').daterangepicker(datepickerParam)
        $('input[name="due_date"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });

        // Prevent user to enter ',' since every desc is joined with ','
        $('input[name="custom_milestone"]').on('keydown', function(e) {
            if (e.key == ',') {
                e.preventDefault()
                return false
            }
        })
        $('input[name="custom_milestone"]').on('keyup', function(e) {
            let val = $(this).val();
            if (val == '') return

            if (e.key.toLowerCase() == 'enter') {
                CUSTOM_MILESTONE_IDX++

                let clone = $('#milestone-template')[0].cloneNode(true);

                $(clone).find('.ms-name').text(val)
                $(clone).find('.ms-name').attr('for', `cms-${CUSTOM_MILESTONE_IDX}`)
                $(clone).find('.first-half input').attr('id', `cms-${CUSTOM_MILESTONE_IDX}`)
                $(clone).find('.second-half input').attr('data-milestone-id', `cms-${CUSTOM_MILESTONE_IDX}`)
                $(clone).attr('data-milestone-id', `cms-${CUSTOM_MILESTONE_IDX}`)
                $(clone).removeClass('hidden')
                $(clone).addClass('milestones')
                $(clone).removeAttr('id')

                $('#milestone-list-container').append(clone)

                $(this).val(null)

                MILESTONES[`cms-${CUSTOM_MILESTONE_IDX}`] = {
                    material_use_product_ids: [],
                    sequence: $('.milestones').length + 1,
                    is_checked: false,
                    title: val,
                }
            }
        })
        $('body').on('change', '.first-half input', function() {
            let isChecked = $(this).is(':checked')
            let milestoneId = $(this).parent().parent().data('milestone-id')

            if (isChecked) {
                MILESTONES[milestoneId].is_checked = true
            } else {
                MILESTONES[milestoneId].is_checked = false
            }
        })
        // $('select[name="order"]').on('change', function() {
        //     let val = $(this).val()

        //     if (SALES != null) {
        //         for (let i = 0; i < SALES.length; i++) {
        //             if (SALES[i].id == val) {
        //                 // $('select[name="product"] option').not(':first').remove()

        //                 for (let j = 0; j < SALES[i].products.length; j++) {
        //                     let opt = new Option(SALES[i].products[j].product.model_name, SALES[i].products[j].product.id)
        //                     $('select[name="product"]').append(opt)
        //                 }
        //                 $('select[name="product"]').val(null).trigger('change')
        //                 break
        //             }
        //         }
        //     }
        // })
        $('#submit-btn').on('click', function() {
            CAN_SUBMIT = true

            let materialUseProduct = []
            for (const key in MILESTONES) {
                if (!MILESTONES[key].is_checked) continue

                if (key.includes('cms-')) {
                    materialUseProduct.push({
                        is_custom: true,
                        id: key.replace('cms-', ''),
                        value: MILESTONES[key]['material_use_product_ids'],
                        title: MILESTONES[key]['title'],
                        sequence: MILESTONES[key]['sequence']
                    })
                } else {
                    materialUseProduct.push({
                        is_custom: false,
                        id: key.replace('ms-', ''),
                        value: MILESTONES[key]['material_use_product_ids'],
                        title: MILESTONES[key]['title'],
                        sequence: MILESTONES[key]['sequence']
                    })
                }
            }
            if (materialUseProduct.length == 0) {
                $('input[name="material_use_product"]').val(null)
            } else {
                $('input[name="material_use_product"]').val(JSON.stringify(materialUseProduct))
            }
            $('textarea[name="remark"]').val($(`#remark-quill .ql-editor`).html())
            $('form').submit()
        })
        // Filter milestones based on selected product
        $('select[name="product"]').on('change', function() {
            let productId = $(this).val()

            if (productId == null || productId == '') return

            getProductMilestones(productId, IS_DUPLICATE == true ? false : (INIT_EDIT ? true : false))
        })
        // Toggle view material use selection
        $('body').on('change', 'input[name="required_serial_no[]"]', function() {
            let milestoneId = $(this).data('milestone-id')
            SELECTED_MILESTONE_ID = milestoneId

            if ($(this).is(':checked')) {
                $('#material-use-selection-container .material-use-selections').remove()

                for (let i = 0; i < MATERIAL_USE.length; i++) {
                    for (let j = 0; j < MATERIAL_USE[i].materials.length; j++) {
                        let clone = $('#material-use-selection-template')[0].cloneNode(true);

                        $(clone).find('input').attr('id', `material-use-${MATERIAL_USE[i].materials[j].id}`)
                        $(clone).find('label').attr('for', `material-use-${MATERIAL_USE[i].materials[j].id}`)
                        $(clone).find('#name').text(MATERIAL_USE[i].materials[j].material.model_name)
                        $(clone).find('label #qty').text(`Quantity needed: x${MATERIAL_USE[i].materials[j].qty}`)
                        $(clone).removeAttr('id')
                        $(clone).removeClass('hidden')
                        $(clone).addClass('flex material-use-selections')
                        $(clone).attr('data-material-use-product-id', MATERIAL_USE[i].materials[j].product_id)

                        if (j == MATERIAL_USE[i].materials.length - 1) {
                            $(clone).removeClass('border-b')
                        }

                        $('#material-use-selection-container').append(clone)
                    }
                }
                $('#production-material-use-modal #action-container').removeClass('hidden')
                $('#production-material-use-modal #action2-container').addClass('hidden')
                $('#production-material-use-modal').addClass('show-modal')
            } else {
                MILESTONES[milestoneId].material_use_product_ids = []
                $(`.milestones[data-milestone-id=${SELECTED_MILESTONE_ID}] .view-material-use-selection-btns`)
                    .addClass('hidden')
            }
        })
        $('body').on('click', '.view-material-use-selection-btns', function() {
            let milestoneId = $(this).parent().parent().data('milestone-id')

            $('#material-use-selection-container .material-use-selections').remove()

            for (let i = 0; i < MATERIAL_USE.length; i++) {
                for (let j = 0; j < MATERIAL_USE[i].materials.length; j++) {
                    if (!MILESTONES[milestoneId]['material_use_product_ids'].includes(MATERIAL_USE[i].materials[j]
                            .product_id)) {
                        continue
                    }

                    let clone = $('#material-use-selection-template')[0].cloneNode(true);

                    $(clone).find('input').attr('id', `material-use-${MATERIAL_USE[i].materials[j].id}`)
                    $(clone).find('input').attr('checked', true)
                    $(clone).find('label').attr('for', `material-use-${MATERIAL_USE[i].materials[j].id}`)
                    $(clone).find('#name').text(MATERIAL_USE[i].materials[j].material.model_name)
                    $(clone).find('label #qty').text(`Quantity needed: x${MATERIAL_USE[i].materials[j].qty}`)
                    $(clone).removeAttr('id')
                    $(clone).removeClass('hidden')
                    $(clone).addClass('flex material-use-selections')
                    $(clone).attr('data-material-use-product-id', MATERIAL_USE[i].materials[j].id)

                    $('#material-use-selection-container').append(clone)
                }
            }
            // Border bottom
            if ($('.material-use-selections').length > 1) {
                $('.material-use-selections').each(function(i, obj) {
                    if (i + 1 == $('.material-use-selections').length) {
                        $(this).removeClass('border-b')
                    }
                })
            } else {
                $('.material-use-selections').each(function(i, obj) {
                    $(this).removeClass('border-b')
                })
            }

            $('#production-material-use-modal #action-container').addClass('hidden')
            $('#production-material-use-modal #action2-container').removeClass('hidden')
            $('#production-material-use-modal').addClass('show-modal')
        })
        $('#production-material-use-modal #no-btn').on('click', function() {
            $(`input[name="required_serial_no[]"][data-milestone-id="${SELECTED_MILESTONE_ID}"]`).trigger('click')

            $('#production-material-use-modal').removeClass('show-modal')
            $('#production-material-use-modal input[name="search"]').val(null)
        })
        $('#production-material-use-modal #yes-btn').on('click', function() {
            if ($('.material-use-selections input:checked').length <= 0) {
                $('#production-material-use-modal #no-btn').trigger('click')
                return
            }

            $('.material-use-selections input[type="checkbox"]').each(function(i, obj) {
                if ($(this).is(':checked')) {
                    if (MILESTONES[SELECTED_MILESTONE_ID] == undefined) {
                        MILESTONES[SELECTED_MILESTONE_ID]['material_use_product_ids'] = []
                    }
                    MILESTONES[SELECTED_MILESTONE_ID]['material_use_product_ids'].push($(this).parent()
                        .data('material-use-product-id'))
                }
            })

            $(`.milestones[data-milestone-id=${SELECTED_MILESTONE_ID}] .view-material-use-selection-btns`)
                .removeClass('hidden')

            $('#production-material-use-modal input[name="search"]').val(null)
            $('#production-material-use-modal').removeClass('show-modal')
        })
        $('#production-material-use-modal input[name="search"]').on('keyup', function() {
            let val = $(this).val()

            $(`.material-use-selections`).removeClass('hidden')

            if (val != '') {
                for (let i = 0; i < MATERIAL_USE.length; i++) {
                    for (let j = 0; j < MATERIAL_USE[i].materials.length; j++) {
                        included = false
                        if (
                            MATERIAL_USE[i].materials[j].material.model_name.includes(val) ||
                            MATERIAL_USE[i].materials[j].material.sku.includes(val)
                        ) {
                            included = true
                        }
                        if (!included) {
                            $(`.material-use-selections[data-material-use-product-id=${MATERIAL_USE[i].materials[j].product_id}]`)
                                .addClass('hidden')
                        }
                    }
                }
            }
        })

        function getProductMilestones(product_id, get_material_use_only = false) {
            let url = '{{ config('app.url') }}'
            url = `${url}/product/get/${product_id}`

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                success: function(res) {
                    MATERIAL_USE = res.product_material_use

                    if (get_material_use_only) return

                    // Clear existing milestones
                    $('#milestone-list-container .milestones').remove()
                    for (let i = 0; i < res.product_milestones.length; i++) {
                        const productMilestone = res.product_milestones[i];

                        let clone = $('#milestone-template')[0].cloneNode(true);
                        $(clone).find('.ms-name').text(productMilestone.milestone.name)
                        $(clone).find('.ms-name').attr('for', `ms-${productMilestone.milestone_id}`)
                        $(clone).find('.first-half input').attr('id', `ms-${productMilestone.milestone_id}`)
                        $(clone).find('.second-half input').attr('data-milestone-id',
                            `ms-${productMilestone.milestone_id}`)
                        $(clone).attr('data-milestone-id', `ms-${productMilestone.milestone_id}`)
                        $(clone).removeClass('hidden')
                        $(clone).addClass('milestones')
                        $(clone).removeAttr('id')

                        $('#milestone-list-container').append(clone)
                    }
                    // Initiate product milestones
                    for (let i = 0; i < res.product_milestones.length; i++) {
                        $(`.milestones[data-milestone-id="ms-${res.product_milestones[i].milestone_id}"] .first-half input`)
                            .attr('checked', true)

                        if (res.product_milestones[i].material_use_product_id.length > 0) {
                            $(`input[name="required_serial_no[]"][data-milestone-id="ms-${res.product_milestones[i].milestone_id}"`)
                                .attr('checked', true)
                            $(`.milestones[data-milestone-id="ms-${res.product_milestones[i].milestone_id}"] .view-material-use-selection-btns`)
                                .removeClass('hidden')
                        }
                        MILESTONES[`ms-${res.product_milestones[i].milestone_id}`] = {
                            material_use_product_ids: res
                                .product_milestones[i]
                                .material_use_product_id,
                            sequence: i + 1,
                            is_checked: true,
                            title: res.product_milestones[i].milestone.name,
                        }
                    }
                },
            });
        }

        function sortMilestone() {
            let sequence = 0

            $('.milestones').each(function(i, obj) {
                sequence++
                MILESTONES[$(this).attr('data-milestone-id')].sequence = sequence
            })
        }

        function buildRemarkQuillEditor() {
            // Create div wrapper for quill (jQuery)
            var $quill = $(`
                <div class="quill-wrapper rounded-md border border-gray-300 bg-white">
                    <div id="remark-quill"></div>
                </div>
            `);

            $(`textarea[name="remark"]`).after($quill);

            var quill = new Quill(`#remark-quill`, {
                theme: 'snow',
                placeholder: "{!! __('Remark') !!}",
                modules: {
                    toolbar: {
                        container: [
                            [{ 'header': [1, 2, false] }],
                            ['bold', 'italic', 'underline'],
                            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                            ['image'],
                        ],
                        handlers: {
                            image: function() {
                                // Create and trigger file input
                                var input = document.createElement('input');
                                input.setAttribute('type', 'file');
                                input.setAttribute('accept', 'image/*');
                                input.click();

                                input.onchange = function() {
                                    var file = input.files[0];
                                    if (!file) return;

                                    // Validate file type
                                    if (!file.type.match('image.*')) {
                                        alert('Please select an image file.');
                                        return;
                                    }

                                    // Validate file size (max 5MB)
                                    if (file.size > 5 * 1024 * 1024) {
                                        alert('Image size should be less than 5MB.');
                                        return;
                                    }

                                    // Prepare upload
                                    var formData = new FormData();
                                    formData.append('image', file);
                                    var range = quill.getSelection(true);

                                    // Show loading
                                    quill.insertText(range.index, 'Uploading image...');
                                    quill.setSelection(range.index + 19);

                                    // Upload to server
                                    $.ajax({
                                        url: '{{ route("quill.upload.image") }}',
                                        type: 'POST',
                                        data: formData,
                                        processData: false,
                                        contentType: false,
                                        headers: {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },
                                        success: function(response) {
                                            quill.deleteText(range.index, 19);
                                            quill.insertEmbed(range.index, 'image', response.url);
                                            quill.setSelection(range.index + 1);
                                            // Sync to textarea
                                            var html = quill.root.innerHTML;
                                            var isEmpty = html === '<p><br></p>' || quill.getText().trim() === '';
                                            $(`textarea[name="remark"]`).val(isEmpty ? '' : html);
                                        },
                                        error: function(xhr) {
                                            quill.deleteText(range.index, 19);
                                            var errorMsg = 'Failed to upload image.';
                                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                                errorMsg = xhr.responseJSON.message;
                                            }
                                            alert(errorMsg);
                                        }
                                    });
                                };
                            }
                        }
                    }
                },
            });

            // Load existing content from textarea (for old values after validation)
            setTimeout(function() {
                var existingContent = $(`textarea[name="remark"]`).val();
                if (existingContent && existingContent.trim() !== '') {
                    quill.root.innerHTML = existingContent;
                }
            }, 100);
        }
    </script>
@endpush
