<div class="bg-white p-4 border rounded-md" id="additional-remark-container">
    <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M20,0H4A4,4,0,0,0,0,4V16a4,4,0,0,0,4,4H6.9l4.451,3.763a1,1,0,0,0,1.292,0L17.1,20H20a4,4,0,0,0,4-4V4A4,4,0,0,0,20,0Zm2,16a2,2,0,0,1-2,2H17.1a2,2,0,0,0-1.291.473L12,21.69,8.193,18.473h0A2,2,0,0,0,6.9,18H4a2,2,0,0,1-2-2V4A2,2,0,0,1,4,2H20a2,2,0,0,1,2,2Z"/><path d="M7,7h5a1,1,0,0,0,0-2H7A1,1,0,0,0,7,7Z"/><path d="M17,9H7a1,1,0,0,0,0,2H17a1,1,0,0,0,0-2Z"/><path d="M17,13H7a1,1,0,0,0,0,2H17a1,1,0,0,0,0-2Z"/></svg>
        <span class="text-md ml-3 font-bold">{{ __('Remarks') }}</span>
    </div>
    <div class="grid grid-cols-3 gap-8 w-full mb-8">
        <div class="flex flex-col col-span-3">
            <x-app.input.label id="remark" class="mb-1">{{ __('Additional Note') }}</x-app.input.label>
            <textarea name="remark" id="remark" class="hidden">{!! isset($replicate) ? $replicate->remark : (isset($sale) ? $sale->remark : null) !!}</textarea>
            <x-app.message.error id="remark_err"/>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            buildAddRemarkQuillEditor();
        });

        function buildAddRemarkQuillEditor() {
            // Create div wrapper for quill (jQuery)
            var $quill = $(`
                <div class="quill-wrapper rounded-md border border-gray-300 bg-white">
                    <div id="add-remark"></div>
                </div>
            `);

            $('#additional-remark-container textarea[name="remark"]').after($quill);

            var quill = new Quill(`#add-remark`, {
                theme: 'snow',
                placeholder: "{!! __('Remark') !!}",
                modules: {
                    toolbar: {
                        container: [
                            [{ 'header': [1, 2, false] }],
                            ['bold', 'italic', 'underline'],
                            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                            ['image'],
                        ],
                        handlers: {
                            image: function() {
                                // Create and trigger file input
                                var input = document.createElement('input');
                                input.setAttribute('type', 'file');
                                input.setAttribute('accept', 'image/*');
                                input.click();

                                input.onchange = function() {
                                    var file = input.files[0];
                                    if (!file) return;

                                    // Validate file type
                                    if (!file.type.match('image.*')) {
                                        alert('Please select an image file.');
                                        return;
                                    }

                                    // Validate file size (max 5MB)
                                    if (file.size > 5 * 1024 * 1024) {
                                        alert('Image size should be less than 5MB.');
                                        return;
                                    }

                                    // Prepare upload
                                    var formData = new FormData();
                                    formData.append('image', file);
                                    var range = quill.getSelection(true);

                                    // Show loading
                                    quill.insertText(range.index, 'Uploading image...');
                                    quill.setSelection(range.index + 19);

                                    // Upload to server
                                    $.ajax({
                                        url: '{{ route("quill.upload.image") }}',
                                        type: 'POST',
                                        data: formData,
                                        processData: false,
                                        contentType: false,
                                        headers: {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },
                                        success: function(response) {
                                            quill.deleteText(range.index, 19);
                                            quill.insertEmbed(range.index, 'image', response.url);
                                            quill.setSelection(range.index + 1);
                                            // Sync to textarea
                                            var html = quill.root.innerHTML;
                                            var isEmpty = html === '<p><br></p>' || quill.getText().trim() === '';
                                            $('#additional-remark-container textarea[name="remark"]').val(isEmpty ? '' : html);
                                        },
                                        error: function(xhr) {
                                            quill.deleteText(range.index, 19);
                                            var errorMsg = 'Failed to upload image.';
                                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                                errorMsg = xhr.responseJSON.message;
                                            }
                                            alert(errorMsg);
                                        }
                                    });
                                };
                            }
                        }
                    }
                },
            });

            // Load existing content from textarea (for old values after validation)
            setTimeout(function() {
                let existingContent = $('#additional-remark-container textarea[name="remark"]').val();
                if (existingContent && existingContent.trim() !== '') {
                    quill.root.innerHTML = existingContent;
                }
            }, 100);

            var toolbar = quill.container.previousSibling;
            toolbar.querySelector('button.ql-bold').setAttribute('title', 'Bold');
            toolbar.querySelector('button.ql-italic').setAttribute('title', 'Italic');
            toolbar.querySelector('button.ql-underline').setAttribute('title', 'Underline');
            toolbar.querySelector('button.ql-list[aria-label="list: ordered"]').setAttribute('title', 'Ordered List');
            toolbar.querySelector('button.ql-list[aria-label="list: bullet"]').setAttribute('title', 'Bullet List');
            toolbar.querySelector('button.ql-image').setAttribute('title', 'Insert Image');
        }
    </script>
@endpush