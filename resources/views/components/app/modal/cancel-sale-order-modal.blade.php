<x-app.modal.base-modal id="cancel-sale-order-modal">
    <div class="aspect-[2/1] flex flex-col">
        <form class="m-0">
            <div class="border-b py-2 px-4 bg-gray-100">
                <h6 class="text-lg font-black" id="title">{{ __('Cancel Confirmation') }}</h6>
            </div>
            <div class="flex-1 flex flex-col p-4 h-full">
                <div class="flex-1">
                    <div class="flex flex-col mb-2">
                        <x-app.input.label id="charge" class="mb-1">{{ __('Cancellation Charge') }} <span class="text-xs">{{ __('(Leave it blank, if no charge is required)') }}</span></x-app.input.label>
                        <x-app.input.input name="charge" id="charge" class="decimal-input" />
                    </div>
                </div>
                <div class="flex gap-x-6">
                    <div class="flex-1">
                        <button type="button" class="w-full p-2 rounded-md text-red-600 text-sm font-medium transiton-all duration-300 hover:bg-red-50" id="no-btn">{{ __('No') }}</button>
                    </div>
                    <div class="flex-1 flex">
                        <button type="button" class="w-full p-2 rounded-md bg-blue-600 text-white text-sm font-medium transiton-all duration-300 text-center hover:bg-blue-700" id="yes-btn">{{ __('Yes') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-app.modal.base-modal>

@push('scripts')
<script>
    $('#cancel-sale-order-modal #no-btn').on('click', function() {
        $('#cancel-sale-order-modal').removeClass('show-modal')
    })

    $('#cancel-sale-order-modal #yes-btn').on('click', function() {
        $('#cancel-sale-order-modal form').submit()
    })
</script>
@endpush