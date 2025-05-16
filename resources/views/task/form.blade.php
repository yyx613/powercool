@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title
            url="{{ $for_role == 'driver' ? route('task.driver.index') : ($for_role == 'technician' ? route('task.technician.index') : route('task.sale.index')) }}">{{ isset($task) ? __('Edit Task - ') . $task->sku : __('Create New Task') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    @if (isset($from_ticket) && isset($so_inv_labels) && $for_role == 'technician')
        <div class="flex gap-3 mb-4">
            @foreach ($so_inv_labels as $key => $label)
                <div
                    class="rounded-full px-3 py-1.5 text-xs {{ $key == $so_inv_idx ? 'bg-yellow-400 font-semibold' : 'bg-yellow-100 font-light' }}">
                    {{ $label->sku }}
                </div>
            @endforeach
        </div>
    @endif
    <form action="{{ isset($task) ? route($form_route_name, ['task' => $task]) : route($form_route_name) }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        @if (isset($from_ticket) && isset($so_inv_labels) && $for_role == 'technician')
            <input type="hidden" name="so_inv_idx" value="{{ $so_inv_idx }}" />
        @endif
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full mb-4">
                <input type="hidden" name="ticket" value="{{ isset($from_ticket) ? $from_ticket->id : null }}">
                @if ($for_role == 'technician')
                    <div class="flex flex-col">
                        <x-app.input.label id="sale_order_id" class="mb-1">{{ __('Sale Order ID') }}</x-app.input.label>
                        <x-app.input.select name="sale_order_id" id="sale_order_id" :hasError="$errors->has('sale_order_id')">
                            <option value="">{{ __('Select a sale order') }}</option>
                            @foreach ($sale_orders as $so)
                                <option value="{{ $so->id }}" @selected(old('sale_order_id', isset($task) ? $task->sale_order_id : null) == $so->id)>{{ $so->sku }}
                                </option>
                            @endforeach
                        </x-app.input.select>
                        <x-input-error :messages="$errors->get('sale_order_id')" class="mt-1" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="product_id" class="mb-1">{{ __('Product ID') }}</x-app.input.label>
                        <x-app.input.select2 name="product_id" id="product_id" :hasError="$errors->has('product_id')"
                            placeholder="{{ __('Select a product') }}">
                            <option value="">{{ __('Select a product') }}</option>
                        </x-app.input.select2>
                        <x-input-error :messages="$errors->get('product_id')" class="mt-1" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="product_child_id"
                            class="mb-1">{{ __('Product Child ID') }}</x-app.input.label>
                        <x-app.input.select2 name="product_child_id" id="product_child_id" :hasError="$errors->has('product_child_id')"
                            placeholder="{{ __('Select a product child') }}">
                            <option value="">{{ __('Select a product child') }}</option>
                        </x-app.input.select2>
                        <x-input-error :messages="$errors->get('product_child_id')" class="mt-1" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="task" class="mb-1">{{ __('Task') }} <span
                                class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select2 name="task" id="task" :hasError="$errors->has('task')"
                            placeholder="{{ __('Select a task') }}">
                            <option value="">{{ __('Select a task') }}</option>
                            @foreach ($task_types as $key => $val)
                                <option value="{{ $key }}" @selected(old('task', isset($task) ? $task->task_type : null) == $key)>{{ $val }}
                                </option>
                            @endforeach
                        </x-app.input.select2>
                        <x-input-error :messages="$errors->get('task')" class="mt-1" />
                    </div>
                @endif
                <div class="flex flex-col">
                    <x-app.input.label id="customer" class="mb-1">{{ __('Customer') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="customer" id="customer" :hasError="$errors->has('customer')"
                        placeholder="{{ __('Select a customer') }}">
                        <option value="">{{ __('Select a customer') }}</option>
                        @foreach ($customers as $cu)
                            <option value="{{ $cu->id }}" @selected(old('customer', isset($from_ticket) ? $from_ticket->customer_id : (isset($task) ? $task->customer_id : null)) == $cu->id)>{{ $cu->name }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('customer')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="name" class="mb-1">{{ __('Name') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="name" id="name" :hasError="$errors->has('name')"
                        value="{{ old('name', isset($from_ticket) ? $from_ticket->subject : (isset($task) ? $task->name : null)) }}" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                @if ($for_role == 'driver')
                    <div class="flex flex-col">
                        <x-app.input.label id="task" class="mb-1">{{ __('Task') }} <span
                                class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select2 name="task" id="task" :hasError="$errors->has('task')"
                            placeholder="{{ __('Select a task') }}">
                            <option value="">{{ __('Select a task') }}</option>
                            @foreach ($task_types as $key => $val)
                                <option value="{{ $key }}" @selected(old('task', isset($task) ? $task->task_type : null) == $key)>{{ $val }}
                                </option>
                            @endforeach
                        </x-app.input.select2>
                        <x-input-error :messages="$errors->get('task')" class="mt-1" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="delivery_order_id"
                            class="mb-1">{{ __('Delivery Order') }}</x-app.input.label>
                        <x-app.input.select name="delivery_order_id" id="delivery_order_id" :hasError="$errors->has('delivery_order_id')">
                            <option value="">{{ __('Select a delivery order') }}</option>
                        </x-app.input.select>
                        <x-input-error :messages="$errors->get('delivery_order_id')" class="mt-1" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="sale_order_id" class="mb-1">{{ __('Sale Order') }}</x-app.input.label>
                        <x-app.input.select name="sale_order_id" id="sale_order_id" :hasError="$errors->has('sale_order_id')">
                            <option value="">{{ __('Select a sale order') }}</option>
                        </x-app.input.select>
                        <x-input-error :messages="$errors->get('sale_order_id')" class="mt-1" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="delivery_address" class="mb-1">{{ __('Delivery Address') }} <span
                                class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select name="delivery_address" id="delivery_address" :hasError="$errors->has('delivery_address')">
                            <option value="">{{ __('Select a delivery address') }}</option>
                        </x-app.input.select>
                        <x-input-error :messages="$errors->get('delivery_address')" class="mt-1" />
                    </div>
                @endif
                <div class="flex flex-col col-span-1 lg:col-span-2">
                    <x-app.input.label id="desc" class="mb-1">{{ __('Description') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="desc" id="desc" :hasError="$errors->has('desc')"
                        value="{{ old('desc', isset($from_ticket) ? $from_ticket->body : (isset($task) ? $task->desc : null)) }}" />
                    <x-input-error :messages="$errors->get('desc')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="start_date" class="mb-1">{{ __('State Date') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="start_date" id="start_date" :hasError="$errors->has('start_date')"
                        value="{{ old('start_date', isset($task) ? $task->start_date : null) }}" />
                    <x-input-error :messages="$errors->get('start_date')" class="mt-1" />
                </div>
                <div class="flex flex-col col-span-1 lg:col-span-2">
                    <x-app.input.label id="remark" class="mb-1">{{ __('Remark') }}</x-app.input.label>
                    <x-app.input.input name="remark" id="remark" :hasError="$errors->has('remark')"
                        value="{{ old('remark', isset($task) ? $task->remark : null) }}" />
                    <x-input-error :messages="$errors->get('remark')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="due_date" class="mb-1">{{ __('Due Date') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="due_date" id="due_date" :hasError="$errors->has('due_date')"
                        value="{{ old('due_date', isset($task) ? $task->due_date : null) }}" />
                    <x-input-error :messages="$errors->get('due_date')" class="mt-1" />
                </div>
                @if ($for_role == 'driver')
                    <div class="flex flex-col">
                        <x-app.input.label id="estimated_time" class="mb-1">{{ __('Estimated Time') }} <span
                                class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.input name="estimated_time" id="estimated_time" :hasError="$errors->has('estimated_time')"
                            value="{{ old('estimated_time', isset($task) ? $task->estimated_time : null) }}" />
                        <x-input-error :messages="$errors->get('estimated_time')" class="mt-1" />
                    </div>
                @endif
                <div class="flex flex-col">
                    <x-app.input.label id="status" class="mb-1">{{ __('Status') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                        <option value="">{{ __('Select a status') }}</option>
                        <option value="1" @selected(old('status', isset($task) ? $task->status : null) == 1)>{{ __('To Do') }}</option>
                        <option value="2" @selected(old('status', isset($task) ? $task->status : null) == 2)>{{ __('Doing') }}</option>
                        <option value="3" @selected(old('status', isset($task) ? $task->status : null) == 3)>{{ __('In Review') }}</option>
                        <option value="4" @selected(old('status', isset($task) ? $task->status : null) == 4)>{{ __('Completed') }}</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                </div>
                @if ($for_role != 'sale')
                    <div class="flex flex-col">
                        <x-app.input.label id="amount_to_collect"
                            class="mb-1">{{ __('Amount to Collect') }}</x-app.input.label>
                        <x-app.input.input name="amount_to_collect" id="amount_to_collect" :hasError="$errors->has('amount_to_collect')"
                            value="{{ old('amount_to_collect', isset($task) ? $task->amount_to_collect : null) }}" />
                        <x-input-error :messages="$errors->get('amount_to_collect')" class="mt-1" />
                    </div>
                @endif
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">{{ __('Attachments') }}</x-app.input.label>
                    <x-app.input.file id="attachment[]" :hasError="$errors->has('attachment')" multiple="true" />
                    <x-input-error :messages="$errors->get('attachment')" class="mt-1" />
                    <div class="uploaded-file-preview-container" data-id="attachment">
                        <div class="p-y.5 px-1.5 rounded bg-blue-50 mt-2 hidden" id="uploaded-file-template">
                            <a href="" target="_blank" class="text-blue-700 text-xs"></a>
                        </div>
                        @if (isset($from_ticket))
                            @foreach ($from_ticket->attachments as $att)
                                <div class="p-y.5 px-1.5 rounded bg-blue-50 mt-2 old-preview truncate">
                                    <a href="{{ $att->url }}" target="_blank"
                                        class="text-blue-700 text-xs">{{ $att->src }}</a>
                                </div>
                            @endforeach
                        @elseif (isset($task))
                            @foreach ($task->attachments as $att)
                                <div class="p-y.5 px-1.5 rounded bg-blue-50 mt-2 old-preview truncate">
                                    <a href="{{ $att->url }}" target="_blank"
                                        class="text-blue-700 text-xs">{{ $att->src }}</a>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                @if ($for_role == 'technician' && count($services) > 0)
                    <div class="flex flex-col col-span-2 lg:col-span-3">
                        <x-app.input.label id="services" class="mb-1">{{ __('Services') }}</x-app.input.label>
                        @foreach ($services as $key => $ser)
                            <div class="flex items-center gap-x-2 {{ $key + 1 != count($services) ? 'mb-1.5' : '' }}">
                                <input type="checkbox" name="services[]" id="service_{{ $ser->id }}"
                                    value="{{ $ser->id }}" class="border-slate-400 rounded-sm"
                                    @checked(in_array($ser->id, old('service', isset($task) ? $task->services()->pluck('service_id')->toArray() : [])))>
                                <label for="service_{{ $ser->id }}" class="text-sm">{{ $ser->name }}
                                    (RM{{ number_format($ser->amount, 2) }})
                                </label>
                            </div>
                        @endforeach
                        <x-input-error :messages="$errors->get('services')" class="mt-1" />
                    </div>
                @endif
                <div class="flex flex-col col-span-2 lg:col-span-3">
                    <x-app.input.label id="assign" class="mb-1">{{ __('Assigned') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="assign[]" id="assign" :hasError="$errors->has('assign')" multiple>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(in_array($user->id, old('assign', isset($task) ? $task->users()->pluck('user_id')->toArray() : [])))>{{ $user->name }}
                            </option>
                        @endforeach
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('assign')" class="mt-1" />
                </div>
                <div class="flex flex-col col-span-2 lg:col-span-3">
                    <x-app.input.label class="mb-2">{{ __('Service Task Milestones') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="custom_milestone" class="mb-2"
                        placeholder="{{ __('Enter custom milestone here') }}" />
                    @foreach ($milestones as $stone)
                        <div class="flex items-center gap-x-2 mb-2 {{ $for_role == 'technician' ? 'hidden' : '' }} milestone-selection"
                            data-type="{{ $stone->type }}">
                            <input type="checkbox" name="milestone[]" id="{{ $stone->id }}"
                                value="{{ $stone->id }}" class="rounded-sm" @checked(in_array($stone->id, old('milestone', isset($task) ? $task->milestones()->pluck('milestone_id')->toArray() : [])))>
                            <label for="{{ $stone->id }}" class="text-sm">{{ $stone->name }}</label>
                        </div>
                    @endforeach
                    <div id="custom-milestones-container">
                        <div class="flex items-center gap-x-2 mb-2 hidden milestone-selection"
                            id="custom-milestone-template">
                            <input type="checkbox" name="custom_milestone[]" id="" value=""
                                class="rounded-sm">
                            <label for="" class="text-sm"></label>
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('milestone')" class="mt-1" />
                </div>
            </div>
            <div class="mt-8 flex justify-end">
                @if (isset($from_ticket) && isset($so_inv_labels) && $for_role == 'technician')
                    <x-app.button.submit
                        id="submit-btn">{{ $so_inv_idx + 1 == count($so_inv) ? __('Create New Task') : __('Create & Continue') }}</x-app.button.submit>
                @else
                    <x-app.button.submit
                        id="submit-btn">{{ isset($task) ? __('Update Task') : __('Create New Task') }}</x-app.button.submit>
                @endif
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        FOR_ROLE = @json($for_role ?? null);
        TASK = @json($task ?? null);
        SERVICES = @json($services ?? null);
        SALE_PRODUCTS = @json($sale_products ?? null);
        INIT_EDIT = true;
        SO_INV_IDX = @json($so_inv_idx ?? null);
        SO_INV = @json($so_inv ?? null);
        SO_INV_TYPE = @json($so_inv_type ?? null);
        SO_INV_PRODUCT = @json($product ?? null);
        SO_INV_PRODUCT_CHILDREN = @json($product_child ?? null);
        OLD_DELIVERY_ADDRESS_ID = @json(old('delivery_address') ?? null);
        if (OLD_DELIVERY_ADDRESS_ID == null && TASK != null) {
            OLD_DELIVERY_ADDRESS_ID = TASK.delivery_address_id;
        }

        $(document).ready(function() {
            if (TASK != null) {
                if (TASK.task_type != null) {
                    $('select[name="task"]').trigger('change')
                }
                if (TASK.sale_order_id != null && TASK.product_id != null) {
                    $('select[name="sale_order_id"]').trigger('change')
                    $('select[name="product_id"]').val(TASK.product_id)
                    $('select[name="product_child_id"]').val(TASK.product_child_id)
                }
            } else if (SO_INV != null) {
                if (SO_INV_TYPE[SO_INV_IDX] == 'so') {
                    $('select[name="sale_order_id"]').val(SO_INV[SO_INV_IDX]).trigger('change')
                    $('select[name="product_id"]').val(SO_INV_PRODUCT[SO_INV_IDX])
                    $('select[name="product_child_id"]').val(SO_INV_PRODUCT_CHILDREN[SO_INV_IDX])
                } else if (SO_INV_TYPE[SO_INV_IDX] == 'inv') {}
            }

            if ($('select[name="customer"]').val() != '') {
                $('select[name="customer"]').trigger('change')
            }

            INIT_EDIT = false
        })

        var param = {
            singleDatePicker: true,
            showDropdowns: true,
            autoUpdateInput: false,
            locale: {
                format: 'YYYY-MM-DD'
            }
        }
        $('input[name="start_date"]').daterangepicker(param)
        $('input[name="start_date"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });
        $('input[name="due_date"]').daterangepicker(param)
        $('input[name="due_date"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });


        $('select[name="customer"]').on('change', function() {
            let val = $(this).val()

            $('select[name="sale_order_id"]').val(null)

            let url = '{{ config('app.url') }}'
            url = `${url}/customer/get-so-do/${val}?type=so,do`

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                async: false,
                success: function(res) {
                    if (res.do) {
                        $('select[name="delivery_order_id"] option:not(:first)').remove()

                        for (let i = 0; i < res.do.length; i++) {
                            let opt = new Option(res.do[i].sku, res.do[i].id)
                            $('select[name="delivery_order_id"]').append(opt)
                        }
                    }
                    if (res.so) {
                        $('select[name="sale_order_id"] option:not(:first)').remove()

                        for (let i = 0; i < res.so.length; i++) {
                            let opt = new Option(res.so[i].sku, res.so[i].id)
                            $('select[name="sale_order_id"]').append(opt)
                        }
                    }
                },
            });
            // Get delivery address
            url = '{{ config('app.url') }}'
            url = `${url}/customer/get-location/?customer_id=${val}&type=delivery`

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                async: false,
                success: function(res) {
                    if (res.locations) {
                        $('select[name="delivery_address"] option:not(:first)').remove()

                        for (let i = 0; i < res.locations.length; i++) {
                            let opt = new Option(res.locations[i].address, res.locations[i].id)
                            $('select[name="delivery_address"]').append(opt)
                        }
                    }
                },
            });
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
        $('select[name="task"]').on('change', function() {
            let val = $(this).val()

            if (!INIT_EDIT) {
                $('.milestone-selection').find('input').prop('checked', false)
                $('.milestone-selection').addClass('hidden')
            }

            $('.milestone-selection').each(function(i, obj) {
                if ($(obj).data('type') == val) {
                    $(obj).removeClass('hidden')
                }
            })
        })
        $('input[name="services[]"]').on('change', function() {
            let amount = 0
            let selectedServices = []

            $('input[name="services[]"]:checked').each(function(i, obj) {
                selectedServices.push(parseInt($(this).val()))
            })

            for (let i = 0; i < SERVICES.length; i++) {
                if (selectedServices.includes(SERVICES[i].id)) {
                    amount += SERVICES[i].amount
                }
            }

            $('input[name="amount_to_collect"]').val(priceFormat(amount))
        })
        $('select[name="sale_order_id"]').on('change', function() {
            let val = $(this).val()

            if (FOR_ROLE == 'technician') {
                $(`select[name="product_id"]`).find('option').not(':first').remove();
                $(`select[name="product_child_id"]`).find('option').not(':first').remove();

                for (let i = 0; i < SALE_PRODUCTS.length; i++) {
                    if (SALE_PRODUCTS[i].sale_id == val) {

                        let opt = new Option(
                            `${SALE_PRODUCTS[i].product.model_name} (${SALE_PRODUCTS[i].product.sku})`,
                            SALE_PRODUCTS[i].product_id)
                        $(`select[name="product_id"]`).append(opt)

                        // Append children
                        for (let j = 0; j < SALE_PRODUCTS[i].product.children.length; j++) {
                            let opt = new Option(`${SALE_PRODUCTS[i].product.children[j].sku}`, SALE_PRODUCTS[i]
                                .product.children[j].id)
                            $(`select[name="product_child_id"]`).append(opt)
                        }
                    }
                }
            }
        })
        $('select[name="delivery_order_id"]').on('change', function() {
            let val = $(this).val()
        })

        // Prevent form submit when hitting 'Enter' key
        $(window).keydown(function(event) {
            if (event.keyCode == 13) {
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
                $(clone).removeAttr('id')
                $(clone).find('label').text(val)
                $(clone).find('label').attr('for', `custom_milestone_${customMilestoneCount}`)
                $(clone).find('input').attr('id', `custom_milestone_${customMilestoneCount}`)
                $(clone).find('input').attr('value', val)
                $(clone).removeClass('hidden')
                $('#custom-milestones-container').append(clone)

                $(this).val(null)
            }
        })
        $('#submit-btn').on('click', function() {
            $('form').submit()
        })
    </script>
@endpush
