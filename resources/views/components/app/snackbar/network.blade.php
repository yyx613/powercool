<!-- Network Connection -->
<div class="fixed w-full bottom-[-50px] z-50 bg-amber-300 p-2 transition-all duration-300" id="snackbar-network">
    <p class="text-sm text-center font-black">You are now disconnected to the internet. Please check your connection.</p>
</div>

@push('scripts')
    <script>
        // Detect netwerk connection
        window.addEventListener('online', function() {
            $('#snackbar-network').removeClass('show-snackbar-network')
        });
        window.addEventListener('offline', function(){
            $('#snackbar-network').addClass('show-snackbar-network')
        });
    </script>
@endpush