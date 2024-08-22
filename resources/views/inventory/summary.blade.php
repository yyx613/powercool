@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <x-app.page-title>Inventory Summary</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <!-- Summary -->
    <div class="mb-6 flex gap-x-4">
        @php
            $stocks = [
                [
                    'label' => 'Total Stock',
                    'value' => $total_stock,
                    'bgColor' => 'bg-slate-200',
                ],
                [
                    'label' => 'Reserved Stock',
                    'value' => $reserved_stock,
                    'bgColor' => 'bg-sky-100',
                ],
                [
                    'label' => 'Available Stock',
                    'value' => $total_stock - $reserved_stock,
                    'bgColor' => 'bg-slate-300',
                ],
                [
                    'label' => 'Production Stock',
                    'value' => $production_stock,
                    'bgColor' => 'bg-sky-200',
                ],
            ];
        @endphp
        @foreach ($stocks as $stock)
            <div class="p-3 flex-1 flex flex-col rounded-lg {{ $stock['bgColor'] }}">
                <span class="text-2xl font-black">{{ $stock['label'] }}</span>
                <span class="text-4xl font-black text-center mt-8">{{ $stock['value'] }}</span>
            </div>
        @endforeach
    </div>
    <!-- Content -->
    <div class="flex gap-4">
        <!-- Left -->
        <div class="flex-[2] flex flex-col gap-4">
            <!-- Low Quantity Stock (Products) -->
            <div class="border-2 border-slate-200 rounded-lg px-2 py-1">
                <h6 class="font-black text-xl mb-2">Low Quantity Stock (Products)</h6>
                @foreach ($products as $pro)
                    @if ($pro->isLowStock())
                        <div class="mb-2 flex items-center gap-4">
                            <div class="h-8 w-8">
                                <img src="{{ $pro->image->url }}" alt="" class="h-full w-full object-contain">
                            </div>
                            <span class="flex-1 text-lg font-medium">{{ $pro->model_name }}</span>
                            <span class="flex-1 text-slate-500 text-center flex justify-center items-center">Remaining Qty: <span class="text-2xl ml-1">{{ $pro->totalStockCount($pro->id) - $pro->reservedStockCount($pro->id) }}</span></span>
                        </div>
                    @endif
                @endforeach
            </div>
            <!-- Low Quantity Stock (Raw Materials) -->
            <div class="border-2 border-slate-200 rounded-lg px-2 py-1">
                <h6 class="font-black text-xl mb-2">Low Quantity Stock (Raw Materials)</h6>
                @foreach ($raw_materials as $pro)
                    @if ($pro->isLowStock())
                        <div class="mb-2 flex items-center gap-4">
                            <div class="h-8 w-8">
                                <img src="{{ $pro->image->url }}" alt="" class="h-full w-full object-contain">
                            </div>
                            <span class="flex-1 text-lg font-medium">{{ $pro->model_name }}</span>
                            <span class="flex-1 text-slate-500 text-center flex justify-center items-center">Remaining Qty: <span class="text-2xl ml-1">{{ $pro->totalStockCount($pro->id) - $pro->reservedStockCount($pro->id) }}</span></span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        <!-- Right -->
        <div class="flex-1 flex flex-col gap-4">
            <!-- Inventory Summary -->
            <div class="border-2 border-slate-200 rounded-lg px-2 py-1">
                <h6 class="font-black text-xl mb-2">Inventory Summary</h6>
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
                            'value' => 10 - 3,
                        ],
                        [
                            'label' => 'Quantity To Be Received',
                            'value' => 10 - 3,
                        ],
                    ];
                @endphp
                @foreach ($data as $d)
                    <div class="flex justify-between my-1.5">
                        <span>{{ $d['label'] }}</span>
                        <span>{{ $d['value'] }}</span>
                    </div>
                @endforeach
            </div>
            <!-- Category -->
            <div class="border-2 border-slate-200 rounded-lg px-2 py-1">
                <h6 class="font-black text-xl mb-2">Inventory Category</h6>
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