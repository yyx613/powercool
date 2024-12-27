@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">{{ __('Dashboard') }}</h1>
        <div class="flex flex-col">
            <span class="text-2xl font-semibold">{{ __(now()->format('l')) }}</span>
            <span class="text-lg leading-none">{{ now()->format('d ') . __(now()->format('F')) . now()->format(' Y, H:i A') }}</span>
        </div>
    </div>
    <!-- Content -->
    <div class="flex flex-col lg:flex-row gap-4">
        <div class="flex-[2] flex flex-col gap-4">
            <!-- Today Task -->
            <div class="bg-white rounded-lg p-3 border">
                <div class="mb-4 flex items-center justify-between">
                    <h6 class="text-lg font-semibold">{{ __('Today Task') }}</h6>
                    <x-app.input.select name="task_status" id="" class="w-1/4 text-xs">
                        <option value="">{{ __('Select a status') }}</option>
                        @foreach ($task_status as $key => $label)
                            <option value="{{ $key }}" @selected($selected_task_status == $key)>{{ __($label) }}</option>
                        @endforeach
                    </x-app.input.select>
                </div>
                <div>
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th class="px-1 py-2 border-b text-sm w-4/6 text-left">{{ __('Task name') }}</th>
                                <th class="px-1 py-2 border-b text-sm w-1/6">{{ __('Due date') }}</th>
                                <th class="px-1 py-2 border-b text-sm w-1/6">{{ __('Status') }}</th>
                                <th class="px-1 py-2 border-b text-sm w-1/6">{{ __('Progress') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($today_tasks as $task)
                                <tr>
                                    <td class="px-1 py-2 text-sm">{{ $task->name }}</td>
                                    <td class="px-1 py-2 text-sm text-center">{{ $task->due_date }}</td>
                                    <td class="px-1 py-2 text-center">
                                        @php
                                            if ($task->status == 1) {
                                                $color = 'bg-red-400';
                                            } else if ($task->status == 2) {
                                                $color = 'bg-orange-400';
                                            } else if ($task->status == 3) {
                                                $color = 'bg-blue-400';
                                            } else if ($task->status == 4) {
                                                $color = 'bg-teal-400';
                                            }
                                        @endphp
                                        <span class="rounded-full py-1 px-2 text-xs font-semibold text-white {{ $color }}">{{ __($task->statusToHumanRead($task->status)) }}</span>
                                    </td>
                                    <td class="px-1 py-2 flex justify-center">
                                        <x-app.circular-progress perc="{{ $task->getProgress($task) }}" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Production Summary -->
            <div class="bg-white rounded-lg p-3 border">
                <div class="mb-4 flex items-center justify-between">
                    <h6 class="text-lg font-semibold">{{ __('Production Summary') }}</h6>
                    <x-app.input.select name="production_status" id="" class="w-1/4 text-xs">
                        <option value="">{{ __('Select a status') }}</option>
                        @foreach ($production_status as $key => $label)
                            <option value="{{ $key }}" @selected($selected_production_status == $key)>{{ __($label) }}</option>
                        @endforeach
                    </x-app.input.select>
                </div>
                <div>
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th class="px-1 py-2 border-b text-sm w-3/6 text-left">{{ __('Task name') }}</th>
                                <th class="px-1 py-2 border-b text-sm w-1/6">{{ __('Assigned') }}</th>
                                <th class="px-1 py-2 border-b text-sm w-1/6">{{ __('Due date') }}</th>
                                <th class="px-1 py-2 border-b text-sm w-1/6">{{ __('Status') }}</th>
                                <th class="px-1 py-2 border-b text-sm w-1/6">{{ __('Progress') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($production_summary as $ps)
                                <tr>
                                    <td class="px-1 py-2 text-sm">{{ $ps->name }}</td>
                                    <td class="px-1 py-2 text-sm text-center">
                                        @php
                                            $assigned = $ps->users()->pluck('name')->toArray();
                                            if (count($assigned) <= 0) {
                                                $assigned = '-';
                                            } else {
                                                $assigned = join(', ', $assigned);
                                            }
                                        @endphp
                                        {{ $assigned }}
                                    </td>
                                    <td class="px-1 py-2 text-sm text-center">{{ $ps->due_date }}</td>
                                    <td class="px-1 py-2 text-center">
                                        @php
                                            if ($ps->status == 1) {
                                                $color = 'bg-red-400';
                                            } else if ($ps->status == 2) {
                                                $color = 'bg-orange-400';
                                            } else if ($ps->status == 3) {
                                                $color = 'bg-blue-400';
                                            } else if ($ps->status == 4) {
                                                $color = 'bg-teal-400';
                                            }
                                        @endphp
                                        <span class="rounded-full py-1 px-2 text-xs font-semibold text-white {{ $color }}">{{ __($ps->statusToHumanRead($ps->status)) }}</span>
                                    </td>
                                    <td class="px-1 py-2 flex justify-center">
                                        <x-app.circular-progress perc="{{ $ps->getProgress($ps) }}" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Stock Alert (Product) -->
            <div class="bg-white rounded-lg p-3 border">
                <h6 class="text-lg font-semibold mb-4">{{ __('Stock Alert (Products)') }}</h6>
                @foreach ($products as $pro)
                    @if ($pro->isLowStock())
                        <div class="mb-2 flex items-center gap-4">
                            <div class="h-8 w-8">
                                @if ($pro->image != null)
                                    <img src="{{ $pro->image->url }}" alt="" class="h-full w-full object-contain">
                                @endif
                            </div>
                            <span class="flex-1 text-lg font-medium">{{ $pro->model_name }}</span>
                            <span class="flex-1 text-red-500 text-center">{{ $pro->warehouseAvailableStock($pro->id) }} {{ __('Left') }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
            <!-- Stock Alert (Raw Material) -->
            <div class="bg-white rounded-lg p-3 border">
                <h6 class="text-lg font-semibold mb-4">{{ __('Stock Alert (Raw Materials)') }}</h6>
                @foreach ($raw_materials as $pro)
                    @if ($pro->isLowStock())
                        <div class="mb-2 flex items-center gap-4">
                            <div class="h-8 w-8">
                                @if ($pro->image != null)
                                    <img src="{{ $pro->image->url }}" alt="" class="h-full w-full object-contain">
                                @endif
                            </div>
                            <span class="flex-1 text-lg font-medium">{{ $pro->model_name }}</span>
                            <span class="flex-1 text-red-500 text-center">{{ $pro->warehouseAvailableStock($pro->id) }} {{ __('Left') }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        <div class="flex-1 flex flex-col gap-4">
            <!-- Suppliers & Customers -->
            <div class="bg-white rounded-lg p-3 border">
                <h6 class="text-lg font-semibold mb-4">{{ __('Suppliers & Customers') }}</h6>
                <div class="flex">
                    <div class="flex-1 flex flex-col items-center">
                        <div class="p-2 rounded-full bg-sky-200">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                <path d="m9,12c3.309,0,6-2.691,6-6S12.309,0,9,0,3,2.691,3,6s2.691,6,6,6Zm0-10c2.206,0,4,1.794,4,4s-1.794,4-4,4-4-1.794-4-4,1.794-4,4-4Zm12,12h-5c-1.654,0-3,1.346-3,3v4c0,1.654,1.346,3,3,3h5c1.654,0,3-1.346,3-3v-4c0-1.654-1.346-3-3-3Zm1,7c0,.552-.449,1-1,1h-5c-.551,0-1-.448-1-1v-4c0-.552.449-1,1-1h5c.551,0,1,.448,1,1v4Zm-2-3c0,.553-.448,1-1,1h-1c-.552,0-1-.447-1-1s.448-1,1-1h1c.552,0,1,.447,1,1Zm-9.351-1.072c.42.358.47.989.112,1.41l-.5.586c-.06.07-.129.132-.207.183-.79.527-1.859.386-2.487-.331l-2.331-2.767c-2.03,1.294-3.237,3.495-3.237,5.886v1.105c0,.553-.448,1-1,1s-1-.447-1-1v-1.105c0-3.075,1.551-5.906,4.148-7.571.846-.542,1.973-.371,2.618.397l2.211,2.625.261-.307c.358-.42.99-.472,1.41-.111Z"/>
                            </svg>
                        </div>
                        <span class="text-lg font-semibold mt-2">{{ $suppliers_count }}</span>
                        <span class="text-sm">{{ __('Number of Suppliers') }}</span>
                    </div>
                    <div class="flex-1 flex flex-col items-center border-l">
                        <div class="p-2 rounded-full bg-emerald-200">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,16a4,4,0,1,1,4-4A4,4,0,0,1,12,16Zm0-6a2,2,0,1,0,2,2A2,2,0,0,0,12,10Zm6,13A6,6,0,0,0,6,23a1,1,0,0,0,2,0,4,4,0,0,1,8,0,1,1,0,0,0,2,0ZM18,8a4,4,0,1,1,4-4A4,4,0,0,1,18,8Zm0-6a2,2,0,1,0,2,2A2,2,0,0,0,18,2Zm6,13a6.006,6.006,0,0,0-6-6,1,1,0,0,0,0,2,4,4,0,0,1,4,4,1,1,0,0,0,2,0ZM6,8a4,4,0,1,1,4-4A4,4,0,0,1,6,8ZM6,2A2,2,0,1,0,8,4,2,2,0,0,0,6,2ZM2,15a4,4,0,0,1,4-4A1,1,0,0,0,6,9a6.006,6.006,0,0,0-6,6,1,1,0,0,0,2,0Z"/></svg>
                        </div>
                        <span class="text-lg font-semibold mt-2">{{ $customers_count }}</span>
                        <span class="text-sm">{{ __('Number of Customers') }}</span>
                    </div>
                </div>
            </div>
            <!-- Best Selling Products -->
            <div class="bg-white rounded-lg p-3 border">
                <h6 class="text-lg font-semibold mb-4">{{ __('Best Selling Products') }}</h6>
                @foreach ($best_selling_products as $row)
                    <div class="mb-2 flex items-center gap-4 bg-slate-50 p-2 rounded-md">
                        <div class="h-8 w-8 bg-white rounded-md">
                            @if ($row['product']->image != null)
                                <img src="{{ $row['product']->image->url }}" alt="" class="h-full w-full object-contain">
                            @else
                                <x-app.no-image-icon/>
                            @endif
                        </div>
                        <div class="flex flex-col flex-1">
                            <span class="text-md font-medium">{{ $row['product']->model_name }}</span>
                            <span class="text-xs font-medium text-slate-400">{{ $row['product']->sku }}</span>
                        </div>
                        <span class="flex-1 text-md font-medium text-center">{{ $row['count'] }} SO Involved</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $('select[name="task_status"], select[name="production_status"]').on('change', function() {
        let task_status = $('select[name="task_status"]').val()
        let production_status = $('select[name="production_status"]').val()
        let url = "{{ route('dashboard.index') }}"
        if (task_status != '' || production_status != '') {
            url = `${url}?task_status=${task_status}&production_status=${production_status}`
        }
        window.location.href = url
    })
</script>
@endpush
