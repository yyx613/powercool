<x-app.modal.base-modal id="to-material-use-modal">
    <div class="aspect-[2/1] flex flex-col">
        <div class="py-2 px-4 bg-gray-100">
            <h6 class="text-lg font-black">{{ __('To B.O.M Material Use') }}</h6>
        </div>
        <div class="flex-1 flex flex-col p-4">
            <div class="flex-1">
                <span class="font-medium text-sm mb-1 block">{{ __('Select a product to B.O.M Material Use') }}</span>
                <x-app.input.select name="product" id="product" class="w-full">
                    <option value="">{{ __('Select a product') }}</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">({{ $product->sku }}) {{ $product->model_name }}</option>
                    @endforeach
                </x-app.input.select>
            </div>
            <div class="flex gap-x-6">
                <div class="flex-1">
                    <button type="button"
                        class="w-full p-2 rounded-md text-red-600 text-sm font-medium transiton-all duration-300 hover:bg-red-50"
                        id="no-btn">{{ __('No') }}</button>
                </div>
                <div class="flex-1 flex">
                    <button
                        class="w-full p-2 rounded-md bg-blue-600 text-white text-sm font-medium transiton-all duration-300 text-center hidden hover:bg-blue-700"
                        id="yes-btn">{{ __('Yes') }}</button>
                </div>
            </div>
        </div>
    </div>
</x-app.modal.base-modal>

@push('scripts')
    <script>
        $('#to-material-use-modal #no-btn').on('click', function() {
            $('#to-material-use-modal').removeClass('show-modal')
        })
        $('#to-material-use-modal #yes-btn').one('click', function(e) {
            e.preventDefault()

            let productId = $('#to-material-use-modal select').val()
            if (productId == 'null' || productId == '') return

            let id = $(this).data('sale-production-request-id')
            let url = "{{ config('app.url') }}"
            url = `${url}/production-request/to-material-use/${id}/${productId}`

            window.location.href = url
        })
        $('#to-material-use-modal select[name="product"]').change(function() {
            let val = $(this).val()

            if (val != 'null' && val != '') {
                $('#to-material-use-modal #yes-btn').removeClass('hidden')
            } else {
                $('#to-material-use-modal #yes-btn').addClass('hidden')
            }
        })
    </script>
@endpush
