<x-app.modal.base-modal id="stock-in-modal">
    <div class="aspect-[2/1] flex flex-col">
        <form action="{{ route('grn.stock_in') }}" method="POST">
            @csrf
            <x-app.input.input name="sku" id="sku" value="{{ $sku }}" class="hidden" />

            <div class="py-2 px-4 bg-purple-100 flex items-center">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M21,12h-3c-1.103,0-2,.897-2,2s-.897,2-2,2h-4c-1.103,0-2-.897-2-2s-.897-2-2-2H3c-1.654,0-3,1.346-3,3v4c0,2.757,2.243,5,5,5h14c2.757,0,5-2.243,5-5v-4c0-1.654-1.346-3-3-3Zm1,7c0,1.654-1.346,3-3,3H5c-1.654,0-3-1.346-3-3v-4c0-.552,.448-1,1-1l3-.002v.002c0,2.206,1.794,4,4,4h4c2.206,0,4-1.794,4-4h3c.552,0,1,.448,1,1v4ZM7.293,7.121c-.391-.391-.391-1.023,0-1.414s1.023-.391,1.414,0l2.293,2.293V1c0-.553,.447-1,1-1s1,.447,1,1v7l2.293-2.293c.391-.391,1.023-.391,1.414,0s.391,1.023,0,1.414l-3.293,3.293c-.387,.387-.896,.582-1.405,.584l-.009,.002-.009-.002c-.509-.002-1.018-.197-1.405-.584l-3.293-3.293Z"/></svg>
                <h6 class="text-lg font-black ml-3">{{ __('Stock In') }}</h6>
            </div>
            <div class="flex-1 flex flex-col p-4">
                <div class="mb-8">
                    @foreach($grns as $grn) 
                        <div class="mb-4">
                            <div class="mb-0.5 flex items-start justify-between">
                                <h6 class="text-sm">{{ $grn->product->model_name }}</h6>
                                <span class="text-xs">{{ __('Stock In UOM') }}: {{ $grn->product->uomUnit->name ?? '-' }}</span>
                            </div>
                            <div>
                                <x-app.input.input name="product[]" id="product[]" value="{{ $grn->product_id }}" class="hidden" />
                                @if ($grn->product->type == 1 || $grn->product->is_sparepart == true)
                                    <x-app.input.multi-input name="serial_no_{{ $grn->product_id }}" id="serial_no_{{ $grn->product_id }}" placeholder="Enter serial no" />
                                @else
                                    <x-app.input.input name="qty_{{ $grn->product_id }}" id="qty_{{ $grn->product_id }}" class="text-sm int-input" placeholder="Enter quantity to stock in"/>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="flex gap-x-6">
                    <div class="flex-1">
                        <button type="button" class="w-full p-2 rounded-md text-red-600 text-sm font-medium transiton-all duration-300 hover:bg-red-50" id="no-btn">{{ __('No') }}</button>
                    </div>
                    <div class="flex-1 flex">
                        <button type="submit" class="w-full p-2 rounded-md bg-blue-600 text-white text-sm font-medium transiton-all duration-300 text-center hover:bg-blue-700" id="yes-btn">{{ __('Confirm') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-app.modal.base-modal>

@push('scripts')
<script>
    $('#stock-in-modal #no-btn').on('click', function() {
        $('#stock-in-modal').removeClass('show-modal')
    })
</script>
@endpush