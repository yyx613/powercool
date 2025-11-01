@props([
    'materialUse' => null,
    'production' => null,
])

<x-app.modal.base-modal id="add-milestone-modal">
    <div class="aspect-[2/1] flex flex-col">
        <div class="py-2 px-4 bg-blue-100">
            <h6 class="text-lg font-black">{{ __('Add Milestone') }}</h6>
        </div>
        <div class="flex-1 flex flex-col p-4">
            <form method="POST" action="{{ route('production.add_milestone', ['production' => $production->id]) }}">
                @csrf
                <div class="flex flex-col mb-4 flex-1">
                    <x-app.input.label id="name" class="mb-1">{{ __('Name') }}</x-app.input.label>
                    <x-app.input.input name="name" />
                </div>
                <div class="flex flex-col mb-4 flex-1">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-medium text-sm block">{{ __('Materials') }}</span>
                        <input name="search" placeholder="{{ __('Search') }}"
                            class="text-xs rounded border-slate-200 p-1" />
                    </div>
                    <div class="border px-2 rounded overflow-y-auto max-h-64" id="material-use-selection-container">
                        @foreach ($materialUse->materials as $key => $mat)
                            <div class="py-2 flex items-center {{ $key > 0 ? 'border-t' : '' }}">
                                <input id="material-use-{{ $mat->id }}" name="material-use-{{ $mat->id }}" type="checkbox"
                                    class="rounded-full border-slate-400">
                                <label for="material-use-{{ $mat->id }}" class="text-sm ml-2 flex flex-col flex-1">
                                    <span>{{ $mat->material->model_name }}</span>
                                    <span class="text-slate-400 text-sm">Quantity needed: x{{ $mat->qty }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-x-6">
                    <div class="flex-1">
                        <button type="button"
                            class="w-full p-2 rounded-md text-red-600 text-sm font-medium transiton-all duration-300 hover:bg-red-50"
                            id="no-btn">{{ __('No') }}</button>
                    </div>
                    <div class="flex-1 flex">
                        <button type="submit"
                            class="w-full p-2 rounded-md bg-blue-600 text-white text-sm font-medium transiton-all duration-300 text-center hover:bg-blue-700"
                            id="yes-btn">{{ __('Yes') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app.modal.base-modal>

@push('scripts')
    <script>
        $('#add-milestone-modal #no-btn').on('click', function() {
            $('#add-milestone-modal').removeClass('show-modal')
        })
        // Search material uses
        $('#add-milestone-modal input[name="search"]').on('input', function() {
            let search = $(this).val().toLowerCase()
            $('#add-milestone-modal #material-use-selection-container div.py-2').each(function() {
                let name = $(this).find('label span:first').text().toLowerCase()
                if (name.includes(search)) {
                    $(this).removeClass('hidden')
                } else {
                    $(this).addClass('hidden')
                }
            })
        })
    </script>
@endpush
