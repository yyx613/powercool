@extends('layouts.app')
@section('title', 'Invoice')

@section('content')
    <div class="mb-6">
        <x-app.page-title url="{{ route('invoice.e-invoice.index') }}">Submit Credit/Debit Note</x-app.page-title>
    </div>
    <div class="bg-white p-4 border rounded-md flex gap-x-14">
        <!-- Steps -->
        <div class="flex flex-col gap-y-6 flex-1">
            <div class="flex items-center bg-yellow-100 rounded overflow-hidden {{ $step != 1 ? 'opacity-25' : ''  }}">
                <h6 class="font-semibold mx-4 p-2">From Selection</h6>
            </div>
            <div class="flex items-center bg-yellow-100 rounded overflow-hidden {{ $step != 2 ? 'opacity-25' : ''  }}">
                <div class="bg-yellow-300 p-2">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,24C5.383,24,0,18.617,0,12S5.383,0,12,0s12,5.383,12,12-5.383,12-12,12Zm0-22C6.486,2,2,6.486,2,12s4.486,10,10,10,10-4.486,10-10S17.514,2,12,2Zm1,15V7c0-.404-.244-.77-.617-.924-.375-.157-.805-.069-1.09,.217l-2.444,2.444c-.391,.391-.391,1.023,0,1.414s1.023,.391,1.414,0l.737-.737v7.586c0,.553,.448,1,1,1s1-.447,1-1Z"/></svg>
                </div>
                <h6 class="font-semibold mx-4">Company Selection</h6>
            </div>
            <div class="flex items-center bg-yellow-100 rounded overflow-hidden {{ $step != 3 ? 'opacity-25' : ''  }}">
                <div class="bg-yellow-300 p-2">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,24C5.383,24,0,18.617,0,12S5.383,0,12,0s12,5.383,12,12-5.383,12-12,12Zm0-22C6.486,2,2,6.486,2,12s4.486,10,10,10,10-4.486,10-10S17.514,2,12,2Zm4,15c0-.553-.448-1-1-1h-4.781c.426-.37,1.069-.72,1.742-1.086,1.754-.956,4.156-2.265,4.035-5.131-.089-2.121-1.844-3.783-3.995-3.783-2.206,0-4,1.794-4,4,0,.553,.448,1,1,1s1-.447,1-1c0-1.103,.897-2,2-2,1.058,0,1.954,.838,1.997,1.867,.064,1.513-1.088,2.253-2.994,3.29-.99,.54-1.925,1.049-2.559,1.797-.475,.56-.58,1.319-.272,1.983,.304,.655,.942,1.062,1.666,1.062h5.162c.552,0,1-.447,1-1Z"/></svg>
                </div>
                <h6 class="font-semibold mx-4">Note Type Selection</h6>
            </div>
            <!-- Step 1 -->
            <div class="flex items-center bg-yellow-100 rounded overflow-hidden {{ $step != 4 ? 'opacity-25' : ''  }}">
                <div class="bg-yellow-300 p-2">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,0C5.383,0,0,5.383,0,12s5.383,12,12,12,12-5.383,12-12S18.617,0,12,0Zm0,22c-5.514,0-10-4.486-10-10S6.486,2,12,2s10,4.486,10,10-4.486,10-10,10Zm4-8c0,2.206-1.794,4-4,4h-3c-.552,0-1-.447-1-1s.448-1,1-1h3c1.103,0,2-.897,2-2s-.897-2-2-2h-2c-.552,0-1-.447-1-1s.448-1,1-1h2c.551,0,1-.448,1-1s-.449-1-1-1h-3c-.552,0-1-.447-1-1s.448-1,1-1h3c1.654,0,3,1.346,3,3,0,.68-.236,1.301-.619,1.805,.977,.73,1.619,1.885,1.619,3.195Z"/></svg>
                </div>
                <h6 class="font-semibold mx-4">Customer Selection</h6>
            </div>
            <!-- Step 2 -->
            <div class="flex items-center bg-yellow-100 rounded overflow-hidden {{ $step != 5 ? 'opacity-25' : ''  }}">
                <div class="bg-yellow-300 p-2">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,0C5.383,0,0,5.383,0,12s5.383,12,12,12,12-5.383,12-12S18.617,0,12,0Zm0,22c-5.514,0-10-4.486-10-10S6.486,2,12,2s10,4.486,10,10-4.486,10-10,10Zm4-15v10c0,.553-.447,1-1,1s-1-.447-1-1v-3h-3c-1.654,0-3-1.346-3-3V7c0-.553,.447-1,1-1s1,.447,1,1v4c0,.552,.448,1,1,1h3V7c0-.553,.447-1,1-1s1,.447,1,1Z"/></svg>
                </div>
                <h6 class="font-semibold mx-4">E Invoice Selection</h6>
            </div>
            <div class="flex items-center bg-yellow-100 rounded overflow-hidden {{ $step != 6 ? 'opacity-25' : ''  }}">
                <div class="bg-yellow-300 p-2">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,24C5.383,24,0,18.617,0,12S5.383,0,12,0s12,5.383,12,12-5.383,12-12,12Zm0-22C6.486,2,2,6.486,2,12s4.486,10,10,10,10-4.486,10-10S17.514,2,12,2Zm2.901,14.754c.763-.804,1.151-1.857,1.094-2.968-.111-2.123-1.941-3.786-4.165-3.786h-1.83v-2h4c.553,0,1-.447,1-1s-.447-1-1-1h-4c-1.103,0-2,.897-2,2v3c0,.553,.447,1,1,1h2.83c1.141,0,2.112,.849,2.167,1.891,.029,.557-.165,1.084-.547,1.486-.382,.401-.896,.623-1.45,.623h-3c-.553,0-1,.447-1,1s.447,1,1,1h3c1.092,0,2.149-.454,2.901-1.246Z"/></svg>
                </div>
                <h6 class="font-semibold mx-4">Update E Invoice</h6>
            </div>
        </div>
        <!-- Steps Content -->
        <div class="flex-[3]">
            <!-- Step 1 -->
            @if ($step == 1)
                <div>
                    <div class="mb-2">
                        <h5 class="text-md font-semibold">Select from to proceed</h5>
                    </div>
                    <ul>
                        <li class="mb-4 rounded-md cursor-pointer transition duration-300 border border-slate-100 hover:border-black customer-selections" >
                            <a href="{{ route('to_note') }}?fromBilling=false" class="text-sm flex items-center justify-between p-2 font-semibold">
                                Customer
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="arrow-circle-down" viewBox="0 0 24 24" width="512" height="512"><path d="M0,12A12,12,0,1,0,12,0,12.013,12.013,0,0,0,0,12Zm22,0A10,10,0,1,1,12,2,10.011,10.011,0,0,1,22,12Z"/><path d="M16,12a2.993,2.993,0,0,1-.752,1.987c-.291.327-.574.637-.777.84L11.647,17.7a1,1,0,1,1-1.426-1.4L13.05,13.42c.187-.188.441-.468.7-.759a1,1,0,0,0,0-1.323c-.258-.29-.512-.57-.693-.752L10.221,7.7a1,1,0,1,1,1.426-1.4l2.829,2.879c.2.2.48.507.769.833A2.99,2.99,0,0,1,16,12Z"/></svg>
                            </a>
                        </li>
                        <li class="mb-4 rounded-md cursor-pointer transition duration-300 border border-slate-100 hover:border-black customer-selections" >
                            <a href="{{ route('to_note') }}?fromBilling=true" class="text-sm flex items-center justify-between p-2 font-semibold">
                                Billing
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="arrow-circle-down" viewBox="0 0 24 24" width="512" height="512"><path d="M0,12A12,12,0,1,0,12,0,12.013,12.013,0,0,0,0,12Zm22,0A10,10,0,1,1,12,2,10.011,10.011,0,0,1,22,12Z"/><path d="M16,12a2.993,2.993,0,0,1-.752,1.987c-.291.327-.574.637-.777.84L11.647,17.7a1,1,0,1,1-1.426-1.4L13.05,13.42c.187-.188.441-.468.7-.759a1,1,0,0,0,0-1.323c-.258-.29-.512-.57-.693-.752L10.221,7.7a1,1,0,1,1,1.426-1.4l2.829,2.879c.2.2.48.507.769.833A2.99,2.99,0,0,1,16,12Z"/></svg>
                            </a>
                        </li>
                    </ul>
                </div>
            @endif
            @if ($step == 2)
                <div>
                    <div class="mb-2">
                        <h5 class="text-md font-semibold">Select a company to proceed</h5>
                    </div>
                    <ul>
                        <li class="mb-4 rounded-md cursor-pointer transition duration-300 border border-slate-100 hover:border-black customer-selections" >
                            <a href="{{ route('to_note') }}?company=powercool" class="text-sm flex items-center justify-between p-2 font-semibold">
                                PowerCool
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="arrow-circle-down" viewBox="0 0 24 24" width="512" height="512"><path d="M0,12A12,12,0,1,0,12,0,12.013,12.013,0,0,0,0,12Zm22,0A10,10,0,1,1,12,2,10.011,10.011,0,0,1,22,12Z"/><path d="M16,12a2.993,2.993,0,0,1-.752,1.987c-.291.327-.574.637-.777.84L11.647,17.7a1,1,0,1,1-1.426-1.4L13.05,13.42c.187-.188.441-.468.7-.759a1,1,0,0,0,0-1.323c-.258-.29-.512-.57-.693-.752L10.221,7.7a1,1,0,1,1,1.426-1.4l2.829,2.879c.2.2.48.507.769.833A2.99,2.99,0,0,1,16,12Z"/></svg>
                            </a>
                        </li>
                        <li class="mb-4 rounded-md cursor-pointer transition duration-300 border border-slate-100 hover:border-black customer-selections" >
                            <a href="{{ route('to_note') }}?company=hiten" class="text-sm flex items-center justify-between p-2 font-semibold">
                                HiTen
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="arrow-circle-down" viewBox="0 0 24 24" width="512" height="512"><path d="M0,12A12,12,0,1,0,12,0,12.013,12.013,0,0,0,0,12Zm22,0A10,10,0,1,1,12,2,10.011,10.011,0,0,1,22,12Z"/><path d="M16,12a2.993,2.993,0,0,1-.752,1.987c-.291.327-.574.637-.777.84L11.647,17.7a1,1,0,1,1-1.426-1.4L13.05,13.42c.187-.188.441-.468.7-.759a1,1,0,0,0,0-1.323c-.258-.29-.512-.57-.693-.752L10.221,7.7a1,1,0,1,1,1.426-1.4l2.829,2.879c.2.2.48.507.769.833A2.99,2.99,0,0,1,16,12Z"/></svg>
                            </a>
                        </li>
                    </ul>
                </div>
            @endif
            @if ($step == 3)
                <div>
                    <div class="mb-2">
                        <h5 class="text-md font-semibold">Select Credit or Debit Note to proceed</h5>
                    </div>
                    <ul>
                        <li class="mb-4 rounded-md cursor-pointer transition duration-300 border border-slate-100 hover:border-black customer-selections" >
                            <a href="{{ route('to_note') }}?type=credit" class="text-sm flex items-center justify-between p-2 font-semibold">
                                Credit Note
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="arrow-circle-down" viewBox="0 0 24 24" width="512" height="512"><path d="M0,12A12,12,0,1,0,12,0,12.013,12.013,0,0,0,0,12Zm22,0A10,10,0,1,1,12,2,10.011,10.011,0,0,1,22,12Z"/><path d="M16,12a2.993,2.993,0,0,1-.752,1.987c-.291.327-.574.637-.777.84L11.647,17.7a1,1,0,1,1-1.426-1.4L13.05,13.42c.187-.188.441-.468.7-.759a1,1,0,0,0,0-1.323c-.258-.29-.512-.57-.693-.752L10.221,7.7a1,1,0,1,1,1.426-1.4l2.829,2.879c.2.2.48.507.769.833A2.99,2.99,0,0,1,16,12Z"/></svg>
                            </a>
                        </li>
                        <li class="mb-4 rounded-md cursor-pointer transition duration-300 border border-slate-100 hover:border-black customer-selections" >
                            <a href="{{ route('to_note') }}?type=debit" class="text-sm flex items-center justify-between p-2 font-semibold">
                                Debit Note
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="arrow-circle-down" viewBox="0 0 24 24" width="512" height="512"><path d="M0,12A12,12,0,1,0,12,0,12.013,12.013,0,0,0,0,12Zm22,0A10,10,0,1,1,12,2,10.011,10.011,0,0,1,22,12Z"/><path d="M16,12a2.993,2.993,0,0,1-.752,1.987c-.291.327-.574.637-.777.84L11.647,17.7a1,1,0,1,1-1.426-1.4L13.05,13.42c.187-.188.441-.468.7-.759a1,1,0,0,0,0-1.323c-.258-.29-.512-.57-.693-.752L10.221,7.7a1,1,0,1,1,1.426-1.4l2.829,2.879c.2.2.48.507.769.833A2.99,2.99,0,0,1,16,12Z"/></svg>
                            </a>
                        </li>
                    </ul>
                </div>
            @endif
            @if ($step == 4)
                <div>
                    <div class="mb-2">
                        <h5 class="text-md font-semibold">Select a customer to proceed</h5>
                    </div>
                    <div class="mb-4">
                        <x-app.input.input name="search_customer" placeholder="Search customer" />
                    </div>
                    @if (count($customers) > 0)
                        <ul>
                            @foreach ($customers as $cus)
                                <li class="mb-4 rounded-md cursor-pointer transition duration-300 border border-slate-100 hover:border-black customer-selections" data-id="{{ $cus->id }}">
                                    <a href="{{ route('to_note') }}?cus={{ $cus->id }}" class="text-sm flex items-center justify-between p-2 font-semibold">
                                        {{ $cus->name }}
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="arrow-circle-down" viewBox="0 0 24 24" width="512" height="512"><path d="M0,12A12,12,0,1,0,12,0,12.013,12.013,0,0,0,0,12Zm22,0A10,10,0,1,1,12,2,10.011,10.011,0,0,1,22,12Z"/><path d="M16,12a2.993,2.993,0,0,1-.752,1.987c-.291.327-.574.637-.777.84L11.647,17.7a1,1,0,1,1-1.426-1.4L13.05,13.42c.187-.188.441-.468.7-.759a1,1,0,0,0,0-1.323c-.258-.29-.512-.57-.693-.752L10.221,7.7a1,1,0,1,1,1.426-1.4l2.829,2.879c.2.2.48.507.769.833A2.99,2.99,0,0,1,16,12Z"/></svg>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        @include('components.app.no-data')
                    @endif
                </div>
            @endif
            <!-- Step 2 -->
            @if ($step == 5)
                <div>
                    <div class="mb-2">
                        <h5 class="text-md font-semibold">Select e-invoices to proceed</h5>
                    </div>
                    <div class="mb-4">
                        <x-app.input.input name="search_eInvoices" placeholder="Search e-invoices" />
                    </div>
                    @if (count($eInvoices) > 0)
                        <ul>
                            @foreach ($eInvoices as $eInvoice)
                                <li class="mb-4 rounded-md cursor-pointer transition duration-300 border border-slate-100 hover:border-black e-invoices-selections"  data-id="{{ $eInvoice->id }}">
                                    <label class="flex items-center p-2">
                                        <input type="checkbox" name="selected_einvoices[]" value="{{ $eInvoice->id }}" class="mr-2">
                                        <span class="text-sm font-semibold">{{ $eInvoice->uuid }}</span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                        <div class="flex justify-end mt-8">
                            <a href="#" onclick="submitSelectedInvoices()" class="bg-slate-100 rounded-md py-2 px-4 flex justify-center items-center gap-x-2" id="convert-btn">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="arrow-circle-down" viewBox="0 0 24 24" width="512" height="512"><g><path d="M23,16H2.681l.014-.015L4.939,13.7a1,1,0,1,0-1.426-1.4L1.274,14.577c-.163.163-.391.413-.624.676a2.588,2.588,0,0,0,0,3.429c.233.262.461.512.618.67l2.245,2.284a1,1,0,0,0,1.426-1.4L2.744,18H23a1,1,0,0,0,0-2Z"/><path d="M1,8H21.255l-2.194,2.233a1,1,0,1,0,1.426,1.4l2.239-2.279c.163-.163.391-.413.624-.675a2.588,2.588,0,0,0,0-3.429c-.233-.263-.461-.513-.618-.67L20.487,2.3a1,1,0,0,0-1.426,1.4l2.251,2.29L21.32,6H1A1,1,0,0,0,1,8Z"/></g></svg>
                                <span class="text-sm font-semibold">Next</span>
                            </a>
                        </div>
                    @else
                        @include('components.app.no-data')
                    @endif
                </div>
            @endif
            @if ($step == 6)
                <div>
                    <div class="mb-2">
                        <h5 class="text-md font-semibold">Update Invoice Data to proceed</h5>
                    </div>

                    @if (count($results) > 0)
                        <form action="{{ route('submit.note') }}" method="POST" id="main-form">
                            @csrf
                            <input type="hidden" name="noteType" value="credit">
                            <input type="hidden" name="type" value="eInvoice">
                        
                            <ul>
                                @foreach ($results as $result)
                                    <li class="mb-4 rounded-md border border-slate-100 p-2">
                                        <h6 class="font-semibold">Invoice UUID: {{ $result['invoice_uuid'] }}</h6>
                        
                                        <input type="hidden" name="invoices[{{ $loop->index }}][invoice_uuid]" value="{{ $result['invoice_uuid'] }}">
                        
                                        <table class="text-sm rounded-lg overflow-hidden w-full mt-2">
                                            <thead>
                                                <tr>
                                                    <th style="width: 10%;">No.</th>
                                                    <th style="width: 40%;">Name</th>
                                                    <th style="width: 25%;">Price</th>
                                                    <th style="width: 25%;">Quantity</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $totalPrice = 0;
                                                @endphp
                                                @foreach ($result['items'] as $key => $item)
                                                    @php
                                                        $itemTotal = $item['price'] * $item['qty'];
                                                        $totalPrice += $itemTotal;
                                                    @endphp
                                                    <tr>
                                                        <input type="hidden" name="invoices[{{ $loop->parent->index }}][items][{{ $key }}][product_id]" value="{{ $item['product_id']}}" class="text-sm w-full border-none focus:ring-0">
                                                        <td>{{ $key + 1 }}</td>
                                                        <td style="max-width: 100px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                            {{ $item['name'] }}
                                                        </td>
                                                        <td>
                                                            <div class="bg-white rounded-md border border-gray-300 p-1 text-center" style="width: 50%; display: inline-block;">
                                                                <input type="text" name="invoices[{{ $loop->parent->index }}][items][{{ $key }}][price]" value="{{ $item['price'] }}" class="text-sm w-full border-none focus:ring-0">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="bg-white rounded-md border border-gray-300 p-1 text-center" style="width: 50%; display: inline-block;">
                                                                <input type="text" name="invoices[{{ $loop->parent->index }}][items][{{ $key }}][qty]" value="{{ $item['qty'] }}" class="text-sm w-full border-none focus:ring-0">
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td class="font-black pt-2">Total</td>
                                                    <td class="font-black pt-2 total-price">RM {{ number_format($totalPrice, 2) }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </li>
                                @endforeach
                            </ul>
                        
                            <div class="flex justify-end mt-8">
                                <button type="submit" class="bg-slate-100 rounded-md py-2 px-4 flex justify-center items-center gap-x-2">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="arrow-circle-down" viewBox="0 0 24 24" width="512" height="512">
                                    </svg>
                                    <span class="text-sm font-semibold">Submit All</span>
                                </button>
                            </div>
            
                            <div id="loading-indicator" style="display: none;">
                                <span class="loader"></span>
                            </div>
                            
                        
                        </form>     
                    @else
                        @include('components.app.no-data')
                    @endif
                </div>
            @endif

        </div>
    </div>
@endsection

@push('scripts')
<script>
    CUSTOMERS = @json($customers ?? null);
    EINVOICES = @json($eInvoices ?? null);
    SELECTED_SOS = []

    $('input[name="search_customer"]').on('keyup', function() {
        let val = $(this).val()

        $('.customer-selections').addClass('hidden')

        for (let i = 0; i < CUSTOMERS.length; i++) {
            const element = CUSTOMERS[i];
            
            if (element.name.includes(val)) {
                $(`.customer-selections[data-id="${element.id}"]`).removeClass('hidden')
            }
        }
    })
    $('input[name="search_eInvoices"]').on('keyup', function() {
        let val = $(this).val()

        $('.e-invoices-selections').addClass('hidden')

        for (let i = 0; i < EINVOICES.length; i++) {
            const element = EINVOICES[i];
            if (element.uuid.includes(val)) {
                $(`.e-invoices-selections[data-id="${element.id}"]`).removeClass('hidden')
            }
        }
    })
    $('.sale-order-selections').on('click', function() {
        let id = $(this).data('id')

        const index = SELECTED_SOS.indexOf(id);
        if (index > -1) {
            SELECTED_SOS.splice(index, 1)

            $(`.sale-order-selections[data-id="${id}"]`).removeClass('!border-black')
        } else {
            SELECTED_SOS.push(id)

            $(`.sale-order-selections[data-id="${id}"]`).addClass('!border-black')
        }

        if (SELECTED_SOS.length <= 0) {
            $('#confirm-btn').removeClass('bg-green-200')
            $('#confirm-btn').addClass('bg-slate-100')
        } else {
            $('#confirm-btn').addClass('bg-green-200')
            $('#confirm-btn').removeClass('bg-slate-100')
        }
    })
    $('#confirm-btn').on('click', function(e) {
        e.preventDefault()

        if (SELECTED_SOS.length <= 0) return
        
        let url = $(this).attr('href')
        url = `${url}?so=${SELECTED_SOS}`

        window.location.href = url
    })
    $('.products input[type="checkbox"]').on('change', function() {
        let val = $(this).val()

        if ($(this).is(':checked')) {
            $(`.products[data-id="${val}"]`).addClass('!border-black')
        } else {
            $(`.products[data-id="${val}"]`).removeClass('!border-black')
        }

        let canConvert = false
        $('.products input[type="checkbox"]').each(function() {
            if ($(this).is(':checked')) {
                canConvert = true
            }
        })

        if (canConvert) {
            $('#convert-btn').addClass('bg-green-200')
            $('#convert-btn').removeClass('bg-slate-100')
        } else {
            $('#convert-btn').removeClass('bg-green-200')
            $('#convert-btn').addClass('bg-slate-100')
        }
    })
    $('.add-btns').on('click', function() {
        let id = $(this).data('id')
        let currentQty = $(this).parent().data('current-qty')
        let maxQty = $(this).parent().data('max-qty')

        currentQty++

        if (currentQty <= maxQty) {
            $(`.qty-to-convert[data-id="${id}"]`).text(currentQty)
            $(this).parent().data('current-qty', currentQty)
        }
    })
    $('.sub-btns').on('click', function() {
        let id = $(this).data('id')
        let currentQty = $(this).parent().data('current-qty')

        currentQty--

        if (currentQty >= 1) {
            $(`.qty-to-convert[data-id="${id}"]`).text(currentQty)
            $(this).parent().data('current-qty', currentQty)
        }
    })
    $('#convert-btn').on('click', function(e) {
        e.preventDefault()

        let canConvert = false
        $('.products input[type="checkbox"]').each(function() {
            if ($(this).is(':checked')) {
                canConvert = true
            }
        })
        if (!canConvert) return

        // Prepare data
        let prodIds = []
        let qty = []

        $('.products').each(function() {
            if ($(this).find('input[type="checkbox"]').is(':checked')) {
                prodIds.push($(this).data('id'))
                qty.push($(this).find('.qty-summary').data('current-qty'))
            }
        })

        let url = $(this).attr('href')
        url = `${url}?prod=${prodIds}&qty=${qty}`

        window.location.href = url
    })

    function submitSelectedInvoices() {
        console.log(1);
        const selectedInvoices = [];
        document.querySelectorAll('input[name="selected_einvoices[]"]:checked').forEach((checkbox) => {
            selectedInvoices.push(checkbox.value);
        });

        if (selectedInvoices.length > 0) {
            const params = new URLSearchParams();
            params.append('invs', selectedInvoices.join(','));

            window.location.href = "{{ route('to_note') }}?" + params.toString();
        } else {
            alert("Please select at least one e-invoice.");
        }
    }
   
    document.getElementById('main-form').addEventListener('submit', async function (e) {
        e.preventDefault(); 

        const loadingIndicator = document.getElementById('loading-indicator'); 

        loadingIndicator.style.display = 'flex';

        const formData = new FormData(this);

        try {
            const response = await fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: formData
            });

            if (response.ok) {
                loadingIndicator.style.display = 'none';
                const responseData = await response.json();

                if (responseData.errorDetails && responseData.errorDetails.length > 0) {
                    const errorMessages = responseData.errorDetails.map(error => 
                        `Invoice Code: ${error.invoiceCodeNumber}, Error: ${error.error}`
                    ).join('\n');
                    alert('提交失败:\n' + errorMessages);
                } else {
                    alert('Submit Succesfully!');
                }
            } else {
                console.log('2');
                throw new Error('提交失败');
            }
        } catch (error) {
            // 处理错误
            console.log(error);
            loadingIndicator.style.display = 'none';
            alert('提交时发生错误，请重试');
        }
    });

    $(document).ready(function () {
        $('input[name*="[price]"], input[name*="[qty]"]').on('input', function () {
            const $row = $(this).closest('tr');

            const price = parseFloat($row.find('input[name*="[price]"]').val()) || 0;
            const qty = parseInt($row.find('input[name*="[qty]"]').val()) || 0;

            const itemTotal = price * qty;

            $row.find('.item-total').text(`RM ${itemTotal.toFixed(2)}`);

            updateTableTotal($row.closest('table'));
        });

        function updateTableTotal($table) {
            let totalPrice = 0;

            $table.find('tbody tr').each(function () {
                const price = parseFloat($(this).find('input[name*="[price]"]').val()) || 0;
                const qty = parseInt($(this).find('input[name*="[qty]"]').val()) || 0;

                totalPrice += price * qty;
            });

            $table.find('.total-price').text(`RM ${totalPrice.toFixed(2)}`);
        }
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