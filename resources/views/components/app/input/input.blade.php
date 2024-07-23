@props([
    'type' => 'text',
    'name' => '',
    'id' => '',
    'placeholder' => '',
    'disabled' => false,
    'hasError' => false,
    'value' => null,
])

<div {{ $attributes->class(['border-red-500' => $hasError, '!bg-gray-100' => $disabled])->merge(['class' => 'bg-white rounded-md border border-gray-300 overflow-hidden p-2']) }}>
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}" placeholder="{{ $placeholder }}" value="{!! $value !!}" step="{{ $type == 'number' ? 'any' : null }}" autocomplete="off" class="text-sm p-0 w-full border-none border-transparent focus:border-transparent focus:ring-0 {{ $disabled ? 'bg-gray-100 pointer-events-none' : '' }}">
    {{ $slot }}
</div>