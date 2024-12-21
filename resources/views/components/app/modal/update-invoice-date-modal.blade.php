<x-app.modal.base-modal id="update-invoice-date-modal">
    <div class="aspect-[2/1] flex flex-col">
        <div class="py-2 px-4 bg-gray-100">
            <h6 class="text-lg font-black">{{ __('Update Invoice Date (Should Not More Than 72 hours)') }}</h6>
        </div>
        <div class="flex-1 flex flex-col p-4">
            <div id="overdue-invoices-container" class="flex flex-col mb-2">
                <!-- 动态生成的发票信息会插入到这里 -->
            </div>
            <div class="flex gap-x-6 mt-3">
                <div class="flex-1">
                    <button type="button" class="w-full p-2 rounded-md text-red-600 text-sm font-medium transiton-all duration-300 hover:bg-red-50" id="no-btn">{{ __('Cancel') }}</button>
                </div>
                <div class="flex-1 flex">
                    <button class="w-full p-2 rounded-md bg-blue-600 text-white text-sm font-medium transiton-all duration-300 text-center hover:bg-blue-700" id="yes-btn" data-type="credit">{{ __('Update and Submit') }}</button>
                </div>
            </div>
        </div>
    </div>
</x-app.modal.base-modal>

@push('scripts')
<script>
    $('#update-invoice-date-modal #no-btn').on('click', function() {
        $('#update-invoice-date-modal').removeClass('show-modal')
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