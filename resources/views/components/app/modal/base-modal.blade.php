@props([
    'id' => null,
])

<div class="opacity-0 -z-50 top-0 left-0 right-0 bottom-0 fixed bg-black bg-opacity-20 flex items-center justify-center" id="{{ $id }}">
    <div class="opacity-0 bg-white rounded-md shadow-md w-full max-w-md transiton-all duration-300 delay-200 scale-125 overflow-hidden" id="modal-inner">
        {{ $slot }}
    </div>
</div>