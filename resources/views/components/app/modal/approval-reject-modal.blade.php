<x-app.modal.base-modal id="approval-reject-modal">
    <div class="aspect-[2/1] flex flex-col">
        <div class="py-2 px-4 bg-gray-100">
            <h6 class="text-lg font-black">{{ __("Reject Reason") }}</h6>
        </div>
        <div class="flex-1 flex flex-col p-4">
            <div class="flex flex-col mb-2 flex-1">
                <x-app.input.label id="remark" class="mb-1">{{ __('Remark') }}</x-app.input.label>
                <x-app.input.textarea name="remark" />
            </div>
            <div class="flex gap-x-6 mt-3">
                <div class="flex-1">
                    <button type="button" class="w-full p-2 rounded-md text-red-600 text-sm font-medium transiton-all duration-300 hover:bg-red-50" id="no-btn">{{ __('Cancel') }}</button>
                </div>
                <div class="flex-1 flex">
                    <button class="w-full p-2 rounded-md bg-blue-600 text-white text-sm font-medium transiton-all duration-300 text-center hover:bg-blue-700" id="yes-btn" data-type="credit">{{ __('Submit') }}</button>
                </div>
            </div>
        </div>
    </div>
</x-app.modal.base-modal>

@push('scripts')
<script>
    $('#approval-reject-modal #no-btn').on('click', function() {
        $('#approval-reject-modal').removeClass('show-modal')
    })
</script>
@endpush