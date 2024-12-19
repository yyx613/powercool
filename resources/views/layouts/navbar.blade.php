<div class="py-2 px-4 border-b flex items-center justify-between" id="navbar">
    <div class="flex items-center gap-4">
        <button type="button" class="lg:hidden mobile-sidebar-trigger-btn">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><rect y="11" width="24" height="2" rx="1"/><rect y="4" width="24" height="2" rx="1"/><rect y="18" width="24" height="2" rx="1"/></svg>
        </button>
        @if (isSuperAdmin())
            <select name="as_branch" id="as_branch" class="border border-slate-200 rounded-full text-xs">
                @foreach ($branches as $key => $val)
                    <option value="{{ $key }}" @selected(session('as_branch') == $key)>
                        @php
                            $title = 'As ' . $val . ' Branch';
                        @endphp
                        {{ __($title) }}
                    </option>
                @endforeach
            </select>
        @endif
    </div>
    <!-- Language -->
    <x-app.language-selector/>
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
</script>
@endpush