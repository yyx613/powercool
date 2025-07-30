<div class="bg-white p-4 border rounded-md" id="additional-remark-container">
    <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M20,0H4A4,4,0,0,0,0,4V16a4,4,0,0,0,4,4H6.9l4.451,3.763a1,1,0,0,0,1.292,0L17.1,20H20a4,4,0,0,0,4-4V4A4,4,0,0,0,20,0Zm2,16a2,2,0,0,1-2,2H17.1a2,2,0,0,0-1.291.473L12,21.69,8.193,18.473h0A2,2,0,0,0,6.9,18H4a2,2,0,0,1-2-2V4A2,2,0,0,1,4,2H20a2,2,0,0,1,2,2Z"/><path d="M7,7h5a1,1,0,0,0,0-2H7A1,1,0,0,0,7,7Z"/><path d="M17,9H7a1,1,0,0,0,0,2H17a1,1,0,0,0,0-2Z"/><path d="M17,13H7a1,1,0,0,0,0,2H17a1,1,0,0,0,0-2Z"/></svg>
        <span class="text-md ml-3 font-bold">{{ __('Remarks') }}</span>
    </div>
    <div class="grid grid-cols-3 gap-8 w-full mb-8">
        <div class="flex flex-col col-span-3">
            <x-app.input.label id="remark" class="mb-1">{{ __('Additional Note') }}</x-app.input.label>
            <x-app.input.textarea name="remark" id="remark" :hasError="$errors->has('remark')" text="{{ isset($replicate) ? $replicate->remark : (isset($sale) ? $sale->remark : null) }}" />
            <x-app.message.error id="remark_err"/>
        </div>
    </div>
</div>


@push('scripts')
    <script>
        // REMARK_FORM_CAN_SUBMIT = true

        // $('#remark-form').on('submit', function(e) {
        //     e.preventDefault()

        //     if (!REMARK_FORM_CAN_SUBMIT) return

        //     REMARK_FORM_CAN_SUBMIT = false

        //     $('#remark-form #submit-btn').text('Updating')
        //     $('#remark-form #submit-btn').removeClass('bg-yellow-400 shadow')
        //     $('.err_msg').addClass('hidden') // Remove error messages
        //     // Submit
        //     let url = ''
        //     url = `${url}?type=quo`

        //     $.ajax({
        //         headers: {
        //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //         },
        //         url: url,
        //         type: 'POST',
        //         data: {
        //             'sale_id': typeof SALE !== 'undefined' && SALE != null ? SALE.id : null,
        //             'remark': $('#remark-form textarea[name="remark"]').val(),
        //         },
        //         success: function(res) {
        //             setTimeout(() => {
        //                 $('#remark-form #submit-btn').text('Updated')
        //                 $('#remark-form #submit-btn').addClass('bg-green-400 shadow')

        //                 setTimeout(() => {
        //                     $('#remark-form #submit-btn').text('Save and Update')
        //                     $('#remark-form #submit-btn').removeClass('bg-green-400')
        //                     $('#remark-form #submit-btn').addClass('bg-yellow-400 shadow')
                            
        //                     REMARK_FORM_CAN_SUBMIT = true
        //                 }, 2000);
        //             }, 300);
        //         },
        //         error: function(err) {
        //             setTimeout(() => {
        //                 if (err.status == StatusCodes.UNPROCESSABLE_ENTITY) {
        //                     let errors = err.responseJSON.errors
    
        //                     for (const key in errors) {
        //                         $(`#remark-form #${key}_err`).find('p').text(errors[key])
        //                         $(`#remark-form #${key}_err`).removeClass('hidden')
        //                     }
        //                 }
        //                 $('#remark-form #submit-btn').text('Save and Update')
        //                 $('#remark-form #submit-btn').addClass('bg-yellow-400 shadow')

        //                 REMARK_FORM_CAN_SUBMIT = true
        //             }, 300);
        //         },
        //     });
        // })
    </script>
@endpush