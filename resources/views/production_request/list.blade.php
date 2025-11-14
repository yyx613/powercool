@extends('layouts.app')
@section('title', 'Production Request')

@vite(['resources/css/jquery.dataTables.min.css'])

@push('styles')
    <style>
        #data-table,
        #data-table-sale {
            border: solid 1px rgb(209 213 219);
        }

        #data-table thead th,
        #data-table tbody tr td,
        #data-table-sale thead th,
        #data-table-sale tbody tr td {

            border-bottom: solid 1px rgb(209 213 219);
        }

        #data-table tbody tr:last-of-type td,
        #data-table-sale tbody tr:last-of-type td {
            border-bottom: none;
        }
    </style>
@endpush

@section('content')
    <div class="mb-6 flex justify-between items-start md:items-center flex-col md:flex-row">
        <x-app.page-title class="mb-4 md:mb-0">{{ __('Production Request') }}</x-app.page-title>
        <div class="flex gap-4">
            <a href="{{ route('production_request.create') }}"
                class="bg-yellow-400 shadow rounded-md py-2 px-4 flex items-center gap-x-2">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                    version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512"
                    style="enable-background:new 0 0 512 512;" xml:space="preserve" width="512" height="512">
                    <path
                        d="M480,224H288V32c0-17.673-14.327-32-32-32s-32,14.327-32,32v192H32c-17.673,0-32,14.327-32,32s14.327,32,32,32h192v192   c0,17.673,14.327,32,32,32s32-14.327,32-32V288h192c17.673,0,32-14.327,32-32S497.673,224,480,224z" />
                </svg>
                {{ __('New') }}
            </a>
        </div>
    </div>
    @include('components.app.alert.parent')

    @include('production_request.list_production_request')
    @include('production_request.list_sale_production_request')

    <x-app.modal.delete-modal />
    {{-- <x-app.modal.to-material-use-modal /> --}}
@endsection

@push('scripts')
    <script>
        $('#view-normal-btn').on('click', function() {
            $('#list-sale-production-request-container').addClass('hidden')
            $('#list-production-request-container').removeClass('hidden')
        })
        $('#view-sale-btn').on('click', function() {
            $('#list-sale-production-request-container').removeClass('hidden')
            $('#list-production-request-container').addClass('hidden')
        })
    </script>
@endpush
