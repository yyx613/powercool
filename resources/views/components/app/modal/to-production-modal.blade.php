<x-app.modal.base-modal id="to-production-modal">
    <div class="aspect-[2/1] flex flex-col">
        <div class="border-b py-2 px-4 bg-gray-100">
            <h6 class="text-lg font-black">To Production</h6>
        </div>
        <div class="flex-1 flex flex-col p-4">
            <div class="flex-1">
                <span class="font-medium text-sm mb-1 block">Select product to build</span>
                <x-app.input.select name="product" id="product" class="w-full">
                    <option value="">Select a product</option>
                </x-app.input.select>
            </div>
            <div class="flex gap-x-6">
                <div class="flex-1">
                    <button type="button" class="w-full p-2 rounded-md text-red-600 text-sm font-medium transiton-all duration-300 hover:bg-red-50" id="no-btn">No</button>
                </div>
                <div class="flex-1 flex">
                    <button class="w-full p-2 rounded-md bg-blue-600 text-white text-sm font-medium transiton-all duration-300 text-center hover:bg-blue-700" id="yes-btn">Yes</button>
                </div>
            </div>
        </div>
    </div>
</x-app.modal.base-modal>

@push('scripts')
<script>
    $('#to-production-modal #no-btn').on('click', function() {
        $('#to-production-modal').removeClass('show-modal')
    })
    $('#to-production-modal #yes-btn').one('click', function(e) {
        e.preventDefault()

        let productId = $('#to-production-modal select').val()
        if (productId == 'null') return
        
        let id = $(this).data('id')
        let url = "{{ config('app.url') }}"
        url = `${url}/sale/to-production/${id}?product=${productId}`

        window.location.href = url
    })
</script>
@endpush