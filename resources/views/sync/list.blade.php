@extends('layouts.app')

@vite(['resources/css/jquery.dataTables.min.css'])

@push('styles')
    <style>
        #data-table {
            border: solid 1px rgb(209 213 219);
        }
        #data-table thead th,
        #data-table tbody tr td {
            border-bottom: solid 1px rgb(209 213 219);
        }
        #data-table tbody tr:last-of-type td {
            border-bottom: none;
        }
    </style>
@endpush

@section('content')
    <div class="mb-6 flex justify-between items-start md:items-center flex-col md:flex-row">
        <x-app.page-title class="mb-4 md:mb-0">{{ __('Sync') }}</x-app.page-title>
    </div>
    <div class="flex gap-2">
        <a href="{{ config('app.url') }}/sync-msic-codes" target="_blank" class="bg-amber-200 font-semibold rounded-md py-2 px-4 text-sm">{{ __('Sync MSIC') }}</a>
        <a href="{{ config('app.url') }}/sync-classification-codes" target="_blank" class="bg-amber-200 font-semibold rounded-md py-2 px-4 text-sm">{{ __('Sync Classification Code') }}</a>
    </div>
@endsection
