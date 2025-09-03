@extends('layouts.app')
@section('title', 'Draft E Invoice')

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
    <div class="mb-6 flex justify-between items-start lg:items-center flex-col lg:flex-row">
        <x-app.page-title class="mb-4 lg:mb-0">{{ __('Draft E Invoice') }}</x-app.page-title>
        <div class="flex gap-x-4">
            <a href="#" class="bg-purple-200 shadow rounded-md py-2 px-4 flex items-center gap-x-2"
                id="submit-consolidated-btn">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="arrow-circle-down" viewBox="0 0 24 24"
                    width="512" height="512">
                    <g>
                        <path
                            d="M23,16H2.681l.014-.015L4.939,13.7a1,1,0,1,0-1.426-1.4L1.274,14.577c-.163.163-.391.413-.624.676a2.588,2.588,0,0,0,0,3.429c.233.262.461.512.618.67l2.245,2.284a1,1,0,0,0,1.426-1.4L2.744,18H23a1,1,0,0,0,0-2Z" />
                        <path
                            d="M1,8H21.255l-2.194,2.233a1,1,0,1,0,1.426,1.4l2.239-2.279c.163-.163.391-.413.624-.675a2.588,2.588,0,0,0,0-3.429c-.233-.263-.461-.513-.618-.67L20.487,2.3a1,1,0,0,0-1.426,1.4l2.251,2.29L21.32,6H1A1,1,0,0,0,1,8Z" />
                    </g>
                </svg>
                <span>{{ __('Submit Consolidated E-Invoice') }}</span>
            </a>
        </div>
    </div>
    @include('components.app.alert.parent')
    <div>
        <!-- Filters -->
        <div class="flex max-w-xs w-full mb-4">
            <div class="flex-1">
                <x-app.input.input name="filter_search" id="filter_search" class="flex items-center"
                    placeholder="{{ __('Search') }}">
                    <div class="rounded-md border border-transparent p-1 ml-1">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24">
                            <path
                                d="M23.707,22.293l-5.969-5.969a10.016,10.016,0,1,0-1.414,1.414l5.969,5.969a1,1,0,0,0,1.414-1.414ZM10,18a8,8,0,1,1,8-8A8.009,8.009,0,0,1,10,18Z" />
                        </svg>
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
                    <th>{{ __('Doc No.') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Debtor Code') }}</th>
                    <th>{{ __('Transfer From') }}</th>
                    <th>{{ __('Debtor Name') }}</th>
                    <th>{{ __('Agent') }}</th>
                    <th>{{ __('Curr. Code') }}</th>
                    <th>{{ __('Total') }}</th>
                    <th>{{ __('Created By') }}</th>
                    <th>{{ __('Company Group') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <div id="loading-indicator" style="display: none;">
        <span class="loader"></span>
    </div>
@endsection

@push('scripts')
    <script>
        INIT_LOAD = true;
        DEFAULT_PAGE = @json($default_page ?? null);

        $('#data-table').on('draw.dt', function() {
            $('.order-checkbox').each(function() {
                let invoiceId = $(this).data('id');

                if (selectedInvoices.some(invoice => invoice.id === invoiceId)) {
                    $(this).prop('checked', true);
                } else {
                    $(this).prop('checked', false);
                }
            });

            checkSelectAllStatus();
        });

        // Datatable
        var dt = new DataTable('#data-table', {
            dom: 'rtip',
            pagingType: 'numbers',
            pageLength: 10,
            processing: true,
            serverSide: true,
            order: [],
            displayStart: DEFAULT_PAGE != null ? (DEFAULT_PAGE - 1) * 10 : 0,
            columns: [{
                    data: 'id'
                },
                {
                    data: 'doc_no'
                },
                {
                    data: 'date'
                },
                {
                    data: 'debtor_code'
                },
                {
                    data: 'transfer_from'
                },
                {
                    data: 'debtor_name'
                },
                {
                    data: 'agent'
                },
                {
                    data: 'curr_code'
                },
                {
                    data: 'total'
                },
                {
                    data: 'created_by'
                },
                {
                    data: 'company_group'
                },
                {
                    data: 'status'
                },
                {
                    data: 'action'
                },
            ],
            columnDefs: [{
                    "width": "5%",
                    "targets": 0,
                    orderable: false,
                    render: function(data, type, row) {
                        var disabled = row.enable ? 'disabled' : '';
                        var style = row.enable ? 'style="opacity: 0.5; cursor: not-allowed;"' : '';
                        return `<input type="checkbox" class="order-checkbox" data-id="${data}" data-company="${row.company_group}" ${disabled} ${style}>`;
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
                    "width": "10%",
                    "targets": 3,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "10%",
                    "targets": 4,
                    orderable: false,
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
                    "width": "10%",
                    "targets": 7,
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "10%",
                    "targets": 8,
                    orderable: false,
                    render: function(data, type, row) {
                        return `RM ${data}`
                    }
                },
                {
                    "width": "10%",
                    "targets": 9,
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "10%",
                    "targets": 10,
                    orderable: false,
                    render: function(data, type, row) {
                        if (data == 'powercool') {
                            return 'Power Cool'
                        } else if (data == 'hi_ten') {
                            return 'Hi-Ten'
                        }
                        return data
                    }
                },
                {
                    "width": "10%",
                    "targets": 11,
                    render: function(data, type, row) {
                        let label = ''
                        if (data == 1) {
                            label = '{!! __('Approved') !!}'
                        } else if (data == 2) {
                            label = '{!! __('Submitted to E-Invoice') !!}'
                        }
                        return `<span class="status" data-id="${row.id}">${label}</span>`
                    }
                },
                {
                    "width": "5%",
                    "targets": 12,
                    orderable: false,
                    render: function(data, type, row) {
                        return `<div class="flex items-center justify-end gap-x-2 px-2">
                            <button class="rounded-full p-2 bg-purple-200 inline-block to-e-invoice-btns ${row.status == 1 && row.tin_number != null ? '' : 'hidden'}" data-has-tin-number="${row.tin_number == null ? false : true}" data-id="${row.id}" data-invoice-id="${row.invoice_id}" data-company="${row.company_group}" title="{!! __('To E-Invoice') !!}">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="arrow-circle-down" viewBox="0 0 24 24" width="512" height="512"> <g> <path d="M23,16H2.681l.014-.015L4.939,13.7a1,1,0,1,0-1.426-1.4L1.274,14.577c-.163.163-.391.413-.624.676a2.588,2.588,0,0,0,0,3.429c.233.262.461.512.618.67l2.245,2.284a1,1,0,0,0,1.426-1.4L2.744,18H23a1,1,0,0,0,0-2Z" /> <path d="M1,8H21.255l-2.194,2.233a1,1,0,1,0,1.426,1.4l2.239-2.279c.163-.163.391-.413.624-.675a2.588,2.588,0,0,0,0-3.429c-.233-.263-.461-.513-.618-.67L20.487,2.3a1,1,0,0,0-1.426,1.4l2.251,2.29L21.32,6H1A1,1,0,0,0,1,8Z" /> </g> </svg>
                            </button>
                            ${
                                row.status == 1 ? '' :
                                `<button class="rounded-full p-2 bg-green-200 inline-block approve-btns" data-id="${row.id}" title="{!! __('Approve') !!}">
                                                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="m18.214,9.098c.387.394.381,1.027-.014,1.414l-4.426,4.345c-.783.768-1.791,1.151-2.8,1.151-.998,0-1.996-.376-2.776-1.129l-1.899-1.867c-.394-.387-.399-1.02-.012-1.414.386-.395,1.021-.4,1.414-.012l1.893,1.861c.776.75,2.001.746,2.781-.018l4.425-4.344c.393-.388,1.024-.381,1.414.013Zm5.786,2.902c0,6.617-5.383,12-12,12S0,18.617,0,12,5.383,0,12,0s12,5.383,12,12Zm-2,0c0-5.514-4.486-10-10-10S2,6.486,2,12s4.486,10,10,10,10-4.486,10-10Z"/></svg>
                                                                </button>`
                            }
                       </div>`
                    }
                },
            ],
            ajax: {
                data: function() {
                    var info = $('#data-table').DataTable().page.info();
                    var url = "{{ route('invoice.get_data_draft_e_invoice') }}"

                    url =
                        `${url}?page=${ INIT_LOAD == true && DEFAULT_PAGE != null ? DEFAULT_PAGE : info.page + 1 }`
                    $('#data-table').DataTable().ajax.url(url);

                    INIT_LOAD = false
                },
            },
        });
        $('#filter_search').on('keyup', function() {
            dt.search($(this).val()).draw()
        })

        function getOtherInvolvedInv(inv_id) {
            $('#do-inv-void-transfer-back-modal .cancellation-hint').remove()

            let url = "{{ config('app.url') }}"
            url = `${url}/invoice/get-cancellation-involved-inv/${inv_id}`

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                contentType: 'application/json',
                success: function(res) {
                    for (const key in res.involved) {
                        const element = res.involved[key];

                        let clone = $('#do-inv-void-transfer-back-modal #info-template')[0].cloneNode(true);

                        $(clone).find('#main').text(key)
                        for (let i = 0; i < element.length; i++) {
                            let soClone = $('#do-inv-void-transfer-back-modal #info-body-container #sub')[0]
                                .cloneNode(
                                    true);

                            $(soClone).text(element[i])
                            $(clone).append(soClone)
                        }
                        $(clone).addClass('cancellation-hint')
                        $(clone).removeClass('hidden')

                        $('#do-inv-void-transfer-back-modal #info-body-container').append(clone)
                    }

                    $('#do-inv-void-transfer-back-modal #warning-txt').text("{!! __('Following INV & DO will be cancelled, SO will be remain as active.') !!}")
                    $('#do-inv-void-transfer-back-modal #void-btn').attr('href',
                        `{{ config('app.url') }}/invoice/cancel?involved_inv_skus=${JSON.stringify(res.involved_inv_skus)}&involved_do_skus=${JSON.stringify(res.involved_do_skus)}&involved_so_skus=${JSON.stringify(res.involved_so_skus)}&type=void`
                    )
                    $('#do-inv-void-transfer-back-modal #transfer-back-btn').attr('href',
                        `{{ config('app.url') }}/invoice/cancel?involved_inv_skus=${JSON.stringify(res.involved_inv_skus)}&involved_do_skus=${JSON.stringify(res.involved_do_skus)}&involved_so_skus=${JSON.stringify(res.involved_so_skus)}&type=transfer-back`
                    )
                    $('#do-inv-void-transfer-back-modal').addClass('show-modal')
                }
            });
        }

        let selectedInvoices = [];

        $('#select-all').on('click', function() {
            let checked = this.checked;
            $('.order-checkbox').each(function() {
                if (!$(this).prop('disabled')) {
                    $(this).prop('checked', checked).trigger('change');
                }
            });
        });

        let firstCompany = "";

        $(document).on('change', '.order-checkbox', function() {
            let invoiceId = $(this).data('id');
            let invoiceCompany = $(this).data('company');
            let isChecked = this.checked;

            firstCompany = selectedInvoices.length > 0 ? selectedInvoices[0].company : null;

            if (isChecked) {
                if (firstCompany && invoiceCompany !== firstCompany) {
                    alert("You cannot select invoices from different companies.");
                    $(this).prop('checked', false);
                    return;
                }

                selectedInvoices.push({
                    id: invoiceId,
                    company: invoiceCompany
                });
                firstCompany = firstCompany == null ? invoiceCompany : firstCompany;
            } else {
                selectedInvoices = selectedInvoices.filter(invoice => invoice.id !== invoiceId);
            }

            checkSelectAllStatus();
            toggleAssignButton();
        });


        function checkSelectAllStatus() {
            let enabledCheckboxes = $('.order-checkbox:not(:disabled)').length;
            let checkedEnabledCheckboxes = $('.order-checkbox:not(:disabled):checked').length;

            let allChecked = enabledCheckboxes > 0 && enabledCheckboxes === checkedEnabledCheckboxes;
            $('#select-all').prop('checked', allChecked);
        }

        $('#submit-consolidated-btn').on('click', function(e) {
            e.preventDefault();

            if (selectedInvoices.length === 0) {
                alert("Please select at least one order to submit.");
                return;
            }

            const loadingIndicator = document.getElementById('loading-indicator');
            loadingIndicator.style.display = 'flex';

            let url = "{{ config('app.url') }}";
            url = `${url}/e-invoice/submit-consolidated`;

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'POST',
                data: JSON.stringify({
                    invoices: selectedInvoices,
                    company: firstCompany
                }),
                contentType: 'application/json',
                success: function(response) {
                    loadingIndicator.style.display = 'none';
                    $('.order-checkbox').prop('checked', false);
                    selectedInvoices = [];
                    $('#select-all').prop('checked', false);
                    if (response.errorDetails && response.errorDetails.length > 0) {
                        let errorMessage = "Some documents were rejected:\n";

                        response.errorDetails.forEach(function(document) {
                            errorMessage +=
                                `\nInvoice: ${document.invoiceCodeNumber}\nError Code: ${document.error_code}\nMessage: ${document.error_message}\n`;

                            document.details.forEach(function(detail) {
                                errorMessage +=
                                    ` - Detail Code: ${detail.code}\n   Message: ${detail.message}\n   Target: ${detail.target}\n   Path: ${detail.propertyPath}\n`;
                            });
                        });

                        alert(errorMessage);
                    } else {
                        alert(response.message || "Submit success");
                    }
                },
                error: function(error) {
                    loadingIndicator.style.display = 'none';
                    try {

                        if (error.responseJSON.rejectedDocuments) {
                            error.responseJSON.rejectedDocuments.forEach(function(document) {
                                errorMessage +=
                                    `\nInvoice: ${document.invoiceCodeNumber}\nError Code: ${document.error_code}\nMessage: ${document.error_message}\n`;
                                document.details.forEach(function(detail) {
                                    errorMessage +=
                                        ` - Detail Code: ${detail.code}\n   Message: ${detail.message}\n   Target: ${detail.target}\n   Path: ${detail.propertyPath}\n`;
                                });
                            });
                        }
                    } catch (error) {
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
                    }


                    alert(errorMessage);
                }
            });
        });

        function toggleAssignButton() {
            if (selectedInvoices.length === 0) {
                $('#assign-btn').addClass('bg-gray-200 cursor-not-allowed').removeClass('bg-green-200').prop('disabled',
                    true);
            } else {
                $('#assign-btn').removeClass('bg-gray-200 cursor-not-allowed').addClass('bg-green-200').prop('disabled',
                    false);
            }
        }

        $('#yes-btn').on('click', function() {
            const container = $('#overdue-invoices-container');
            const updatedInvoices = [];
            let validationError = false;

            const now = new Date();

            const seventyTwoHoursAgo = new Date(now.getTime() - (72 * 60 * 60 * 1000));

            container.find('input').each(function(index) {
                const sku = container.find('label').eq(index).text().replace('Invoice SKU: ', '').trim();
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

                updatedInvoices.push({
                    sku,
                    date
                });
            });

            if (validationError) {
                return;
            }

            $.ajax({
                url: "{{ route('update_invoice_date') }}",
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                contentType: 'application/json',
                data: JSON.stringify({
                    invoices: updatedInvoices
                }),
                success: function(response) {
                    submitEinvoice()
                },
                error: function(xhr) {
                    let errorMessage = 'Error updating invoices: ';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage += xhr.responseJSON.message;
                    } else {
                        errorMessage += 'Unknown error occurred';
                    }
                    alert(errorMessage);
                },
            });
        });

        $('body').on('click', '.to-e-invoice-btns', function() {
            let id = $(this).data('id')
            let invoiceId = $(this).data('invoice-id')
            let companyGroup = $(this).data('company')
            let selectedInvoices = [{
                'id': invoiceId,
                'company': companyGroup
            }]

            submitEinvoice(selectedInvoices, companyGroup, id)
        })
        $('body').on('click', '.approve-btns', function() {
            let id = $(this).data('id')

            let url = "{{ config('app.url') }}";
            url = `${url}/invoice/approve-draft-e-invoice/${id}`;
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                contentType: 'application/json',
                success: function(response) {
                    $(`.to-e-invoice-btns[data-id=${id}][data-has-tin-number="true"]`).removeClass(
                        'hidden')
                    $(`.approve-btns[data-id=${id}]`).remove()
                    $(`.status[data-id=${id}]`).text('{!! __('Approved') !!}')
                },
            })
        })

        function submitEinvoice(selectedInvoices, firstCompan, id) {
            const loadingIndicator = document.getElementById('loading-indicator');
            loadingIndicator.style.display = 'flex';

            let url = "{{ config('app.url') }}";
            url = `${url}/e-invoice/submit`;
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'POST',
                data: JSON.stringify({
                    invoices: selectedInvoices,
                    company: firstCompany
                }),
                contentType: 'application/json',
                success: function(response) {
                    loadingIndicator.style.display = 'none';
                    $('.order-checkbox').prop('checked', false);
                    $('#select-all').prop('checked', false);
                    const modal = document.getElementById('update-invoice-date-modal');
                    if (modal && modal.classList.contains('show-modal')) {
                        modal.classList.remove('show-modal');
                    }
                    if (response.errorDetails && response.errorDetails.length > 0) {
                        let errorMessage = "Some documents were rejected:\n";
                        try {
                            response.errorDetails.forEach(function(document) {
                                errorMessage +=
                                    `\nInvoice: ${document.invoiceCodeNumber}\nError Code: ${document.error_code}\nMessage: ${document.error_message}\n`;

                                document.details.forEach(function(detail) {
                                    errorMessage +=
                                        ` - Detail Code: ${detail.code}\n   Message: ${detail.message}\n   Target: ${detail.target}\n   Path: ${detail.propertyPath}\n`;
                                });
                            });
                        } catch (error) {
                            errorMessage = ""
                            response.errorDetails.forEach(function(document) {
                                errorMessage +=
                                    `\nInvoice: ${document.invoiceCodeNumber}\nError: ${document.error}\n`;
                            });
                        }


                        alert(errorMessage);
                    } else {
                        $(`.to-e-invoice-btns[data-id=${id}]`).remove()
                        alert(response.message || "Submit success");
                    }
                },
                error: function(error) {
                    loadingIndicator.style.display = 'none';


                    let errorMessage = "An error occurred.";

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

                        if (error.responseJSON.overdue_invoices) {
                            const overdueInvoices = error.responseJSON.overdue_invoices;

                            const container = document.getElementById('overdue-invoices-container');
                            container.innerHTML = '';

                            overdueInvoices.forEach((invoice, index) => {
                                const label = document.createElement('label');
                                label.className = 'mb-1';
                                label.innerHTML = `Invoice SKU: ${invoice.sku}`;

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
                                modalTitle.textContent = `Update Invoice Date (Should Not More Than 72 hours)`;
                            }
                            $('#update-invoice-date-modal').addClass('show-modal');

                        }
                    }

                    alert(errorMessage);
                }
            });
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
