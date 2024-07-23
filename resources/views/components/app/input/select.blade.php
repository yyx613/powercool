@props([
    'name' => '',
    'id' => '',
    'hasError' => false
])

<select name="{{ $name }}" id="{{ $id }}" {{ $attributes->class(['border-red-500' => $hasError])->merge(['class' => 'text-sm rounded-md border-gray-300 p-2.5']) }}>
    {{ $slot }}
</select>