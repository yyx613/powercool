<x-app.modal.base-modal id="cancel-e-invoice-modal">
    <div class="aspect-[2/1] flex flex-col">
        <div class="border-b py-2 px-4 bg-gray-100">
            <h6 class="text-lg font-black">{{ __('Cancel E-Invoice') }}</h6>
        </div>
        <div class="flex-1 flex flex-col p-4">
            <div class="flex flex-col mb-2">
                <x-app.input.label id="reference" class="mb-1">{{ __('Cancel Reason') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                <x-app.input.input name="reference" id="cancel-reason" />
            </div>
            <div class="flex gap-x-6 mt-3">
                <div class="flex-1">
                    <button type="button" class="w-full p-2 rounded-md text-red-600 text-sm font-medium transiton-all duration-300 hover:bg-red-50" id="no-btn-cancel">{{ __('Cancel') }}</button>
                </div>
                <div class="flex-1 flex">
                    <button class="w-full p-2 rounded-md bg-blue-600 text-white text-sm font-medium transiton-all duration-300 text-center hover:bg-blue-700" id="yes-btn-cancel" data-type="credit">{{ __('Submit') }}</button>
                </div>
            </div>
        </div>
    </div>
</x-app.modal.base-modal>

@push('scripts')
<script>
    $('#cancel-e-invoice-modal #no-btn-cancel').on('click', function() {
        $('#cancel-e-invoice-modal').removeClass('show-modal')
    })

</script>
@endpush