<x-app.modal.base-modal id="assign-sale-person-modal">
    <div class="aspect-[2/1] flex flex-col">
        <div class="py-2 px-4 bg-gray-100">
            <h6 class="text-lg font-black">{{ __('Assign Sales Person') }}</h6>
        </div>
        <div class="flex-1 flex flex-col p-4">
            <div id="selected-orders-list" class="mb-4">
                <h6 class="text-sm font-medium">{{ __('Selected Orders') }}</h6>
                <ul></ul> 
            </div>
            <div class="flex-1">
                <span class="font-medium text-sm mb-1 block">{{ __('Select Sales Person') }}</span>
                <x-app.input.select name="salesperson" id="salesperson" class="w-full">
                    <option value="">{{ __('Select a product') }}</option>
                </x-app.input.select>
            </div>
            <div class="flex gap-x-6 mt-3">
                <div class="flex-1">
                    <button type="button" class="w-full p-2 rounded-md text-red-600 text-sm font-medium transiton-all duration-300 hover:bg-red-50" id="no-btn">{{ __('Cancel') }}</button>
                </div>
                <div class="flex-1 flex">
                    <button class="w-full p-2 rounded-md bg-blue-600 text-white text-sm font-medium transiton-all duration-300 text-center hover:bg-blue-700" id="yes-btn">{{ __('Assign') }}</button>
                </div>
            </div>
        </div>
    </div>
</x-app.modal.base-modal>

@push('scripts')
<script>
    $('#assign-sale-person-modal #no-btn').on('click', function() {
        $('#assign-sale-person-modal').removeClass('show-modal')
    })
    

    // $('#to-production-modal select[name="product"]').change(function() {
    //     let val = $(this).val()

    //     if (val != 'null') {
    //         $('#to-production-modal #yes-btn').removeClass('hidden')
    //     } else {
    //         $('#to-production-modal #yes-btn').addClass('hidden')
    //     }
    // })
</script>
@endpush