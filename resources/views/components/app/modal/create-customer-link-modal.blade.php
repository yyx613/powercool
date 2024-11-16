<x-app.modal.base-modal id="create-customer-link-modal">
    <div class="aspect-[2/1] flex flex-col">
        <div class="border-b py-2 px-4 bg-blue-100 flex items-center">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M19.333,14.667a4.66,4.66,0,0,0-3.839,2.024L8.985,13.752a4.574,4.574,0,0,0,.005-3.488l6.5-2.954a4.66,4.66,0,1,0-.827-2.643,4.633,4.633,0,0,0,.08.786L7.833,8.593a4.668,4.668,0,1,0-.015,6.827l6.928,3.128a4.736,4.736,0,0,0-.079.785,4.667,4.667,0,1,0,4.666-4.666ZM19.333,2a2.667,2.667,0,1,1-2.666,2.667A2.669,2.669,0,0,1,19.333,2ZM4.667,14.667A2.667,2.667,0,1,1,7.333,12,2.67,2.67,0,0,1,4.667,14.667ZM19.333,22A2.667,2.667,0,1,1,22,19.333,2.669,2.669,0,0,1,19.333,22Z"/></svg>
            <h6 class="text-lg font-black ml-3">{{ __('Create Customer Link') }}</h6>
        </div>
        <div class="flex-1 flex flex-col p-4">
            <div class="mb-8 flex-1">
                <div>
                    <span class="font-medium mb-1 block">{{ __('Choose a branch for the customer') }}</span>
                    @foreach ($branches as $key => $val)
                        <div class="flex items-center gap-x-2 my-1.5">
                            <input type="radio" id="{{ $key }}" value="{{ $key }}" name="branch" class="border-slate-400" data-url="{{ $links[$key] }}">
                            <label for="{{ $key }}" class="text-sm">{{ $val }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="flex gap-x-6">
                <div class="flex-1">
                    <button type="button" class="w-full p-2 rounded-md text-red-600 text-sm font-medium transiton-all duration-300 hover:bg-red-50" id="no-btn">{{ __('Cancel') }}</button>
                </div>
                <div class="flex-1 flex">
                    <button class="w-full p-2 rounded-md bg-blue-600 text-white text-sm font-medium transiton-all duration-300 text-center hidden hover:bg-blue-700" id="yes-btn">{{ __('Copy Link') }}</button>
                </div>
            </div>
        </div>
    </div>
</x-app.modal.base-modal>

@push('scripts')
<script>
    $('#create-customer-link-modal #no-btn').on('click', function() {
        $('#create-customer-link-modal input[name="branch"]').prop('checked', false)
        $('#create-customer-link-modal #yes-btn').addClass('hidden')
        $('#create-customer-link-modal').removeClass('show-modal')
    })
    $('#create-customer-link-modal #yes-btn').on('click', function(e) {
        let url = $('#create-customer-link-modal input[name="branch"]:checked').data('url')
        navigator.clipboard.writeText(url);

        $('#create-customer-link-modal #yes-btn').text('Copied')
        
        setTimeout(() => {
            $('#create-customer-link-modal #yes-btn').text('Copy Link')
        }, 1500);
    })
    $('#create-customer-link-modal input[name="branch"]').on('change', function() {
        $('#create-customer-link-modal #yes-btn').removeClass('hidden')
    })
</script>
@endpush