@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <x-app.page-title url="{{ route('raw_material_request.index') }}">
            {{ __('Create Raw Material Request') }}
        </x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ route('raw_material_request.store') }}" method="POST" enctype="multipart/form-data" id="form">
        @csrf
        <div class="bg-white p-4 border rounded-md">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full items-start">
                <div class="flex flex-col">
                    <x-app.input.label id="production_id" class="mb-1">{{ __('Production ID') }}</x-app.input.label>
                    <x-app.input.select2 name="production_id" id="production_id" :hasError="$errors->has('production_id')"
                        placeholder="{{ __('Select a production') }}">
                        <option value="">{{ __('Select a production') }}</option>
                        @foreach ($productions as $val)
                            <option value="{{ $val->id }}" @selected(old('production_id') == $val->id)>{{ $val->sku }}
                            </option>
                        @endforeach
                    </x-app.input.select2>

                    <x-input-error :messages="$errors->get('production_id')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="remark" class="mb-1">{{ __('Remark') }}</x-app.input.label>
                    <x-app.input.input name="remark" id="remark" value="{{ old('remark') }}" :hasError="$errors->has('remark')" />
                    <x-input-error :messages="$errors->get('remark')" class="mt-1" />
                </div>
            </div>
            <!-- Materials -->
            <div class="mt-4 w-full md:w-1/2">
                <div class="flex flex-col">
                    <x-app.input.label id="material" class="mb-1">{{ __('Materials') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <div id="material-container"></div>
                    <!-- Template -->
                    <div class="mb-4 hidden" id="material-template">
                        <div class="flex items-center gap-4 w-full rounded-md">
                            <div class="flex flex-col flex-1">
                                <x-app.input.select name="material[]" placeholder="{{ __('Select a material') }}">
                                    <option value="">{{ __('Select a material') }}</option>
                                    @foreach ($products as $prod)
                                        <option value="{{ $prod->id }}">{{ $prod->model_name }}</option> 
                                    @endforeach
                                </x-app.input.select>
                            </div>
                            <div class="flex flex-col flex-1">
                                <x-app.input.input name="qty[]" id="qty" :hasError="$errors->has('qty')" class="int-input"
                                    placeholder="{{ __('Enter quantity') }}" />
                            </div>
                            <button type="button"
                                class="bg-rose-400 p-2 rounded-full h-8 w-8 flex items-center justify-center delete-item-btns"
                                title="Delete Product">
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
    ITEMS_COUNT = 0

    $(document).ready(function(){
        $('#add-material-btn').click()
    })
    $('#add-material-btn').on('click', function() {
        let clone = $('#material-template')[0].cloneNode(true);
        
        ITEMS_COUNT++
        $(clone).attr('data-id', ITEMS_COUNT)
        $(clone).find('.delete-item-btns').attr('data-id', ITEMS_COUNT)
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
    $('form').on('submit', function() {
        $('#material-template').remove()
    })

    function buildMaterialSelect2(item_id) {
        $(`.items[data-id="${item_id}"] select[name="material[]"]`).select2({
            placeholder: "{!! __('Select a material') !!}"
        })
        $(`.items[data-id="${ITEMS_COUNT}"] .select2`).addClass('border border-gray-300 rounded-md overflow-hidden')
    }
</script>
@endpush