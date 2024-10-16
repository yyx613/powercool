<!-- Language -->
<select name="change_lang" id="change_lang" class="border border-slate-200 rounded-full text-xs">
    @foreach ($languages as $key => $val)
        <option value="{{ $key }}" @selected(session('selected_lang') == $key)>{{ $val }}</option>
    @endforeach
</select>

@push('scripts')
<script>
    $('select[name="change_lang"]').on('change', function() {
        let url = '{{ config("app.url") }}'
        url = `${url}/change-language/${$(this).val()}`

        window.location.href = url
    })
</script>
@endpush
