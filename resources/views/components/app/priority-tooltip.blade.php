@props(['priorities', 'selectName' => 'priority'])

<div class="relative inline-flex items-center" id="priority-tooltip-wrapper">
    {{-- Info icon - hidden until a priority is selected --}}
    <div id="priority-info-icon" class="hidden cursor-pointer ml-2">
        <svg class="h-4 w-4 text-blue-500 hover:text-blue-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
        </svg>
    </div>

    {{-- Tooltip panel - hidden by default, shown on hover --}}
    <div id="priority-tooltip-panel" class="hidden absolute left-6 top-0 z-50 w-72 bg-white rounded-lg shadow-lg border border-gray-200">
        {{-- Header --}}
        <div class="bg-blue-50 px-4 py-2 rounded-t-lg border-b border-gray-200">
            <span id="priority-tooltip-code" class="font-bold text-gray-800"></span>
            <span class="text-gray-800">-</span>
            <span id="priority-tooltip-name" class="font-semibold text-gray-800"></span>
        </div>

        {{-- Body --}}
        <div class="px-4 py-3 space-y-3">
            {{-- Description --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">{{ __('Description') }}</p>
                <p id="priority-tooltip-desc" class="text-sm text-gray-900"></p>
            </div>

            {{-- Response Time --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">{{ __('Response Time') }}</p>
                <p id="priority-tooltip-response" class="text-sm text-green-700 font-semibold"></p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function() {
        // Store priorities data
        const priorities = @json($priorities);
        const selectName = '{{ $selectName }}';

        $(document).ready(function() {
            // Select2 change event - update tooltip content
            $(`select[name="${selectName}"]`).on('change', function() {
                const priorityId = $(this).val();

                if (priorityId) {
                    const priority = priorities.find(p => p.id == priorityId);
                    if (priority) {
                        $('#priority-tooltip-code').text(priority.priority || '');
                        $('#priority-tooltip-name').text(priority.name || '');
                        $('#priority-tooltip-desc').text(priority.description || '-');
                        $('#priority-tooltip-response').text(priority.response_time || '-');
                        $('#priority-info-icon').removeClass('hidden');
                    }
                } else {
                    $('#priority-info-icon').addClass('hidden');
                    $('#priority-tooltip-panel').addClass('hidden');
                }
            });

            // jQuery hover for tooltip show/hide
            $('#priority-info-icon').hover(
                function() {
                    $('#priority-tooltip-panel').removeClass('hidden');
                },
                function() {
                    $('#priority-tooltip-panel').addClass('hidden');
                }
            );

            // Also hide tooltip when mouse leaves the panel (for smoother UX)
            $('#priority-tooltip-panel').hover(
                function() {
                    $(this).removeClass('hidden');
                },
                function() {
                    $(this).addClass('hidden');
                }
            );

            // Initialize tooltip if a priority is already selected (e.g., on edit page)
            const initialValue = $(`select[name="${selectName}"]`).val();
            if (initialValue) {
                $(`select[name="${selectName}"]`).trigger('change');
            }
        });
    })();
</script>
@endpush
