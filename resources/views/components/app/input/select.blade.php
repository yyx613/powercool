@props([
    'name' => '',
    'id' => '',
    'hasError' => false,
    'multiple' => false
])

<select name="{{ $name }}" id="{{ $id }}" {{ $multiple ? 'multiple' : '' }} {{ $attributes->class(['border-red-500' => $hasError])->merge(['class' => 'text-sm rounded-md border-gray-300 p-2.5 aria-disabled:bg-gray-100']) }}>
    {{ $slot }}
</select>