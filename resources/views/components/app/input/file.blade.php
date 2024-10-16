@props([
    'id' => null,
    'hasError' => false,
    'multiple' => false,
])

<div>
    <label for="{{ $id }}" 
    {{ $attributes->class(['border-red-500' => $hasError])->merge(['class' => 'text-sm border border-gray-300 p-2.5 block w-full rounded-md']) }}
    >{{ __('Choose File') }}</label>
    <input type="file" id="{{ $id }}" name="{{ $id }}" {{ $multiple ? 'multiple' : '' }} class="hidden">
</div>