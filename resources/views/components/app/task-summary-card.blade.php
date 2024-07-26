@props([
    'label' => null,
    'value' => 0
])

<div {{ $attributes->merge(['class' => 'rounded-md border aspect-square flex flex-col']) }}>
    <div class="px-4 py-2 flex-1 relative">
        <div class="bg-white opacity-30 h-1/2 w-1/2 rounded-br-md absolute top-0 left-0 flex items-center px-4 py-2"></div>
        <div class="h-1/2 w-1/2 rounded-br-md absolute top-0 left-0 flex items-center px-4 py-2">
            <h6 class="text-5xl font-black text-white">{{ $value }}</h6>
        </div>
    </div>
    <div class="px-4 py-2 relative">
        <div class="bg-white opacity-40 h-1/2 w-1/2 rounded-tl-md absolute bottom-0 right-0"></div>
        <h6 class="text-4xl font-black text-white">{{ $label }}</h6>
    </div>
</div>