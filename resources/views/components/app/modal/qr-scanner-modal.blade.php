<x-app.modal.base-modal id="qr-scanner-modal">
    <div id="reader" width="600px"></div>
    <div>
        <button type="button" class="w-full p-3 text-red-600 text-sm font-medium transiton-all duration-300 hover:bg-red-50" id="cancel-btn">{{ __('Cancel') }}</button>
    </div>
</x-app.modal.base-modal>

@push('scripts')
<script>
    $('#qr-scanner-modal #cancel-btn').on('click', function() {
        html5QrCode.stop().then((ignore) => {
            // QR Code scanning is stopped.
            $('#qr-scanner-modal').removeClass('show-modal')
        }).catch((err) => {
            // Stop failed, handle it.
        });
    })
</script>
@endpush