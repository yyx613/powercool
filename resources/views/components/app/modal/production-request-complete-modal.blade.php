<x-app.modal.base-modal id="production-request-complete-modal">
    <div class="aspect-[2/1] flex flex-col">
        <div class="py-2 px-4 bg-gray-100">
            <h6 class="text-lg font-black" id="title">{{ __('Enter Production ID') }}</h6>
        </div>
        <div class="flex-1 flex flex-col p-4">
            <div class="flex-1">
                <x-app.input.label id="production_id" class="mb-1">{{ __('Production ID') }} <span class="text-sm text-slate-400">(Optional)</span> </x-app.input.label>
                <x-app.input.input name="production_id" id="production_id" value="" />
            </div>
            <div class="flex gap-x-6">
                <div class="flex-1">
                    <button type="button"
                        class="w-full p-2 rounded-md text-red-600 text-sm font-medium transiton-all duration-300 hover:bg-red-50"
                        id="no-btn">{{ __('No') }}</button>
                </div>
                <div class="flex-1 flex">
                    <a href=""
                        class="w-full p-2 rounded-md bg-blue-600 text-white text-sm font-medium transiton-all duration-300 text-center hover:bg-blue-700"
                        id="yes-btn">{{ __('Yes') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-app.modal.base-modal>

@push('scripts')
    <script>
        $('#production-request-complete-modal #no-btn').on('click', function() {
            $('#production-request-complete-modal').removeClass('show-modal')
        })
    </script>
@endpush
