@extends('layouts.app')
@section('title', 'Milestone')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title
            url="{{ route('milestone.index') }}">{{ isset($milestones) ? __('Edit Milestone') : __('Create Milestone') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ isset($milestones) ? route('milestone.update', ['batch' => $batch]) : route('milestone.store') }}"
        method="POST" enctype="multipart/form-data" id="form">
        @csrf
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label id="type" class="mb-1">{{ __('Inventory Type') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="type" id="type" :hasError="$errors->has('type')">
                        <option value="">{{ __('Select a Type') }}</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}" @selected(old('type', isset($type_id) ? $type_id : null) == $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('type')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="category" class="mb-1">{{ __('Inventory Category') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="category[]" id="category" :hasError="$errors->has('category')" multiple>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(isset($category_ids) && in_array($cat->id, $category_ids))>{{ $cat->name }}</option>
                        @endforeach
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('category')" class="mt-1" />
                </div>
            </div>
            {{-- Milestones --}}
            <div class="w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label id="milestone"
                        class="mb-1">{{ __("Milestones (Hit 'Enter' button to add the milestone)") }}</x-app.input.label>
                    <x-app.input.input name="milestone" id="milestone" />
                </div>
                <div id="milestone-list-container" class="mt-2">
                    <div class="group hidden justify-between leading-1 hover:bg-slate-50 py-1 cursor-grab"
                        id="milestone-template">
                        <span class="text-sm value"></span>
                        <button type="button"
                            class="text-sm font-semibold text-red-500 px-1.5 rounded hidden group-hover:block remove-btns">Remove</button>
                    </div>
                </div>
                <input name="milestones" type="hidden" />
            </div>
            <div class="mt-8 flex justify-end gap-x-4">
                <x-app.button.submit type="button" id="submit-btn">{{ __('Save and Update') }}</x-app.button.submit>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        CAN_SUBMIT = false
        MILESTONE_IDX = 0
        MILESTONES = {} // idx : value
        EDIT_MILESTONES = @json($milestones ?? null);
        EXISTING_MILESTONES = @json($existing_milestones ?? null);

        $(document).ready(function() {
            var elem = document.getElementById('milestone-list-container')
            var sortable = Sortable.create(elem, {
                onEnd: function(evt) {
                    sortMilestone()
                },
            })

            if (EDIT_MILESTONES == null) return

            $('select[name="type"]').trigger('change')

            for (i = 0; i < EDIT_MILESTONES.length; i++) {
                MILESTONE_IDX++

                MILESTONES[MILESTONE_IDX] = EDIT_MILESTONES[i].name

                let clone = $('#milestone-template')[0].cloneNode(true)

                $(clone).find('.value').text(EDIT_MILESTONES[i].name)
                $(clone).removeClass('hidden')
                $(clone).addClass('flex milestones')
                $(clone).removeAttr('id')
                $(clone).attr('data-idx', MILESTONE_IDX)
                $('#milestone-list-container').append(clone)
            }

            // Select category
            let categoryToSelect = EDIT_MILESTONES[0].inventory_category_id.split(',')
            for (let i = 0; i < categoryToSelect.length; i++) {
                $(`#category option[value="${categoryToSelect[i]}"]`).attr('selected', true)
            }

        })
        $('form').on('submit', function(e) {
            if (!CAN_SUBMIT) {
                e.preventDefault()
            }
        })
        $('#submit-btn').on('click', function() {
            CAN_SUBMIT = true
            $('input[name="milestones"]').val(Object.values(MILESTONES))
            $('form').submit()
        })
        $('input[name="milestone"]').on('keydown', function(e) {
            if (e.key == ',') {
                e.preventDefault()
            }
        })
        $('input[name="milestone"]').on('keyup', function(e) {
            if ($(this).val() != '' && e.key.toLowerCase() == 'enter') {
                MILESTONE_IDX++
                let clone = $('#milestone-template')[0].cloneNode(true)

                $(clone).find('.value').text($(this).val())
                $(clone).removeClass('hidden')
                $(clone).addClass('flex milestones')
                $(clone).removeAttr('id')
                $(clone).attr('data-idx', MILESTONE_IDX)
                $('#milestone-list-container').append(clone)

                MILESTONES[MILESTONE_IDX] = $(this).val()

                $(this).val(null)
            }
        })
        $('body').on('click', '.remove-btns', function() {
            let idx = $(this).parent().data('idx')

            $(`#milestone-list-container .milestones[data-idx=${idx}]`).remove()

            delete MILESTONES[idx]
        })
        $('select[name="type"]').on('change', function() {
            $(`#category option`).removeAttr('selected')
            $(`#category option`).removeClass('hidden')

            let val = $(this).val()
            for (let i = 0; i < EXISTING_MILESTONES.length; i++) {
                if (EXISTING_MILESTONES[i].inventory_type_id == val) {
                    let categoryToHide = EXISTING_MILESTONES[i].inventory_category_id.split(',')

                    for (let j = 0; j < categoryToHide.length; j++) {
                        $(`#category option[value="${categoryToHide[j]}"]`).addClass('hidden')
                    }
                    break
                }
            }
        })

        function sortMilestone() {
            MILESTONES = {}
            $('.milestones').each(function(i, obj) {
                $(this).attr('data-idx', i + 1)
                MILESTONES[i + 1] = $(this).find('.value').text()
            })
        }
    </script>
@endpush
