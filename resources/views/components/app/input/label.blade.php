<label for="{{ $id ?? null }}" {{ $attributes->merge(['class' => 'text-sm']) }}>{{ $slot }}</label>