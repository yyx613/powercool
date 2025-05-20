@props([
    'type' => 'submit'
])

<button type="{{ $type }}" {{ $attributes->merge(['class' => 'w-full max-w-40 bg-yellow-400 shadow font-medium py-2 px-4 rounded-md text-sm']) }}>{{ $slot }}</button>