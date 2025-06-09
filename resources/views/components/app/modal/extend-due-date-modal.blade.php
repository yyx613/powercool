@props([
    'production' => null
])

<x-app.modal.base-modal id="extend-due-date-modal">
    <div class="aspect-[2/1] flex flex-col">
        <div class="py-2 px-4 bg-blue-100 flex items-center">
            <svg class="h-5 w-5" id="Layer_1" height="512" viewBox="0 0 24 24" width="512"
                xmlns="http://www.w3.org/2000/svg" data-name="Layer 1">
                <path d="m23 18h-3v-3a1 1 0 0 0 -2 0v3h-3a1 1 0 0 0 0 2h3v3a1 1 0 0 0 2 0v-3h3a1 1 0 0 0 0-2z" />
                <path d="m11 7v4.586l-2.707 2.707a1 1 0 1 0 1.414 1.414l3-3a1 1 0 0 0 .293-.707v-5a1 1 0 0 0 -2 0z" />
                <path
                    d="m14.728 21.624a9.985 9.985 0 1 1 6.9-6.895 1 1 0 1 0 1.924.542 11.989 11.989 0 1 0 -8.276 8.277 1 1 0 1 0 -.544-1.924z" />
            </svg>
            <h6 class="text-lg font-black ml-3">{{ __('Extend Due Date') }}</h6>
        </div>
        <div class="flex-1 flex flex-col p-4">
            <form action="{{ route('production.extend_due_date', ['production' => $production->id]) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <span class="font-medium text-sm mb-1 block">{{ __('New Due Date') }}<span
                            class="text-sm text-red-500">*</span></span>
                    <x-app.input.input name="new_due_date" />
                </div>
                <div class="mb-8">
                    <span class="font-medium text-sm mb-1 block">{{ __('Remark') }}</span>
                    <x-app.input.textarea name="remark" id="remark"  />
                </div>
                <div class="flex gap-x-6">
                    <div class="flex-1">
                        <button type="button"
                            class="w-full p-2 rounded-md text-red-600 text-sm font-medium transiton-all duration-300 hover:bg-red-50"
                            id="no-btn">{{ __('No') }}</button>
                    </div>
                    <div class="flex-1 flex">
                        <button type="submit"
                            class="w-full p-2 rounded-md bg-blue-600 text-white text-sm font-medium transiton-all duration-300 text-center hover:bg-blue-700"
                            id="yes-btn">{{ __('Confirm') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app.modal.base-modal>

@push('scripts')
    <script>
        $('#extend-due-date-modal #no-btn').on('click', function() {
            $('#extend-due-date-modal').removeClass('show-modal')
        })

        $('input[name="new_due_date"]').daterangepicker(datepickerParam)
        $('input[name="new_due_date"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });
    </script>
@endpush
