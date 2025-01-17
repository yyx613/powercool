<x-app.modal.base-modal id="tin-info-modal">
    <div class="aspect-[2/1] flex flex-col">
        <div class="py-2 px-4 bg-slate-100 flex items-center">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M12,0A12,12,0,1,0,24,12,12.013,12.013,0,0,0,12,0Zm0,22A10,10,0,1,1,22,12,10.011,10.011,0,0,1,12,22Z"/><path d="M12,10H11a1,1,0,0,0,0,2h1v6a1,1,0,0,0,2,0V12A2,2,0,0,0,12,10Z"/><circle cx="12" cy="6.5" r="1.5"/></svg>
            <h6 class="text-lg font-black ml-3">{{ __('Information') }}</h6>
        </div>
        <div class="flex-1 flex flex-col p-4">
            <div class="mb-4 flex-1 flex flex-col">
                <p class="text-md text-center font-semibold" id="msg"></p>
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
    </div>
</x-app.modal.base-modal>

@push('scripts')
<script>
    $('#tin-info-modal #no-btn').on('click', function() {
        $('#tin-info-modal').removeClass('show-modal')
    })
</script>
@endpush
