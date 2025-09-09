@props([
    'name' => '',
    'id' => '',
    'placeholder' => '',
    'disabled' => false,
    'hasError' => false,
    'text' => '',
    'required' => false,
])

<div {{ $attributes->class(['border-red-500' => $hasError, 'bg-gray-100' => $disabled])->merge(['class' => 'bg-white rounded-md border border-gray-300 overflow-hidden p-2']) }}>
    <textarea name="{{ $name }}" id="{{ $id }}" placeholder="{{ $placeholder }}" {{ $required == true ? 'required' : '' }} class="h-24 resize-none text-sm p-0 w-full border-transparent focus:border-transparent focus:ring-0 {{ $disabled ? 'bg-gray-100 pointer-events-none' : ''}}">{!! $text !!}</textarea>
</div>
