<button type="button" {{ $attributes->merge(['class' => 'flex items-center bg-blue-200 py-1 px-2 rounded']) }}>
    {{ $slot }}
</button>