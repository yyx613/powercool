@extends('layouts.app')
@section('title', 'E-Invoice')

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
        <x-app.page-title class="mb-4 md:mb-0">{{ __('E-Invoice') }}</x-app.page-title>
        <div class="flex gap-x-4">
            <a href="{{ route('to_note', ['from' => 'eInvoice']) }}" class="bg-green-200 shadow rounded-md py-2 px-4 flex items-center gap-x-2" id="submit-credit-btn1">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve" width="512" height="512">
                    <path d="M480,224H288V32c0-17.673-14.327-32-32-32s-32,14.327-32,32v192H32c-17.673,0-32,14.327-32,32s14.327,32,32,32h192v192   c0,17.673,14.327,32,32,32s32-14.327,32-32V288h192c17.673,0,32-14.327,32-32S497.673,224,480,224z"/>
                </svg>
                <span>{{ __('Submit Credit/Debit Note') }}</span>
            </a>
        </div>
    </div>
    @include('components.app.alert.parent')
    <div>
        <!-- Filters -->
        <div class="flex max-w-xs w-full mb-4">
            <div class="flex-1">
                <x-app.input.input name="filter_search" id="filter_search" class="flex items-center" placeholder="{{ __('Search') }}">
                    <div class="rounded-md border border-transparent p-1 ml-1">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24"><path d="M23.707,22.293l-5.969-5.969a10.016,10.016,0,1,0-1.414,1.414l5.969,5.969a1,1,0,0,0,1.414-1.414ZM10,18a8,8,0,1,1,8-8A8.009,8.009,0,0,1,10,18Z"/></svg>
                    </div>
                </x-app.input.input>
            </div>
        </div>

        <!-- Table -->
        <table id="data-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
            <thead>
                <tr>
                    <th>{{ __('UUID') }}</th>
                    <th>{{ __('Debtor Name') }}</th>
                    <th>{{ __('Total') }}</th>
                    <th>{{ __('Invoice Date') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('From') }}</th>
                    <th>{{ __('Submission Date') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <div id="loading-indicator" style="display: none;">
        <span class="loader"></span>
    </div>
    <x-app.modal.submit-credit-debit-note-modal/>
    <x-app.modal.cancel-e-invoice-modal/>
@endsection

@push('scripts')
    <script>
        $(document).ready(function(){
            dt.draw()
        })

        // Datatable
        var dt = new DataTable('#data-table', {
            dom: 'rtip',
            pagingType: 'numbers',
            pageLength: 10,
            processing: true,
            serverSide: true,
            order: [],
            columns: [
                { data: 'uuid' },
                { data: 'debtor_name' },
                { data: 'total' },
                { data: 'invoice_date' },
                { data: 'status' },
                { data: 'from' },
                { data: 'submission_date' },
                { data: 'id' },
            ],
            columnDefs: [
                { 
                    "width": "10%",
                    "targets": 0,
                    render: function(data, type, row) {
                        return data
                    }
                },
                { 
                    "width": "10%",
                    "targets": 1,
                    render: function(data, type, row) {
                        return data
                    }
                },
                { 
                    "width": "10%",
                    "targets": 2,
                    render: function(data, type, row) {
                        return `RM ${data}`
                    }
                },
                { 
                    "width": "10%",
                    "targets": 3,
                    render: function(data, type, row) {
                        return data
                    }
                },
                { 
                    "width": "10%",
                    "targets": 4,
                    render: function(data, type, row) {
                        return data
                    }
                },
                { 
                    "width": "10%",
                    "targets": 5,
                    render: function(data, type, row) {
                        return data
                    }
                },
                { 
                    "width": "10%",
                    "targets": 6,
                    render: function(data, type, row) {
                        return data
                    }
                },
                { 
                    "width": "5%",
                    "targets": 7,
                    orderable: false,
                    render: function (data, type, row) {
                        const submissionDate = new Date(row.submission_date);
                        const currentDate = new Date();
                        const hoursDifference = Math.abs(currentDate - submissionDate) / 36e5;
                        return  `<div class="flex items-center justify-end gap-x-2 px-2">
                            <a href="${row.validation_link}" class="rounded-full p-2 bg-gray-200 inline-block" target="_blank" title="Link For E-Invoice">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-link" viewBox="0 0 16 16">
                                <path d="M6.354 5.5H4a3 3 0 0 0 0 6h3a3 3 0 0 0 2.83-4H9q-.13 0-.25.031A2 2 0 0 1 7 10.5H4a2 2 0 1 1 0-4h1.535c.218-.376.495-.714.82-1z"/>
                                <path d="M9 5.5a3 3 0 0 0-2.83 4h1.098A2 2 0 0 1 9 6.5h3a2 2 0 1 1 0 4h-1.535a4 4 0 0 1-.82 1H12a3 3 0 1 0 0-6z"/>
                                </svg>                            
                            </a>
                            ${row.status !== "Cancelled" && hoursDifference <= 72 ? `
                                <a href="#" class="rounded-full p-2 bg-green-200 inline-block" target="_blank" id="cancel_document" data-id="${row.uuid}" title="{!! __('Cancel Document') !!}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16">
                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
                                    </svg>
                                </a>
                            ` : ''}
                            ${row.status == "Cancelled" ? `
                                <a href="#" class="rounded-full p-2 bg-green-200 inline-block" target="_blank" id="resubmit_document" data-id="${row.uuid}" title="{!! __('Resubmit Document') !!}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cloud-upload" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M4.406 1.342A5.53 5.53 0 0 1 8 0c2.69 0 4.923 2 5.166 4.579C14.758 4.804 16 6.137 16 7.773 16 9.569 14.502 11 12.687 11H10a.5.5 0 0 1 0-1h2.688C13.979 10 15 8.988 15 7.773c0-1.216-1.02-2.228-2.313-2.228h-.5v-.5C12.188 2.825 10.328 1 8 1a4.53 4.53 0 0 0-2.941 1.1c-.757.652-1.153 1.438-1.153 2.055v.448l-.445.049C2.064 4.805 1 5.952 1 7.318 1 8.785 2.23 10 3.781 10H6a.5.5 0 0 1 0 1H3.781C1.708 11 0 9.366 0 7.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383"/>
                                        <path fill-rule="evenodd" d="M7.646 4.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707V14.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708z"/>
                                    </svg>
                                </a>
                            ` : ''}
                            <a href="{{ config('app.url') }}/e-invoice/download?uuid=${row.uuid}&type=eInvoice" class="rounded-full p-2 bg-green-200 inline-block" target="_blank" title="{!! __('Download') !!}">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M17.974,7.146c-.332-.066-.603-.273-.742-.569-1.552-3.271-5.143-5.1-8.735-4.438-3.272,.6-5.837,3.212-6.384,6.501-.162,.971-.15,1.943,.033,2.89,.06,.309-.073,.653-.346,.901-1.145,1.041-1.801,2.524-1.801,4.07,0,3.032,2.467,5.5,5.5,5.5h11c4.136,0,7.5-3.364,7.5-7.5,0-3.565-2.534-6.658-6.026-7.354Zm-1.474,12.854H5.5c-1.93,0-3.5-1.57-3.5-3.5,0-.983,.418-1.928,1.146-2.59,.786-.715,1.155-1.773,.963-2.763-.138-.712-.146-1.445-.024-2.181,.403-2.422,2.365-4.421,4.771-4.862,.385-.07,.768-.104,1.145-.104,2.312,0,4.406,1.289,5.422,3.434,.414,.872,1.2,1.481,2.158,1.673,2.559,.511,4.417,2.778,4.417,5.394,0,3.032-2.467,5.5-5.5,5.5Zm-1.379-6.707c.391,.391,.391,1.023,0,1.414l-2.707,2.707c-.387,.387-.896,.582-1.405,.584l-.009,.002-.009-.002c-.509-.002-1.018-.197-1.405-.584l-2.707-2.707c-.391-.391-.391-1.023,0-1.414s1.023-.391,1.414,0l1.707,1.707v-5c0-.553,.448-1,1-1s1,.447,1,1v5l1.707-1.707c.391-.391,1.023-.391,1.414,0Z"/></svg>
                            </a>
                           ${row.from !== "Billing" ? `
                            <button class="send-to-customer-btn rounded-full p-2 bg-green-200 inline-block"
                                data-id="${row.id}" data-type="eInvoice" title="{!! __('Send Email') !!}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope-arrow-up" viewBox="0 0 16 16">
                                    <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v4.5a.5.5 0 0 1-1 0V5.383l-7 4.2-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h5.5a.5.5 0 0 1 0 1H2a2 2 0 0 1-2-1.99zm1 7.105 4.708-2.897L1 5.383zM1 4v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1"/>
                                    <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.354-5.354 1.25 1.25a.5.5 0 0 1-.708.708L13 12.207V14a.5.5 0 0 1-1 0v-1.717l-.28.305a.5.5 0 0 1-.737-.676l1.149-1.25a.5.5 0 0 1 .722-.016"/>
                                </svg>
                            </button>
                            ` : ''}
                        </div>`;
                    }
                },
            ],
            ajax: {
                data: function(){
                    var info = $('#data-table').DataTable().page.info();
                    var url = "{{ route('invoice.get_data_e-invoice') }}"
                    
                    url = `${url}?page=${ info.page + 1 }`
                    $('#data-table').DataTable().ajax.url(url);
                },
            },
        });
        $('#filter_search').on('keyup', function() {
            dt.search($(this).val()).draw()
        })

        $(document).ready(function() {
            $('#data-table').on('click', '.send-to-customer-btn', function() {
                let invoiceId = $(this).data('id');
                let type = $(this).data('type');

                $.ajax({
                    url: "{{ config('app.url') }}/e-invoice/send-to-customer",
                    type: "GET",
                    data: { id: invoiceId, type: type },
                    success: function(response) {
                        alert("Email has been Send Succesfully!");
                    },
                    error: function(xhr, status, error) {
                        alert("Email Send Unsuccesfull!");
                    }
                });
            });
        });

        $(document).on('click', '#cancel_document', function(e) {
            e.preventDefault();

            const uuid = $(this).data('id');
            $('#cancel-e-invoice-modal').addClass('show-modal');
            $('#cancel-e-invoice-modal').data('uuid', uuid);
        });

        $('#cancel-e-invoice-modal #yes-btn-cancel').on('click', function(e) {
            e.preventDefault();

            const uuid = $('#cancel-e-invoice-modal').data('uuid');
            const reason = $('#cancel-reason').val();

            if (!reason) {
                alert('Please enter a reason for cancellation.');
                return;
            }
            const loadingIndicator = document.getElementById('loading-indicator'); 
            loadingIndicator.style.display = 'flex';
            let url = "{{ route('cancel_e_invoice') }}";
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'POST',
                data: {
                    uuid: uuid,
                    reason: reason
                },
                success: function(response) {
                    $('#cancel-e-invoice-modal').removeClass('show-modal');
                    loadingIndicator.style.display = 'none';
                    if(response.error){
                        alert(response.error);
                    }else{
                        dt.ajax.reload();
                        alert("E-Invoice cancel Successfully!");
                    }
                },
                error: function(error) {
                    alert('Failed to cancel document.');
                }
            });
        });

        $(document).on('click', '#resubmit_document', function(e) {
            e.preventDefault();

            const loadingIndicator = document.getElementById('loading-indicator'); 
            loadingIndicator.style.display = 'flex';

            const uuid = $(this).data('id');
            let url = "{{ route('resubmit_e_invoice') }}";
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'POST',
                data: {
                    uuid: uuid,
                },
                success: function(response) {
                    loadingIndicator.style.display = 'none';
                    dt.ajax.reload();
                    alert(response.message || "Submit success");
                },
                error: function(error) {
                    alert("Submit failed: " + (error.responseJSON.message || "Unknown error"));
                }
            });
        });
    </script>
@endpush
@push('styles')
<style>
    #loading-indicator {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .loader {
        width: 48px;
        height: 48px;
        border: 5px solid #FFF;
        border-bottom-color: transparent;
        border-radius: 50%;
        display: inline-block;
        box-sizing: border-box;
        animation: rotation 1s linear infinite;
    }

    @keyframes rotation {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
</style>
@endpush