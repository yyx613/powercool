<x-app.modal.base-modal id="submit-credit-debit-note-modal">
    <div class="aspect-[2/1] flex flex-col">
        <div class="border-b py-2 px-4 bg-gray-100">
            <h6 class="text-lg font-black">{{ __('Credit Note') }}</h6>
        </div>
        <div class="flex-1 flex flex-col p-4">
            <div class="content flex flex-col" style="max-height: calc(100vh - 8rem); overflow-y: auto;">
                <div id="selected-orders-list" class="mb-4">
                    <h6 class="text-md font-medium">{{ __('E-Invoice UUID') }}</h6>
                    <ul></ul> 
                </div>
                <div id="selected-orders-list" class="mb-2">
                    <h6 class="text-md font-medium">{{ __('Invoice Item') }}</h6>
                    <ul></ul>
                </div>
                <div class="flex-1">
                    <table id="invoice-data-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>
                                    {{ __('No.') }}
                                </th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Price') }}</th>
                                <th>{{ __('Quantity') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="text-center pb-2">
                                <td>
                                    1
                                </td>
                                <td class="text-center">
                                    Item A
                                </td>
                                <td style="text-align: center;">
                                    <div class="bg-white rounded-md border border-gray-300 overflow-hidden p-1 text-center" style="width: 50%; display: inline-block;">
                                        <input type="text" name="nane" id="id" placeholder="" value="RM 20.00"  autocomplete="off" class="text-sm p-0 w-full border-none border-transparent focus:border-transparent focus:ring-0">
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <div class="bg-white rounded-md border border-gray-300 overflow-hidden p-1" style="width: 50%; display: inline-block;">
                                        <input type="text" name="nane" id="id" placeholder="" value="3"  autocomplete="off" class="text-sm p-0 w-full border-none border-transparent focus:border-transparent focus:ring-0">
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>
                                    
                                </td>
                                <td>
                                    
                                </td>
                                <td style="text-align: center;" class="font-black pt-2">
                                    Total
                                </td>
                                <td style="text-align: center;" class="font-black pt-2">
                                    RM 180.00
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
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
    $('#submit-credit-debit-note-modal #no-btn').on('click', function() {
        $('#submit-credit-debit-note-modal').removeClass('show-modal')
    })
    

</script>
@endpush