<x-app.modal.base-modal id="to-production-modal">
    <div class="flex flex-col">
        <div class="py-2 px-4 bg-gray-100">
            <h6 class="text-lg font-black">{{ __('To Sale Production Request') }}</h6>
        </div>
        <div class="flex-1 flex flex-col p-4 overflow-auto max-h-96">
            <form action="" method="POST">
                @csrf
                <div class="flex-1 mb-8">
                    <div class="flex flex-col mb-4" id="product-selector">
                        <span
                            class="font-medium text-sm mb-1 block">{{ __('Select a product to production request') }}</span>
                        <x-app.input.select name="product" id="product" class="w-full">
                            <option value="">{{ __('Select a product') }}</option>
                        </x-app.input.select>
                    </div>
                    <div class="flex flex-col mb-4">
                        <x-app.input.label id="qty" class="mb-1">{{ __('Quantity') }}</x-app.input.label>
                        <x-app.input.input name="qty" id="qty" class="int-input" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="remark" class="mb-1">{{ __('Remark') }}</x-app.input.label>
                        <textarea name="remark" id="remark" class="hidden"></textarea>
                    </div>
                </div>
                <div class="flex gap-x-6">
                    <div class="flex-1">
                        <button type="button"
                            class="w-full p-2 rounded-md text-red-600 text-sm font-medium transiton-all duration-300 hover:bg-red-50"
                            id="no-btn">{{ __('No') }}</button>
                    </div>
                    <div class="flex-1 flex">
                        <button type="submit"
                            class="w-full p-2 rounded-md bg-blue-600 text-white text-sm font-medium transiton-all duration-300 text-center hidden hover:bg-blue-700"
                            id="yes-btn">{{ __('Yes') }}</button>
                    </div>
                </div>
                <input type="hidden" name="product_id" />
            </form>
        </div>
    </div>
</x-app.modal.base-modal>

@push('scripts')
    <script>
        $('#to-production-modal #no-btn').on('click', function() {
            $('#to-production-modal').removeClass('show-modal')
        })
        $('#to-production-modal form').one('submit', function(e) {
            e.preventDefault()

            let productId = $('#to-production-modal select').val()
            if (productId == '') {
                productId = $('#to-production-modal #product-selector').attr('data-product-id')
            }
            if (productId == 'null') return

            let saleProductId = $('#to-production-modal #yes-btn').data('sp-id')
            if (saleProductId == undefined) {
                saleProductId = $('#to-production-modal select option:selected').data('sp-id')
            }
            let url = "{{ config('app.url') }}"
            url = `${url}/sale/to-sale-production-request/${saleProductId}`

            $('#to-production-modal textarea[name="remark"]').val($('#remark-quill .ql-editor').html())
            $('#to-production-modal input[name="product_id"]').val(productId)
            $('#to-production-modal form').attr('action', url)
            $('#to-production-modal form').submit()
        })
        $('#to-production-modal select[name="product"]').change(function() {
            let val = $(this).val()

            if (val != 'null') {
                $('#to-production-modal #yes-btn').removeClass('hidden')
            } else {
                $('#to-production-modal #yes-btn').addClass('hidden')
            }
        })

        $(document).ready(function() {
            buildRemarkQuillEditorForToProductionModal()
        });

        function buildRemarkQuillEditorForToProductionModal() {
            // Create div wrapper for quill (jQuery)
            var $quill = $(`
                <div class="quill-wrapper rounded-md border border-gray-300 bg-white">
                    <div id="remark-quill-to-production-modal"></div>
                </div>
            `);

            $(`#to-production-modal textarea[name="remark"]`).after($quill);

            var quill = new Quill(`#remark-quill-to-production-modal`, {
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
                                            $(`#to-production-modal textarea[name="remark"]`).val(isEmpty ? '' : html);
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
