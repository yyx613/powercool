@extends('layouts.app')
@section('title', 'Select Branch')

@section('content')
    <div class="flex items-center justify-center min-h-[80vh]">
        <div class="w-full max-w-2xl">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-semibold text-gray-800 mb-2">{{ __('Select Branch') }}</h1>
                <p class="text-gray-600">{{ __('Please select a branch before proceeding.') }}</p>
            </div>

            <form action="{{ route('branch.store') }}" method="POST" id="branch-form">
                @csrf
                <input type="hidden" name="branch" id="branch-input" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach ($branches as $key => $label)
                        <div class="branch-card cursor-pointer bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-200 p-6 border-2 border-transparent hover:border-blue-500"
                            data-branch="{{ $key }}">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800">{{ $label }}</h3>
                                <p class="text-sm text-gray-500 mt-1">{{ __('Click to select') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                @error('branch')
                    <p class="mt-4 text-center text-sm text-red-500">{{ $message }}</p>
                @enderror
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.branch-card').on('click', function() {
                var branch = $(this).data('branch');
                $('#branch-input').val(branch);
                $('#branch-form').submit();
            });
        });
    </script>
@endpush
