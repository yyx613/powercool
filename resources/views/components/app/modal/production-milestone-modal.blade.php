<x-app.modal.base-modal id="production-milestone-modal">
    <div class="aspect-[2/1] flex flex-col">
        <div class="border-b py-2 px-4 bg-slate-100 flex items-center">
            <h6 class="text-lg font-black">Check In Milestone</h6>
        </div>
        <div class="flex-1 flex flex-col p-4">
            <div class="flex-1 flex flex-col gap-4">
                <div>
                    <span class="font-medium text-sm mb-1 block">Date</span>
                    <div class="border px-2 py-1.5 rounded">
                        <p id="date" class="text-sm"></p>
                    </div>
                </div>
                <div id="serial-no-container">
                    <span class="font-medium text-sm block mb-2">Materials</span>
                    <div class="border px-2 py-1.5 rounded overflow-y-auto max-h-64">
                        @foreach($product->materialUse->materials as $key => $material)
                            <div class="mb-4 {{ ($key + 1) < count($product->materialUse->materials) ? 'pb-4 border-b' : '' }}">
                                <div class="mb-2 flex flex-col">
                                    <span class="font-medium text-sm">{{ $material->material->model_name }}</span>
                                    <span class="font-medium text-xs text-slate-400">Quantity Needed: x{{ $material->qty }}</span>
                                </div>
                                @if ($material->material->is_sparepart)
                                    <div class="grid grid-cols-2 gap-2">
                                        @foreach($material->material->childrenWithoutAssigned($production->id) as $m)
                                            @if ($m->location == 'warehouse')
                                                <div class="flex items-center gap-x-2">
                                                    <input type="checkbox" name="serial_no[]" id="{{ $m->id }}" class="border-slate-500 rounded" data-mu-id="{{ $material->id }}">
                                                    <label for="{{ $m->id }}" class="text-sm">{{ $m->sku }}</label>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                    <x-app.message.error id="materials_err" class="mt-2" data-material-id="{{ $material->id }}" />
                                @else
                                    <p class="text-sm text-blue-600">Not spare part, selection is not required</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="flex gap-x-6 mt-6">
                <button type="button" class="w-full p-2 rounded-md text-red-600 text-sm font-medium transiton-all duration-300 hover:bg-red-50" id="no-btn">No</button>
                <button type="button" class="w-full p-2 rounded-md bg-blue-600 text-white text-sm font-medium transiton-all duration-300 text-center hover:bg-blue-700" id="yes-btn">Confirm</button>
            </div>
        </div>
        <div class="border-t px-2 py-3 hidden" id="last-milestone-msg">
            <p class="text-sm text-blue-500 leading-tight font-medium text-center">Checking in this milestone will complete the production. Please make sure the materials used are correct (if there is any).</p>
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
        // Submit
        let url = '{{ route("production.check_in_milestone") }}'

        let materials = {}
        $('#serial-no-container input[name="serial_no[]"]').each(function(i, obj) {
            if ($(this).is(':checked')) {
                if (materials[$(this).data('mu-id')] === undefined) materials[$(this).data('mu-id')] = []

                let vals = materials[$(this).data('mu-id')]
                vals.push($(this).attr('id'))
                materials[$(this).data('mu-id')] = vals
                MATERIALS_NEEDED.push($(this).attr('id'))
            }
        })

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: url,
            type: 'POST',
            data: {
                'production_milestone_id': $('#production-milestone-modal #yes-btn').attr('data-id'),
                'datetime': $('#production-milestone-modal #date').text(),
                'materials': materials,
                'last_milestone': !($('#production-milestone-modal #last-milestone-msg').hasClass('hidden'))
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
    
                                $(`#production-milestone-modal #${field}_err[data-material-id="${idx}"]`).find('p').text(errors[key])
                                $(`#production-milestone-modal #${field}_err[data-material-id="${idx}"]`).removeClass('hidden') 
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