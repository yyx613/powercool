<x-app.modal.base-modal id="transfer-modal">
    <div class="aspect-[2/1] flex flex-col">
        <div class="border-b py-2 px-4 bg-emerald-100 flex items-center">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M24,12.649a5,5,0,0,0-.256-1.581L22.405,7.051A3,3,0,0,0,19.559,5H17V4a3,3,0,0,0-3-3H3A3,3,0,0,0,0,4V19.5a3.517,3.517,0,0,0,6,2.447A3.517,3.517,0,0,0,12,19.5V19h3v.5a3.5,3.5,0,0,0,7,0V19h2ZM19.559,7a1,1,0,0,1,.948.684L21.613,11H17V7ZM2,4A1,1,0,0,1,3,3H14a1,1,0,0,1,1,1V17H2ZM3.5,21A1.5,1.5,0,0,1,2,19.5V19H5v.5A1.5,1.5,0,0,1,3.5,21ZM10,19.5a1.5,1.5,0,0,1-3,0V19h3Zm10,0a1.5,1.5,0,0,1-3,0V19h3ZM17,17V13h5v4Z"/></svg>
            <h6 class="text-lg font-black ml-3">{{ __('Transfer') }}</h6>
        </div>
        <div class="flex-1 flex flex-col p-4">
            <div class="mb-8">
                <div class="mb-4">
                    <span class="font-medium text-sm mb-1 block">{{ __('Date') }}</span>
                    <div class="border px-2 py-1.5 rounded">
                        <p id="date" class="text-sm"></p>
                    </div>
                </div>
                <div class="mb-4">
                    <span class="font-medium text-sm mb-1 block">{{ __('Serial No') }}</span>
                    <div class="border px-2 py-1.5 rounded">
                        <p id="serial-no" class="text-sm"></p>
                    </div>
                </div>
                <div class="mb-4">
                    <span class="font-medium text-sm mb-1 block">{{ __('Assigned Order ID') }}</span>
                    <div class="border px-2 py-1.5 rounded">
                        <p id="order-id" class="text-sm"></p>
                    </div>
                </div>
                <div class="mb-4">
                    <span class="font-medium text-sm mb-1 block">{{ __('Assigned Driver') }}</span>
                    <select name="driver" id="driver" class="border border-slate-200 px-2 py-1.5 rounded w-full text-sm">
                        <option value="">{{ __('Select a driver') }}</option>
                        @foreach ($drivers as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <span class="font-medium text-sm mb-1 block">{{ __('Branch to Transfer') }}</span>
                    <select name="branch" id="branch" class="border border-slate-200 px-2 py-1.5 rounded w-full text-sm">
                        <option value="">{{ __('Select a branch') }}</option>
                        @foreach ($branches as $key => $val)
                            <option value="{{ $key }}">{{ $val }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex gap-x-6">
                <div class="flex-1">
                    <button type="button" class="w-full p-2 rounded-md text-red-600 text-sm font-medium transiton-all duration-300 hover:bg-red-50" id="no-btn">{{ __('No') }}</button>
                </div>
                <div class="flex-1 flex">
                    <a href="" class="w-full p-2 rounded-md bg-blue-600 text-white text-sm font-medium transiton-all duration-300 text-center hover:bg-blue-700" id="yes-btn">{{ __('Confirm') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-app.modal.base-modal>

@push('scripts')
<script>
    $('#transfer-modal #no-btn').on('click', function() {
        $('#transfer-modal').removeClass('show-modal')
    })

    $('#transfer-modal #driver').on('change', function() {
        updateTransferUrl()
    })
    
    $('#transfer-modal #branch').on('change', function() {
        updateTransferUrl()
    })

    function updateTransferUrl() {
        let driver = $('#transfer-modal #driver').val()
        let branch = $('#transfer-modal #branch').val()

        if (driver == '' || branch == '') {
            $('#transfer-modal #yes-btn').addClass('hidden')
            return
        }
        
        let url = $('#transfer-modal #yes-btn').attr('href')
        url = `${url}?driver=${driver}&branch=${branch}`
        $('#transfer-modal #yes-btn').attr('href', url)

        $('#transfer-modal #yes-btn').removeClass('hidden')
    }
</script>
@endpush