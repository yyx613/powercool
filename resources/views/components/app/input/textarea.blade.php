@props([
    'name' => '',
    'id' => '',
    'placeholder' => '',
    'disabled' => false,
    'hasError' => false,
    'text' => '',
])

<div {{ $attributes->class(['border-red-500' => $hasError, 'bg-gray-100' => $disabled])->merge(['class' => 'rounded-md border border-gray-300 overflow-hidden p-2 py-1.5']) }}>
    <textarea name="{{ $name }}" id="{{ $id }}" placeholder="{{ $placeholder }}" class="h-20 resize-none text-sm p-0 w-full border-transparent focus:border-transparent focus:ring-0 {{ $disabled ? 'bg-gray-100 pointer-events-none' : ''}}">{!! $text !!}</textarea>
</div>