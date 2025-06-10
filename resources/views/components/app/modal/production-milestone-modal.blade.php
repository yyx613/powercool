<x-app.modal.base-modal id="production-milestone-modal">
    <div class="aspect-[2/1] flex flex-col">
        <div class="py-2 px-4 bg-slate-100 flex items-center">
            <h6 class="text-lg font-black">{{ __('Check In Milestone') }}</h6>
        </div>
        <div class="flex-1 flex flex-col p-4">
            <div class="flex-1 flex flex-col gap-4">
                <div id="checked-in-by-container" class="hidden">
                    <span class="font-medium text-sm mb-1 block">{{ __('Checked In By') }}</span>
                    <div class="border px-2 py-1.5 rounded">
                        <p id="checked-in-by" class="text-sm"></p>
                    </div>
                </div>
                <div>
                    <span class="font-medium text-sm mb-1 block">{{ __('Date') }}</span>
                    <div class="border px-2 py-1.5 rounded">
                        <p id="date" class="text-sm"></p>
                    </div>
                </div>
                <div id="remark-container">
                    <span class="font-medium text-sm mb-1 block">{{ __('Remark') }}</span>
                    <x-app.input.textarea name="remark" id="remark" />
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
                                <div class="flex gap-2 items-center justify-between">
                                    <span class="font-medium text-sm" id="product-name"></span>
                                    <x-app.input.production-milestone-search-input />
                                </div>
                                <span class="font-medium text-xs text-slate-400" id="qty-needed"></span>
                            </div>
                            <div class="flex flex-col gap-2 sp-serial-no-container">
                                <div class="flex items-center justify-between gap-x-2 hidden sp-serial-no-template">
                                    <div class="first-half">
                                        <input type="checkbox" name="serial_no[]" id=""
                                            class="border-slate-500 rounded" data-mu-id="">
                                        <label for="" class="text-sm"></label>
                                    </div>
                                    <div class="flex gap-x-2 second-half">
                                        <div class="flex items-center gap-x-1">
                                            <input type="radio" name="reason" id=""
                                                class="border-slate-500 rounded-full" value="install-wrongly">
                                            <label for="" class="text-xs">{{ __('Install Wrongly') }}</label>
                                        </div>
                                        <div class="flex items-center gap-x-1">
                                            <input type="radio" name="reason" id=""
                                                class="border-slate-500 rounded-full" value="broken">
                                            <label for="" class="text-xs">{{ __('Broken') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <x-app.message.error id="materials_err" class="mt-2" data-mu-id="" />
                        </div>
                    </div>
                </div>
            </div>
            <x-app.message.error id="general_err" class="mt-2" />
            <div class="flex gap-x-6 mt-6">
                <button type="button"
                    class="w-full p-2 rounded-md text-red-600 text-sm font-medium transiton-all duration-300 hover:bg-red-50"
                    id="no-btn">{{ __('No') }}</button>
                <button type="button"
                    class="w-full p-2 rounded-md bg-blue-600 text-white text-sm font-medium transiton-all duration-300 text-center hover:bg-blue-700"
                    id="yes-btn">{{ __('Confirm') }}</button>
                <button type="button"
                    class="hidden w-full p-2 rounded-md bg-red-600 text-white text-sm font-medium transiton-all duration-300 text-center hover:bg-red-700"
                    id="reject-btn">{{ __('Reject') }}</button>
            </div>
        </div>
    </div>
</x-app.modal.base-modal>

@push('scripts')
    <script>
        FORM_CAN_SUBMIT = true

        $('#production-milestone-modal #no-btn').on('click', function() {
            $('#production-milestone-modal').removeClass('show-modal')
            $('#production-milestone-modal .err_msg').addClass('hidden') // Remove error messages
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
                    if (materials[$(this).data('product-id')] === undefined) materials[$(this).data(
                        'product-id')] = []

                    let vals = materials[$(this).data('product-id')]
                    vals.push($(this).attr('id'))
                    materials[$(this).data('product-id')] = vals

                    if (PRODUCTION_MILESTONE_MATERIALS[$('#production-milestone-modal #yes-btn').attr(
                            'data-id')] === undefined) {
                        PRODUCTION_MILESTONE_MATERIALS[$('#production-milestone-modal #yes-btn').attr(
                            'data-id')] = []
                    }
                    PRODUCTION_MILESTONE_MATERIALS[$('#production-milestone-modal #yes-btn').attr(
                        'data-id')] = PRODUCTION_MILESTONE_MATERIALS[$(
                        '#production-milestone-modal #yes-btn').attr('data-id')].concat(vals.map(
                        function(item) {
                            return parseInt(item)
                        }))
                }
            })
            // Submit
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('production.check_in_milestone') }}',
                type: 'POST',
                data: {
                    'production_milestone_id': $('#production-milestone-modal #yes-btn').attr('data-id'),
                    'datetime': $('#production-milestone-modal #date').text(),
                    'materials': materials,
                },
                success: function(res) {
                    location.reload()
                    // $('#production-milestone-modal').removeClass('show-modal')

                    // $(`.ms-row[data-id="${$('#production-milestone-modal #yes-btn').attr('data-id')}"] .not-completed-icon`)
                    //     .addClass('hidden')
                    // $(`.ms-row[data-id="${$('#production-milestone-modal #yes-btn').attr('data-id')}"] .completed-icon`)
                    //     .removeClass('hidden')
                    // $(`.ms-row[data-id="${$('#production-milestone-modal #yes-btn').attr('data-id')}"]`)
                    //     .data('completed', true)
                    // $(`#status`).text(res.status)
                    // $(`#progress`).text(`${res.progress}%`)

                    // FORM_CAN_SUBMIT = true
                },
                error: function(err) {
                    setTimeout(() => {
                        if (err.status == StatusCodes.UNPROCESSABLE_ENTITY) {
                            let errors = err.responseJSON.errors

                            for (const key in errors) {
                                if (!key.includes('.')) {
                                    $(`#production-milestone-modal #${key}_err`).find('p').text(
                                        errors[key])
                                    $(`#production-milestone-modal #${key}_err`).removeClass(
                                        'hidden')
                                } else {
                                    let field = key.split('.')[0]
                                    let idx = key.split('.')[1]

                                    $(`#production-milestone-modal #${field}_err[data-product-id="${idx}"]`)
                                        .find('p').text(errors[key])
                                    $(`#production-milestone-modal #${field}_err[data-product-id="${idx}"]`)
                                        .removeClass('hidden')
                                }
                            }
                        }
                        FORM_CAN_SUBMIT = true
                    }, 300);
                },
            });
        })
        $('#production-milestone-modal #reject-btn').on('click', function(e) {
            e.preventDefault()

            if (!FORM_CAN_SUBMIT) return

            FORM_CAN_SUBMIT = false

            $('.err_msg').addClass('hidden') // Remove error messages

            // Prepare data
            var data = {
                'remark': $('#production-milestone-modal textarea[name="remark"]').val()
            }
            $('#production-milestone-modal .second-half').each(function(i, obj) {
                if ($(this).data('product-child-id') != undefined) {
                    data[`product-child-${$(this).data('product-child-id')}`] = $(this).find(
                        'input:checked').val() == undefined ? null : $(this).find('input:checked').val()
                }
            })

            let url = '{{ route('production.reject_milestone') }}'
            url = `${url}?production_milestone_id=${ $(this).data('id') }`

            // Submit
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'POST',
                data: data,
                success: function(res) {
                    location.reload()
                },
                error: function(err) {
                    if (err.status == StatusCodes.UNPROCESSABLE_ENTITY) {
                        let errors = err.responseJSON.errors

                        for (const key in errors) {
                            if (!key.includes('.')) {
                                $(`#production-milestone-modal #${key}_err`).find('p').text(
                                    errors[key])
                                $(`#production-milestone-modal #${key}_err`).removeClass(
                                    'hidden')
                            } else {
                                let field = key.split('.')[0]
                                let idx = key.split('.')[1]

                                $(`#production-milestone-modal #${field}_err[data-product-id="${idx}"]`)
                                    .find('p').text(errors[key])
                                $(`#production-milestone-modal #${field}_err[data-product-id="${idx}"]`)
                                    .removeClass('hidden')
                            }
                        }
                    }
                    FORM_CAN_SUBMIT = true
                }
            });
        })
    </script>
@endpush
