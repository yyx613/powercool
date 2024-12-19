@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <x-app.page-title>{{ __('Inventory Summary') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <!-- Summary -->
    <div class="mb-6">
        <div class="flex gap-4 flex-col md:flex-row">
            <div class="flex-1 bg-slate-200 p-2 rounded">
                <div>
                    <span class="text-lg font-black">{{ __('Warehouse') }}</span>
                </div>
                <div class="flex border-t border-slate-300 mt-4">
                    <div class="flex-1 flex items-center justify-between pt-2 border-r border-slate-300 pr-3 mr-3">
                        <span class="text-md">{{ __('Available Stock') }}</span>
                        <span class="text-lg font-black">{{ $warehouse_available_stock }}</span>
                    </div>
                    <div class="flex-1 flex items-center justify-between pt-2">
                        <span class="text-md">{{ __('Reserved Stock') }}</span>
                        <span class="text-lg font-black">{{ $warehouse_reserved_stock }}</span>
                    </div>
                </div>
            </div>
            <div class="flex-1 bg-slate-200 p-2 rounded">
                <div>
                    <span class="text-lg font-black">{{ __('Factory') }}</span>
                </div>
                <div class="flex border-t border-slate-300 mt-4">
                    <div class="flex-1 flex items-center justify-between pt-2 border-r border-slate-300 pr-3 mr-3">
                        <span class="text-md">{{ __('Production Stock') }}</span>
                        <span class="text-lg font-black">{{ $production_stock }}</span>
                    </div>
                    <div class="flex-1 flex items-center justify-between pt-2">
                        <span class="text-md">{{ __('Reserved Stock') }}</span>
                        <span class="text-lg font-black">{{ $production_reserved_stock }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Content -->
    <div class="flex gap-4 flex-col md:flex-row">
        <!-- Left -->
        <div class="flex-[2] flex flex-col gap-4">
            <!-- Low Quantity Stock (Products) -->
            <div class="border-2 border-slate-200 rounded-lg px-2 py-1">
                <h6 class="font-black text-xl mb-2">{{ __('Low Quantity Stock (Products)') }}</h6>
                @foreach ($products as $pro)
                    @if ($pro->isLowStock())
                        <div class="mb-2 flex items-center gap-4">
                            <div class="h-8 w-8">
                                @if ($pro->image != null)
                                    <img src="{{ $pro->image->url }}" alt="" class="h-full w-full object-contain">
                                @endif
                            </div>
                            <span class="flex-1 text-lg font-medium">{{ $pro->model_name }}</span>
                            <span class="flex-1 text-slate-500 text-center flex justify-center items-center">{{ __('Remaining Qty:') }} <span class="text-2xl ml-1">{{ $pro->warehouseAvailableStock($pro->id) }}</span></span>
                        </div>
                    @endif
                @endforeach
            </div>
            <!-- Low Quantity Stock (Raw Materials) -->
            <div class="border-2 border-slate-200 rounded-lg px-2 py-1">
                <h6 class="font-black text-xl mb-2">{{ __('Low Quantity Stock (Raw Materials)') }}</h6>
                @foreach ($raw_materials as $pro)
                    @if ($pro->isLowStock())
                        <div class="mb-2 flex items-center gap-4">
                            <div class="h-8 w-8">
                                @if ($pro->image != null)
                                    <img src="{{ $pro->image->url }}" alt="" class="h-full w-full object-contain">
                                @endif
                            </div>
                            <span class="flex-1 text-lg font-medium">{{ $pro->model_name }}</span>
                            <span class="flex-1 text-slate-500 text-center flex justify-center items-center">{{ __('Remaining Qty:') }} <span class="text-2xl ml-1">{{ $pro->warehouseAvailableStock($pro->id) }}</span></span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        <!-- Right -->
        <div class="flex-1 flex flex-col gap-4">
            <!-- Inventory Summary -->
            <div class="border-2 border-slate-200 rounded-lg px-2 py-1">
                <h6 class="font-black text-xl mb-2">{{ __('Inventory Summary') }}</h6>
                @php
                    $data = [
                        [
                            'label' => 'Active Products',
                            'value' => $active_product_count,
                        ],
                        [
                            'label' => 'Inactive Products',
                            'value' => $inactive_product_count,
                        ],
                        [
                            'label' => 'Quantity In Hand',
                            'value' => $warehouse_available_stock,
                        ],
                        [
                            'label' => 'Quantity To Be Received',
                            'value' => $production_stock,
                        ],
                    ];
                @endphp
                @foreach ($data as $d)
                    <div class="flex justify-between my-1.5">
                        <span>{{ __($d['label']) }}</span>
                        <span>{{ $d['value'] }}</span>
                    </div>
                @endforeach
            </div>
            <!-- Category -->
            <div class="border-2 border-slate-200 rounded-lg px-2 py-1">
                <h6 class="font-black text-xl mb-2">{{ __('Inventory Category') }}</h6>
                <canvas id="chart1"></canvas>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        CATEGORIES = @json($categories);
        // Chart 1
        const ctx = document.getElementById('chart1');
        const data = {
            type: 'doughnut',
            data: {
                labels: CATEGORIES['label'],
                datasets: [
                    {
                        data: CATEGORIES['data'],
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                }
            },
        }
        new Chart(ctx, data);
    </script>
@endpush