@extends('layouts.app')
@section('title', 'Vehicle Details')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('vehicle.index') }}">{{ __('Vehicle Details') }}</x-app.page-title>
    </div>

    @include('components.app.alert.parent')

    <!-- Vehicle Information -->
    <div class="bg-white p-6 rounded-md shadow mb-6">
        <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M19.5,16c0,.553-.447,1-1,1h-2c-.553,0-1-.447-1-1s.447-1,1-1h2c.553,0,1,.447,1,1Zm4.5-1v5c0,2.206-1.794,4-4,4H4c-2.206,0-4-1.794-4-4v-5c0-2.206,1.794-4,4-4h1V4C5,1.794,6.794,0,9,0h6c2.206,0,4,1.794,4,4v7h1c2.206,0,4,1.794,4,4ZM7,11h10V4c0-1.103-.897-2-2-2h-6c-1.103,0-2,.897-2,2v7Zm-3,11h7V13H4c-1.103,0-2,.897-2,2v5c0,1.103,.897,2,2,2Zm18-7c0-1.103-.897-2-2-2h-7v9h7c1.103,0,2-.897,2-2v-5Zm-14.5,0h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1s-.447-1-1-1ZM14,5c0-.553-.447-1-1-1h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1Z"/></svg>
            <span class="text-lg ml-3 font-bold">{{ __('Vehicle Information') }}</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <p class="text-sm text-gray-600">{{ __('Plate Number') }}</p>
                <p class="font-semibold">{{ $vehicle->plate_number }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">{{ __('No Chasis / No Enjin') }}</p>
                <p class="font-semibold">{{ $vehicle->chasis }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">{{ __('Buatan / Nama Model') }}</p>
                <p class="font-semibold">{{ $vehicle->buatan_nama_model }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">{{ __('Keupayaan Enjin') }}</p>
                <p class="font-semibold">{{ $vehicle->keupayaan_enjin }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">{{ __('Bahan Bakar') }}</p>
                <p class="font-semibold">{{ $vehicle->bahan_bakar }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">{{ __('Department') }}</p>
                <p class="font-semibold">{{ $vehicle->department }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">{{ __('Status') }}</p>
                <p class="font-semibold">
                    @if($vehicle->status == 1)
                        <span class="text-green-600">{{ __('Active') }}</span>
                    @else
                        <span class="text-red-600">{{ __('Sold') }}</span>
                    @endif
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-600">{{ __('Type') }}</p>
                <p class="font-semibold">{{ $vehicle->type == 1 ? __('Car') : __('Lorry') }}</p>
            </div>
        </div>
    </div>

    <!-- Vehicle Services -->
    <div class="bg-white p-6 rounded-md shadow">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M19.5,16c0,.553-.447,1-1,1h-2c-.553,0-1-.447-1-1s.447-1,1-1h2c.553,0,1,.447,1,1Zm4.5-1v5c0,2.206-1.794,4-4,4H4c-2.206,0-4-1.794-4-4v-5c0-2.206,1.794-4,4-4h1V4C5,1.794,6.794,0,9,0h6c2.206,0,4,1.794,4,4v7h1c2.206,0,4,1.794,4,4ZM7,11h10V4c0-1.103-.897-2-2-2h-6c-1.103,0-2,.897-2,2v7Zm-3,11h7V13H4c-1.103,0-2,.897-2,2v5c0,1.103,.897,2,2,2Zm18-7c0-1.103-.897-2-2-2h-7v9h7c1.103,0,2-.897,2-2v-5Zm-14.5,0h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1s-.447-1-1-1ZM14,5c0-.553-.447-1-1-1h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1Z"/></svg>
                <span class="text-lg ml-3 font-bold">{{ __('Latest Service Per Type') }}</span>
            </div>
        </div>

        @if($latest_services->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">{{ __('Service Type') }}</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">{{ __('Key In Date') }}</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">{{ __('Expiration Date') }}</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">{{ __('Reminder Date') }}</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">{{ __('Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($latest_services as $service)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <span class="font-medium">{{ \App\Models\VehicleService::types[$service->type] }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($service->date)
                                        {{ \Carbon\Carbon::parse($service->date)->format('Y M d') }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($service->type == 1 && $service->to_date)
                                        <span class="font-semibold text-blue-600">{{ \Carbon\Carbon::parse($service->to_date)->format('Y M d') }}</span>
                                    @elseif($service->type != 1 && $service->date)
                                        <span class="font-semibold text-blue-600">{{ \Carbon\Carbon::parse($service->date)->format('Y M d') }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($service->remind_at)
                                        <span class="text-orange-600">{{ \Carbon\Carbon::parse($service->remind_at)->format('Y M d') }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($service->amount)
                                        RM {{ number_format($service->amount, 2) }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <svg class="h-16 w-16 mx-auto text-gray-300 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-gray-500 text-lg">{{ __('No service records found for this vehicle') }}</p>
                <a href="{{ route('vehicle_service.create') }}?vehicle={{ $vehicle->id }}" class="mt-4 inline-block bg-yellow-400 shadow rounded-md py-2 px-4">
                    {{ __('Add First Service') }}
                </a>
            </div>
        @endif
    </div>
@endsection
