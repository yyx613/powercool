<x-app.modal.base-modal id="product-material-use-modal">
    <div class="aspect-[2/1] flex flex-col">
        <div class="py-2 px-4 bg-slate-100 flex items-center">
            <h6 class="text-lg font-black">{{ __('Material Use Selection') }}</h6>
        </div>
        <div class="flex-1 flex flex-col p-4">
            <span class="font-medium text-sm block mb-2">{{ __('Materials') }}</span>
            <div class="border px-2 rounded overflow-y-auto max-h-64" id="material-use-selection-container">
                <div class="hidden py-2 items-center border-b" id="material-use-selection-template">
                    <input type="checkbox" class="rounded-full border-slate-400">
                    <label for="" class="text-sm ml-2 flex flex-col flex-1">
                        <span  id="name"></span>
                        <span class="text-slate-400 text-sm" id="qty"></span>
                    </label>
                </div>
            </div>
            <div class="flex gap-x-6 mt-6" id="action-container">
                <button type="button" class="w-full p-2 rounded-md text-red-600 text-sm font-medium transiton-all duration-300 hover:bg-red-50" id="no-btn">{{ __('Cancel') }}</button>
                <button type="button" class="w-full p-2 rounded-md bg-blue-600 text-white text-sm font-medium transiton-all duration-300 text-center hover:bg-blue-700" id="yes-btn">{{ __('Confirm') }}</button>
            </div>
            <div class="flex gap-x-6 mt-6" id="action2-container">
                <button type="button" class="w-full p-2 rounded-md bg-emerald-400 text-white text-sm font-medium transiton-all duration-300 text-center hover:bg-emerald-500" id="ok-btn">{{ __('OK') }}</button>
            </div>
        </div>
    </div>
</x-app.modal.base-modal>

@push('scripts')
<script>
    $('#product-material-use-modal #ok-btn').on('click', function() {
        $('#product-material-use-modal').removeClass('show-modal')
    })
</script>
@endpush