<x-app.modal.base-modal id="production-milestone-modal">
    <div class="aspect-[2/1] flex flex-col">
        <div class="py-2 px-4 bg-slate-100 flex items-center">
            <h6 class="text-lg font-black">{{ __('Check In Milestone') }}</h6>
        </div>
        <div class="flex-1 flex flex-col p-4">
            <div class="flex-1 flex flex-col gap-4">
                <div>
                    <span class="font-medium text-sm mb-1 block">{{ __('Date') }}</span>
                    <div class="border px-2 py-1.5 rounded">
                        <p id="date" class="text-sm"></p>
                    </div>
                </div>
                <div id="serial-no-container">
                    <span class="font-medium text-sm block mb-2">{{ __('Materials') }}</span>
                    <div class="border px-2 py-1.5 rounded overflow-y-auto max-h-64" id="serial-no-selection-container">
                        <!-- Raw Material Template -->
                        <div class="mb-4 hidden" id="rm-template">
                            <div class="mb-2 flex flex-col">
                                <span class="font-medium text-sm" id="product-name"></span>
                                <span class="font-medium text-xs text-slate-400" id="qty-needed"></span>
                            </div>
                            <p class="text-sm text-blue-600">{{ __('Not spare part, selection is not required') }}</p>
                        </div>
                        <!-- Sparepart Template -->
                        <div class="mb-4 hidden" id="sp-template">
                            <div class="mb-2 flex flex-col">
                                <span class="font-medium text-sm" id="product-name"></span>
                                <span class="font-medium text-xs text-slate-400" id="qty-needed"></span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 sp-serial-no-container">
                                <div class="flex items-center gap-x-2 hidden sp-serial-no-template">
                                    <input type="checkbox" name="serial_no[]" id="" class="border-slate-500 rounded" data-mu-id="">
                                    <label for="" class="text-sm"></label>
                                </div>
                            </div>
                            <x-app.message.error id="materials_err" class="mt-2" data-mu-id="" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex gap-x-6 mt-6">
                <button type="button" class="w-full p-2 rounded-md text-red-600 text-sm font-medium transiton-all duration-300 hover:bg-red-50" id="no-btn">{{ __('No') }}</button>
                <button type="button" class="w-full p-2 rounded-md bg-blue-600 text-white text-sm font-medium transiton-all duration-300 text-center hover:bg-blue-700" id="yes-btn">{{ __('Confirm') }}</button>
            </div>
        </div>
    </div>
</x-app.modal.base-modal>

@push('scripts')
<script>
    FORM_CAN_SUBMIT = true

    $('#production-milestone-modal #no-btn').on('click', function() {
        $('#production-milestone-modal').removeClass('show-modal')
    })
    $('#production-milestone-modal #yes-btn').on('click', function(e) {
        e.preventDefault()

        if (!FORM_CAN_SUBMIT) return

        FORM_CAN_SUBMIT = false

        $('.err_msg').addClass('hidden') // Remove error messages

        // Prepare data
        let materials = {}
        $('#serial-no-container input[name="serial_no[]"]').each(function(i, obj) {
            if ($(this).is(':checked')) {
                if (materials[$(this).data('product-id')] === undefined) materials[$(this).data('product-id')] = []

                let vals = materials[$(this).data('product-id')]
                vals.push($(this).attr('id'))
                materials[$(this).data('product-id')] = vals

                if (PRODUCTION_MILESTONE_MATERIALS[$('#production-milestone-modal #yes-btn').attr('data-id')] === undefined) {
                    PRODUCTION_MILESTONE_MATERIALS[$('#production-milestone-modal #yes-btn').attr('data-id')] = []
                }
                PRODUCTION_MILESTONE_MATERIALS[$('#production-milestone-modal #yes-btn').attr('data-id')] = PRODUCTION_MILESTONE_MATERIALS[$('#production-milestone-modal #yes-btn').attr('data-id')].concat(vals.map(function(item) {
                    return parseInt(item)
                }))
            }
        })
        // Submit
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route("production.check_in_milestone") }}',
            type: 'POST',
            data: {
                'production_milestone_id': $('#production-milestone-modal #yes-btn').attr('data-id'),
                'datetime': $('#production-milestone-modal #date').text(),
                'materials': materials,
            },
            success: function(res) {
                $('#production-milestone-modal').removeClass('show-modal')

                $(`.ms-row[data-id="${$('#production-milestone-modal #yes-btn').attr('data-id')}"] .not-completed-icon`).addClass('hidden')
                $(`.ms-row[data-id="${$('#production-milestone-modal #yes-btn').attr('data-id')}"] .completed-icon`).removeClass('hidden')
                $(`.ms-row[data-id="${$('#production-milestone-modal #yes-btn').attr('data-id')}"]`).data('completed', true)
                $(`#status`).text(res.status)
                $(`#progress`).text(`${res.progress}%`)

                FORM_CAN_SUBMIT = true
            },
            error: function(err) {
                setTimeout(() => {
                    if (err.status == StatusCodes.UNPROCESSABLE_ENTITY) {
                        let errors = err.responseJSON.errors

                        for (const key in errors) {
                            if (!key.includes('.')) {
                                $(`#production-milestone-modal #${key}_err`).find('p').text(errors[key])
                                $(`#production-milestone-modal #${key}_err`).removeClass('hidden')
                            } else {
                                let field = key.split('.')[0]
                                let idx = key.split('.')[1]

                                $(`#production-milestone-modal #${field}_err[data-product-id="${idx}"]`).find('p').text(errors[key])
                                $(`#production-milestone-modal #${field}_err[data-product-id="${idx}"]`).removeClass('hidden')
                            }
                        }
                    }
                    FORM_CAN_SUBMIT = true
                }, 300);
            },
        });
    })
</script>
@endpush
