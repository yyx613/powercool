<x-app.modal.base-modal id="milestone-rejections-modal">
    <div class="aspect-[2/1] flex flex-col">
        <div class="py-2 px-4 bg-slate-100 flex items-center">
            <svg class="h-5 w-5" id="Layer_1" height="512" viewBox="0 0 24 24" width="512"
                xmlns="http://www.w3.org/2000/svg" data-name="Layer 1">
                <path d="m9 24h-8a1 1 0 0 1 0-2h8a1 1 0 0 1 0 2z" />
                <path d="m7 20h-6a1 1 0 0 1 0-2h6a1 1 0 0 1 0 2z" />
                <path d="m5 16h-4a1 1 0 0 1 0-2h4a1 1 0 0 1 0 2z" />
                <path
                    d="m13 23.955a1 1 0 0 1 -.089-2 10 10 0 1 0 -10.87-10.865 1 1 0 0 1 -1.992-.18 12 12 0 0 1 23.951 1.09 11.934 11.934 0 0 1 -10.91 11.951c-.03.003-.061.004-.09.004z" />
                <path
                    d="m12 6a1 1 0 0 0 -1 1v5a1 1 0 0 0 .293.707l3 3a1 1 0 0 0 1.414-1.414l-2.707-2.707v-4.586a1 1 0 0 0 -1-1z" />
            </svg>
            <h6 class="text-lg font-black ml-3">{{ __('Rejection History') }}</h6>
        </div>
        <div class="flex-1 flex flex-col p-4">
            <div class="mb-4 flex items-start">
                <span class="text-sm mr-1 text-slate-500">Milestone: </span>
                <h6 class="text-sm font-semibold" id="milestone"></h6>
            </div>
            <div class="mb-4 overflow-y-auto max-h-[60vh]" id="record-container">

                <div class="mb-4 border border-slate-200 px-2 py-1.5 rounded hidden" id="record-template">
                    <div class="flex gap-x-2">
                        <div class="flex-1 flex flex-col">
                            <span class="text-xs text-slate-500">Rejected By: </span>
                            <span class="text-sm" id="rejected-by"></span>
                        </div>
                        <div class="flex-1 flex flex-col">
                            <span class="text-xs text-slate-500">Submitted By: </span>
                            <span class="text-sm" id="submitted-by"></span>
                        </div>
                        <div class="flex-1 flex flex-col">
                            <span class="text-xs text-slate-500">Submitted At: </span>
                            <span class="text-sm" id="submitted-at"></span>
                        </div>
                    </div>
                    <div class="border-t border-slate-200 mt-1.5 pt-1.5" id="material-container">
                        <span class="text-xs text-slate-500">Materials: </span>
                        <div class="hidden mb-2 flex items-center justify-between hover:bg-slate-100 transition duration-200"
                            id="rm-template">
                            <div class="flex items-center">
                                <svg class="h-3 w-3 mr-2 fill-blue-400" xmlns="http://www.w3.org/2000/svg" id="Layer_1"
                                    data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512">
                                    <path
                                        d="m12,0C5.383,0,0,5.383,0,12s5.383,12,12,12,12-5.383,12-12S18.617,0,12,0Zm0,22c-5.514,0-10-4.486-10-10S6.486,2,12,2s10,4.486,10,10-4.486,10-10,10Zm4-10c0,2.209-1.791,4-4,4s-4-1.791-4-4,1.791-4,4-4,4,1.791,4,4Z" />
                                </svg>
                                <span class="text-sm font-medium" id="product"></span>
                            </div>
                            <span class="text-sm" id="qty"></span>
                        </div>
                        <div class="hidden mb-2 hover:bg-slate-100 transition duration-200" id="sp-template">
                            <div class="flex items-center">
                                <svg class="h-3 w-3 mr-2 fill-blue-400" xmlns="http://www.w3.org/2000/svg" id="Layer_1"
                                    data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512">
                                    <path
                                        d="m12,0C5.383,0,0,5.383,0,12s5.383,12,12,12,12-5.383,12-12S18.617,0,12,0Zm0,22c-5.514,0-10-4.486-10-10S6.486,2,12,2s10,4.486,10,10-4.486,10-10,10Zm4-10c0,2.209-1.791,4-4,4s-4-1.791-4-4,1.791-4,4-4,4,1.791,4,4Z" />
                                </svg>
                                <span class="text-sm font-medium" id="product"></span>
                            </div>
                            <div class="mt-1" id="sp-child-container">
                                <div class="flex items-center justify-between" id="sp-child-template">
                                    <span class="text-xs text-slate-500" id="sku"></span>
                                    <span class="text-xs capitalize" id="reason"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



            </div>
            <div class="flex gap-x-6">
                <button type="button"
                    class="w-full p-2 rounded-md bg-emerald-400 text-white text-sm font-medium transiton-all duration-300 text-center hover:bg-emerald-500"
                    id="ok-btn">{{ __('OK') }}</button>
            </div>
        </div>
    </div>
</x-app.modal.base-modal>

@push('scripts')
    <script>
        $('#milestone-rejections-modal #ok-btn').on('click', function() {
            $('#milestone-rejections-modal').removeClass('show-modal')
        })
    </script>
@endpush
