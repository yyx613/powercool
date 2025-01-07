<x-app.modal.base-modal id="stock-out-to-hi-ten-modal">
    <div class="aspect-[2/1] flex flex-col">
        <div class="py-2 px-4 bg-orange-100 flex items-center">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M21,12h-3c-1.103,0-2,.897-2,2s-.897,2-2,2h-4c-1.103,0-2-.897-2-2s-.897-2-2-2H3c-1.654,0-3,1.346-3,3v4c0,2.757,2.243,5,5,5h14c2.757,0,5-2.243,5-5v-4c0-1.654-1.346-3-3-3Zm1,7c0,1.654-1.346,3-3,3H5c-1.654,0-3-1.346-3-3v-4c0-.552,.448-1,1-1l3-.002v.002c0,2.206,1.794,4,4,4h4c2.206,0,4-1.794,4-4h3c.552,0,1,.448,1,1v4ZM7.293,5.293c-.391-.391-.391-1.023,0-1.414L10.586,.586C10.972,.2,11.479,.006,11.986,.003l.014-.003,.014,.003c.508,.003,1.014,.197,1.4,.583l3.293,3.293c.391,.391,.391,1.023,0,1.414-.195,.195-.451,.293-.707,.293s-.512-.098-.707-.293l-2.293-2.293v7c0,.553-.447,1-1,1s-1-.447-1-1V3l-2.293,2.293c-.391,.391-1.023,.391-1.414,0Z"/></svg>
            <h6 class="text-lg font-black ml-3">{{ __('Stock Out To Hi-Ten') }}</h6>
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
                    <span class="font-medium text-sm mb-1 block">{{ __('Stock Out To') }}</span>
                    <div class="border px-2 py-1.5 rounded">
                        <p id="stock-out-to" class="text-sm"></p>
                    </div>
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
    $('#stock-out-to-hi-ten-modal #no-btn').on('click', function() {
        $('#stock-out-to-hi-ten-modal').removeClass('show-modal')
    })
</script>
@endpush
