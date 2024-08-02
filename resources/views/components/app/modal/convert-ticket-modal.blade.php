<x-app.modal.base-modal id="convert-ticket-modal">
    <div class="aspect-[2/1] flex flex-col">
        <div class="border-b py-2 px-4 bg-gray-100">
            <h6 class="text-lg font-black" id="title">Convert Ticket</h6>
        </div>
        <div class="flex-1 flex flex-col p-4">
            <div class="flex-1">
                <div class="flex flex-col">
                    <x-app.input.label id="department" class="mb-1">Department <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="department" id="department" :hasError="$errors->has('status')">
                        <option value="">Select a department</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </x-app.input.select>
                </div>
            </div>
            <div class="flex gap-x-6">
                <div class="flex-1">
                    <button type="button" class="w-full p-2 rounded-md text-red-600 text-sm font-medium transiton-all duration-300 hover:bg-red-50" id="no-btn">No</button>
                </div>
                <div class="flex-1 flex">
                    <a href="" class="w-full p-2 rounded-md bg-blue-600 text-white text-sm font-medium transiton-all duration-300 text-center hidden hover:bg-blue-700" id="yes-btn">Yes</a>
                </div>
            </div>
        </div>
    </div>
</x-app.modal.base-modal>

@push('scripts')
<script>
    $('#convert-ticket-modal #no-btn').on('click', function() {
        $('#convert-ticket-modal').removeClass('show-modal')
        $('#convert-ticket-modal select[name="department"]').val(null).trigger('change')
        $('#convert-ticket-modal #yes-btn').addClass('hidden')
    })
    $('#convert-ticket-modal select[name="department"]').on('change', function() {
        let val = $(this).val()
        let ticketId = $('#convert-ticket-modal').data('ticket-id')

        if (val != '' && val != null) {

            if (val == 2) {
                $('#convert-ticket-modal #yes-btn').attr('href', `{{ route('task.sale.create') }}?tic_id=${ticketId}`)
            } else if (val == 3) {
                $('#convert-ticket-modal #yes-btn').attr('href', `{{ route('task.technician.create') }}?tic_id=${ticketId}`)
            } else if (val == 4) {
                $('#convert-ticket-modal #yes-btn').attr('href', `{{ route('task.driver.create') }}?tic_id=${ticketId}`)
            }

            $('#convert-ticket-modal #yes-btn').removeClass('hidden')
        }
    })
</script>
@endpush