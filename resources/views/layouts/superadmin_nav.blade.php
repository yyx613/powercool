<div class="py-2 px-4 border-b" id="superadmin-nav">
    <select name="as_branch" id="as_branch" class="border border-slate-200 rounded-full text-xs">
        @foreach ($branches as $key => $val)
            <option value="{{ $key }}" @selected(session('as_branch') == $key)>As {{ $val }} Branch</option>
        @endforeach
    </select>
</div>

@push('scripts')
<script>
    $('#superadmin-nav #as_branch').on('change', function() {
        let url = '{{ route("user_management.as_branch") }}'
        url = `${url}?branch=${$(this).val()}`

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: url,
            type: 'GET',
        });
    })
</script>
@endpush
