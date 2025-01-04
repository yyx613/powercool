@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('production.index') }}">{{ isset($production) ? __('Edit Production - ') . $production->sku : __('Create Production') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ isset($production) && !isset($is_duplicate) ? route('production.upsert', ['production' => $production->id]) : route('production.upsert') }}" method="POST" enctype="multipart/form-data" id="info-form">
        @csrf
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label id="name" class="mb-1">{{ __('Name') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="name" id="name" :hasError="$errors->has('name')" value="{{ old('name', isset($from_ticket) ? $from_ticket->subject : (isset($production) ? $production->name : null)) }}" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
                <div class="flex flex-col col-span-1 md:col-span-2">
                    <x-app.input.label id="desc" class="mb-1">{{ __('Description') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="desc" id="desc" :hasError="$errors->has('desc')" value="{{ old('desc', isset($from_ticket) ? $from_ticket->body : (isset($production) ? $production->desc : null)) }}" />
                    <x-input-error :messages="$errors->get('desc')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="start_date" class="mb-1">{{ __('State Date') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="start_date" id="start_date" :hasError="$errors->has('start_date')" value="{{ old('start_date', isset($production) ? $production->start_date : null) }}" />
                    <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                </div>
                <div class="flex flex-col col-span-1 md:col-span-2">
                    <x-app.input.label id="remark" class="mb-1">{{ __('Remark') }}</x-app.input.label>
                    <x-app.input.input name="remark" id="remark" :hasError="$errors->has('remark')" value="{{ old('remark', isset($production) ? $production->remark : null) }}" />
                    <x-input-error :messages="$errors->get('remark')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="due_date" class="mb-1">{{ __('Due Date') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="due_date" id="due_date" :hasError="$errors->has('due_date')" value="{{ old('due_date', isset($production) ? $production->due_date : null) }}" />
                    <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="status" class="mb-1">{{ __('Status') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                        <option value="">{{ __('Select a status') }}</option>
                        <option value="1" @selected(old('status', isset($production) ? $production->status : null) == 1)>{{ __('To Do') }}</option>
                        <option value="2" @selected(old('status', isset($production) ? $production->status : null) == 2)>{{ __('Doing') }}</option>
                        <option value="3" @selected(old('status', isset($production) ? $production->status : null) == 3)>{{ __('Completed') }}</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">{{ __('Product') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="product" id="product" placeholder="{{ __('Select a product') }}" :hasError="$errors->has('product')">
                        <option value="">{{ __('Select a product') }}</option>
                        @foreach ($products as $pro)
                            <option value="{{ $pro->id }}" @selected(old('product', isset($production) ? $production->product_id : (isset($default_product) ? $default_product->id : null)) == $pro->id)>{{ $pro->model_name }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('product')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">{{ __('Assigned Order ID') }}</x-app.input.label>
                    <x-app.input.select2 name="order" id="order" placeholder="{{ __('Select a order') }}" :hasError="$errors->has('order')">
                        <option value="">{{ __('Select a order') }}</option>
                        @foreach ($sales as $sale)
                            <option value="{{ $sale->id }}" @selected(old('order', isset($production) ? $production->sale_id : (isset($default_sale) ? $default_sale->id : null)) == $sale->id)>{{ $sale->sku }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('order')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">{{ __('Priority') }}</x-app.input.label>
                    <x-app.input.select2 name="priority" id="priority" placeholder="{{ __('Select a priority') }}" :hasError="$errors->has('priority')">
                        <option value="">{{ __('Select a priority') }}</option>
                        @foreach ($priorities as $priority)
                            <option value="{{ $priority->id }}" @selected(old('priority', isset($production) ? $production->priority_id : null) == $priority->id)>{{ $priority->name }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('priority')" class="mt-2" />
                </div>
                <div class="flex flex-col col-span-2 md:col-span-3">
                    <x-app.input.label id="assign" class="mb-1">{{ __('Assigned Staff') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="assign[]" id="assign" :hasError="$errors->has('assign')" multiple>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected(in_array($user->id, old('assign', isset($production) ? $production->users()->pluck('user_id')->toArray() : [])))>{{ $user->name }}</option>
                    @endforeach
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('assign')" class="mt-2" />
                </div>
                <div class="flex flex-col col-span-2 md:col-span-3">
                    <x-app.input.label class="mb-2">{{ __('Milestones') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="custom_milestone" class="mb-2" placeholder="{{ __('Enter custom milestone here') }}" />
                    <p class="text-xs text-end mb-2 text-slate-500">{{ __("'Yes' represent the milestone is required to fill up material use.") }}</p>
                    @foreach($milestones as $stone)
                        <div class="flex justify-between mb-2 hidden milestone-selection" data-id="{{ $stone->id }}">
                            <div class="flex items-center gap-x-2">
                                <input type="checkbox" name="milestone[]" id="{{ $stone->id }}" value="{{ $stone->id }}" class="rounded-sm" @checked(in_array($stone->id, old('milestone', isset($production) ? $production->milestones()->pluck('milestone_id')->toArray() : [])))>
                                <label for="{{ $stone->id }}" class="text-sm">{{ $stone->name }}</label>
                            </div>
                            <div class="flex items-center">
                                <button type="button" data-id="{{ $stone->id }}" data-is-custom="false" class="mr-3 view-material-use-selection-btns {{ isset($production) && $production->milestones()->where('milestone_id', $stone->id)->value('material_use_product_id') ? '' : 'hidden'  }}" title="View Material Use Selection">
                                    <svg class="h-4 w-4 fill-slate-400 hover:fill-black" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                        <path d="M23.707,22.293l-5.969-5.969c1.412-1.725,2.262-3.927,2.262-6.324C20,4.486,15.514,0,10,0S0,4.486,0,10s4.486,10,10,10c2.397,0,4.599-.85,6.324-2.262l5.969,5.969c.195,.195,.451,.293,.707,.293s.512-.098,.707-.293c.391-.391,.391-1.023,0-1.414ZM2,10C2,5.589,5.589,2,10,2s8,3.589,8,8-3.589,8-8,8S2,14.411,2,10Zm13.933-1.261c-.825-1.21-2.691-3.239-5.933-3.239s-5.108,2.03-5.933,3.239c-.522,.766-.522,1.755,0,2.521,.825,1.21,2.692,3.24,5.933,3.24s5.108-2.03,5.933-3.239c.522-.766,.522-1.755,0-2.521Zm-1.652,1.395c-.735,1.08-2.075,2.366-4.28,2.366s-3.544-1.287-4.28-2.367c-.056-.081-.056-.185,0-.267,.735-1.08,2.075-2.366,4.28-2.366s3.545,1.287,4.28,2.366h0c.056,.082,.056,.186,0,.268Zm-2.78-.134c0,.829-.671,1.5-1.5,1.5s-1.5-.671-1.5-1.5,.671-1.5,1.5-1.5,1.5,.671,1.5,1.5Z"/>
                                    </svg>
                                </button>
                                <label class="flex items-center rounded-full overflow-hidden relative cursor-pointer select-none border border-grey-200 w-24 h-7">
                                    <input type="checkbox" class="hidden peer" name="required_serial_no[]" data-id="{{ $stone->id }}" data-is-custom="false" @checked(isset($production) ? $production->milestones()->where('milestone_id', $stone->id)->value('material_use_product_id') : null) />
                                    <div class="flex items-center w-full">
                                        <span class="flex-1 font-medium uppercase z-20 text-center text-xs">{{ __('No') }}</span>
                                        <span class="flex-1 font-medium uppercase z-20 text-center text-xs">{{ __('Yes') }}</span>
                                    </div>
                                    <span class="w-1/2 h-6 peer-checked:translate-x-full absolute rounded-full transition-all bg-blue-200 border border-black" />
                                </label>
                            </div>
                        </div>
                    @endforeach
                    <div id="custom-milestones-container">
                        <div class="flex justify-between mb-2 hidden" id="custom-milestone-template">
                            <div class="flex items-center gap-x-2 mb-2 first-part">
                                <input type="checkbox" name="custom_milestone[]" id="" value="" class="rounded-sm">
                                <label for="" class="text-sm"></label>
                            </div>
                            <div class="flex items-center">
                                <button type="button" class="mr-3 view-material-use-selection-btns hidden" title="View Material Use Selection">
                                    <svg class="h-4 w-4 fill-slate-400 hover:fill-black" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                        <path d="M23.707,22.293l-5.969-5.969c1.412-1.725,2.262-3.927,2.262-6.324C20,4.486,15.514,0,10,0S0,4.486,0,10s4.486,10,10,10c2.397,0,4.599-.85,6.324-2.262l5.969,5.969c.195,.195,.451,.293,.707,.293s.512-.098,.707-.293c.391-.391,.391-1.023,0-1.414ZM2,10C2,5.589,5.589,2,10,2s8,3.589,8,8-3.589,8-8,8S2,14.411,2,10Zm13.933-1.261c-.825-1.21-2.691-3.239-5.933-3.239s-5.108,2.03-5.933,3.239c-.522,.766-.522,1.755,0,2.521,.825,1.21,2.692,3.24,5.933,3.24s5.108-2.03,5.933-3.239c.522-.766,.522-1.755,0-2.521Zm-1.652,1.395c-.735,1.08-2.075,2.366-4.28,2.366s-3.544-1.287-4.28-2.367c-.056-.081-.056-.185,0-.267,.735-1.08,2.075-2.366,4.28-2.366s3.545,1.287,4.28,2.366h0c.056,.082,.056,.186,0,.268Zm-2.78-.134c0,.829-.671,1.5-1.5,1.5s-1.5-.671-1.5-1.5,.671-1.5,1.5-1.5,1.5,.671,1.5,1.5Z"/>
                                    </svg>
                                </button>
                                <label class="flex items-center rounded-full overflow-hidden relative cursor-pointer select-none border border-grey-200 w-24 h-7">
                                    <input type="checkbox" class="hidden peer" name="required_serial_no[]" />
                                    <div class="flex items-center w-full">
                                        <span class="flex-1 font-medium uppercase z-20 text-center text-xs">{{ __('No') }}</span>
                                        <span class="flex-1 font-medium uppercase z-20 text-center text-xs">{{ __('Yes') }}</span>
                                    </div>
                                    <span class="w-1/2 h-6 peer-checked:translate-x-full absolute rounded-full transition-all bg-blue-200 border border-black" />
                                </label>
                            </div>
                        </div>
                    </div>
                    @if ($errors->has('milestone'))
                        <x-input-error :messages="$errors->get('milestone')" class="mt-2" />
                    @else
                        <x-input-error :messages="$errors->get('custom_milestone')" class="mt-2" />
                    @endif
                    <input type="hidden" name="material_use_product">
                </div>
            </div>
            @if (!isset($production) || (isset($production) && $production->status != 3))
                <!-- Not completed -->
                <div class="mt-8 flex justify-end">
                    <x-app.button.submit id="submit-btn">{{ __('Save and Update') }}</x-app.button.submit>
                </div>
            @endif
        </div>
    </form>

    <x-app.modal.production-milestone-material-use-modal/>
@endsection

@push('scripts')
    <script>
        INIT_EDIT = false
        PRODUCTS = @json($products ?? null);
        SALES = @json($sales ?? null);
        PRODUCTION = @json($production ?? null);
        MILESTONES = @json($milestones ?? null);
        MATERIAL_USES = @json($material_uses ?? null);
        SELECTED_MATERIAL_USES = []

        $(document).ready(function(){
            if (PRODUCTION != null) {
                INIT_EDIT = true

                for (let i = 0; i < PRODUCTION.milestones.length; i++) {
                    const element = PRODUCTION.milestones[i];
                    if (element.pivot.material_use_product_id != null) {
                        SELECTED_MATERIAL_USES[`ms_${element.pivot.milestone_id}`] = element.pivot.material_use_product_id.split(',')
                    }
                }
                $('select[name="product"]').trigger('change')

                INIT_EDIT = false
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

        // Prevent form submit when hitting 'Enter' key
        $(window).keydown(function(event){
            if(event.keyCode == 13) {
                event.preventDefault();
                return false;
            }
        });
        // Prevent user to enter ',' since every desc is joined with ','
        $('input[name="custom_milestone"]').on('keydown', function(e) {
            if (e.key == ',') {
                e.preventDefault()
                return false
            }
        })
        $('input[name="custom_milestone"]').on('keyup', function(e) {
            if (e.key == 'Enter') {
                let val = $(this).val();
                let customMilestoneCount = $('.custom-milestone').length
                customMilestoneCount++
                let clone = $('#custom-milestone-template')[0].cloneNode(true);

                $(clone).addClass('custom-milestone')
                $(clone).attr('data-id', customMilestoneCount)
                $(clone).removeAttr('id')
                $(clone).find('.first-part label').text(val)
                $(clone).find('.first-part label').attr('for', `custom_milestone_${customMilestoneCount}`)
                $(clone).find('.first-part input').attr('id', `custom_milestone_${customMilestoneCount}`)
                $(clone).find('.first-part input').attr('value', val)
                $(clone).find('input[name="required_serial_no[]"]').attr('data-id', customMilestoneCount)
                $(clone).find('input[name="required_serial_no[]"]').attr('data-is-custom', true)
                $(clone).find('button').attr('data-id', customMilestoneCount)
                $(clone).find('button').attr('data-is-custom', true)
                $(clone).removeClass('hidden')
                $('#custom-milestones-container').append(clone)

                $(this).val(null)
            }
        })

        $('select[name="order"]').on('change', function() {
            let val = $(this).val()

            if (SALES != null) {
                for (let i = 0; i < SALES.length; i++) {
                    if (SALES[i].id == val) {
                        $('select[name="product"] option').not(':first').remove()

                        for (let j = 0; j < SALES[i].products.length; j++) {
                            for (let k = 0; k < PRODUCTS.length; k++) {
                                if (SALES[i].products[j].product_id == PRODUCTS[k].id) {
                                    let opt = new Option(PRODUCTS[k].model_name, PRODUCTS[k].id)
                                    $('select[name="product"]').append(opt)
                                    break
                                }
                            }
                        }
                        break
                    }
                }
            }
        })
        $('form').one('submit', function(e) {
            e.preventDefault()

            let materialUseProduct = []
            for (const key in SELECTED_MATERIAL_USES) {
                if (key.includes('custom_ms_')) {
                    if (SELECTED_MATERIAL_USES[`custom_ms_${ key.replace('custom_ms_', '')}`].length > 0) {
                        materialUseProduct.push({
                            is_custom: true,
                            id: key.replace('custom_ms_', ''),
                            value: SELECTED_MATERIAL_USES[`custom_ms_${ key.replace('custom_ms_', '')}`]
                        })
                    }
                } else {
                    if (SELECTED_MATERIAL_USES[`ms_${ key.replace('ms_', '')}`].length > 0) {
                        materialUseProduct.push({
                            is_custom: false,
                            id: key.replace('ms_', ''),
                            value: SELECTED_MATERIAL_USES[`ms_${ key.replace('ms_', '')}`]
                        })
                    }
                }
            }
            $('input[name="material_use_product"]').val(JSON.stringify(materialUseProduct))

            $(this).submit()
        })

        // Filter milestones based on selected product
        $('select[name="product"]').on('change', function() {
            if (INIT_EDIT == false) {
                $(`.milestone-selection`).addClass('hidden')
                $('input[name="milestone[]"]').prop('checked', false)
            }

            let productId = $(this).val()

            for (let i = 0; i < MILESTONES.length; i++) {
                if (MILESTONES[i].is_custom == false || (MILESTONES[i].is_custom == true && MILESTONES[i].product_id == productId)) {
                    $(`.milestone-selection[data-id="${MILESTONES[i].id}"]`).removeClass('hidden')
                }
            }

            updateMilestoneMaterialUseSelection()
        })

        function updateMilestoneMaterialUseSelection() {
            $('.material-use-selection').remove()

            for (let i = 0; i < MATERIAL_USES.length; i++) {
                if (MATERIAL_USES[i].product_id == $('select[name="product"]').val()) {
                    for (let j = 0; j < MATERIAL_USES[i].materials.length; j++) {
                        let clone = $('#material-use-selection-template')[0].cloneNode(true);

                        $(clone).addClass('material-use-selection')
                        $(clone).find('input').attr('value', MATERIAL_USES[i].materials[j].id)
                        $(clone).find('input').attr('id', MATERIAL_USES[i].materials[j].id)
                        $(clone).find('label').attr('for', MATERIAL_USES[i].materials[j].id)
                        $(clone).find('label #name').text(MATERIAL_USES[i].materials[j].material.model_name)
                        $(clone).find('label #qty').text(`Quantity needed: x${MATERIAL_USES[i].materials[j].qty}`)
                        $(clone).removeClass('hidden')
                        $(clone).addClass('flex')

                        if (j == MATERIAL_USES[i].materials.length - 1) {
                            $(clone).removeClass('border-b')
                        }

                        $('#material-use-selection-container').append(clone)
                    }
                    break
                }
            }
        }
        // Toggle view material use selection
        $('body').on('change', 'input[name="required_serial_no[]"]', function() {
            let id = $(this).data('id')
            let isCustom = $(this).data('is-custom')

            if ($(this).is(':checked')) {
                $('#production-milestone-material-use-modal').data('id', id)
                $('#production-milestone-material-use-modal').data('is-custom', isCustom)
                // Reset selection
                $('#production-milestone-material-use-modal .material-use-selection input').prop('checked', false)

                $('#production-milestone-material-use-modal #action-container').removeClass('hidden')
                $('#production-milestone-material-use-modal #action2-container').addClass('hidden')

                $('#production-milestone-material-use-modal').addClass('show-modal')
            }

            if (id != undefined) {
                if (isCustom == false) {
                    if ($(this).is(':checked')) $(`.milestone-selection[data-id="${id}"]`).find('button').removeClass('hidden')
                    else $(`.milestone-selection[data-id="${id}"]`).find('button').addClass('hidden')
                } else {
                    if ($(this).is(':checked')) $(`.custom-milestone[data-id="${id}"]`).find('button').removeClass('hidden')
                    else $(`.custom-milestone[data-id="${id}"]`).find('button').addClass('hidden')
                }
            }

            if (!$(this).is(':checked')) {
                SELECTED_MATERIAL_USES[`${isCustom ? 'custom_ms' : 'ms'}_${id}`] = []
            }
        })
        $('body').on('click', '.view-material-use-selection-btns', function() {
            $(`.material-use-selection input`).each(function(i, obj) {
                if ($(this).is(':checked')) {
                    $(this).prop('checked', false)
                }
            })

            let id = $(this).data('id')
            let isCustom = $(this).data('is-custom')

            for (let i = 0; i < SELECTED_MATERIAL_USES[`${isCustom ? 'custom_ms' : 'ms'}_${id}`].length; i++) {
                const element = SELECTED_MATERIAL_USES[`${isCustom ? 'custom_ms' : 'ms'}_${id}`][i];

                $(`.material-use-selection input[id="${element}"]`).prop('checked', true)
            }

            $('#production-milestone-material-use-modal #action-container').addClass('hidden')
            $('#production-milestone-material-use-modal #action2-container').removeClass('hidden')
            $('#production-milestone-material-use-modal').addClass('show-modal')
        })
        $('#production-milestone-material-use-modal #no-btn').on('click', function() {
            let id = $(this).closest('#production-milestone-material-use-modal').data('id')
            let isCustom = $(this).closest('#production-milestone-material-use-modal').data('is-custom')

            $(`input[name="required_serial_no[]"][data-id="${id}"][data-is-custom="${isCustom}"]`).trigger('click')

            $('#production-milestone-material-use-modal').removeClass('show-modal')
        })
        $('#production-milestone-material-use-modal #yes-btn').on('click', function() {
            if ($('.material-use-selection input:checked').length <= 0) {
                $('#production-milestone-material-use-modal #no-btn').trigger('click')
                return
            }

            let id = $(this).closest('#production-milestone-material-use-modal').data('id')
            let isCustom = $(this).closest('#production-milestone-material-use-modal').data('is-custom')

            let selectedMaterialUseProductId = [];
            $('.material-use-selection input:checked').each(function(i, obj) {
                selectedMaterialUseProductId.push($(this).val())
            })
            SELECTED_MATERIAL_USES[`${isCustom ? 'custom_ms' : 'ms'}_${id}`] = selectedMaterialUseProductId

            $('#production-milestone-material-use-modal').removeClass('show-modal')
        })
    </script>
@endpush
