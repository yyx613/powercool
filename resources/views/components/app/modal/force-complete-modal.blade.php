@props([
    'production' => null
])

<x-app.modal.base-modal id="force-complete-modal">
    <div class="aspect-[2/1] flex flex-col">
        <div class="py-2 px-4 bg-yellow-100 flex items-center">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" />
            </svg>
            <h6 class="text-lg font-black ml-3">{{ __('Force Complete Production') }}</h6>
        </div>
        <div class="flex-1 flex flex-col p-4">
            <form action="{{ route('production.force_complete_task', ['production' => $production->id]) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <p class="text-sm text-slate-600">{{ __('Are you sure to complete the production without completing all the milestones?') }}</p>
                </div>
                <div class="mb-8">
                    <span class="font-medium text-sm mb-1 block">{{ __('Reason') }}<span class="text-sm text-red-500">*</span></span>
                    <x-app.input.textarea name="reason" id="reason" />
                    @error('reason')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>
                <div class="flex gap-x-6">
                    <div class="flex-1">
                        <button type="button"
                            class="w-full p-2 rounded-md text-red-600 text-sm font-medium transiton-all duration-300 hover:bg-red-50"
                            id="no-btn">{{ __('Cancel') }}</button>
                    </div>
                    <div class="flex-1 flex">
                        <button type="submit"
                            class="w-full p-2 rounded-md bg-blue-600 text-white text-sm font-medium transiton-all duration-300 text-center hover:bg-blue-700"
                            id="yes-btn">{{ __('Confirm') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app.modal.base-modal>

@push('scripts')
    <script>
        $('#force-complete-modal #no-btn').on('click', function() {
            $('#force-complete-modal').removeClass('show-modal')
        })

        @if ($errors->has('reason'))
            $('#force-complete-modal').addClass('show-modal')
        @endif
    </script>
@endpush
