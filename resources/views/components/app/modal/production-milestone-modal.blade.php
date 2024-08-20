<x-app.modal.base-modal id="production-milestone-modal">
    <div class="aspect-[2/1] flex flex-col">
        <div class="border-b py-2 px-4 bg-slate-100 flex items-center">
            <h6 class="text-lg font-black">Check In Milestone</h6>
        </div>
        <div class="flex-1 flex flex-col p-4">
            <div class="mb-8">
                <div class="mb-4">
                    <span class="font-medium text-sm mb-1 block">Date</span>
                    <div class="border px-2 py-1.5 rounded">
                        <p id="date" class="text-sm"></p>
                    </div>
                </div>
                <div class="mb-4">
                    <span class="font-medium text-sm mb-1 block">Serial No</span>
                    <div class="border px-2 py-1.5 rounded">
                        <p id="serial-no" class="text-sm"></p>
                    </div>
                </div>
            </div>
            <div class="flex gap-x-6">
                <div class="flex-1">
                    <button type="button" class="w-full p-2 rounded-md text-red-600 text-sm font-medium transiton-all duration-300 hover:bg-red-50" id="no-btn">No</button>
                </div>
                <div class="flex-1 flex">
                    <button class="w-full p-2 rounded-md bg-blue-600 text-white text-sm font-medium transiton-all duration-300 text-center hover:bg-blue-700" id="yes-btn">Confirm</button>
                </div>
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
        // Submit
        let url = '{{ route("production.check_in_milestone") }}'

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: url,
            type: 'POST',
            data: {
                'production_milestone_id': $('#production-milestone-modal #yes-btn').attr('data-id'),
                'datetime': $('#production-milestone-modal #date').text()
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
                            $(`#production-milestone-modal #${key}_err`).find('p').text(errors[key])
                            $(`#production-milestone-modal #${key}_err`).removeClass('hidden')
                        }
                    }
                    FORM_CAN_SUBMIT = true
                }, 300);
            },
        });
    })
</script>
@endpush