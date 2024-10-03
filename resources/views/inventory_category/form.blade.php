@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('inventory_category.index') }}">{{ isset($cat) ? 'Edit Category' : 'Create Category' }}</x-app.page-title>
    </div>

    <div class="bg-white p-4 border rounded-md">
        <form action="" method="POST" enctype="multipart/form-data" id="form">
            <div>
                <div class="grid grid-cols-3 gap-8 w-full">
                    <div class="flex flex-col">
                        <x-app.input.label id="name" class="mb-1">Name <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.input name="name" id="name" value="{{ isset($cat) ? $cat->name : null }}" />
                        <x-app.message.error id="name_err"/>
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="status" class="mb-1">Status <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select name="status" id="status">
                            <option value="">Select a Active/Inactive</option>
                            <option value="1" @selected(old('status', isset($cat) ? $cat->is_active : null) == 1)>Active</option>
                            <option value="0" @selected(old('status', isset($cat) ? $cat->is_active : null) === 0)>Inactive</option>
                        </x-app.input.select>
                        <x-app.message.error id="status_err"/>
                    </div>
                </div>
            </div>
            <div class="mt-8 flex justify-end">
                <x-app.button.submit id="submit-btn">Save and Update</x-app.button.submit>
            </div>
        </form>
    </div>
    
@endsection

@push('scripts')
<script>
    CATEGORY = @json($cat ?? null);
    FORM_CAN_SUBMIT = true

    $('#form').on('submit', function(e) {
        e.preventDefault()

        if (!FORM_CAN_SUBMIT) return

        FORM_CAN_SUBMIT = false

        $('#form #submit-btn').text('Updating')
        $('#form #submit-btn').removeClass('bg-yellow-400 shadow')
        $('.err_msg').addClass('hidden') // Remove error messages
        // Submit
        let url = '{{ route("inventory_category.upsert") }}'

        var formData = new FormData(this);
        if (CATEGORY != null) formData.append('category_id', CATEGORY != null ? CATEGORY.id : null)

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                $('#form #submit-btn').text('Updated')
                $('#form #submit-btn').addClass('bg-green-400 shadow')

                if (CATEGORY == null) {
                    CATEGORY = res.category
                }


                setTimeout(() => {
                    window.location.href = "{{ route('inventory_category.index') }}"
                    // setTimeout(() => {
                    //     $('#form #submit-btn').text('Save and Update')
                    //     $('#form #submit-btn').removeClass('bg-green-400')
                    //     $('#form #submit-btn').addClass('bg-yellow-400 shadow')
                        
                    //     FORM_CAN_SUBMIT = true
                    // }, 2000);
                }, 300)
            },
            error: function(err) {
                setTimeout(() => {
                    if (err.status == StatusCodes.UNPROCESSABLE_ENTITY) {
                        let errors = err.responseJSON.errors

                        for (const key in errors) {
                            if (key.includes('picture')) {
                                $(`#form #image_err`).find('p').text(errors[key])
                                $(`#form #image_err`).removeClass('hidden')
                            } else {
                                $(`#form #${key}_err`).find('p').text(errors[key])
                                $(`#form #${key}_err`).removeClass('hidden')
                            }
                        }
                    }
                    $('#form #submit-btn').text('Save and Update')
                    $('#form #submit-btn').addClass('bg-yellow-400 shadow')

                    FORM_CAN_SUBMIT = true
                }, 300);
            },
        });
    })
</script>
@endpush