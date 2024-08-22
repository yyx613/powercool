@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title>{{ isset($production) ? 'Edit Production - ' . $production->sku : 'Create Production' }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ isset($production) && !isset($is_duplicate) ? route('production.upsert', ['production' => $production->id]) : route('production.upsert') }}" method="POST" enctype="multipart/form-data" id="info-form">
        @csrf
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="grid grid-cols-3 gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label id="name" class="mb-1">Name <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="name" id="name" :hasError="$errors->has('name')" value="{{ old('name', isset($from_ticket) ? $from_ticket->subject : (isset($production) ? $production->name : null)) }}" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
                <div class="flex flex-col col-span-2">
                    <x-app.input.label id="desc" class="mb-1">Description <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="desc" id="desc" :hasError="$errors->has('desc')" value="{{ old('desc', isset($from_ticket) ? $from_ticket->body : (isset($production) ? $production->desc : null)) }}" />
                    <x-input-error :messages="$errors->get('desc')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="start_date" class="mb-1">State Date <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="start_date" id="start_date" :hasError="$errors->has('start_date')" value="{{ old('start_date', isset($production) ? $production->start_date : null) }}" />
                    <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                </div>
                <div class="flex flex-col col-span-2">
                    <x-app.input.label id="remark" class="mb-1">Remark</x-app.input.label>
                    <x-app.input.input name="remark" id="remark" :hasError="$errors->has('remark')" value="{{ old('remark', isset($production) ? $production->remark : null) }}" />
                    <x-input-error :messages="$errors->get('remark')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="due_date" class="mb-1">Due Date <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="due_date" id="due_date" :hasError="$errors->has('due_date')" value="{{ old('due_date', isset($production) ? $production->due_date : null) }}" />
                    <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="status" class="mb-1">Status <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                        <option value="">Select a status</option>
                        <option value="1" @selected(old('status', isset($production) ? $production->status : null) == 1)>To Do</option>
                        <option value="2" @selected(old('status', isset($production) ? $production->status : null) == 2)>Doing</option>
                        <option value="3" @selected(old('status', isset($production) ? $production->status : null) == 3)>Completed</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">Product <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="product" id="product" placeholder="Select a product" :hasError="$errors->has('product')">
                        <option value="">Select a product</option>
                        @foreach ($products as $pro)
                            <option value="{{ $pro->id }}" @selected(old('product', isset($production) ? $production->product_id : null) == $pro->id)>{{ $pro->model_name }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('product')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">Assigned Order ID <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="order" id="order" placeholder="Select a order" :hasError="$errors->has('order')">
                        <option value="">Select a product</option>
                        @foreach ($sales as $sale)
                            <option value="{{ $sale->id }}" @selected(old('order', isset($production) ? $production->sale_id : null) == $sale->id)>{{ $sale->sku }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('order')" class="mt-2" />
                </div>
                <div class="flex flex-col col-span-3">
                    <x-app.input.label id="assign" class="mb-1">Assigned Staff <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="assign[]" id="assign" :hasError="$errors->has('assign')" multiple>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected(in_array($user->id, old('assign', isset($production) ? $production->users()->pluck('user_id')->toArray() : [])))>{{ $user->name }}</option>
                    @endforeach
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('assign')" class="mt-2" />
                </div>
                <div class="flex flex-col col-span-3">
                    <x-app.input.label class="mb-2">Milestones <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="custom_milestone" class="mb-2" placeholder="Enter milestone here" />
                    @foreach($milestones as $stone)
                        <div class="flex justify-between mb-2 milestone-selection">
                            <div class="flex items-center gap-x-2">
                                <input type="checkbox" name="milestone[]" id="{{ $stone->id }}" value="{{ $stone->id }}" class="rounded-sm" @checked(in_array($stone->id, old('milestone', isset($production) ? $production->milestones()->pluck('milestone_id')->toArray() : [])))>
                                <label for="{{ $stone->id }}" class="text-sm">{{ $stone->name }}</label>
                            </div>
                            <label class="flex items-center rounded-full overflow-hidden relative cursor-pointer select-none border border-grey-200 w-24 h-7">
                                <input type="checkbox" class="hidden peer" name="required_serial_no[]" @checked(isset($production) ? $production->milestones()->where('milestone_id', $stone->id)->value('required_serial_no') : null) />
                                <div class="flex items-center w-full">
                                    <span class="flex-1 font-medium uppercase z-20 text-center text-xs">No</span>
                                    <span class="flex-1 font-medium uppercase z-20 text-center text-xs">Yes</span>
                                </div>
                                <span class="w-1/2 h-6 peer-checked:translate-x-full absolute rounded-full transition-all bg-blue-200 border border-black" />
                            </label>
                        </div>
                    @endforeach
                    <div id="custom-milestones-container">
                        <div class="flex justify-between mb-2 hidden" id="custom-milestone-template">
                            <div class="flex items-center gap-x-2 mb-2 first-part">
                                <input type="checkbox" name="custom_milestone[]" id="" value="" class="rounded-sm">
                                <label for="" class="text-sm"></label>
                            </div>
                            <label class="flex items-center rounded-full overflow-hidden relative cursor-pointer select-none border border-grey-200 w-24 h-7">
                                <input type="checkbox" class="hidden peer" name="required_serial_no[]" />
                                <div class="flex items-center w-full">
                                    <span class="flex-1 font-medium uppercase z-20 text-center text-xs">No</span>
                                    <span class="flex-1 font-medium uppercase z-20 text-center text-xs">Yes</span>
                                </div>
                                <span class="w-1/2 h-6 peer-checked:translate-x-full absolute rounded-full transition-all bg-blue-200 border border-black" />
                            </label>
                        </div>
                    </div>
                    @if ($errors->has('milestone'))
                        <x-input-error :messages="$errors->get('milestone')" class="mt-2" />
                    @else
                        <x-input-error :messages="$errors->get('custom_milestone')" class="mt-2" />
                    @endif
                    <input type="hidden" name="milestone_required_serial_no">
                    <input type="hidden" name="custom_milestone_required_serial_no">
                </div>
            </div>
            <div class="mt-8 flex justify-end">
                <x-app.button.submit id="submit-btn">Save and Update</x-app.button.submit>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        PRODUCTION = @json($production ?? null);

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
                $(clone).removeAttr('id')
                $(clone).find('.first-part label').text(val)
                $(clone).find('.first-part label').attr('for', `custom_milestone_${customMilestoneCount}`)
                $(clone).find('.first-part input').attr('id', `custom_milestone_${customMilestoneCount}`)
                $(clone).find('.first-part input').attr('value', val)
                $(clone).removeClass('hidden')
                $('#custom-milestones-container').append(clone)

                $(this).val(null)
            }
        })

        $('form').one('submit', function(e) {
            e.preventDefault()

            let msRequiredSerialNo = []
            $('.milestone-selection').each(function(i, obj) {
                if ($(this).find('input[name="milestone[]"]').is(':checked')) {
                    msRequiredSerialNo.push($(this).find('input[name="required_serial_no[]"]').is(':checked'))
                }
            })
            $('input[name="milestone_required_serial_no"]').val(msRequiredSerialNo)

            let customMsRequiredSerialNo = []
            $('.custom-milestone').each(function(i, obj) {
                if ($(this).find('input[name="custom_milestone[]"]').is(':checked')) {
                    customMsRequiredSerialNo.push($(this).find('input[name="required_serial_no[]"]').is(':checked'))
                }
            })
            $('input[name="custom_milestone_required_serial_no"]').val(customMsRequiredSerialNo)

            $(this).submit()
        })
    </script>
@endpush