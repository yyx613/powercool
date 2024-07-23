@props([
    'name' => '',
    'id' => '',
    'hasError' => false,
    'placeholder' => '',
    'can_add' => false,
    'multiple' => false,
])

<div {{ $attributes->class(['border-red-500' => $hasError])->merge(['class' => 'border rounded-md border-gray-300 overflow-hidden']) }}>
    <select name="{{ $name }}" id="{{ $id }}" {{ $multiple ? 'multiple' : '' }}>
        {{ $slot }}
    </select>
</div>

@push('scripts')
    <script>
        $("select[name='{{ $name }}']").select2({
            placeholder: '{{ $placeholder }}',
            tags: '{{ $can_add }}'
        })
    </script>
@endpush