@props([
    'histories' => [],
])

<x-app.modal.base-modal id="extend-due-date-history-modal">
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
            <h6 class="text-lg font-black ml-3">{{ __('Extend History') }}</h6>
        </div>
        <div class="flex-1 flex flex-col p-4">
            <div class="mb-4 overflow-y-auto max-h-[60vh]">
                @foreach ($histories as $h)
                    <div class="border border-slate-200 px-2 py-1.5 mb-2 rounded">
                        <div class="flex items-center gap-x-2">
                            <p class="font-semibold text-center text-md">{{ $h->old_date }}</p>
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24"
                                width="512" height="512">
                                <path
                                    d="M13.1,19a1,1,0,0,1-.7-1.71L17,12.71a1,1,0,0,0,0-1.42L12.4,6.71a1,1,0,0,1,0-1.42,1,1,0,0,1,1.41,0L18.4,9.88a3,3,0,0,1,0,4.24l-4.59,4.59A1,1,0,0,1,13.1,19Z" />
                                <path
                                    d="M6.1,19a1,1,0,0,1-.7-1.71L10.69,12,5.4,6.71a1,1,0,0,1,0-1.42,1,1,0,0,1,1.41,0l6,6a1,1,0,0,1,0,1.42l-6,6A1,1,0,0,1,6.1,19Z" />
                            </svg>
                            <p class="font-semibold text-center text-md">{{ $h->new_date }}</p>
                        </div>
                        <div class="mt-2">
                            <div class="flex items-center gap-x-4">
                                <div class="flex items-center gap-x-2">
                                    <svg class="h-3 w-3 fill-slate-500" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24" width="512" height="512">
                                        <g id="_01_align_center" data-name="01 align center">
                                            <path
                                                d="M21,24H19V18.957A2.96,2.96,0,0,0,16.043,16H7.957A2.96,2.96,0,0,0,5,18.957V24H3V18.957A4.963,4.963,0,0,1,7.957,14h8.086A4.963,4.963,0,0,1,21,18.957Z" />
                                            <path
                                                d="M12,12a6,6,0,1,1,6-6A6.006,6.006,0,0,1,12,12ZM12,2a4,4,0,1,0,4,4A4,4,0,0,0,12,2Z" />
                                        </g>
                                    </svg>
                                    <p class="text-sm text-slate-500">{{ $h->doneBy->name }}</p>
                                </div>
                                <div class="flex items-center gap-x-2">
                                    <svg class="h-3 w-3 fill-slate-500" xmlns="http://www.w3.org/2000/svg"
                                        id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512"
                                        height="512">
                                        <path
                                            d="M12,24C5.383,24,0,18.617,0,12S5.383,0,12,0s12,5.383,12,12-5.383,12-12,12Zm0-22C6.486,2,2,6.486,2,12s4.486,10,10,10,10-4.486,10-10S17.514,2,12,2Zm2.5,14.33c.479-.276,.643-.888,.366-1.366l-1.866-3.232V6c0-.552-.447-1-1-1s-1,.448-1,1v6c0,.176,.046,.348,.134,.5l2,3.464c.186,.321,.521,.5,.867,.5,.17,0,.342-.043,.499-.134Z" />
                                    </svg>
                                    <p class="text-sm text-slate-500">{{ $h->created_at }}</p>
                                </div>
                            </div>
                            @if ($h->remark != null)
                                <div class="flex items-center gap-x-2 border-t border-slate-200 pt-1.5 mt-1.5">
                                    <p class="text-sm text-slate-500 leading-snug">{{ $h->remark }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
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
        $('#extend-due-date-history-modal #ok-btn').on('click', function() {
            $('#extend-due-date-history-modal').removeClass('show-modal')
        })
    </script>
@endpush
