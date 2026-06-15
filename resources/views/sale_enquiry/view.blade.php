@extends('layouts.app')
@section('title', 'Sale Enquiry')

@vite(['resources/css/jquery.dataTables.min.css'])

@push('styles')
    <style>        #data-table thead th,
        #data-table tbody tr td {
            border-bottom: solid 1px rgb(209 213 219);
        }
        #data-table tbody tr:last-of-type td {
            border-bottom: none;
        }
    </style>
@endpush

@section('content')
    @php
        $sourceLabels = [
            1 => __('Website'), 2 => __('Facebook'), 3 => __('Shopee'), 4 => __('Lazada'),
            5 => __('Walk In'), 6 => __('Referral'), 7 => __('Instagram'), 8 => __('Tiktok'),
            9 => __('XHS'), 10 => __('Phone Call'), 11 => __('WhatsApp (Not from Platform)'), 12 => __('Google'),
        ];
        $contactLabels = [1 => __('WhatsApp'), 2 => __('Call'), 3 => __('Email')];
        $categoryLabels = [
            1 => __('Product / Pricing Enquiry'), 2 => __('Service Enquiry'),
            3 => __('Relocation Fridge Enquiry'), 4 => __('Trade-IN Enquiry'), 5 => __('Rental Enquiry'),
        ];
        $priorityLabels = [1 => __('Low'), 2 => __('Medium'), 3 => __('High')];
        $qualityLabels = [1 => __('Seen and Reply'), 2 => __('Seen No Reply'), 3 => __('No Seen No Reply')];
        $statusLabels = [
            1 => __('New'), 2 => __('In Progress'), 3 => __('Closed Deal (Converted)'), 4 => __('No Deal'),
        ];
        $statusClasses = [
            1 => 'bg-blue-100 text-blue-800',
            2 => 'bg-yellow-100 text-yellow-800',
            3 => 'bg-green-100 text-green-800',
            4 => 'bg-gray-200 text-gray-700',
        ];
        $isAssignee = auth()->id() == $enquiry->assigned_user_id;
    @endphp

    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('sale_enquiry.index') }}">{{ __('Sale Enquiry') }}: {{ $enquiry->sku }}</x-app.page-title>

        @if ($isAssignee)
            @if ($enquiry->accepted_at !== null)
                <span class="px-3 py-2 bg-green-100 text-green-800 rounded text-sm font-semibold">
                    {{ __('Accepted') }}
                </span>
            @elseif ($enquiry->rejected_at !== null)
                <span class="px-3 py-2 bg-red-100 text-red-800 rounded text-sm font-semibold">
                    {{ __('Rejected') }}
                </span>
            @endif
        @endif
    </div>
    @include('components.app.alert.parent')

    <!-- Enquiry Details -->
    <div class="bg-white p-4 rounded-md shadow mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-gray-800">{{ __('Enquiry Details') }}</h2>
            <span class="px-2 py-1 rounded text-xs font-semibold {{ $statusClasses[$enquiry->status] ?? 'bg-gray-100 text-gray-700' }}">
                {{ $statusLabels[$enquiry->status] ?? '-' }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <div class="text-gray-500">{{ __('Enquiry Date & Time') }}</div>
                <div class="text-gray-900">{{ $enquiry->enquiry_date?->format('d M Y, H:i') ?? '-' }}</div>
            </div>
            <div>
                <div class="text-gray-500">{{ __('Enquiry Source') }}</div>
                <div class="text-gray-900">{{ $sourceLabels[$enquiry->enquiry_source] ?? '-' }}</div>
            </div>
            <div>
                <div class="text-gray-500">{{ __('Category / Type of Enquiry') }}</div>
                <div class="text-gray-900">{{ $categoryLabels[$enquiry->category] ?? '-' }}</div>
            </div>

            <div>
                <div class="text-gray-500">{{ __('Customer Name') }}</div>
                <div class="text-gray-900">{{ $enquiry->name }}</div>
            </div>
            <div>
                <div class="text-gray-500">{{ __('Phone Number') }}</div>
                <div class="text-gray-900">{{ $enquiry->phone_number }}</div>
            </div>
            <div>
                <div class="text-gray-500">{{ __('Email Address') }}</div>
                <div class="text-gray-900">{{ $enquiry->email ?? '-' }}</div>
            </div>

            <div>
                <div class="text-gray-500">{{ __('Preferred Contact Method') }}</div>
                <div class="text-gray-900">{{ $contactLabels[$enquiry->preferred_contact_method] ?? '-' }}</div>
            </div>
            <div>
                <div class="text-gray-500">{{ __('Country') }}</div>
                <div class="text-gray-900">{{ $enquiry->countryModel?->name ?? '-' }}</div>
            </div>
            <div>
                <div class="text-gray-500">{{ __('State') }}</div>
                <div class="text-gray-900">{{ $enquiry->stateModel?->name ?? '-' }}</div>
            </div>

            <div>
                <div class="text-gray-500">{{ __('Product / Service Interested In') }}</div>
                <div class="text-gray-900">{{ $enquiry->product_service_interested ?? '-' }}</div>
            </div>
            <div>
                <div class="text-gray-500">{{ __('Assigned Staff / Salesperson') }}</div>
                <div class="text-gray-900">{{ $enquiry->assignedUser?->name ?? '-' }}</div>
            </div>
            <div>
                <div class="text-gray-500">{{ __('Priority Level') }}</div>
                <div class="text-gray-900">{{ $priorityLabels[$enquiry->priority] ?? '-' }}</div>
            </div>

            <div>
                <div class="text-gray-500">{{ __('Quality') }}</div>
                <div class="text-gray-900">{{ $qualityLabels[$enquiry->quality] ?? '-' }}</div>
            </div>
            <div>
                <div class="text-gray-500">{{ __('Promotion') }}</div>
                <div class="text-gray-900">
                    @if ($enquiry->promotion)
                        {{ $enquiry->promotion->sku }} -
                        {{ $enquiry->promotion->type == 'perc' ? number_format($enquiry->promotion->amount, 2) . '%' : 'RM' . number_format($enquiry->promotion->amount, 2) }}
                    @else
                        -
                    @endif
                </div>
            </div>
            <div>
                <div class="text-gray-500">{{ __('Created By') }}</div>
                <div class="text-gray-900">{{ $enquiry->createdByUser?->name ?? '-' }}</div>
            </div>

            <div>
                <div class="text-gray-500">{{ __('Accepted') }}</div>
                <div class="text-gray-900">
                    @if ($enquiry->accepted_at)
                        {{ $enquiry->accepted_at->format('d M Y, H:i') }}
                        @if ($enquiry->acceptedByUser)
                            ({{ $enquiry->acceptedByUser->name }})
                        @endif
                    @else
                        <span class="text-gray-400">{{ __('Not yet accepted') }}</span>
                    @endif
                </div>
            </div>

            <div>
                <div class="text-gray-500">{{ __('Rejected') }}</div>
                <div class="text-gray-900">
                    @if ($enquiry->rejected_at)
                        {{ $enquiry->rejected_at->format('d M Y, H:i') }}
                        @if ($enquiry->rejectedByUser)
                            ({{ $enquiry->rejectedByUser->name }})
                        @endif
                    @else
                        <span class="text-gray-400">{{ __('Not rejected') }}</span>
                    @endif
                </div>
            </div>

            <div class="md:col-span-3">
                <div class="text-gray-500">{{ __('Customer Message/ Remark') }}</div>
                <div class="text-gray-900 whitespace-pre-line">{{ $enquiry->description ?: '-' }}</div>
            </div>
        </div>
    </div>

    {{-- Sale Enquiry progress — auto-derived from the records this enquiry has spawned --}}
    <div class="bg-white p-4 rounded-md shadow mb-6">
        <h2 class="text-base font-semibold text-gray-800 mb-4">{{ __('Enquiry Progress') }}</h2>
        <ol class="relative border-l border-gray-300 ml-2">
            @foreach ($progress as $step)
                <li class="mb-5 ml-4">
                    <div class="absolute w-3 h-3 rounded-full -left-1.5 mt-1 {{ $step['done'] ? 'bg-green-500' : 'bg-gray-300' }}"></div>
                    <div class="flex flex-wrap items-center gap-x-2">
                        <span class="text-sm font-semibold {{ $step['done'] ? 'text-gray-900' : 'text-gray-400' }}">{{ $step['label'] }}</span>
                        @if ($step['ref'])
                            <span class="text-xs font-medium text-blue-700">{{ $step['ref'] }}</span>
                        @endif
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ $step['date'] ? \Carbon\Carbon::parse($step['date'])->format('d M Y, H:i') : __('Pending') }}
                    </div>
                </li>
            @endforeach
        </ol>
    </div>

    {{-- Salesperson status control: the assigned salesperson may change the
         Current Status only. "No Deal" needs management approval. --}}
    @if ($isAssignee && $enquiry->accepted_at !== null)
        <div class="bg-white p-4 rounded-md shadow mb-6">
            <h2 class="text-base font-semibold text-gray-800 mb-4">{{ __('Update Current Status') }}</h2>

            @if ($noDealPending)
                <div class="mb-4 px-3 py-2 bg-yellow-100 text-yellow-800 rounded text-sm font-semibold">
                    {{ __('A No Deal request is pending management approval.') }}
                </div>
            @endif

            @if ($noDealRejectedRemark)
                <div class="mb-4 px-3 py-2 bg-red-50 text-red-700 rounded text-sm">
                    <span class="font-semibold">{{ __('Last No Deal request was rejected') }}</span>
                    <span class="text-xs text-gray-500">[{{ \Carbon\Carbon::parse($noDealRejectedRemark->updated_at)->format('d M Y, H:i') }}]</span>
                    @if ($noDealRejectedRemark->reject_remark)
                        <div class="mt-1">{{ __('Reason') }}: {{ $noDealRejectedRemark->reject_remark }}</div>
                    @endif
                </div>
            @endif

            <form action="{{ route('sale_enquiry.update_status', ['enquiry' => $enquiry]) }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col">
                        <x-app.input.label id="status" class="mb-1">{{ __('Current Status') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select name="status" id="status" :hasError="$errors->has('status')" onchange="toggleNoDealReason()">
                            <option value="1" @selected(old('status', $enquiry->status) == 1)>{{ __('New') }}</option>
                            <option value="2" @selected(old('status', $enquiry->status) == 2)>{{ __('In Progress') }}</option>
                            <option value="3" @selected(old('status', $enquiry->status) == 3)>{{ __('Closed Deal (Converted)') }}</option>
                            <option value="4" @selected(old('status', $enquiry->status) == 4)>{{ __('No Deal') }}</option>
                        </x-app.input.select>
                        <x-input-error :messages="$errors->get('status')" class="mt-1" />
                    </div>
                    <div class="flex flex-col" id="no-deal-reason-wrapper" style="display: none;">
                        <x-app.input.label id="reason" class="mb-1">{{ __('No Deal Reason') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.textarea name="reason" id="reason" :hasError="$errors->has('reason')" :text="old('reason')" />
                        <x-input-error :messages="$errors->get('reason')" class="mt-1" />
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <x-app.button.submit>{{ __('Update Status') }}</x-app.button.submit>
                </div>
            </form>
        </div>

        {{-- Plain vanilla JS (no jQuery / stack dependency) so the field reliably
             toggles for sales-only users too. --}}
        <script>
            function toggleNoDealReason() {
                // Target the select by name: the label shares id="status", so
                // getElementById('status') would wrongly match the label.
                var sel = document.querySelector('select[name="status"]');
                var wrap = document.getElementById('no-deal-reason-wrapper');
                if (sel && wrap) {
                    wrap.style.display = (sel.value === '4') ? 'flex' : 'none';
                }
            }
            toggleNoDealReason();
        </script>
    @endif

    {{-- No Deal reason, shown once management approved the request --}}
    @if ($enquiry->status == \App\Models\SaleEnquiry::STATUS_CLOSED_DROPPED && $enquiry->no_deal_reason)
        <div class="bg-white p-4 rounded-md shadow mb-6">
            <h2 class="text-base font-semibold text-gray-800 mb-2">{{ __('No Deal Reason') }}</h2>
            <div class="text-sm text-gray-900 whitespace-pre-line">{{ $enquiry->no_deal_reason }}</div>
        </div>
    @endif

    {{-- Salespeople only see the enquiry details above; related sales (with amounts) are hidden from them. --}}
    @unless (isSalesOnly())
        <!-- Related Sales -->
        <div class="bg-white p-4 rounded-md shadow">
            <h2 class="text-base font-semibold text-gray-800 mb-4">{{ __('Related Sales') }}</h2>
            <table id="data-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
                <thead>
                    <tr>
                        <th>{{ __('Sales Order Number') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Customer Name') }}</th>
                        <th>{{ __('Amount (RM)') }}</th>
                        <th>{{ __('Payment Status') }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    @endunless
@endsection

@unless (isSalesOnly())
@push('scripts')
    <script>
        ENQUIRY_ID = @json($enquiry->id);

        // Datatable
        var dt = new DataTable('#data-table', {
            dom: 'rtip',
            scrollX: true,
            pagingType: 'numbers',
            pageLength: 10,
            processing: true,
            serverSide: true,
            order: [],
            columns: [
                { data: 'sku' },
                { data: 'date' },
                { data: 'customer' },
                { data: 'amount' },
                { data: 'payment_status' },
            ],
            columnDefs: [
                {
                    "width": "20%",
                    "targets": 0,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "15%",
                    "targets": 1,
                    orderable: false,
                    render: function(data, type, row) {
                        return data ?? '-'
                    }
                },
                {
                    "width": "30%",
                    "targets": 2,
                    orderable: false,
                    render: function(data, type, row) {
                        return data ?? '-'
                    }
                },
                {
                    "width": "15%",
                    "targets": 3,
                    orderable: false,
                    render: function(data, type, row) {
                        return data ?? '-'
                    }
                },
                {
                    "width": "20%",
                    "targets": 4,
                    orderable: false,
                    render: function(data, type, row) {
                        switch (data) {
                            case 1:
                                return `<span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs font-semibold">{!! __('Unpaid') !!}</span>`
                            case 2:
                                return `<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-semibold">{!! __('Partially Paid') !!}</span>`
                            case 3:
                                return `<span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-semibold">{!! __('Paid') !!}</span>`
                            default:
                                return '-'
                        }
                    }
                },
            ],
            ajax: {
                data: function(){
                    var info = $('#data-table').DataTable().page.info();
                    var url = "{{ route('sale_enquiry.view_get_data') }}"

                    url = `${url}?enquiry_id=${ENQUIRY_ID}&page=${info.page + 1}`
                    $('#data-table').DataTable().ajax.url(url);
                },
            },
        });
    </script>
@endpush
@endunless
