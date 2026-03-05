<x-app.modal.base-modal id="pdf-type-modal">
    <div class="flex flex-col">
        <div class="py-2 px-4 bg-gray-100 flex justify-between items-center">
            <h6 class="text-lg font-black">{{ __('Select PDF Type') }}</h6>
            <button type="button" class="text-gray-500 hover:text-gray-700" id="close-pdf-modal-btn">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="p-4">
            <p class="text-slate-500 text-sm mb-4">{{ __('Choose a PDF format to generate:') }}</p>
            <div class="space-y-2">
                <!-- Service Form - Active -->
                <a href="#" id="pdf-service-form-link" target="_blank" class="block w-full p-3 rounded-md border border-gray-200 hover:bg-blue-50 hover:border-blue-300 transition-all duration-200">
                    <div class="flex items-center gap-x-3">
                        <div class="p-2 bg-blue-100 rounded-md">
                            <svg class="h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <span class="font-medium text-gray-800">{{ __('Service Form') }}</span>
                            <p class="text-xs text-gray-500">{{ __('Standard service requisition form') }}</p>
                        </div>
                    </div>
                </a>

                <!-- Service Quotation - Active -->
                <a href="#" id="pdf-quotation-link" target="_blank" class="block w-full p-3 rounded-md border border-gray-200 hover:bg-green-50 hover:border-green-300 transition-all duration-200">
                    <div class="flex items-center gap-x-3">
                        <div class="p-2 bg-green-100 rounded-md">
                            <svg class="h-5 w-5 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <span class="font-medium text-gray-800">{{ __('Quotation') }}</span>
                            <p class="text-xs text-gray-500">{{ __('Quotation with line items and pricing (auto-selects PowerCool/HiTen based on customer)') }}</p>
                        </div>
                    </div>
                </a>

                <!-- Cash Sale - Active -->
                <a href="#" id="pdf-cash-sale-link" target="_blank" class="block w-full p-3 rounded-md border border-gray-200 hover:bg-amber-50 hover:border-amber-300 transition-all duration-200">
                    <div class="flex items-center gap-x-3">
                        <div class="p-2 bg-amber-100 rounded-md">
                            <svg class="h-5 w-5 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <span class="font-medium text-gray-800">{{ __('Cash Sale') }}</span>
                            <p class="text-xs text-gray-500">{{ __('Cash sale invoice (auto-selects PowerCool/HiTen based on customer)') }}</p>
                        </div>
                    </div>
                </a>

                <!-- Invoice - Active -->
                <a href="#" id="pdf-invoice-link" target="_blank" class="block w-full p-3 rounded-md border border-gray-200 hover:bg-purple-50 hover:border-purple-300 transition-all duration-200">
                    <div class="flex items-center gap-x-3">
                        <div class="p-2 bg-purple-100 rounded-md">
                            <svg class="h-5 w-5 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <span class="font-medium text-gray-800">{{ __('Invoice') }}</span>
                            <p class="text-xs text-gray-500">{{ __('Invoice document (auto-selects PowerCool/HiTen based on customer)') }}</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app.modal.base-modal>

@push('scripts')
<script>
    $('#pdf-type-modal #close-pdf-modal-btn').on('click', function() {
        $('#pdf-type-modal').removeClass('show-modal')
    })

    // Close modal when clicking outside
    $('#pdf-type-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).removeClass('show-modal')
        }
    })
</script>
@endpush
