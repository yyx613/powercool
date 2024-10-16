<div class="py-2 px-4 border-b flex items-center justify-between" id="navbar">
    @if (isSuperAdmin())
        <select name="as_branch" id="as_branch" class="border border-slate-200 rounded-full text-xs">
            @foreach ($branches as $key => $val)
                <option value="{{ $key }}" @selected(session('as_branch') == $key)>As {{ $val }} Branch</option>
            @endforeach
        </select>
    @endif

    <!-- Language -->
    <select name="change_lang" id="change_lang" class="border border-slate-200 rounded-full text-xs">
        @foreach ($languages as $key => $val)
            <option value="{{ $key }}" @selected(session('selected_lang') == $key)>{{ $val }}</option>
        @endforeach
    </select>
</div>

@push('scripts')
<script>
    $('#navbar #as_branch').on('change', function() {
        let url = '{{ route("user_management.as_branch") }}'
        url = `${url}?branch=${$(this).val()}`

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: url,
            type: 'GET',
            success: function() {
                window.location.reload()
            }
        });
    })

    $('#navbar #change_lang').on('change', function() {
        let url = '{{ config("app.url") }}'
        url = `${url}/change-language/${$(this).val()}`

        window.location.href = url
    })
</script>
@endpush
