<button type="button" class="flex items-center gap-x-4 bg-sky-300 p-2 rounded w-fit" id="scanner-btn">
    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
        <path d="m24,11.5c0,.829-.671,1.5-1.5,1.5H1.5c-.829,0-1.5-.671-1.5-1.5s.671-1.5,1.5-1.5h21c.829,0,1.5.671,1.5,1.5ZM1.5,8c.829,0,1.5-.671,1.5-1.5v-1c0-1.378,1.122-2.5,2.5-2.5h1c.829,0,1.5-.671,1.5-1.5s-.671-1.5-1.5-1.5h-1C2.467,0,0,2.467,0,5.5v1c0,.829.671,1.5,1.5,1.5Zm5,13h-1c-1.378,0-2.5-1.122-2.5-2.5v-1c0-.829-.671-1.5-1.5-1.5s-1.5.671-1.5,1.5v1c0,3.033,2.467,5.5,5.5,5.5h1c.829,0,1.5-.671,1.5-1.5s-.671-1.5-1.5-1.5Zm16-5c-.829,0-1.5.671-1.5,1.5v1c0,1.378-1.122,2.5-2.5,2.5h-1c-.829,0-1.5.671-1.5,1.5s.671,1.5,1.5,1.5h1c3.033,0,5.5-2.467,5.5-5.5v-1c0-.829-.671-1.5-1.5-1.5ZM18.5,0h-1c-.829,0-1.5.671-1.5,1.5s.671,1.5,1.5,1.5h1c1.378,0,2.5,1.122,2.5,2.5v1c0,.829.671,1.5,1.5,1.5s1.5-.671,1.5-1.5v-1c0-3.033-2.467-5.5-5.5-5.5Z"/>
    </svg>
    <span class="font-medium">{{ __('Scan Barcode') }}</span>
</button>

<x-app.modal.qr-scanner-modal/>
<!-- <div id="reader" width="600px"></div> -->

@push('scripts')
<script>
    var html5QrCode

    $('#scanner-btn').on('click', function() {
        // This method will trigger user permissions
        Html5Qrcode.getCameras().then(devices => {
        /**
         * devices would be an array of objects of type:
         * { id: "id", label: "label" }
         */
        if (devices && devices.length) {
            var cameraId = devices[0].id;
            // .. use this to start scanning.
            // console.debug(cameraId)

            $('#qr-scanner-modal').addClass('show-modal')

            html5QrCode = new Html5Qrcode("reader");
            const config = { fps: 10, qrbox: { width: 250, height: 250 } };
            const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                /* handle success */
                $('#filter_search').val(decodedText).trigger('keyup')
                // stop scanning
                html5QrCode.stop().then((ignore) => {
                    // QR Code scanning is stopped.
                    $('#qr-scanner-modal').removeClass('show-modal')
                }).catch((err) => {
                    // Stop failed, handle it.
                });
            };
            // If you want to prefer back camera
            html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);
        }
        }).catch(err => {
            // console.debug(err)
            // handle err
        });
    })
</script>
@endpush