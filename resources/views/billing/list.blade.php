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
    <div class="mb-6 flex justify-between">
        <x-app.page-title>{{ __('Billing') }}</x-app.page-title><div class="flex gap-x-4">
            <a href="#" class="bg-purple-200 shadow rounded-md py-2 px-4 flex items-center gap-x-2" id="submit-btn">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="arrow-circle-down" viewBox="0 0 24 24" width="512" height="512"><g><path d="M23,16H2.681l.014-.015L4.939,13.7a1,1,0,1,0-1.426-1.4L1.274,14.577c-.163.163-.391.413-.624.676a2.588,2.588,0,0,0,0,3.429c.233.262.461.512.618.67l2.245,2.284a1,1,0,0,0,1.426-1.4L2.744,18H23a1,1,0,0,0,0-2Z"/><path d="M1,8H21.255l-2.194,2.233a1,1,0,1,0,1.426,1.4l2.239-2.279c.163-.163.391-.413.624-.675a2.588,2.588,0,0,0,0-3.429c-.233-.263-.461-.513-.618-.67L20.487,2.3a1,1,0,0,0-1.426,1.4l2.251,2.29L21.32,6H1A1,1,0,0,0,1,8Z"/></g></svg>
                <span>{{ __('Submit E-Invoice') }}</span>
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
                    <th>
                        <input type="checkbox" id="select-all">
                    </th>
                    <th>{{ __('SKU') }}</th>
                    <th>{{ __('Billing Date') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <div id="loading-indicator" style="display: none;">
        <span class="loader"></span>
    </div>
    <x-app.modal.update-invoice-date-modal/>
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
                { data: 'id' },
                { data: 'sku' },
                { data: 'billing_date' },
                { data: 'action' },
            ],
            columnDefs: [
                {
                    "width": "5%",
                    "targets": 0,
                    orderable: false,
                    render: function(data, type, row) {
                        var disabled = row.enable ? 'disabled' : '';
                        var style = row.enable ? 'style="opacity: 0.5; cursor: not-allowed;"' : '';
                        return `<input type="checkbox" class="order-checkbox" data-id="${data}" ${disabled} ${style}>`;
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
                        return data
                    }
                },
                {
                    "width": "5%",
                    "targets": 3,
                    orderable: false,
                    render: function (data, type, row) {
                       return  `<div class="flex items-center justify-end gap-x-2 px-2">
                            <a href="{{ config('app.url') }}/download?file=${row.do_filename}&type=billing" class="rounded-full p-2 bg-green-200 inline-block" target="_blank" title="{!! __('Download Billing Delivery Order') !!}">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M16,0H8A5.006,5.006,0,0,0,3,5V23a1,1,0,0,0,1.564.825L6.67,22.386l2.106,1.439a1,1,0,0,0,1.13,0l2.1-1.439,2.1,1.439a1,1,0,0,0,1.131,0l2.1-1.438,2.1,1.437A1,1,0,0,0,21,23V5A5.006,5.006,0,0,0,16,0Zm3,21.1-1.1-.752a1,1,0,0,0-1.132,0l-2.1,1.439-2.1-1.439a1,1,0,0,0-1.131,0l-2.1,1.439-2.1-1.439a1,1,0,0,0-1.129,0L5,21.1V5A3,3,0,0,1,8,2h8a3,3,0,0,1,3,3Z"/><rect x="7" y="8" width="10" height="2" rx="1"/><rect x="7" y="12" width="8" height="2" rx="1"/></svg>
                            </a>
                            <a href="{{ config('app.url') }}/download?file=${row.inv_filename}&type=billing" class="rounded-full p-2 bg-blue-200 inline-block" target="_blank" title="{!! __('Download Billing Invoice') !!}">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M19.95,5.536l-3.485-3.485c-1.322-1.322-3.08-2.05-4.95-2.05H7C4.243,0,2,2.243,2,5v14c0,2.757,2.243,5,5,5h10c2.757,0,5-2.243,5-5V10.485c0-1.87-.728-3.627-2.05-4.95Zm-1.414,1.414c.318,.318,.587,.671,.805,1.05h-4.341c-.551,0-1-.449-1-1V2.659c.379,.218,.733,.487,1.05,.805l3.485,3.485Zm1.464,12.05c0,1.654-1.346,3-3,3H7c-1.654,0-3-1.346-3-3V5c0-1.654,1.346-3,3-3h4.515c.163,0,.325,.008,.485,.023V7c0,1.654,1.346,3,3,3h4.977c.015,.16,.023,.322,.023,.485v8.515Zm-4.5-6h-7c-1.378,0-2.5,1.122-2.5,2.5v2c0,1.378,1.122,2.5,2.5,2.5h7c1.378,0,2.5-1.122,2.5-2.5v-2c0-1.378-1.122-2.5-2.5-2.5Zm.5,4.5c0,.276-.224,.5-.5,.5h-7c-.276,0-.5-.224-.5-.5v-2c0-.276,.224-.5,.5-.5h7c.276,0,.5,.224,.5,.5v2ZM6,10c0-.552,.448-1,1-1h2c.552,0,1,.448,1,1s-.448,1-1,1h-2c-.552,0-1-.448-1-1Zm0-4c0-.552,.448-1,1-1h2c.552,0,1,.448,1,1s-.448,1-1,1h-2c-.552,0-1-.448-1-1Z"/></svg>
                            </a>
                       </div>`
                    }
                },
            ],
            ajax: {
                data: function(){
                    var info = $('#data-table').DataTable().page.info();
                    var url = "{{ route('billing.get_data') }}"

                    url = `${url}?page=${ info.page + 1 }`
                    $('#data-table').DataTable().ajax.url(url);
                },
            },
        });
        $('#filter_search').on('keyup', function() {
            dt.search($(this).val()).draw()
        })

        let selectedInvoices = [];

        $('#select-all').on('click', function() {
            let checked = this.checked;
            $('.order-checkbox').each(function() {
                if (!$(this).prop('disabled')) {
                    $(this).prop('checked', checked).trigger('change');
                }
            });
        });

        $(document).on('change', '.order-checkbox', function () {
            let invoiceId = $(this).data('id');
            let isChecked = this.checked;
            
            if (isChecked) {
                selectedInvoices.push({ id: invoiceId });
            } else {
                selectedInvoices = selectedInvoices.filter(invoice => invoice.id != invoiceId);
            }
            console.log(selectedInvoices)
            checkSelectAllStatus();
            toggleAssignButton();
        });


        function checkSelectAllStatus() {
            let enabledCheckboxes = $('.order-checkbox:not(:disabled)').length;
            let checkedEnabledCheckboxes = $('.order-checkbox:not(:disabled):checked').length;

            let allChecked = enabledCheckboxes > 0 && enabledCheckboxes === checkedEnabledCheckboxes;
            $('#select-all').prop('checked', allChecked);
        }

        $('#submit-btn').on('click', function(e) {
            e.preventDefault();
            if (selectedInvoices.length === 0) {
                alert("Please select at least one order to submit.");
                return;
            }
            submitEinvoice();
            
        });

        function submitEinvoice(){
            const loadingIndicator = document.getElementById('loading-indicator'); 
            loadingIndicator.style.display = 'flex';

            let url = "{{ config('app.url') }}";
            url = `${url}/e-invoice/billing-submit`;
            let billingIds = selectedInvoices.map(invoice => invoice.id);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'POST', 
                data: JSON.stringify({ billings: billingIds }),
                contentType: 'application/json',
                success: function(response) {
                    loadingIndicator.style.display = 'none';
                    $('.order-checkbox').prop('checked', false);
                    selectedInvoices = [];
                    $('#select-all').prop('checked', false);
                    const modal = document.getElementById('update-invoice-date-modal');
                    if (modal && modal.classList.contains('show-modal')) {
                        modal.classList.remove('show-modal');
                    }
                    if (response.errorDetails && response.errorDetails.length > 0) {
                        let errorMessage = "Some documents were rejected:\n";
                        
                        response.errorDetails.forEach(function(document) {
                            errorMessage += `\nInvoice: ${document.invoiceCodeNumber}\nError Code: ${document.error_code}\nMessage: ${document.error_message}\n`;
                            
                            document.details.forEach(function(detail) {
                                errorMessage += ` - Detail Code: ${detail.code}\n   Message: ${detail.message}\n   Target: ${detail.target}\n   Path: ${detail.propertyPath}\n`;
                            });
                        });
                        
                        alert(errorMessage);
                    } else {
                        alert(response.message || "Submit success");
                    }
                },
                error: function(error) {
                    loadingIndicator.style.display = 'none';

                    let errorMessage = "An unknown error occurred.";

                    if (error.responseJSON) {
                        if (error.responseJSON.error) {
                            errorMessage = error.responseJSON.error;
                        }

                        if (error.responseJSON.message) {
                            try {
                                const parsedMessage = JSON.parse(error.responseJSON.message);
                                if (parsedMessage.error) {
                                    errorMessage += `\nDetails: ${parsedMessage.error}`;
                                }
                            } catch (e) {
                                errorMessage += `\nDetails: ${error.responseJSON.message}`;
                            }
                        }
                    }

                    if (error.responseJSON.overdue_billing) {
                        const overdueInvoices = error.responseJSON.overdue_billing;

                        const container = document.getElementById('overdue-invoices-container');
                        container.innerHTML = ''; 

                        overdueInvoices.forEach((invoice, index) => {
                            const label = document.createElement('label');
                            label.className = 'mb-1';
                            label.innerHTML = `Billing SKU: ${invoice.sku}`;

                            const input = document.createElement('input');
                            input.type = 'datetime-local';
                            input.name = `invoice_date_${index}`;
                            input.id = `invoice-date-${index}`;
                            input.value = new Date(invoice.date).toISOString().slice(0, 16); 

                            input.className = 'w-full border rounded-md p-2 mb-2';

                            container.appendChild(label);
                            container.appendChild(input);
                        });
                        const modalTitle = document.querySelector('#update-invoice-date-modal h6');
                        if (modalTitle) {
                            modalTitle.textContent = `Update Billing Date (Should Not More Than 72 hours)`;
                        }
                        $('#update-invoice-date-modal').addClass('show-modal');
                                        
                    }

                    alert(errorMessage);
                }
            });
        }

        $('#yes-btn').on('click', function () {
            const container = $('#overdue-invoices-container');
            const updatedInvoices = [];
            let validationError = false;

            const now = new Date();
            
            const seventyTwoHoursAgo = new Date(now.getTime() - (72 * 60 * 60 * 1000));

            container.find('input').each(function (index) {
                const sku = container.find('label').eq(index).text().replace('Billing SKU: ', '').trim();
                const date = $(this).val();

                if (!date) {
                    alert(`Invoice SKU ${sku} date is required.`);
                    validationError = true;
                    return false; 
                }

                const invoiceDate = new Date(date);

                if (invoiceDate > now) {
                    alert(`Invoice SKU ${sku} cannot have a future date.`);
                    validationError = true;
                    return false; 
                }

                if (invoiceDate < seventyTwoHoursAgo) {
                    alert(`Invoice SKU ${sku} date cannot be older than 72 hours.`);
                    validationError = true;
                    return false; 
                }

                updatedInvoices.push({ sku, date });
            });

            if (validationError) {
                return;
            }

            $.ajax({
                url: "{{ route('update_billing_date') }}",
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                contentType: 'application/json',
                data: JSON.stringify({ billings: updatedInvoices }),
                success: function (response) {
                    submitEinvoice()
                },
                error: function (xhr) {
                    let errorMessage = 'Error updating billing: ';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage += xhr.responseJSON.message;
                    } else {
                        errorMessage += 'Unknown error occurred';
                    }
                    alert(errorMessage);
                },
            });
        });

        function toggleAssignButton() {
            if (selectedInvoices.length === 0) {
                $('#assign-btn').addClass('bg-gray-200 cursor-not-allowed').removeClass('bg-green-200').prop('disabled', true);
            } else {
                $('#assign-btn').removeClass('bg-gray-200 cursor-not-allowed').addClass('bg-green-200').prop('disabled', false);
            }
        }
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