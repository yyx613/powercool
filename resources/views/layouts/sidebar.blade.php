<!-- Expand -->
<aside class="max-w-[250px] w-full bg-blue-900 transition-all duration-700 delay-700 hidden lg:block" id="expanded-sidebar">
    <div class="h-screen flex flex-col sticky top-0 overflow-x-hidden">
        <div class="px-4 pt-4 pb-2 flex items-center">
            <button type="button" id="collapse-sidebar-btn">
                <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><rect y="11" width="24" height="2" rx="1"/><rect y="4" width="24" height="2" rx="1"/><rect y="18" width="24" height="2" rx="1"/></svg>
            </button>
            <div class="flex items-center gap-x-2">
                <img src="{{ asset('/images/image_1.png') }}" alt="Power Cool Logo" class="h-8 ml-4">
                <h1 class="text-white font-semibold text-xl whitespace-nowrap">Power Cool</h1>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto px-2 my-4 hide-scrollbar">
            <ul>
                <!-- Notification -->
                <li>
                    <a href="{{ route('notification.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'notification.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <div class="relative">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M22.555,13.662l-1.9-6.836A9.321,9.321,0,0,0,2.576,7.3L1.105,13.915A5,5,0,0,0,5.986,20H7.1a5,5,0,0,0,9.8,0h.838a5,5,0,0,0,4.818-6.338ZM12,22a3,3,0,0,1-2.816-2h5.632A3,3,0,0,1,12,22Zm8.126-5.185A2.977,2.977,0,0,1,17.737,18H5.986a3,3,0,0,1-2.928-3.651l1.47-6.616a7.321,7.321,0,0,1,14.2-.372l1.9,6.836A2.977,2.977,0,0,1,20.126,16.815Z"/></svg>
                            <span class="absolute flex h-2 w-2 top-0 right-0 {{ hasUnreadNotifications() ? '' : 'hidden' }}">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                            </span>
                        </div>
                        <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">Notification</span>
                    </a>
                </li>
                <!-- Approval -->
                <li>
                    <a href="{{ route('approval.index') }}" class="p-2 flex items-center rounded-md {{ str_contains(Route::currentRouteName(), 'approval.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                            <path d="m8,11h-3c-.552,0-1-.448-1-1s.448-1,1-1h3c.552,0,1,.448,1,1s-.448,1-1,1Zm15.759,12.651c-.198.23-.478.349-.76.349-.23,0-.462-.079-.65-.241l-2.509-2.151c-1.041.868-2.379,1.391-3.84,1.391-3.314,0-6-2.686-6-6s2.686-6,6-6,6,2.686,6,6c0,1.13-.318,2.184-.862,3.087l2.513,2.154c.419.359.468.991.108,1.41Zm-6.78-4.325l2.703-2.614c.398-.383.411-1.016.029-1.414-.383-.399-1.017-.41-1.414-.029l-2.713,2.624c-.143.141-.379.144-.522.002l-1.354-1.331c-.396-.388-1.028-.381-1.414.014-.387.395-.381,1.027.014,1.414l1.354,1.332c.46.449,1.062.674,1.663.674s1.201-.225,1.653-.671Zm-5.979,3.674c0,.552-.448,1-1,1h-5c-2.757,0-5-2.243-5-5V5C0,2.243,2.243,0,5,0h4.515c1.87,0,3.627.728,4.95,2.05l3.485,3.485c.888.888,1.521,2,1.833,3.217.077.299.011.617-.179.861s-.481.387-.79.387h-5.813c-1.654,0-3-1.346-3-3V2.023c-.16-.015-.322-.023-.485-.023h-4.515c-1.654,0-3,1.346-3,3v14c0,1.654,1.346,3,3,3h5c.552,0,1,.448,1,1Zm1-16c0,.551.449,1,1,1h4.338c-.219-.382-.489-.736-.803-1.05l-3.485-3.485c-.318-.318-.671-.587-1.05-.806v4.341Zm-5,6h-2c-.552,0-1,.448-1,1s.448,1,1,1h2c.552,0,1-.448,1-1s-.448-1-1-1Zm0,4h-2c-.552,0-1,.448-1,1s.448,1,1,1h2c.552,0,1-.448,1-1s-.448-1-1-1Z"/>
                        </svg>
                        <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Approval') }}</span>
                    </a>
                </li>
                <!-- Dashboard -->
                <li>
                    <a href="{{ route('dashboard.index') }}" class="p-2 flex items-center rounded-md {{ str_contains(Route::currentRouteName(), 'dashboard.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                            <path d="M14,12c0,1.019-.308,1.964-.832,2.754l-2.875-2.875c-.188-.188-.293-.442-.293-.707V7.101c2.282,.463,4,2.48,4,4.899Zm-6-.414V7.101c-2.55,.518-4.396,2.976-3.927,5.767,.325,1.934,1.82,3.543,3.729,3.992,1.47,.345,2.86,.033,3.952-.691l-3.169-3.169c-.375-.375-.586-.884-.586-1.414Zm11-4.586h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c.553,0,1-.448,1-1s-.447-1-1-1Zm0,4h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c.553,0,1-.448,1-1s-.447-1-1-1Zm0,4h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c.553,0,1-.448,1-1s-.447-1-1-1Zm5-7v8c0,2.757-2.243,5-5,5H5c-2.757,0-5-2.243-5-5V8C0,5.243,2.243,3,5,3h14c2.757,0,5,2.243,5,5Zm-2,0c0-1.654-1.346-3-3-3H5c-1.654,0-3,1.346-3,3v8c0,1.654,1.346,3,3,3h14c1.654,0,3-1.346,3-3V8Z"/>
                        </svg>
                        <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Dashboard') }}</span>
                    </a>
                </li>
                <!-- Contact -->
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="6">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,16a4,4,0,1,1,4-4A4,4,0,0,1,12,16Zm0-6a2,2,0,1,0,2,2A2,2,0,0,0,12,10Zm6,13A6,6,0,0,0,6,23a1,1,0,0,0,2,0,4,4,0,0,1,8,0,1,1,0,0,0,2,0ZM18,8a4,4,0,1,1,4-4A4,4,0,0,1,18,8Zm0-6a2,2,0,1,0,2,2A2,2,0,0,0,18,2Zm6,13a6.006,6.006,0,0,0-6-6,1,1,0,0,0,0,2,4,4,0,0,1,4,4,1,1,0,0,0,2,0ZM6,8a4,4,0,1,1,4-4A4,4,0,0,1,6,8ZM6,2A2,2,0,1,0,8,4,2,2,0,0,0,6,2ZM2,15a4,4,0,0,1,4-4A1,1,0,0,0,6,9a6.006,6.006,0,0,0-6,6,1,1,0,0,0,2,0Z"/></svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Contact') }}</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="6">
                        <div class="overflow-hidden">
                            <ul>
                                @can('customer.view')
                                <li>
                                    <a href="{{ route('customer.index') }}" class="p-2 flex items-center rounded-md {{ str_contains(Route::currentRouteName(), 'customer.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Debtor') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('supplier.view')
                                <li>
                                    <a href="{{ route('supplier.index') }}" class="p-2 flex items-center rounded-md {{ str_contains(Route::currentRouteName(), 'supplier.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Supplier') }}</span>
                                    </a>
                                </li>
                                @endcan
                                 @can('dealer.view')
                                <li>
                                    <a href="{{ route('dealer.index') }}" class="p-2 flex items-center rounded-md {{ str_contains(Route::currentRouteName(), 'dealer.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Dealer') }}</span>
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </div>
                </li>
                <!-- Vehicle -->
                @can('vehicle.view')
                    <li>
                        <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="11">
                            <button class="p-2 flex items-center rounded-md w-full">
                                <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M24,13a3,3,0,0,0-3-3h-.478L15.84,3.285A3,3,0,0,0,13.379,2h-7A3.016,3.016,0,0,0,3.575,3.937l-2.6,6.848A2.994,2.994,0,0,0,0,13v5H2v.5a3.5,3.5,0,0,0,7,0V18h6v.5a3.5,3.5,0,0,0,7,0V18h2ZM14.2,4.428,18.084,10H11V4h2.379A1,1,0,0,1,14.2,4.428Zm-8.753.217A1,1,0,0,1,6.381,4H9v6H3.416ZM7,18.5a1.5,1.5,0,0,1-3,0V18H7Zm13,0a1.5,1.5,0,0,1-3,0V18h3ZM22,16H2V13a1,1,0,0,1,1-1H21a1,1,0,0,1,1,1Z"/></svg>
                                <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Vehicle') }}</span>
                            </button>
                        </div>
                        <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="11">
                            <div class="overflow-hidden">
                                <ul>
                                    <li>
                                        <a href="{{ route('vehicle.index') }}" class="p-2 flex items-center rounded-md {{ str_contains(Route::currentRouteName(), 'vehicle.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                            <span class="block text-sm ml-9 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Vehicle') }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('vehicle_service.index') }}" class="p-2 flex items-center rounded-md {{ str_contains(Route::currentRouteName(), 'vehicle_service.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                            <span class="block text-sm ml-9 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Vehicle Service') }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </li>
                @endcan
                <!-- Sale & Invoice -->
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="3">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M23,22H3a1,1,0,0,1-1-1V1A1,1,0,0,0,0,1V21a3,3,0,0,0,3,3H23a1,1,0,0,0,0-2Z"/><path d="M15,20a1,1,0,0,0,1-1V12a1,1,0,0,0-2,0v7A1,1,0,0,0,15,20Z"/><path d="M7,20a1,1,0,0,0,1-1V12a1,1,0,0,0-2,0v7A1,1,0,0,0,7,20Z"/><path d="M19,20a1,1,0,0,0,1-1V7a1,1,0,0,0-2,0V19A1,1,0,0,0,19,20Z"/><path d="M11,20a1,1,0,0,0,1-1V7a1,1,0,0,0-2,0V19A1,1,0,0,0,11,20Z"/></svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Sale & Invoice') }}</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="3">
                        <div class="overflow-hidden">
                            <ul>
                                @can('sale.quotation.view')
                                <li>
                                    <a href="{{ route('quotation.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'quotation.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Quotation') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('sale.sale_order.view')
                                <li>
                                    <a href="{{ route('pending_order.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'pending_order.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('E-Order Assign') }}</span>
                                        <svg id="pending-orders-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#ee6b6e" class="bi bi-exclamation-circle-fill ml-2" viewBox="0 0 16 16">
                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4m.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/>
                                        </svg>
                                    </a>
                                </li>
                                @endcan
                                @can('sale.sale_order.view')
                                <li>
                                    <a href="{{ route('sale_order.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'sale_order.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Sale Order') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('sale.delivery_order.view')
                                <li>
                                    <a href="{{ route('delivery_order.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'delivery_order.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Delivery Order') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('sale.transport_acknowledgement.view')
                                <li>
                                    <a href="{{ route('transport_ack.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'transport_ack.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Transport Acknowledgement') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('sale.invoice.view')
                                <li>
                                    <a href="{{ route('invoice.index') }}" class="rounded-md p-2 flex items-center {{ Route::currentRouteName() == 'invoice.index' ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Invoice') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('sale.invoice_return.view')
                                <li>
                                    <a href="{{ route('invoice_return.index') }}" class="rounded-md p-2 flex items-center {{ Route::currentRouteName() == 'invoice_return.index' ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Invoice Return') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('sale.billing.view')
                                <li>
                                    <a href="{{ route('billing.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'billing.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Billing') }}</span>
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </div>
                </li>
                <!-- E - Invoice -->
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="101">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="white" class="bi bi-receipt" viewBox="0 0 16 16">
                                <path d="M1.92.506a.5.5 0 0 1 .434.14L3 1.293l.646-.647a.5.5 0 0 1 .708 0L5 1.293l.646-.647a.5.5 0 0 1 .708 0L7 1.293l.646-.647a.5.5 0 0 1 .708 0L9 1.293l.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .801.13l.5 1A.5.5 0 0 1 15 2v12a.5.5 0 0 1-.053.224l-.5 1a.5.5 0 0 1-.8.13L13 14.707l-.646.647a.5.5 0 0 1-.708 0L11 14.707l-.646.647a.5.5 0 0 1-.708 0L9 14.707l-.646.647a.5.5 0 0 1-.708 0L7 14.707l-.646.647a.5.5 0 0 1-.708 0L5 14.707l-.646.647a.5.5 0 0 1-.708 0L3 14.707l-.646.647a.5.5 0 0 1-.801-.13l-.5-1A.5.5 0 0 1 1 14V2a.5.5 0 0 1 .053-.224l.5-1a.5.5 0 0 1 .367-.27m.217 1.338L2 2.118v11.764l.137.274.51-.51a.5.5 0 0 1 .707 0l.646.647.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.509.509.137-.274V2.118l-.137-.274-.51.51a.5.5 0 0 1-.707 0L12 1.707l-.646.647a.5.5 0 0 1-.708 0L10 1.707l-.646.647a.5.5 0 0 1-.708 0L8 1.707l-.646.647a.5.5 0 0 1-.708 0L6 1.707l-.646.647a.5.5 0 0 1-.708 0L4 1.707l-.646.647a.5.5 0 0 1-.708 0z"/>
                                <path d="M3 4.5a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5m8-6a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5"/>
                            </svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('E - Invoice') }}</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="101">
                        <div class="overflow-hidden">
                            <ul>
                                @can('sale.invoice.view')
                                <li>
                                    <a href="{{ route('invoice.e-invoice.index') }}" class="rounded-md p-2 flex items-center {{ Route::currentRouteName() == 'invoice.e-invoice.index' ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('E Invoice') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('invoice.consolidated-e-invoice.index') }}" class="rounded-md p-2 flex items-center {{ Route::currentRouteName() == 'invoice.consolidated-e-invoice.index' ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Consolidated E Invoice') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('invoice.credit-note.index') }}" class="rounded-md p-2 flex items-center {{ Route::currentRouteName() == 'invoice.credit-note.index' ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Credit Note') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('invoice.debit-note.index') }}" class="rounded-md p-2 flex items-center {{ Route::currentRouteName() == 'invoice.debit-note.index' ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Debit Note') }}</span>
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </div>
                </li>
                <!-- Inventory -->
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="4">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M19.5,16c0,.553-.447,1-1,1h-2c-.553,0-1-.447-1-1s.447-1,1-1h2c.553,0,1,.447,1,1Zm4.5-1v5c0,2.206-1.794,4-4,4H4c-2.206,0-4-1.794-4-4v-5c0-2.206,1.794-4,4-4h1V4C5,1.794,6.794,0,9,0h6c2.206,0,4,1.794,4,4v7h1c2.206,0,4,1.794,4,4ZM7,11h10V4c0-1.103-.897-2-2-2h-6c-1.103,0-2,.897-2,2v7Zm-3,11h7V13H4c-1.103,0-2,.897-2,2v5c0,1.103,.897,2,2,2Zm18-7c0-1.103-.897-2-2-2h-7v9h7c1.103,0,2-.897,2-2v-5Zm-14.5,0h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1s-.447-1-1-1ZM14,5c0-.553-.447-1-1-1h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1Z"/></svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Inventory') }}</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="4">
                        <div class="overflow-hidden">
                            <ul>
                                @can('inventory.summary.view')
                                <li>
                                    <a href="{{ route('inventory_summary.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'inventory_summary.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Summary') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('grn.view')
                                <li>
                                    <a href="{{ route('grn.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'grn.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('GRN') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('inventory.product.view')
                                <li>
                                    <a href="{{ route('product.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'product.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Product') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('inventory.raw_material.view')
                                <li>
                                    <a href="{{ route('raw_material.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'raw_material.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Raw Material') }}</span>
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </div>
                </li>
                <!-- Production -->
                @can('production.view')
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="10">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                <path d="m22.97,6.251c-.637-.354-1.415-.331-2.1.101l-4.87,3.649v-2.001c0-.727-.395-1.397-1.03-1.749-.637-.354-1.416-.331-2.1.101l-4.87,3.649V2c.553,0,1-.448,1-1s-.447-1-1-1H1C.447,0,0,.448,0,1s.447,1,1,1v17c0,2.757,2.243,5,5,5h13c2.757,0,5-2.243,5-5v-11c0-.727-.395-1.397-1.03-1.749Zm-.97,12.749c0,1.654-1.346,3-3,3H6c-1.654,0-3-1.346-3-3V2h3v9.991c0,.007,0,.014,0,.02v5.989c0,.552.447,1,1,1s1-.448,1-1v-5.5l6-4.5v4c0,.379.214.725.553.895s.743.134,1.047-.094l6.4-4.8v11Zm-8-2v1c0,.552-.448,1-1,1h-1c-.552,0-1-.448-1-1v-1c0-.552.448-1,1-1h1c.552,0,1,.448,1,1Zm2,1v-1c0-.552.448-1,1-1h1c.552,0,1,.448,1,1v1c0,.552-.448,1-1,1h-1c-.552,0-1-.448-1-1Z"/>
                            </svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Production') }}</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="10">
                        <div class="overflow-hidden">
                            <ul>
                                <li>
                                    <a href="{{ route('production.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'production.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Task') }}</span>
                                    </a>
                                </li>
                                @can('production_material.view')
                                <li>
                                    <a href="{{ route('production_material.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'production_material.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Material') }}</span>
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </div>
                </li>
                @endcan
                <!-- Ticket & Task -->
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="8">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M16,0h-.13a2.02,2.02,0,0,0-1.941,1.532,2,2,0,0,1-3.858,0A2.02,2.02,0,0,0,8.13,0H8A5.006,5.006,0,0,0,3,5V21a3,3,0,0,0,3,3H8.13a2.02,2.02,0,0,0,1.941-1.532,2,2,0,0,1,3.858,0A2.02,2.02,0,0,0,15.87,24H18a3,3,0,0,0,3-3V5A5.006,5.006,0,0,0,16,0Zm2,22-2.143-.063A4,4,0,0,0,8.13,22H6a1,1,0,0,1-1-1V17H7a1,1,0,0,0,0-2H5V5A3,3,0,0,1,8,2l.143.063A4.01,4.01,0,0,0,12,5a4.071,4.071,0,0,0,3.893-3H16a3,3,0,0,1,3,3V15H17a1,1,0,0,0,0,2h2v4A1,1,0,0,1,18,22Z"/><path d="M13,15H11a1,1,0,0,0,0,2h2a1,1,0,0,0,0-2Z"/></svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Ticket & Task') }}</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="8">
                        <div class="overflow-hidden">
                            <ul>
                                @can('ticket.view')
                                <li>
                                    <a href="{{ route('ticket.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'ticket.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Ticket') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('task.view')
                                <li>
                                    <a href="{{ route('task.driver.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'task.driver.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Driver') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('task.technician.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'task.technician.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Technician') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('task.sale.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'task.sale.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Sale') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('sale.target.view')
                                <li>
                                    <a href="{{ route('target.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'target.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Target') }}</span>
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </div>
                </li>
                <!-- Service Reminder/History & Warranty -->
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="7">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                <path d="m7.283,3.697c-.385-.396-.376-1.029.021-1.414.396-.384,1.029-.376,1.414.021l2.374,2.442c.34.339.912.341,1.266-.007L16.792.294c.391-.392,1.023-.392,1.414-.002.392.39.392,1.023.002,1.414l-4.439,4.45c-.567.561-1.309.841-2.049.841s-1.487-.283-2.052-.847l-2.384-2.453Zm13.717-.697c-1.654,0-3,1.346-3,3v5.184c-.266-.094-.542-.164-.832-.18-.794-.042-1.567.226-2.173.769l-3.009,2.768c-.082-.084-.16-.172-.247-.252l-2.744-2.525c-.844-.756-1.996-.931-2.996-.579v-5.186c0-1.654-1.346-3-3-3S0,4.346,0,6v10.101c0,2.137.832,4.146,2.343,5.657l1.95,1.95c.195.195.451.293.707.293s.512-.098.707-.293c.391-.391.391-1.023,0-1.414l-1.95-1.95c-1.133-1.133-1.757-2.64-1.757-4.243V6c0-.551.449-1,1-1s1,.449,1,1c0,0-.005,8.077,0,8.118.03.654.286,1.308.747,1.854l2.616,2.721c.383.399,1.017.411,1.414.028.398-.383.411-1.016.028-1.414l-2.57-2.671c-.317-.377-.308-.938.021-1.305.367-.41.999-.444,1.397-.086l2.734,2.516c1.026.944,1.615,2.285,1.615,3.68v3.559c0,.552.448,1,1,1s1-.448,1-1v-3.559c0-1.152-.294-2.275-.824-3.276l3.163-2.911c.198-.178.454-.272.72-.253.266.015.51.132.687.33.16.178.241.402.255.623v.045s0,.001,0,.002c.003.217-.058.427-.188.584l-2.533,2.634c-.383.398-.37,1.031.027,1.414.194.187.443.279.693.279.263,0,.524-.103.721-.307l2.578-2.685c.45-.536.686-1.195.701-1.857.002-.023,0-8.065,0-8.065,0-.551.448-1,1-1s1,.449,1,1v10.101c0,1.603-.624,3.109-1.757,4.243l-1.95,1.95c-.391.39-.391,1.023,0,1.414.195.195.451.293.707.293s.512-.098.707-.293l1.95-1.95c1.511-1.511,2.343-3.52,2.343-5.657V6c0-1.654-1.346-3-3-3Z"/>
                            </svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Service & Warranty') }}</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="7">
                        <div class="overflow-hidden">
                            <ul>
                                @can('service_history.view')
                                <li>
                                    <a href="{{ route('service_history.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'service_history.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Service History') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('service_reminder.view')
                                <li>
                                    <a href="{{ route('service_reminder.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'service_reminder.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Service Reminder') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('warranty.view')
                                <li>
                                    <a href="{{ route('warranty.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'warranty.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Warranty') }}</span>
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </div>
                </li>
                <!-- Report -->
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="5">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                <path d="m22.204,1.162c-1.141-.952-2.634-1.343-4.098-1.081l-3.822.695c-.913.167-1.706.634-2.284,1.289-.578-.655-1.371-1.123-2.285-1.289L5.894.082C4.433-.181,2.938.21,1.796,1.162c-1.142.953-1.796,2.352-1.796,3.839v12.792c0,2.417,1.727,4.486,4.106,4.919l6.284,1.143c.534.097,1.072.146,1.61.146s1.076-.048,1.61-.146l6.285-1.143c2.379-.433,4.105-2.502,4.105-4.919V5.001c0-1.487-.655-2.886-1.796-3.839Zm-11.204,20.766c-.084-.012-6.536-1.184-6.536-1.184-1.428-.26-2.464-1.501-2.464-2.952V5.001c0-.892.393-1.731,1.078-2.303.545-.455,1.223-.697,1.919-.697.179,0,.36.016.54.049l3.821.695c.952.173,1.643,1.001,1.643,1.968v17.216Zm11-4.135c0,1.451-1.036,2.692-2.463,2.952,0,0-6.452,1.171-6.537,1.184V4.712c0-.967.691-1.794,1.642-1.968l3.821-.695c.878-.161,1.773.076,2.459.648.685.572,1.078,1.411,1.078,2.303v12.792ZM8.984,6.224c-.088.483-.509.821-.983.821-.059,0-3.18-.562-3.18-.562-.543-.099-.904-.619-.805-1.163.099-.543.615-.901,1.163-.805l3,.545c.543.099.904.619.805,1.163Zm0,3.955c-.088.483-.509.821-.983.821-.059,0-3.18-.562-3.18-.562-.543-.099-.904-.619-.805-1.163.099-.543.615-.903,1.163-.805l3,.545c.543.099.904.619.805,1.163Zm0,4c-.088.483-.509.821-.983.821-.059,0-3.18-.562-3.18-.562-.543-.099-.904-.619-.805-1.163.099-.543.615-.902,1.163-.805l3,.545c.543.099.904.619.805,1.163Zm11-8.857c.099.543-.262,1.064-.805,1.163,0,0-3.121.562-3.18.562-.474,0-.895-.338-.983-.821-.099-.543.262-1.064.805-1.163l3-.545c.541-.097,1.064.262,1.163.805Zm0,3.955c.099.543-.262,1.064-.805,1.163,0,0-3.121.562-3.18.562-.474,0-.895-.338-.983-.821-.099-.543.262-1.064.805-1.163l3-.545c.541-.098,1.064.262,1.163.805Zm0,4c.099.543-.262,1.064-.805,1.163,0,0-3.121.562-3.18.562-.474,0-.895-.338-.983-.821-.099-.543.262-1.064.805-1.163l3-.545c.541-.097,1.064.262,1.163.805Zm-2,4.364c.099.543-.262,1.064-.805,1.163,0,0-1.121.198-1.18.198-.474,0-.895-.338-.983-.821-.099-.543.262-1.064.805-1.163l1-.182c.549-.098,1.064.262,1.163.805Zm-11,.221c-.088.483-.509.821-.983.821-.059,0-1.18-.198-1.18-.198-.543-.099-.904-.619-.805-1.163.099-.543.615-.906,1.163-.805l1,.182c.543.099.904.619.805,1.163Z"/>
                            </svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Report') }}</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="5">
                        <div class="overflow-hidden">
                            <ul>
                                <li>
                                    <a href="{{ route('report.production_report.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'report.production_report.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Production Report') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('report.sales_report.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'report.sales_report.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Sales Report') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('report.stock_report.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'report.stock_report.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Stock Report') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('report.earning_report.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'report.earning_report.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Earning Report') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('report.service_report.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'report.service_report.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Service Report') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('report.technician_stock_report.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'report.technician_stock_report.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Technician Stock Report') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>
                <!-- User Role Management -->
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="9">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M15,6c0-3.309-2.691-6-6-6S3,2.691,3,6s2.691,6,6,6,6-2.691,6-6Zm-6,4c-2.206,0-4-1.794-4-4s1.794-4,4-4,4,1.794,4,4-1.794,4-4,4Zm-.008,4.938c.068,.548-.32,1.047-.869,1.116-3.491,.436-6.124,3.421-6.124,6.946,0,.552-.448,1-1,1s-1-.448-1-1c0-4.531,3.386-8.37,7.876-8.93,.542-.069,1.047,.32,1.116,.869Zm13.704,4.195l-.974-.562c.166-.497,.278-1.019,.278-1.572s-.111-1.075-.278-1.572l.974-.562c.478-.276,.642-.888,.366-1.366-.277-.479-.887-.644-1.366-.366l-.973,.562c-.705-.794-1.644-1.375-2.723-1.594v-1.101c0-.552-.448-1-1-1s-1,.448-1,1v1.101c-1.079,.22-2.018,.801-2.723,1.594l-.973-.562c-.48-.277-1.09-.113-1.366,.366-.276,.479-.112,1.09,.366,1.366l.974,.562c-.166,.497-.278,1.019-.278,1.572s.111,1.075,.278,1.572l-.974,.562c-.478,.276-.642,.888-.366,1.366,.186,.321,.521,.5,.867,.5,.169,0,.341-.043,.499-.134l.973-.562c.705,.794,1.644,1.375,2.723,1.594v1.101c0,.552,.448,1,1,1s1-.448,1-1v-1.101c1.079-.22,2.018-.801,2.723-1.594l.973,.562c.158,.091,.33,.134,.499,.134,.346,0,.682-.179,.867-.5,.276-.479,.112-1.09-.366-1.366Zm-5.696,.866c-1.654,0-3-1.346-3-3s1.346-3,3-3,3,1.346,3,3-1.346,3-3,3Z"/></svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('User Role Management') }}</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="9">
                        <div class="overflow-hidden">
                            <ul>
                                <li>
                                    <a href="{{ route('user_management.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'user_management.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('User Management') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('role_management.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'role_management.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Role Management') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>
                <!-- Settings -->
                @can('setting.view')
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="2">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M12,8a4,4,0,1,0,4,4A4,4,0,0,0,12,8Zm0,6a2,2,0,1,1,2-2A2,2,0,0,1,12,14Z"/><path d="M21.294,13.9l-.444-.256a9.1,9.1,0,0,0,0-3.29l.444-.256a3,3,0,1,0-3-5.2l-.445.257A8.977,8.977,0,0,0,15,3.513V3A3,3,0,0,0,9,3v.513A8.977,8.977,0,0,0,6.152,5.159L5.705,4.9a3,3,0,0,0-3,5.2l.444.256a9.1,9.1,0,0,0,0,3.29l-.444.256a3,3,0,1,0,3,5.2l.445-.257A8.977,8.977,0,0,0,9,20.487V21a3,3,0,0,0,6,0v-.513a8.977,8.977,0,0,0,2.848-1.646l.447.258a3,3,0,0,0,3-5.2Zm-2.548-3.776a7.048,7.048,0,0,1,0,3.75,1,1,0,0,0,.464,1.133l1.084.626a1,1,0,0,1-1,1.733l-1.086-.628a1,1,0,0,0-1.215.165,6.984,6.984,0,0,1-3.243,1.875,1,1,0,0,0-.751.969V21a1,1,0,0,1-2,0V19.748a1,1,0,0,0-.751-.969A6.984,6.984,0,0,1,7.006,16.9a1,1,0,0,0-1.215-.165l-1.084.627a1,1,0,1,1-1-1.732l1.084-.626a1,1,0,0,0,.464-1.133,7.048,7.048,0,0,1,0-3.75A1,1,0,0,0,4.79,8.992L3.706,8.366a1,1,0,0,1,1-1.733l1.086.628A1,1,0,0,0,7.006,7.1a6.984,6.984,0,0,1,3.243-1.875A1,1,0,0,0,11,4.252V3a1,1,0,0,1,2,0V4.252a1,1,0,0,0,.751.969A6.984,6.984,0,0,1,16.994,7.1a1,1,0,0,0,1.215.165l1.084-.627a1,1,0,1,1,1,1.732l-1.084.626A1,1,0,0,0,18.746,10.125Z"/></svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Setting') }}</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="2">
                        <div class="overflow-hidden">
                            <ul>
                                <li>
                                    <a href="{{ route('area.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'area.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Area') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('credit_term.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'credit_term.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Credit Term') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('currency.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'currency.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Currency') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('debtor_type.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'debtor_type.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Debtor Type') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('material_use.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'material_use.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Material Use') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('inventory_type.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'inventory_type.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Product Type') }}</span>
                                    </a>
                                </li>
                                @can('inventory.category.view')
                                <li>
                                    <a href="{{ route('inventory_category.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'inventory_category.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Product Category') }}</span>
                                    </a>
                                </li>
                                @endcan
                                <li>
                                    <a href="{{ route('promotion.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'promotion.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Promotion') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('project_type.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'project_type.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Project Type') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('platform.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'platform.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Platform') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('priority.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'priority.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Priority') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('service.index') }}" class="rounded-md p-2 flex items-center {{ !str_contains(Route::currentRouteName(), 'service.') && str_contains(Route::currentRouteName(), 'service.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Service') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('sync.index') }}" class="rounded-md p-2 flex items-center {{ !str_contains(Route::currentRouteName(), 'sync.') && str_contains(Route::currentRouteName(), 'service.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Sync') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('uom.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'uom.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('UOM') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('warranty_period.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'warranty_period.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Warranty Period') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>
                @endcan
            </ul>
        </div>
        <div class="px-4 py-3 border-t border-gray-50 flex items-center justify-between">
            <div class="flex-1 flex flex-col overflow-hidden">
                <span class="text-sm font-light text-slate-300 leading-none whitespace-nowrap" id="time-section"></span>
                <h3 class="text-base font-medium leading-tight truncate whitespace-nowrap text-white">{{ Auth::user()->name }}</h3>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="rounded-full bg-white p-2.5 transition duration-300 hover:bg-red-100" title="Logout">
                    <svg class="h-4 w-4 fill-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M22.763,10.232l-4.95-4.95L16.4,6.7,20.7,11H6.617v2H20.7l-4.3,4.3,1.414,1.414,4.95-4.95a2.5,2.5,0,0,0,0-3.536Z"/><path d="M10.476,21a1,1,0,0,1-1,1H3a1,1,0,0,1-1-1V3A1,1,0,0,1,3,2H9.476a1,1,0,0,1,1,1V8.333h2V3a3,3,0,0,0-3-3H3A3,3,0,0,0,0,3V21a3,3,0,0,0,3,3H9.476a3,3,0,0,0,3-3V15.667h-2Z"/></svg>
                </button>
            </form>
        </div>
    </div>
</aside>

<!-- Collapse -->
<aside class="max-w-0 bg-blue-900 -z-10 opacity-0 transition-all duration-700 hidden lg:block" id="collapsed-sidebar">
    <div class="h-screen py-4 px-2 sticky top-0 z-50">
        <div class="relative flex flex-col h-full">
            <div class="flex items-center justify-center">
                <button type="button" class="rounded-full p-2.5 hover:bg-blue-600" id="expand-sidebar-btn">
                    <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><rect y="11" width="24" height="2" rx="1"/><rect y="4" width="24" height="2" rx="1"/><rect y="18" width="24" height="2" rx="1"/></svg>
                </button>
            </div>
            <div class="flex-1 my-4">
                <ul>
                    <!-- Notification -->
                    <li>
                        <a href="{{ route('notification.index') }}" class="relative group tooltip-triggers rounded-full p-2.5 flex items-center justify-center hover:bg-blue-600">
                            <div class="relative">
                                <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M22.555,13.662l-1.9-6.836A9.321,9.321,0,0,0,2.576,7.3L1.105,13.915A5,5,0,0,0,5.986,20H7.1a5,5,0,0,0,9.8,0h.838a5,5,0,0,0,4.818-6.338ZM12,22a3,3,0,0,1-2.816-2h5.632A3,3,0,0,1,12,22Zm8.126-5.185A2.977,2.977,0,0,1,17.737,18H5.986a3,3,0,0,1-2.928-3.651l1.47-6.616a7.321,7.321,0,0,1,14.2-.372l1.9,6.836A2.977,2.977,0,0,1,20.126,16.815Z"/></svg>
                                <span class="absolute flex h-2 w-2 top-0 right-0 {{ hasUnreadNotifications() ? '' : 'hidden' }}">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                </span>
                            </div>
                            <!-- Tooltip -->
                            <div class="absolute top-0 transition-all duration-500 left-0 opacity-0 invisible group-hover:visible group-hover:left-12 group-hover:opacity-100 rounded py-1.5 px-3 bg-blue-900 shadow h-full flex items-center border">
                                <span class="text-sm leading-tight font-semibold text-white whitespace-nowrap">{{ __('Notification') }}</span>
                            </div>
                        </a>
                    </li>
                    <!-- Approval -->
                    <li>
                        <a href="{{ route('approval.index') }}" class="relative group tooltip-triggers rounded-full p-2.5 flex items-center justify-center hover:bg-blue-600">
                            <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                <path d="m8,11h-3c-.552,0-1-.448-1-1s.448-1,1-1h3c.552,0,1,.448,1,1s-.448,1-1,1Zm15.759,12.651c-.198.23-.478.349-.76.349-.23,0-.462-.079-.65-.241l-2.509-2.151c-1.041.868-2.379,1.391-3.84,1.391-3.314,0-6-2.686-6-6s2.686-6,6-6,6,2.686,6,6c0,1.13-.318,2.184-.862,3.087l2.513,2.154c.419.359.468.991.108,1.41Zm-6.78-4.325l2.703-2.614c.398-.383.411-1.016.029-1.414-.383-.399-1.017-.41-1.414-.029l-2.713,2.624c-.143.141-.379.144-.522.002l-1.354-1.331c-.396-.388-1.028-.381-1.414.014-.387.395-.381,1.027.014,1.414l1.354,1.332c.46.449,1.062.674,1.663.674s1.201-.225,1.653-.671Zm-5.979,3.674c0,.552-.448,1-1,1h-5c-2.757,0-5-2.243-5-5V5C0,2.243,2.243,0,5,0h4.515c1.87,0,3.627.728,4.95,2.05l3.485,3.485c.888.888,1.521,2,1.833,3.217.077.299.011.617-.179.861s-.481.387-.79.387h-5.813c-1.654,0-3-1.346-3-3V2.023c-.16-.015-.322-.023-.485-.023h-4.515c-1.654,0-3,1.346-3,3v14c0,1.654,1.346,3,3,3h5c.552,0,1,.448,1,1Zm1-16c0,.551.449,1,1,1h4.338c-.219-.382-.489-.736-.803-1.05l-3.485-3.485c-.318-.318-.671-.587-1.05-.806v4.341Zm-5,6h-2c-.552,0-1,.448-1,1s.448,1,1,1h2c.552,0,1-.448,1-1s-.448-1-1-1Zm0,4h-2c-.552,0-1,.448-1,1s.448,1,1,1h2c.552,0,1-.448,1-1s-.448-1-1-1Z"/>
                            </svg>
                            <!-- Tooltip -->
                            <div class="absolute top-0 transition-all duration-500 left-0 opacity-0 invisible group-hover:visible group-hover:left-12 group-hover:opacity-100 rounded py-1.5 px-3 bg-blue-900 shadow h-full flex items-center border">
                                <span class="text-sm leading-tight font-semibold text-white whitespace-nowrap">{{ __('Approval') }}</span>
                            </div>
                        </a>
                    </li>
                    <!-- Dashboard -->
                    <li>
                        <a href="{{ route('dashboard.index') }}" class="relative group tooltip-triggers rounded-full p-2.5 flex items-center justify-center hover:bg-blue-600">
                            <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                <path d="M14,12c0,1.019-.308,1.964-.832,2.754l-2.875-2.875c-.188-.188-.293-.442-.293-.707V7.101c2.282,.463,4,2.48,4,4.899Zm-6-.414V7.101c-2.55,.518-4.396,2.976-3.927,5.767,.325,1.934,1.82,3.543,3.729,3.992,1.47,.345,2.86,.033,3.952-.691l-3.169-3.169c-.375-.375-.586-.884-.586-1.414Zm11-4.586h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c.553,0,1-.448,1-1s-.447-1-1-1Zm0,4h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c.553,0,1-.448,1-1s-.447-1-1-1Zm0,4h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c.553,0,1-.448,1-1s-.447-1-1-1Zm5-7v8c0,2.757-2.243,5-5,5H5c-2.757,0-5-2.243-5-5V8C0,5.243,2.243,3,5,3h14c2.757,0,5,2.243,5,5Zm-2,0c0-1.654-1.346-3-3-3H5c-1.654,0-3,1.346-3,3v8c0,1.654,1.346,3,3,3h14c1.654,0,3-1.346,3-3V8Z"/>
                            </svg>
                            <!-- Tooltip -->
                            <div class="absolute top-0 transition-all duration-500 left-0 opacity-0 invisible group-hover:visible group-hover:left-12 group-hover:opacity-100 rounded py-1.5 px-3 bg-blue-900 shadow h-full flex items-center border">
                                <span class="text-sm leading-tight font-semibold text-white whitespace-nowrap">{{ __('Dashboard') }}</span>
                            </div>
                        </a>
                    </li>
                    <!-- Contact -->
                    <li class="expand-sub-menu-triggers" data-type="contact">
                        <button class="p-2.5 flex items-center justify-center rounded-full hover:bg-blue-600">
                            <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,16a4,4,0,1,1,4-4A4,4,0,0,1,12,16Zm0-6a2,2,0,1,0,2,2A2,2,0,0,0,12,10Zm6,13A6,6,0,0,0,6,23a1,1,0,0,0,2,0,4,4,0,0,1,8,0,1,1,0,0,0,2,0ZM18,8a4,4,0,1,1,4-4A4,4,0,0,1,18,8Zm0-6a2,2,0,1,0,2,2A2,2,0,0,0,18,2Zm6,13a6.006,6.006,0,0,0-6-6,1,1,0,0,0,0,2,4,4,0,0,1,4,4,1,1,0,0,0,2,0ZM6,8a4,4,0,1,1,4-4A4,4,0,0,1,6,8ZM6,2A2,2,0,1,0,8,4,2,2,0,0,0,6,2ZM2,15a4,4,0,0,1,4-4A1,1,0,0,0,6,9a6.006,6.006,0,0,0-6,6,1,1,0,0,0,2,0Z"/></svg>
                        </button>
                    </li>
                    <!-- Vehicle -->
                    <li class="expand-sub-menu-triggers" data-type="vehicle">
                        <button class="p-2.5 flex items-center justify-center rounded-full hover:bg-blue-600">
                            <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M24,13a3,3,0,0,0-3-3h-.478L15.84,3.285A3,3,0,0,0,13.379,2h-7A3.016,3.016,0,0,0,3.575,3.937l-2.6,6.848A2.994,2.994,0,0,0,0,13v5H2v.5a3.5,3.5,0,0,0,7,0V18h6v.5a3.5,3.5,0,0,0,7,0V18h2ZM14.2,4.428,18.084,10H11V4h2.379A1,1,0,0,1,14.2,4.428Zm-8.753.217A1,1,0,0,1,6.381,4H9v6H3.416ZM7,18.5a1.5,1.5,0,0,1-3,0V18H7Zm13,0a1.5,1.5,0,0,1-3,0V18h3ZM22,16H2V13a1,1,0,0,1,1-1H21a1,1,0,0,1,1,1Z"/></svg>
                        </button>
                    </li>

                    <!-- Sale & Invoice -->
                    <li class="expand-sub-menu-triggers" data-type="sale">
                        <button class="p-2.5 flex items-center justify-center rounded-full hover:bg-blue-600">
                            <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M23,22H3a1,1,0,0,1-1-1V1A1,1,0,0,0,0,1V21a3,3,0,0,0,3,3H23a1,1,0,0,0,0-2Z"/><path d="M15,20a1,1,0,0,0,1-1V12a1,1,0,0,0-2,0v7A1,1,0,0,0,15,20Z"/><path d="M7,20a1,1,0,0,0,1-1V12a1,1,0,0,0-2,0v7A1,1,0,0,0,7,20Z"/><path d="M19,20a1,1,0,0,0,1-1V7a1,1,0,0,0-2,0V19A1,1,0,0,0,19,20Z"/><path d="M11,20a1,1,0,0,0,1-1V7a1,1,0,0,0-2,0V19A1,1,0,0,0,11,20Z"/></svg>
                        </button>
                    </li>
                    <!-- E - Invoice -->
                    <li class="expand-sub-menu-triggers" data-type="e-invoice">
                        <button class="p-2.5 flex items-center justify-center rounded-full hover:bg-blue-600">
                            <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="white" class="bi bi-receipt" viewBox="0 0 16 16">
                                <path d="M1.92.506a.5.5 0 0 1 .434.14L3 1.293l.646-.647a.5.5 0 0 1 .708 0L5 1.293l.646-.647a.5.5 0 0 1 .708 0L7 1.293l.646-.647a.5.5 0 0 1 .708 0L9 1.293l.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .801.13l.5 1A.5.5 0 0 1 15 2v12a.5.5 0 0 1-.053.224l-.5 1a.5.5 0 0 1-.8.13L13 14.707l-.646.647a.5.5 0 0 1-.708 0L11 14.707l-.646.647a.5.5 0 0 1-.708 0L9 14.707l-.646.647a.5.5 0 0 1-.708 0L7 14.707l-.646.647a.5.5 0 0 1-.708 0L5 14.707l-.646.647a.5.5 0 0 1-.708 0L3 14.707l-.646.647a.5.5 0 0 1-.801-.13l-.5-1A.5.5 0 0 1 1 14V2a.5.5 0 0 1 .053-.224l.5-1a.5.5 0 0 1 .367-.27m.217 1.338L2 2.118v11.764l.137.274.51-.51a.5.5 0 0 1 .707 0l.646.647.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.509.509.137-.274V2.118l-.137-.274-.51.51a.5.5 0 0 1-.707 0L12 1.707l-.646.647a.5.5 0 0 1-.708 0L10 1.707l-.646.647a.5.5 0 0 1-.708 0L8 1.707l-.646.647a.5.5 0 0 1-.708 0L6 1.707l-.646.647a.5.5 0 0 1-.708 0L4 1.707l-.646.647a.5.5 0 0 1-.708 0z"/>
                                <path d="M3 4.5a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5m8-6a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5"/>
                            </svg>
                        </button>
                    </li>
                    <!-- Inventory -->
                    <li class="expand-sub-menu-triggers" data-type="inventory">
                        <button class="p-2.5 flex items-center justify-center rounded-full hover:bg-blue-600">
                            <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M19.5,16c0,.553-.447,1-1,1h-2c-.553,0-1-.447-1-1s.447-1,1-1h2c.553,0,1,.447,1,1Zm4.5-1v5c0,2.206-1.794,4-4,4H4c-2.206,0-4-1.794-4-4v-5c0-2.206,1.794-4,4-4h1V4C5,1.794,6.794,0,9,0h6c2.206,0,4,1.794,4,4v7h1c2.206,0,4,1.794,4,4ZM7,11h10V4c0-1.103-.897-2-2-2h-6c-1.103,0-2,.897-2,2v7Zm-3,11h7V13H4c-1.103,0-2,.897-2,2v5c0,1.103,.897,2,2,2Zm18-7c0-1.103-.897-2-2-2h-7v9h7c1.103,0,2-.897,2-2v-5Zm-14.5,0h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1s-.447-1-1-1ZM14,5c0-.553-.447-1-1-1h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1Z"/></svg>
                        </button>
                    </li>
                    <!-- Production -->
                    @can('production.view')
                    <li class="expand-sub-menu-triggers" data-type="production">
                        <button class="p-2.5 flex items-center justify-center rounded-full hover:bg-blue-600">
                            <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                <path d="m22.97,6.251c-.637-.354-1.415-.331-2.1.101l-4.87,3.649v-2.001c0-.727-.395-1.397-1.03-1.749-.637-.354-1.416-.331-2.1.101l-4.87,3.649V2c.553,0,1-.448,1-1s-.447-1-1-1H1C.447,0,0,.448,0,1s.447,1,1,1v17c0,2.757,2.243,5,5,5h13c2.757,0,5-2.243,5-5v-11c0-.727-.395-1.397-1.03-1.749Zm-.97,12.749c0,1.654-1.346,3-3,3H6c-1.654,0-3-1.346-3-3V2h3v9.991c0,.007,0,.014,0,.02v5.989c0,.552.447,1,1,1s1-.448,1-1v-5.5l6-4.5v4c0,.379.214.725.553.895s.743.134,1.047-.094l6.4-4.8v11Zm-8-2v1c0,.552-.448,1-1,1h-1c-.552,0-1-.448-1-1v-1c0-.552.448-1,1-1h1c.552,0,1,.448,1,1Zm2,1v-1c0-.552.448-1,1-1h1c.552,0,1,.448,1,1v1c0,.552-.448,1-1,1h-1c-.552,0-1-.448-1-1Z"/>
                            </svg>
                        </button>
                    </li>
                    @endcan
                    <!-- Ticket & Task -->
                    <li class="expand-sub-menu-triggers" data-type="ticket-and-task">
                        <button class="p-2.5 flex items-center justify-center rounded-full hover:bg-blue-600">
                            <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M16,0h-.13a2.02,2.02,0,0,0-1.941,1.532,2,2,0,0,1-3.858,0A2.02,2.02,0,0,0,8.13,0H8A5.006,5.006,0,0,0,3,5V21a3,3,0,0,0,3,3H8.13a2.02,2.02,0,0,0,1.941-1.532,2,2,0,0,1,3.858,0A2.02,2.02,0,0,0,15.87,24H18a3,3,0,0,0,3-3V5A5.006,5.006,0,0,0,16,0Zm2,22-2.143-.063A4,4,0,0,0,8.13,22H6a1,1,0,0,1-1-1V17H7a1,1,0,0,0,0-2H5V5A3,3,0,0,1,8,2l.143.063A4.01,4.01,0,0,0,12,5a4.071,4.071,0,0,0,3.893-3H16a3,3,0,0,1,3,3V15H17a1,1,0,0,0,0,2h2v4A1,1,0,0,1,18,22Z"/><path d="M13,15H11a1,1,0,0,0,0,2h2a1,1,0,0,0,0-2Z"/></svg>
                        </button>
                    </li>
                    <!-- Service Reminder/History & Warranty -->
                    <li class="expand-sub-menu-triggers" data-type="service-history-and-warranty">
                        <button class="p-2.5 flex items-center justify-center rounded-full hover:bg-blue-600">
                            <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                <path d="m7.283,3.697c-.385-.396-.376-1.029.021-1.414.396-.384,1.029-.376,1.414.021l2.374,2.442c.34.339.912.341,1.266-.007L16.792.294c.391-.392,1.023-.392,1.414-.002.392.39.392,1.023.002,1.414l-4.439,4.45c-.567.561-1.309.841-2.049.841s-1.487-.283-2.052-.847l-2.384-2.453Zm13.717-.697c-1.654,0-3,1.346-3,3v5.184c-.266-.094-.542-.164-.832-.18-.794-.042-1.567.226-2.173.769l-3.009,2.768c-.082-.084-.16-.172-.247-.252l-2.744-2.525c-.844-.756-1.996-.931-2.996-.579v-5.186c0-1.654-1.346-3-3-3S0,4.346,0,6v10.101c0,2.137.832,4.146,2.343,5.657l1.95,1.95c.195.195.451.293.707.293s.512-.098.707-.293c.391-.391.391-1.023,0-1.414l-1.95-1.95c-1.133-1.133-1.757-2.64-1.757-4.243V6c0-.551.449-1,1-1s1,.449,1,1c0,0-.005,8.077,0,8.118.03.654.286,1.308.747,1.854l2.616,2.721c.383.399,1.017.411,1.414.028.398-.383.411-1.016.028-1.414l-2.57-2.671c-.317-.377-.308-.938.021-1.305.367-.41.999-.444,1.397-.086l2.734,2.516c1.026.944,1.615,2.285,1.615,3.68v3.559c0,.552.448,1,1,1s1-.448,1-1v-3.559c0-1.152-.294-2.275-.824-3.276l3.163-2.911c.198-.178.454-.272.72-.253.266.015.51.132.687.33.16.178.241.402.255.623v.045s0,.001,0,.002c.003.217-.058.427-.188.584l-2.533,2.634c-.383.398-.37,1.031.027,1.414.194.187.443.279.693.279.263,0,.524-.103.721-.307l2.578-2.685c.45-.536.686-1.195.701-1.857.002-.023,0-8.065,0-8.065,0-.551.448-1,1-1s1,.449,1,1v10.101c0,1.603-.624,3.109-1.757,4.243l-1.95,1.95c-.391.39-.391,1.023,0,1.414.195.195.451.293.707.293s.512-.098.707-.293l1.95-1.95c1.511-1.511,2.343-3.52,2.343-5.657V6c0-1.654-1.346-3-3-3Z"/>
                            </svg>
                        </button>
                    </li>
                    <!-- Report -->
                    <li class="expand-sub-menu-triggers" data-type="report">
                        <button class="p-2.5 flex items-center justify-center rounded-full hover:bg-blue-600">
                            <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                <path d="m22.204,1.162c-1.141-.952-2.634-1.343-4.098-1.081l-3.822.695c-.913.167-1.706.634-2.284,1.289-.578-.655-1.371-1.123-2.285-1.289L5.894.082C4.433-.181,2.938.21,1.796,1.162c-1.142.953-1.796,2.352-1.796,3.839v12.792c0,2.417,1.727,4.486,4.106,4.919l6.284,1.143c.534.097,1.072.146,1.61.146s1.076-.048,1.61-.146l6.285-1.143c2.379-.433,4.105-2.502,4.105-4.919V5.001c0-1.487-.655-2.886-1.796-3.839Zm-11.204,20.766c-.084-.012-6.536-1.184-6.536-1.184-1.428-.26-2.464-1.501-2.464-2.952V5.001c0-.892.393-1.731,1.078-2.303.545-.455,1.223-.697,1.919-.697.179,0,.36.016.54.049l3.821.695c.952.173,1.643,1.001,1.643,1.968v17.216Zm11-4.135c0,1.451-1.036,2.692-2.463,2.952,0,0-6.452,1.171-6.537,1.184V4.712c0-.967.691-1.794,1.642-1.968l3.821-.695c.878-.161,1.773.076,2.459.648.685.572,1.078,1.411,1.078,2.303v12.792ZM8.984,6.224c-.088.483-.509.821-.983.821-.059,0-3.18-.562-3.18-.562-.543-.099-.904-.619-.805-1.163.099-.543.615-.901,1.163-.805l3,.545c.543.099.904.619.805,1.163Zm0,3.955c-.088.483-.509.821-.983.821-.059,0-3.18-.562-3.18-.562-.543-.099-.904-.619-.805-1.163.099-.543.615-.903,1.163-.805l3,.545c.543.099.904.619.805,1.163Zm0,4c-.088.483-.509.821-.983.821-.059,0-3.18-.562-3.18-.562-.543-.099-.904-.619-.805-1.163.099-.543.615-.902,1.163-.805l3,.545c.543.099.904.619.805,1.163Zm11-8.857c.099.543-.262,1.064-.805,1.163,0,0-3.121.562-3.18.562-.474,0-.895-.338-.983-.821-.099-.543.262-1.064.805-1.163l3-.545c.541-.097,1.064.262,1.163.805Zm0,3.955c.099.543-.262,1.064-.805,1.163,0,0-3.121.562-3.18.562-.474,0-.895-.338-.983-.821-.099-.543.262-1.064.805-1.163l3-.545c.541-.098,1.064.262,1.163.805Zm0,4c.099.543-.262,1.064-.805,1.163,0,0-3.121.562-3.18.562-.474,0-.895-.338-.983-.821-.099-.543.262-1.064.805-1.163l3-.545c.541-.097,1.064.262,1.163.805Zm-2,4.364c.099.543-.262,1.064-.805,1.163,0,0-1.121.198-1.18.198-.474,0-.895-.338-.983-.821-.099-.543.262-1.064.805-1.163l1-.182c.549-.098,1.064.262,1.163.805Zm-11,.221c-.088.483-.509.821-.983.821-.059,0-1.18-.198-1.18-.198-.543-.099-.904-.619-.805-1.163.099-.543.615-.906,1.163-.805l1,.182c.543.099.904.619.805,1.163Z"/>
                            </svg>
                        </button>
                    </li>
                    <!-- User Role Management -->
                    <li class="expand-sub-menu-triggers" data-type="user-role-management">
                        <button class="p-2.5 flex items-center justify-center rounded-full hover:bg-blue-600">
                            <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M15,6c0-3.309-2.691-6-6-6S3,2.691,3,6s2.691,6,6,6,6-2.691,6-6Zm-6,4c-2.206,0-4-1.794-4-4s1.794-4,4-4,4,1.794,4,4-1.794,4-4,4Zm-.008,4.938c.068,.548-.32,1.047-.869,1.116-3.491,.436-6.124,3.421-6.124,6.946,0,.552-.448,1-1,1s-1-.448-1-1c0-4.531,3.386-8.37,7.876-8.93,.542-.069,1.047,.32,1.116,.869Zm13.704,4.195l-.974-.562c.166-.497,.278-1.019,.278-1.572s-.111-1.075-.278-1.572l.974-.562c.478-.276,.642-.888,.366-1.366-.277-.479-.887-.644-1.366-.366l-.973,.562c-.705-.794-1.644-1.375-2.723-1.594v-1.101c0-.552-.448-1-1-1s-1,.448-1,1v1.101c-1.079,.22-2.018,.801-2.723,1.594l-.973-.562c-.48-.277-1.09-.113-1.366,.366-.276,.479-.112,1.09,.366,1.366l.974,.562c-.166,.497-.278,1.019-.278,1.572s.111,1.075,.278,1.572l-.974,.562c-.478,.276-.642,.888-.366,1.366,.186,.321,.521,.5,.867,.5,.169,0,.341-.043,.499-.134l.973-.562c.705,.794,1.644,1.375,2.723,1.594v1.101c0,.552,.448,1,1,1s1-.448,1-1v-1.101c1.079-.22,2.018-.801,2.723-1.594l.973,.562c.158,.091,.33,.134,.499,.134,.346,0,.682-.179,.867-.5,.276-.479,.112-1.09-.366-1.366Zm-5.696,.866c-1.654,0-3-1.346-3-3s1.346-3,3-3,3,1.346,3,3-1.346,3-3,3Z"/></svg>
                        </button>
                    </li>
                    <!-- Settings -->
                    @can('setting.view')
                    <li class="expand-sub-menu-triggers" data-type="setting">
                        <button class="p-2.5 flex items-center justify-center rounded-full hover:bg-blue-600">
                            <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M12,8a4,4,0,1,0,4,4A4,4,0,0,0,12,8Zm0,6a2,2,0,1,1,2-2A2,2,0,0,1,12,14Z"/><path d="M21.294,13.9l-.444-.256a9.1,9.1,0,0,0,0-3.29l.444-.256a3,3,0,1,0-3-5.2l-.445.257A8.977,8.977,0,0,0,15,3.513V3A3,3,0,0,0,9,3v.513A8.977,8.977,0,0,0,6.152,5.159L5.705,4.9a3,3,0,0,0-3,5.2l.444.256a9.1,9.1,0,0,0,0,3.29l-.444.256a3,3,0,1,0,3,5.2l.445-.257A8.977,8.977,0,0,0,9,20.487V21a3,3,0,0,0,6,0v-.513a8.977,8.977,0,0,0,2.848-1.646l.447.258a3,3,0,0,0,3-5.2Zm-2.548-3.776a7.048,7.048,0,0,1,0,3.75,1,1,0,0,0,.464,1.133l1.084.626a1,1,0,0,1-1,1.733l-1.086-.628a1,1,0,0,0-1.215.165,6.984,6.984,0,0,1-3.243,1.875,1,1,0,0,0-.751.969V21a1,1,0,0,1-2,0V19.748a1,1,0,0,0-.751-.969A6.984,6.984,0,0,1,7.006,16.9a1,1,0,0,0-1.215-.165l-1.084.627a1,1,0,1,1-1-1.732l1.084-.626a1,1,0,0,0,.464-1.133,7.048,7.048,0,0,1,0-3.75A1,1,0,0,0,4.79,8.992L3.706,8.366a1,1,0,0,1,1-1.733l1.086.628A1,1,0,0,0,7.006,7.1a6.984,6.984,0,0,1,3.243-1.875A1,1,0,0,0,11,4.252V3a1,1,0,0,1,2,0V4.252a1,1,0,0,0,.751.969A6.984,6.984,0,0,1,16.994,7.1a1,1,0,0,0,1.215.165l1.084-.627a1,1,0,1,1,1,1.732l-1.084.626A1,1,0,0,0,18.746,10.125Z"/></svg>
                        </button>
                    </li>
                    @endcan
                </ul>
            </div>
            <div class="flex items-center justify-between">
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="p-2.5 bg-white rounded-full">
                        <svg class="h-4 w-4 fill-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M22.763,10.232l-4.95-4.95L16.4,6.7,20.7,11H6.617v2H20.7l-4.3,4.3,1.414,1.414,4.95-4.95a2.5,2.5,0,0,0,0-3.536Z"/><path d="M10.476,21a1,1,0,0,1-1,1H3a1,1,0,0,1-1-1V3A1,1,0,0,1,3,2H9.476a1,1,0,0,1,1,1V8.333h2V3a3,3,0,0,0-3-3H3A3,3,0,0,0,0,3V21a3,3,0,0,0,3,3H9.476a3,3,0,0,0,3-3V15.667h-2Z"/></svg>
                    </button>
                </form>
            </div>
        </div>
        <!-- Contact -->
        <div class="absolute top-0 left-14 shadow-[10px_0px_15px_#00000010] bg-blue-900 h-full py-4 px-2 border-l opacity-0 -z-50 invisible transition-all duration-300 max-w-0 min-w-[200px] sub-menu-content" data-type="contact">
            <div class="mb-4 p-2 border-b">
                <h6 class="text-lg font-semibold whitespace-nowrap text-white">{{ __('Contact') }}</h6>
            </div>
            <ul>
                @can('customer.view')
                <li>
                    <a href="{{ route('customer.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'customer.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Debtor') }}</span>
                    </a>
                </li>
                @endcan
                @can('supplier.view')
                <li>
                    <a href="{{ route('supplier.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'supplier.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Supplier') }}</span>
                    </a>
                </li>
                @endcan
                @can('dealer.view')
                <li>
                    <a href="{{ route('dealer.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'dealer.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Dealer') }}</span>
                    </a>
                </li>
                @endcan
            </ul>
        </div>
        <!-- Vehicle -->
        @can('vehicle.view')
        <div class="absolute top-0 left-14 shadow-[10px_0px_15px_#00000010] bg-blue-900 h-full py-4 px-2 border-l opacity-0 -z-50 invisible transition-all duration-300 max-w-0 min-w-[200px] sub-menu-content" data-type="vehicle">
            <div class="mb-4 p-2 border-b">
                <h6 class="text-lg font-semibold whitespace-nowrap text-white">{{ __('Vehicle') }}</h6>
            </div>
            <ul>
                <li>
                    <a href="{{ route('vehicle.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'vehicle.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Vehicle') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('vehicle_service.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'vehicle_service.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Vehicle Service') }}</span>
                    </a>
                </li>

            </ul>
        </div>
        @endcan

        <!-- Inventory -->
        <div class="absolute top-0 left-14 shadow-[10px_0px_15px_#00000010] bg-blue-900 h-full py-4 px-2 border-l opacity-0 -z-50 invisible transition-all duration-300 max-w-0 min-w-[200px] sub-menu-content" data-type="inventory">
            <div class="mb-4 p-2 border-b">
                <h6 class="text-lg font-semibold whitespace-nowrap text-white">{{ __('Inventory') }}</h6>
            </div>
            <ul>
                @can('inventory.summary.view')
                <li>
                    <a href="{{ route('inventory_summary.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'inventory_summary.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Summary') }}</span>
                    </a>
                </li>
                @endcan
                @can('grn.view')
                <li>
                    <a href="{{ route('grn.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'grn.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('GRN') }}</span>
                    </a>
                </li>
                @endcan
                @can('inventory.product.view')
                <li>
                    <a href="{{ route('product.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'product.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Product') }}</span>
                    </a>
                </li>
                @endcan
                @can('inventory.raw_material.view')
                <li>
                    <a href="{{ route('raw_material.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'raw_material.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Raw Material') }}</span>
                    </a>
                </li>
                @endcan
            </ul>
        </div>
        <!-- Production -->
        <div class="absolute top-0 left-14 shadow-[10px_0px_15px_#00000010] bg-blue-900 h-full py-4 px-2 border-l opacity-0 -z-50 invisible transition-all duration-300 max-w-0 min-w-[200px] sub-menu-content" data-type="production">
            <div class="mb-4 p-2 border-b">
                <h6 class="text-lg font-semibold whitespace-nowrap text-white">{{ __('Production') }}</h6>
            </div>
            <ul>
                <li>
                    <a href="{{ route('production.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'production.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Task') }}</span>
                    </a>
                </li>
                @can('production_material.view')
                <li>
                    <a href="{{ route('production_material.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'production_material.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Material') }}</span>
                    </a>
                </li>
                @endcan
            </ul>
        </div>
        <!-- Sale & Invoice -->
        <div class="absolute top-0 left-14 shadow-[10px_0px_15px_#00000010] bg-blue-900 h-full py-4 px-2 border-l opacity-0 -z-50 invisible transition-all duration-300 max-w-0 min-w-[200px] sub-menu-content" data-type="sale">
            <div class="mb-4 p-2 border-b">
                <h6 class="text-lg font-semibold whitespace-nowrap text-white">{{ __('Sale & Invoice') }}</h6>
            </div>
            <ul>
                @can('sale.quotation.view')
                <li>
                    <a href="{{ route('quotation.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'quotation.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Quotation') }}</span>
                    </a>
                </li>
                @endcan
                @can('sale.sale_order.view')
                <li>
                    <a href="{{ route('pending_order.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'pending_order.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('E-Order Assign') }}</span>
                    </a>
                </li>
                @endcan
                @can('sale.sale_order.view')
                <li>
                    <a href="{{ route('sale_order.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'sale_order.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Sale Order') }}</span>
                    </a>
                </li>
                @endcan
                @can('sale.delivery_order.view')
                <li>
                    <a href="{{ route('delivery_order.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'delivery_order.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Delivery Order') }}</span>
                    </a>
                </li>
                @can('sale.transport_acknowledgement.view')
                <li>
                    <a href="{{ route('transport_ack.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'transport_ack.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Transport Acknowledgement') }}</span>
                    </a>
                </li>
                @endcan
                @endcan
                @can('sale.invoice.view')
                <li>
                    <a href="{{ route('invoice.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'invoice.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Invoice') }}</span>
                    </a>
                </li>
                @endcan
                @can('sale.invoice_return.view')
                <li>
                    <a href="{{ route('invoice_return.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'invoice_return.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Invoice Return') }}</span>
                    </a>
                </li>
                @endcan
                @can('sale.billing.view')
                <li>
                    <a href="{{ route('billing.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'billing.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Billing') }}</span>
                    </a>
                </li>
                @endcan
            </ul>
        </div>
        <!-- E - Invoice -->
        <div class="absolute top-0 left-14 shadow-[10px_0px_15px_#00000010] bg-blue-900 h-full py-4 px-2 border-l opacity-0 -z-50 invisible transition-all duration-300 max-w-0 min-w-[200px] sub-menu-content" data-type="e-invoice">
            <div class="mb-4 p-2 border-b">
                <h6 class="text-lg font-semibold whitespace-nowrap text-white">{{ __('E - Invoice') }}</h6>
            </div>
            <ul>
                @can('sale.invoice.view')
                <li>
                    <a href="{{ route('invoice.e-invoice.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'invoice.e-invoice.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('E Invoice') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('invoice.consolidated-e-invoice.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'invoice.consolidated-e-invoice.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Consolidated E Invoice') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('invoice.credit-note.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'invoice.credit-note.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Credit Note') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('invoice.debit-note.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'invoice.debit-note.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Debit Note') }}</span>
                    </a>
                </li>
                @endcan
            </ul>
        </div>
        <!-- Ticket & Task -->
        <div class="absolute top-0 left-14 shadow-[10px_0px_15px_#00000010] bg-blue-900 h-full py-4 px-2 border-l opacity-0 -z-50 invisible transition-all duration-300 max-w-0 min-w-[200px] sub-menu-content" data-type="ticket-and-task">
            <div class="mb-4 p-2 border-b">
                <h6 class="text-lg font-semibold whitespace-nowrap text-white">{{ __('Ticket & Task') }}</h6>
            </div>
            <ul>
                @can('ticket.view')
                <li>
                    <a href="{{ route('ticket.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'ticket.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Ticket') }}</span>
                    </a>
                </li>
                @endcan
                <li>
                    <a href="{{ route('task.driver.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'task.driver.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Driver') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('task.technician.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'task.technician.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Technician') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('task.sale.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'task.sale.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Sale') }}</span>
                    </a>
                </li>
                @can('sale.target.view')
                <li>
                    <a href="{{ route('target.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'target.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Target') }}</span>
                    </a>
                </li>
                @endcan
            </ul>
        </div>
        <!-- Service Reminder/History & Warranty -->
        <div class="absolute top-0 left-14 shadow-[10px_0px_15px_#00000010] bg-blue-900 h-full py-4 px-2 border-l opacity-0 -z-50 invisible transition-all duration-300 max-w-0 min-w-[200px] sub-menu-content" data-type="service-history-and-warranty">
            <div class="mb-4 p-2 border-b">
                <h6 class="text-lg font-semibold whitespace-nowrap text-white">{{ __('Service & Warranty') }}</h6>
            </div>
            <ul>
                @can('service_history.view')
                <li>
                    <a href="{{ route('service_history.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'service_history.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Service History') }}</span>
                    </a>
                </li>
                @endcan
                @can('service_reminder.view')
                <li>
                    <a href="{{ route('service_reminder.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'service_reminder.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Service Reminder') }}</span>
                    </a>
                </li>
                @endcan
                @can('warranty.view')
                <li>
                    <a href="{{ route('warranty.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'warranty.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Warranty') }}</span>
                    </a>
                </li>
                @endcan
            </ul>
        </div>
        <!-- Report -->
        <div class="absolute top-0 left-14 shadow-[10px_0px_15px_#00000010] bg-blue-900 h-full py-4 px-2 border-l opacity-0 -z-50 invisible transition-all duration-300 max-w-0 min-w-[200px] sub-menu-content" data-type="report">
            <div class="mb-4 p-2 border-b">
                <h6 class="text-lg font-semibold whitespace-nowrap text-white">{{ __('Report') }}</h6>
            </div>
            <ul>
                <li>
                    <a href="{{ route('report.production_report.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'report.production_report.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Production Report') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('report.sales_report.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'report.sales_report.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Sales Report') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('report.stock_report.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'report.stock_report.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Stock Report') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('report.earning_report.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'report.earning_report.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Earning Report') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('report.service_report.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'report.service_report.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Service Report') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('report.technician_stock_report.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'report.technician_stock_report.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Technician Stock Report') }}</span>
                    </a>
                </li>
            </ul>
        </div>
        <!-- User Role Management -->
        <div class="absolute top-0 left-14 shadow-[10px_0px_15px_#00000010] bg-blue-900 h-full py-4 px-2 border-l opacity-0 -z-50 invisible transition-all duration-300 max-w-0 min-w-[200px] sub-menu-content" data-type="user-role-management">
            <div class="mb-4 p-2 border-b">
                <h6 class="text-lg font-semibold whitespace-nowrap text-white">{{ __('User Role Management') }}</h6>
            </div>
            <ul>
                <li>
                    <a href="{{ route('user_management.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'user_management.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('User Management') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('role_management.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'role_management.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Role Management') }}</span>
                    </a>
                </li>
            </ul>
        </div>
        <!-- Setting -->
        <div class="absolute top-0 left-14 shadow-[10px_0px_15px_#00000010] bg-blue-900 h-full py-4 px-2 border-l opacity-0 -z-50 invisible transition-all duration-300 max-w-0 min-w-[200px] sub-menu-content" data-type="setting">
            <div class="mb-4 p-2 border-b">
                <h6 class="text-lg font-semibold whitespace-nowrap text-white">{{ __('Setting') }}</h6>
            </div>
            <ul>
                <li>
                    <a href="{{ route('area.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'area.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Area') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('credit_term.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'credit_term.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Credit Term') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('currency.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'currency.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Currency') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('debtor_type.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'debtor_type.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Debtor Type') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('material_use.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'material_use.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Material Use') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('inventory_type.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'inventory_type.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Product Type') }}</span>
                    </a>
                </li>
                @can('inventory.category.view')
                <li>
                    <a href="{{ route('inventory_category.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'inventory_category.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Product Category') }}</span>
                    </a>
                </li>
                @endcan
                <li>
                    <a href="{{ route('promotion.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'promotion.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Promotion') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('project_type.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'project_type.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Project Type') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('platform.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'platform.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Platform') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('priority.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'priority.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Priority') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('service.index') }}" class="rounded-md p-2 flex items-center {{ !str_contains(Route::currentRouteName(), 'service.') && str_contains(Route::currentRouteName(), 'service.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Service') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('sync.index') }}" class="rounded-md p-2 flex items-center {{ !str_contains(Route::currentRouteName(), 'sync.') && str_contains(Route::currentRouteName(), 'service.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Sync') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('uom.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'uom.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('UOM') }}</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('warranty_period.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'warranty_period.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">{{ __('Warranty Period') }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</aside>

<!-- Mobile -->
<aside class="max-w-[250px] w-full bg-blue-900 z-50 fixed top-0 left-0 hidden" id="mobile-sidebar">
    <div class="h-screen flex flex-col overflow-x-hidden">
        <div class="px-4 pt-4 pb-2 flex items-center">
            <button type="button" class="mobile-sidebar-trigger-btn">
                <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><rect y="11" width="24" height="2" rx="1"/><rect y="4" width="24" height="2" rx="1"/><rect y="18" width="24" height="2" rx="1"/></svg>
            </button>
            <div class="flex items-center gap-x-2">
                <img src="{{ asset('/images/image_1.png') }}" alt="Power Cool Logo" class="h-8 ml-4">
                <h1 class="text-white font-semibold text-xl whitespace-nowrap">Power Cool</h1>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto px-2 my-4 hide-scrollbar">
            <ul>
                <!-- Notification -->
                <li>
                    <a href="{{ route('notification.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'notification.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <div class="relative">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M22.555,13.662l-1.9-6.836A9.321,9.321,0,0,0,2.576,7.3L1.105,13.915A5,5,0,0,0,5.986,20H7.1a5,5,0,0,0,9.8,0h.838a5,5,0,0,0,4.818-6.338ZM12,22a3,3,0,0,1-2.816-2h5.632A3,3,0,0,1,12,22Zm8.126-5.185A2.977,2.977,0,0,1,17.737,18H5.986a3,3,0,0,1-2.928-3.651l1.47-6.616a7.321,7.321,0,0,1,14.2-.372l1.9,6.836A2.977,2.977,0,0,1,20.126,16.815Z"/></svg>
                            <span class="absolute flex h-2 w-2 top-0 right-0 {{ hasUnreadNotifications() ? '' : 'hidden' }}">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                            </span>
                        </div>
                        <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">Notification</span>
                    </a>
                </li>
                <!-- Approval-->
                <li>
                    <a href="{{ route('approval.index') }}" class="p-2 flex items-center rounded-md {{ str_contains(Route::currentRouteName(), 'approval.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                            <path d="m8,11h-3c-.552,0-1-.448-1-1s.448-1,1-1h3c.552,0,1,.448,1,1s-.448,1-1,1Zm15.759,12.651c-.198.23-.478.349-.76.349-.23,0-.462-.079-.65-.241l-2.509-2.151c-1.041.868-2.379,1.391-3.84,1.391-3.314,0-6-2.686-6-6s2.686-6,6-6,6,2.686,6,6c0,1.13-.318,2.184-.862,3.087l2.513,2.154c.419.359.468.991.108,1.41Zm-6.78-4.325l2.703-2.614c.398-.383.411-1.016.029-1.414-.383-.399-1.017-.41-1.414-.029l-2.713,2.624c-.143.141-.379.144-.522.002l-1.354-1.331c-.396-.388-1.028-.381-1.414.014-.387.395-.381,1.027.014,1.414l1.354,1.332c.46.449,1.062.674,1.663.674s1.201-.225,1.653-.671Zm-5.979,3.674c0,.552-.448,1-1,1h-5c-2.757,0-5-2.243-5-5V5C0,2.243,2.243,0,5,0h4.515c1.87,0,3.627.728,4.95,2.05l3.485,3.485c.888.888,1.521,2,1.833,3.217.077.299.011.617-.179.861s-.481.387-.79.387h-5.813c-1.654,0-3-1.346-3-3V2.023c-.16-.015-.322-.023-.485-.023h-4.515c-1.654,0-3,1.346-3,3v14c0,1.654,1.346,3,3,3h5c.552,0,1,.448,1,1Zm1-16c0,.551.449,1,1,1h4.338c-.219-.382-.489-.736-.803-1.05l-3.485-3.485c-.318-.318-.671-.587-1.05-.806v4.341Zm-5,6h-2c-.552,0-1,.448-1,1s.448,1,1,1h2c.552,0,1-.448,1-1s-.448-1-1-1Zm0,4h-2c-.552,0-1,.448-1,1s.448,1,1,1h2c.552,0,1-.448,1-1s-.448-1-1-1Z"/>
                        </svg>
                        <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Approval') }}</span>
                    </a>
                </li>
                <!-- Dashboard -->
                <li>
                    <a href="{{ route('dashboard.index') }}" class="p-2 flex items-center rounded-md {{ str_contains(Route::currentRouteName(), 'dashboard.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                            <path d="M14,12c0,1.019-.308,1.964-.832,2.754l-2.875-2.875c-.188-.188-.293-.442-.293-.707V7.101c2.282,.463,4,2.48,4,4.899Zm-6-.414V7.101c-2.55,.518-4.396,2.976-3.927,5.767,.325,1.934,1.82,3.543,3.729,3.992,1.47,.345,2.86,.033,3.952-.691l-3.169-3.169c-.375-.375-.586-.884-.586-1.414Zm11-4.586h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c.553,0,1-.448,1-1s-.447-1-1-1Zm0,4h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c.553,0,1-.448,1-1s-.447-1-1-1Zm0,4h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c.553,0,1-.448,1-1s-.447-1-1-1Zm5-7v8c0,2.757-2.243,5-5,5H5c-2.757,0-5-2.243-5-5V8C0,5.243,2.243,3,5,3h14c2.757,0,5,2.243,5,5Zm-2,0c0-1.654-1.346-3-3-3H5c-1.654,0-3,1.346-3,3v8c0,1.654,1.346,3,3,3h14c1.654,0,3-1.346,3-3V8Z"/>
                        </svg>
                        <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Dashboard') }}</span>
                    </a>
                </li>
                <!-- Contact -->
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="6">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,16a4,4,0,1,1,4-4A4,4,0,0,1,12,16Zm0-6a2,2,0,1,0,2,2A2,2,0,0,0,12,10Zm6,13A6,6,0,0,0,6,23a1,1,0,0,0,2,0,4,4,0,0,1,8,0,1,1,0,0,0,2,0ZM18,8a4,4,0,1,1,4-4A4,4,0,0,1,18,8Zm0-6a2,2,0,1,0,2,2A2,2,0,0,0,18,2Zm6,13a6.006,6.006,0,0,0-6-6,1,1,0,0,0,0,2,4,4,0,0,1,4,4,1,1,0,0,0,2,0ZM6,8a4,4,0,1,1,4-4A4,4,0,0,1,6,8ZM6,2A2,2,0,1,0,8,4,2,2,0,0,0,6,2ZM2,15a4,4,0,0,1,4-4A1,1,0,0,0,6,9a6.006,6.006,0,0,0-6,6,1,1,0,0,0,2,0Z"/></svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Contact') }}</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="6">
                        <div class="overflow-hidden">
                            <ul>
                                @can('customer.view')
                                <li>
                                    <a href="{{ route('customer.index') }}" class="p-2 flex items-center rounded-md {{ str_contains(Route::currentRouteName(), 'customer.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Debtor') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('supplier.view')
                                <li>
                                    <a href="{{ route('supplier.index') }}" class="p-2 flex items-center rounded-md {{ str_contains(Route::currentRouteName(), 'supplier.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Supplier') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('dealer.view')
                                <li>
                                    <a href="{{ route('dealer.index') }}" class="p-2 flex items-center rounded-md {{ str_contains(Route::currentRouteName(), 'dealer.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Dealer') }}</span>
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </div>
                </li>
                <!-- Vehicle -->
                @can('vehicle.view')
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="11">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M24,13a3,3,0,0,0-3-3h-.478L15.84,3.285A3,3,0,0,0,13.379,2h-7A3.016,3.016,0,0,0,3.575,3.937l-2.6,6.848A2.994,2.994,0,0,0,0,13v5H2v.5a3.5,3.5,0,0,0,7,0V18h6v.5a3.5,3.5,0,0,0,7,0V18h2ZM14.2,4.428,18.084,10H11V4h2.379A1,1,0,0,1,14.2,4.428Zm-8.753.217A1,1,0,0,1,6.381,4H9v6H3.416ZM7,18.5a1.5,1.5,0,0,1-3,0V18H7Zm13,0a1.5,1.5,0,0,1-3,0V18h3ZM22,16H2V13a1,1,0,0,1,1-1H21a1,1,0,0,1,1,1Z"/></svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Vehicle') }}</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="11">
                        <div class="overflow-hidden">
                            <ul>
                                <li>
                                    <a href="{{ route('vehicle.index') }}" class="p-2 flex items-center rounded-md {{ str_contains(Route::currentRouteName(), 'vehicle.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Vehicle') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('vehicle_service.index') }}" class="p-2 flex items-center rounded-md {{ str_contains(Route::currentRouteName(), 'vehicle_service.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Vehicle Service') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>
                @endcan
                <!-- Sale & Invoice -->
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="3">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M23,22H3a1,1,0,0,1-1-1V1A1,1,0,0,0,0,1V21a3,3,0,0,0,3,3H23a1,1,0,0,0,0-2Z"/><path d="M15,20a1,1,0,0,0,1-1V12a1,1,0,0,0-2,0v7A1,1,0,0,0,15,20Z"/><path d="M7,20a1,1,0,0,0,1-1V12a1,1,0,0,0-2,0v7A1,1,0,0,0,7,20Z"/><path d="M19,20a1,1,0,0,0,1-1V7a1,1,0,0,0-2,0V19A1,1,0,0,0,19,20Z"/><path d="M11,20a1,1,0,0,0,1-1V7a1,1,0,0,0-2,0V19A1,1,0,0,0,11,20Z"/></svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Sale & Invoice') }}</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="3">
                        <div class="overflow-hidden">
                            <ul>
                                @can('sale.quotation.view')
                                <li>
                                    <a href="{{ route('quotation.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'quotation.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Quotation') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('sale.sale_order.view')
                                <li>
                                    <a href="{{ route('pending_order.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'pending_order.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('E-Order Assign') }}</span>
                                        <svg id="pending-orders-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#ee6b6e" class="bi bi-exclamation-circle-fill ml-2" viewBox="0 0 16 16">
                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4m.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/>
                                        </svg>
                                    </a>
                                </li>
                                @endcan
                                @can('sale.sale_order.view')
                                <li>
                                    <a href="{{ route('sale_order.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'sale_order.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Sale Order') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('sale.delivery_order.view')
                                <li>
                                    <a href="{{ route('delivery_order.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'delivery_order.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Delivery Order') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('sale.transport_acknowledgement.view')
                                <li>
                                    <a href="{{ route('transport_ack.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'transport_ack.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Transport Acknowledgement') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('sale.invoice.view')
                                <li>
                                    <a href="{{ route('invoice.index') }}" class="rounded-md p-2 flex items-center {{ Route::currentRouteName() == 'invoice.index' ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Invoice') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('sale.invoice_return.view')
                                <li>
                                    <a href="{{ route('invoice_return.index') }}" class="rounded-md p-2 flex items-center {{ Route::currentRouteName() == 'invoice_return.index' ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Invoice Return') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('sale.billing.view')
                                <li>
                                    <a href="{{ route('billing.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'billing.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Billing') }}</span>
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </div>
                </li>
                <!-- E - Invoice -->
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="101">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="white" class="bi bi-receipt" viewBox="0 0 16 16">
                                <path d="M1.92.506a.5.5 0 0 1 .434.14L3 1.293l.646-.647a.5.5 0 0 1 .708 0L5 1.293l.646-.647a.5.5 0 0 1 .708 0L7 1.293l.646-.647a.5.5 0 0 1 .708 0L9 1.293l.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .801.13l.5 1A.5.5 0 0 1 15 2v12a.5.5 0 0 1-.053.224l-.5 1a.5.5 0 0 1-.8.13L13 14.707l-.646.647a.5.5 0 0 1-.708 0L11 14.707l-.646.647a.5.5 0 0 1-.708 0L9 14.707l-.646.647a.5.5 0 0 1-.708 0L7 14.707l-.646.647a.5.5 0 0 1-.708 0L5 14.707l-.646.647a.5.5 0 0 1-.708 0L3 14.707l-.646.647a.5.5 0 0 1-.801-.13l-.5-1A.5.5 0 0 1 1 14V2a.5.5 0 0 1 .053-.224l.5-1a.5.5 0 0 1 .367-.27m.217 1.338L2 2.118v11.764l.137.274.51-.51a.5.5 0 0 1 .707 0l.646.647.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.509.509.137-.274V2.118l-.137-.274-.51.51a.5.5 0 0 1-.707 0L12 1.707l-.646.647a.5.5 0 0 1-.708 0L10 1.707l-.646.647a.5.5 0 0 1-.708 0L8 1.707l-.646.647a.5.5 0 0 1-.708 0L6 1.707l-.646.647a.5.5 0 0 1-.708 0L4 1.707l-.646.647a.5.5 0 0 1-.708 0z"/>
                                <path d="M3 4.5a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5m8-6a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5"/>
                            </svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('E - Invoice') }}</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="101">
                        <div class="overflow-hidden">
                            <ul>
                                @can('sale.invoice.view')
                                <li>
                                    <a href="{{ route('invoice.e-invoice.index') }}" class="rounded-md p-2 flex items-center {{ Route::currentRouteName() == 'invoice.e-invoice.index' ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('E Invoice') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('invoice.consolidated-e-invoice.index') }}" class="rounded-md p-2 flex items-center {{ Route::currentRouteName() == 'invoice.consolidated-e-invoice.index' ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Consolidated E Invoice') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('invoice.credit-note.index') }}" class="rounded-md p-2 flex items-center {{ Route::currentRouteName() == 'invoice.credit-note.index' ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Credit Note') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('invoice.debit-note.index') }}" class="rounded-md p-2 flex items-center {{ Route::currentRouteName() == 'invoice.debit-note.index' ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Debit Note') }}</span>
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </div>
                </li>
                <!-- Inventory -->
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="4">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M19.5,16c0,.553-.447,1-1,1h-2c-.553,0-1-.447-1-1s.447-1,1-1h2c.553,0,1,.447,1,1Zm4.5-1v5c0,2.206-1.794,4-4,4H4c-2.206,0-4-1.794-4-4v-5c0-2.206,1.794-4,4-4h1V4C5,1.794,6.794,0,9,0h6c2.206,0,4,1.794,4,4v7h1c2.206,0,4,1.794,4,4ZM7,11h10V4c0-1.103-.897-2-2-2h-6c-1.103,0-2,.897-2,2v7Zm-3,11h7V13H4c-1.103,0-2,.897-2,2v5c0,1.103,.897,2,2,2Zm18-7c0-1.103-.897-2-2-2h-7v9h7c1.103,0,2-.897,2-2v-5Zm-14.5,0h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1s-.447-1-1-1ZM14,5c0-.553-.447-1-1-1h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1Z"/></svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Inventory') }}</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="4">
                        <div class="overflow-hidden">
                            <ul>
                                @can('inventory.summary.view')
                                <li>
                                    <a href="{{ route('inventory_summary.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'inventory_summary.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Summary') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('grn.view')
                                <li>
                                    <a href="{{ route('grn.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'grn.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('GRN') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('inventory.product.view')
                                <li>
                                    <a href="{{ route('product.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'product.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Product') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('inventory.raw_material.view')
                                <li>
                                    <a href="{{ route('raw_material.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'raw_material.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Raw Material') }}</span>
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </div>
                </li>
                <!-- Production -->
                @can('production.view')
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="10">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                <path d="m22.97,6.251c-.637-.354-1.415-.331-2.1.101l-4.87,3.649v-2.001c0-.727-.395-1.397-1.03-1.749-.637-.354-1.416-.331-2.1.101l-4.87,3.649V2c.553,0,1-.448,1-1s-.447-1-1-1H1C.447,0,0,.448,0,1s.447,1,1,1v17c0,2.757,2.243,5,5,5h13c2.757,0,5-2.243,5-5v-11c0-.727-.395-1.397-1.03-1.749Zm-.97,12.749c0,1.654-1.346,3-3,3H6c-1.654,0-3-1.346-3-3V2h3v9.991c0,.007,0,.014,0,.02v5.989c0,.552.447,1,1,1s1-.448,1-1v-5.5l6-4.5v4c0,.379.214.725.553.895s.743.134,1.047-.094l6.4-4.8v11Zm-8-2v1c0,.552-.448,1-1,1h-1c-.552,0-1-.448-1-1v-1c0-.552.448-1,1-1h1c.552,0,1,.448,1,1Zm2,1v-1c0-.552.448-1,1-1h1c.552,0,1,.448,1,1v1c0,.552-.448,1-1,1h-1c-.552,0-1-.448-1-1Z"/>
                            </svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Production') }}</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="10">
                        <div class="overflow-hidden">
                            <ul>
                                <li>
                                    <a href="{{ route('production.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'production.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Task') }}</span>
                                    </a>
                                </li>
                                @can('production_material.view')
                                <li>
                                    <a href="{{ route('production_material.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'production_material.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Material') }}</span>
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </div>
                </li>
                @endcan
                <!-- Ticket & Task -->
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="8">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M16,0h-.13a2.02,2.02,0,0,0-1.941,1.532,2,2,0,0,1-3.858,0A2.02,2.02,0,0,0,8.13,0H8A5.006,5.006,0,0,0,3,5V21a3,3,0,0,0,3,3H8.13a2.02,2.02,0,0,0,1.941-1.532,2,2,0,0,1,3.858,0A2.02,2.02,0,0,0,15.87,24H18a3,3,0,0,0,3-3V5A5.006,5.006,0,0,0,16,0Zm2,22-2.143-.063A4,4,0,0,0,8.13,22H6a1,1,0,0,1-1-1V17H7a1,1,0,0,0,0-2H5V5A3,3,0,0,1,8,2l.143.063A4.01,4.01,0,0,0,12,5a4.071,4.071,0,0,0,3.893-3H16a3,3,0,0,1,3,3V15H17a1,1,0,0,0,0,2h2v4A1,1,0,0,1,18,22Z"/><path d="M13,15H11a1,1,0,0,0,0,2h2a1,1,0,0,0,0-2Z"/></svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Ticket & Task') }}</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="8">
                        <div class="overflow-hidden">
                            <ul>
                                @can('ticket.view')
                                <li>
                                    <a href="{{ route('ticket.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'ticket.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Ticket') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('task.view')
                                <li>
                                    <a href="{{ route('task.driver.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'task.driver.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Driver') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('task.technician.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'task.technician.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Technician') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('task.sale.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'task.sale.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Sale') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('sale.target.view')
                                <li>
                                    <a href="{{ route('target.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'target.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Target') }}</span>
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </div>
                </li>
                <!-- Service Reminder/History & Warranty -->
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="7">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                <path d="m7.283,3.697c-.385-.396-.376-1.029.021-1.414.396-.384,1.029-.376,1.414.021l2.374,2.442c.34.339.912.341,1.266-.007L16.792.294c.391-.392,1.023-.392,1.414-.002.392.39.392,1.023.002,1.414l-4.439,4.45c-.567.561-1.309.841-2.049.841s-1.487-.283-2.052-.847l-2.384-2.453Zm13.717-.697c-1.654,0-3,1.346-3,3v5.184c-.266-.094-.542-.164-.832-.18-.794-.042-1.567.226-2.173.769l-3.009,2.768c-.082-.084-.16-.172-.247-.252l-2.744-2.525c-.844-.756-1.996-.931-2.996-.579v-5.186c0-1.654-1.346-3-3-3S0,4.346,0,6v10.101c0,2.137.832,4.146,2.343,5.657l1.95,1.95c.195.195.451.293.707.293s.512-.098.707-.293c.391-.391.391-1.023,0-1.414l-1.95-1.95c-1.133-1.133-1.757-2.64-1.757-4.243V6c0-.551.449-1,1-1s1,.449,1,1c0,0-.005,8.077,0,8.118.03.654.286,1.308.747,1.854l2.616,2.721c.383.399,1.017.411,1.414.028.398-.383.411-1.016.028-1.414l-2.57-2.671c-.317-.377-.308-.938.021-1.305.367-.41.999-.444,1.397-.086l2.734,2.516c1.026.944,1.615,2.285,1.615,3.68v3.559c0,.552.448,1,1,1s1-.448,1-1v-3.559c0-1.152-.294-2.275-.824-3.276l3.163-2.911c.198-.178.454-.272.72-.253.266.015.51.132.687.33.16.178.241.402.255.623v.045s0,.001,0,.002c.003.217-.058.427-.188.584l-2.533,2.634c-.383.398-.37,1.031.027,1.414.194.187.443.279.693.279.263,0,.524-.103.721-.307l2.578-2.685c.45-.536.686-1.195.701-1.857.002-.023,0-8.065,0-8.065,0-.551.448-1,1-1s1,.449,1,1v10.101c0,1.603-.624,3.109-1.757,4.243l-1.95,1.95c-.391.39-.391,1.023,0,1.414.195.195.451.293.707.293s.512-.098.707-.293l1.95-1.95c1.511-1.511,2.343-3.52,2.343-5.657V6c0-1.654-1.346-3-3-3Z"/>
                            </svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Service & Warranty') }}</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="7">
                        <div class="overflow-hidden">
                            <ul>
                                @can('service_history.view')
                                <li>
                                    <a href="{{ route('service_history.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'service_history.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Service History') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('service_reminder.view')
                                <li>
                                    <a href="{{ route('service_reminder.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'service_reminder.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Service Reminder') }}</span>
                                    </a>
                                </li>
                                @endcan
                                @can('warranty.view')
                                <li>
                                    <a href="{{ route('warranty.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'warranty.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Warranty') }}</span>
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </div>
                </li>
                <!-- Report -->
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="5">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                <path d="m22.204,1.162c-1.141-.952-2.634-1.343-4.098-1.081l-3.822.695c-.913.167-1.706.634-2.284,1.289-.578-.655-1.371-1.123-2.285-1.289L5.894.082C4.433-.181,2.938.21,1.796,1.162c-1.142.953-1.796,2.352-1.796,3.839v12.792c0,2.417,1.727,4.486,4.106,4.919l6.284,1.143c.534.097,1.072.146,1.61.146s1.076-.048,1.61-.146l6.285-1.143c2.379-.433,4.105-2.502,4.105-4.919V5.001c0-1.487-.655-2.886-1.796-3.839Zm-11.204,20.766c-.084-.012-6.536-1.184-6.536-1.184-1.428-.26-2.464-1.501-2.464-2.952V5.001c0-.892.393-1.731,1.078-2.303.545-.455,1.223-.697,1.919-.697.179,0,.36.016.54.049l3.821.695c.952.173,1.643,1.001,1.643,1.968v17.216Zm11-4.135c0,1.451-1.036,2.692-2.463,2.952,0,0-6.452,1.171-6.537,1.184V4.712c0-.967.691-1.794,1.642-1.968l3.821-.695c.878-.161,1.773.076,2.459.648.685.572,1.078,1.411,1.078,2.303v12.792ZM8.984,6.224c-.088.483-.509.821-.983.821-.059,0-3.18-.562-3.18-.562-.543-.099-.904-.619-.805-1.163.099-.543.615-.901,1.163-.805l3,.545c.543.099.904.619.805,1.163Zm0,3.955c-.088.483-.509.821-.983.821-.059,0-3.18-.562-3.18-.562-.543-.099-.904-.619-.805-1.163.099-.543.615-.903,1.163-.805l3,.545c.543.099.904.619.805,1.163Zm0,4c-.088.483-.509.821-.983.821-.059,0-3.18-.562-3.18-.562-.543-.099-.904-.619-.805-1.163.099-.543.615-.902,1.163-.805l3,.545c.543.099.904.619.805,1.163Zm11-8.857c.099.543-.262,1.064-.805,1.163,0,0-3.121.562-3.18.562-.474,0-.895-.338-.983-.821-.099-.543.262-1.064.805-1.163l3-.545c.541-.097,1.064.262,1.163.805Zm0,3.955c.099.543-.262,1.064-.805,1.163,0,0-3.121.562-3.18.562-.474,0-.895-.338-.983-.821-.099-.543.262-1.064.805-1.163l3-.545c.541-.098,1.064.262,1.163.805Zm0,4c.099.543-.262,1.064-.805,1.163,0,0-3.121.562-3.18.562-.474,0-.895-.338-.983-.821-.099-.543.262-1.064.805-1.163l3-.545c.541-.097,1.064.262,1.163.805Zm-2,4.364c.099.543-.262,1.064-.805,1.163,0,0-1.121.198-1.18.198-.474,0-.895-.338-.983-.821-.099-.543.262-1.064.805-1.163l1-.182c.549-.098,1.064.262,1.163.805Zm-11,.221c-.088.483-.509.821-.983.821-.059,0-1.18-.198-1.18-.198-.543-.099-.904-.619-.805-1.163.099-.543.615-.906,1.163-.805l1,.182c.543.099.904.619.805,1.163Z"/>
                            </svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Report') }}</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="5">
                        <div class="overflow-hidden">
                            <ul>
                                <li>
                                    <a href="{{ route('report.production_report.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'report.production_report.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Production Report') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('report.sales_report.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'report.sales_report.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Sales Report') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('report.stock_report.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'report.stock_report.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Stock Report') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('report.earning_report.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'report.earning_report.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Earning Report') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('report.service_report.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'report.service_report.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Service Report') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('report.technician_stock_report.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'report.technician_stock_report.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Technician Stock Report') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>
                <!-- User Role Management -->
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="9">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M15,6c0-3.309-2.691-6-6-6S3,2.691,3,6s2.691,6,6,6,6-2.691,6-6Zm-6,4c-2.206,0-4-1.794-4-4s1.794-4,4-4,4,1.794,4,4-1.794,4-4,4Zm-.008,4.938c.068,.548-.32,1.047-.869,1.116-3.491,.436-6.124,3.421-6.124,6.946,0,.552-.448,1-1,1s-1-.448-1-1c0-4.531,3.386-8.37,7.876-8.93,.542-.069,1.047,.32,1.116,.869Zm13.704,4.195l-.974-.562c.166-.497,.278-1.019,.278-1.572s-.111-1.075-.278-1.572l.974-.562c.478-.276,.642-.888,.366-1.366-.277-.479-.887-.644-1.366-.366l-.973,.562c-.705-.794-1.644-1.375-2.723-1.594v-1.101c0-.552-.448-1-1-1s-1,.448-1,1v1.101c-1.079,.22-2.018,.801-2.723,1.594l-.973-.562c-.48-.277-1.09-.113-1.366,.366-.276,.479-.112,1.09,.366,1.366l.974,.562c-.166,.497-.278,1.019-.278,1.572s.111,1.075,.278,1.572l-.974,.562c-.478,.276-.642,.888-.366,1.366,.186,.321,.521,.5,.867,.5,.169,0,.341-.043,.499-.134l.973-.562c.705,.794,1.644,1.375,2.723,1.594v1.101c0,.552,.448,1,1,1s1-.448,1-1v-1.101c1.079-.22,2.018-.801,2.723-1.594l.973,.562c.158,.091,.33,.134,.499,.134,.346,0,.682-.179,.867-.5,.276-.479,.112-1.09-.366-1.366Zm-5.696,.866c-1.654,0-3-1.346-3-3s1.346-3,3-3,3,1.346,3,3-1.346,3-3,3Z"/></svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('User Role Management') }}</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="9">
                        <div class="overflow-hidden">
                            <ul>
                                <li>
                                    <a href="{{ route('user_management.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'user_management.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('User Management') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('role_management.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'role_management.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Role Management') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>
                <!-- Settings -->
                @can('setting.view')
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="2">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M12,8a4,4,0,1,0,4,4A4,4,0,0,0,12,8Zm0,6a2,2,0,1,1,2-2A2,2,0,0,1,12,14Z"/><path d="M21.294,13.9l-.444-.256a9.1,9.1,0,0,0,0-3.29l.444-.256a3,3,0,1,0-3-5.2l-.445.257A8.977,8.977,0,0,0,15,3.513V3A3,3,0,0,0,9,3v.513A8.977,8.977,0,0,0,6.152,5.159L5.705,4.9a3,3,0,0,0-3,5.2l.444.256a9.1,9.1,0,0,0,0,3.29l-.444.256a3,3,0,1,0,3,5.2l.445-.257A8.977,8.977,0,0,0,9,20.487V21a3,3,0,0,0,6,0v-.513a8.977,8.977,0,0,0,2.848-1.646l.447.258a3,3,0,0,0,3-5.2Zm-2.548-3.776a7.048,7.048,0,0,1,0,3.75,1,1,0,0,0,.464,1.133l1.084.626a1,1,0,0,1-1,1.733l-1.086-.628a1,1,0,0,0-1.215.165,6.984,6.984,0,0,1-3.243,1.875,1,1,0,0,0-.751.969V21a1,1,0,0,1-2,0V19.748a1,1,0,0,0-.751-.969A6.984,6.984,0,0,1,7.006,16.9a1,1,0,0,0-1.215-.165l-1.084.627a1,1,0,1,1-1-1.732l1.084-.626a1,1,0,0,0,.464-1.133,7.048,7.048,0,0,1,0-3.75A1,1,0,0,0,4.79,8.992L3.706,8.366a1,1,0,0,1,1-1.733l1.086.628A1,1,0,0,0,7.006,7.1a6.984,6.984,0,0,1,3.243-1.875A1,1,0,0,0,11,4.252V3a1,1,0,0,1,2,0V4.252a1,1,0,0,0,.751.969A6.984,6.984,0,0,1,16.994,7.1a1,1,0,0,0,1.215.165l1.084-.627a1,1,0,1,1,1,1.732l-1.084.626A1,1,0,0,0,18.746,10.125Z"/></svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">{{ __('Setting') }}</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="2">
                        <div class="overflow-hidden">
                            <ul>
                                <li>
                                    <a href="{{ route('area.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'area.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Area') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('credit_term.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'credit_term.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Credit Term') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('currency.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'currency.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Currency') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('debtor_type.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'debtor_type.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Debtor Type') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('material_use.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'material_use.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Material Use') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('inventory_type.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'inventory_type.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Product Type') }}</span>
                                    </a>
                                </li>
                                @can('inventory.category.view')
                                <li>
                                    <a href="{{ route('inventory_category.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'inventory_category.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Product Category') }}</span>
                                    </a>
                                </li>
                                @endcan
                                <li>
                                    <a href="{{ route('promotion.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'promotion.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Promotion') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('project_type.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'project_type.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Project Type') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('platform.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'platform.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Platform') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('priority.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'priority.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Priority') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('service.index') }}" class="rounded-md p-2 flex items-center {{ !str_contains(Route::currentRouteName(), 'service.') && str_contains(Route::currentRouteName(), 'service.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Service') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('sync.index') }}" class="rounded-md p-2 flex items-center {{ !str_contains(Route::currentRouteName(), 'sync.') && str_contains(Route::currentRouteName(), 'service.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Sync') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('uom.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'uom.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('UOM') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('warranty_period.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'warranty_period.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">{{ __('Warranty Period') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>
                @endcan
            </ul>
        </div>
        <div class="px-4 py-3 border-t border-gray-50 flex items-center justify-between">
            <div class="flex-1 flex flex-col overflow-hidden">
                <span class="text-sm font-light text-slate-300 leading-none whitespace-nowrap" id="time-section"></span>
                <h3 class="text-base font-medium leading-tight truncate whitespace-nowrap text-white">{{ Auth::user()->name }}</h3>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="rounded-full bg-white p-2.5 transition duration-300 hover:bg-red-100" title="Logout">
                    <svg class="h-4 w-4 fill-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M22.763,10.232l-4.95-4.95L16.4,6.7,20.7,11H6.617v2H20.7l-4.3,4.3,1.414,1.414,4.95-4.95a2.5,2.5,0,0,0,0-3.536Z"/><path d="M10.476,21a1,1,0,0,1-1,1H3a1,1,0,0,1-1-1V3A1,1,0,0,1,3,2H9.476a1,1,0,0,1,1,1V8.333h2V3a3,3,0,0,0-3-3H3A3,3,0,0,0,0,3V21a3,3,0,0,0,3,3H9.476a3,3,0,0,0,3-3V15.667h-2Z"/></svg>
                </button>
            </form>
        </div>
    </div>
</aside>

@push('scripts')
    <script>
        CURRENT_ROUTE_NAME = '{{ Route::currentRouteName() }}'
        IS_SIDEBAR_EXPAND = localStorage.getItem('is_sidebar_expand')

        console.debug(CURRENT_ROUTE_NAME)

        $(document).ready(function(){
            getTimeSection()


            if ($(window).width() >= 1024) {
                if (IS_SIDEBAR_EXPAND == 'true' || IS_SIDEBAR_EXPAND == null) {
                    if (CURRENT_ROUTE_NAME.includes('vehicle_service.') || CURRENT_ROUTE_NAME.includes('vehicle.')) {
                        $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="11"]').click()
                    } else if (CURRENT_ROUTE_NAME.includes('customer.') || CURRENT_ROUTE_NAME.includes('supplier.')) {
                        $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="6"]').click()
                    } else if (CURRENT_ROUTE_NAME.includes('invoice.e-invoice.') || CURRENT_ROUTE_NAME.includes('invoice.consolidated-e-invoice.') || CURRENT_ROUTE_NAME.includes('invoice.credit-note.') || CURRENT_ROUTE_NAME.includes('invoice.debit-note.')) {
                        $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="101"]').click()
                    } else if (CURRENT_ROUTE_NAME.includes('quotation.') || CURRENT_ROUTE_NAME.includes('pending_order.') || CURRENT_ROUTE_NAME.includes('sale_order.') || CURRENT_ROUTE_NAME.includes('delivery_order.') || CURRENT_ROUTE_NAME.includes('transport_ack.') || CURRENT_ROUTE_NAME.includes('invoice.') || CURRENT_ROUTE_NAME.includes('billing.') || CURRENT_ROUTE_NAME.includes('invoice_return.')) {
                        $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="3"]').click()
                    } else if (CURRENT_ROUTE_NAME.includes('inventory_summary.') || CURRENT_ROUTE_NAME.includes('grn.') || CURRENT_ROUTE_NAME.includes('product.') || CURRENT_ROUTE_NAME.includes('raw_material.')) {
                        $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="4"]').click()
                    } else if (CURRENT_ROUTE_NAME.includes('ticket.') || CURRENT_ROUTE_NAME.includes('task.') || CURRENT_ROUTE_NAME.includes('target.')) {
                        $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="8"]').click()
                    } else if (CURRENT_ROUTE_NAME.includes('service_history.') || CURRENT_ROUTE_NAME.includes('service_reminder.') || CURRENT_ROUTE_NAME.includes('warranty.')) {
                        $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="7"]').click()
                    } else if (CURRENT_ROUTE_NAME.includes('report.')) {
                        $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="5"]').click()
                    } else if (CURRENT_ROUTE_NAME.includes('user_management.') || CURRENT_ROUTE_NAME.includes('role_management.')) {
                        $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="9"]').click()
                    } else if (CURRENT_ROUTE_NAME.includes('area.') || CURRENT_ROUTE_NAME.includes('credit_term.') || CURRENT_ROUTE_NAME.includes('currency.') || CURRENT_ROUTE_NAME.includes('debtor_type.') || CURRENT_ROUTE_NAME.includes('material_use.') || CURRENT_ROUTE_NAME.includes('inventory_category.') || CURRENT_ROUTE_NAME.includes('promotion.') || CURRENT_ROUTE_NAME.includes('project_type.') || CURRENT_ROUTE_NAME.includes('platform.') || CURRENT_ROUTE_NAME.includes('priority.') || CURRENT_ROUTE_NAME.includes('service.') || CURRENT_ROUTE_NAME.includes('sync.') || CURRENT_ROUTE_NAME.includes('uom.') || CURRENT_ROUTE_NAME.includes('warranty_period.')) {
                        $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="2"]').click()
                    } else if (CURRENT_ROUTE_NAME.includes('production.') || CURRENT_ROUTE_NAME.includes('production_material.')) {
                        $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="10"]').click()
                    }
                } else {
                    $('#expanded-sidebar').removeClass('max-w-[250px] transition-all duration-700 delay-700')
                    $('#expanded-sidebar').addClass('max-w-0 w-0 opacity-0 -z-10')

                    setTimeout(() => {
                        $('#expanded-sidebar').addClass('transition-all duration-700')
                    }, 100);

                    $('#collapsed-sidebar').removeClass('max-w-0 transition-all duration-700 -z-10 opacity-0')
                    $('#collapsed-sidebar').addClass('max-w-[250px]')

                    setTimeout(() => {
                        $('#collapsed-sidebar').addClass('transition-all duration-700 delay-700')
                    }, 100);
                }
            } else {
                if (CURRENT_ROUTE_NAME.includes('vehicle_service.') || CURRENT_ROUTE_NAME.includes('vehicle.')) {
                    $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="11"]').click()
                } else if (CURRENT_ROUTE_NAME.includes('customer.') || CURRENT_ROUTE_NAME.includes('supplier.')) {
                    $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="6"]').click()
                } else if (CURRENT_ROUTE_NAME.includes('invoice.e-invoice.') || CURRENT_ROUTE_NAME.includes('invoice.consolidated-e-invoice.') || CURRENT_ROUTE_NAME.includes('invoice.credit-note.') || CURRENT_ROUTE_NAME.includes('invoice.debit-note.')) {
                    $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="101"]').click()
                } else if (CURRENT_ROUTE_NAME.includes('quotation.') || CURRENT_ROUTE_NAME.includes('pending_order.') || CURRENT_ROUTE_NAME.includes('sale_order.') || CURRENT_ROUTE_NAME.includes('delivery_order.') || CURRENT_ROUTE_NAME.includes('transport_ack.') || CURRENT_ROUTE_NAME.includes('invoice.') || CURRENT_ROUTE_NAME.includes('billing.') || CURRENT_ROUTE_NAME.includes('invoice_return.')) {
                    $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="3"]').click()
                } else if (CURRENT_ROUTE_NAME.includes('inventory_summary.') || CURRENT_ROUTE_NAME.includes('grn.') || CURRENT_ROUTE_NAME.includes('product.') || CURRENT_ROUTE_NAME.includes('raw_material.')) {
                    $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="4"]').click()
                } else if (CURRENT_ROUTE_NAME.includes('ticket.') || CURRENT_ROUTE_NAME.includes('task.') || CURRENT_ROUTE_NAME.includes('target.')) {
                    $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="8"]').click()
                } else if (CURRENT_ROUTE_NAME.includes('service_history.') || CURRENT_ROUTE_NAME.includes('service_reminder.') || CURRENT_ROUTE_NAME.includes('warranty.')) {
                    $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="7"]').click()
                } else if (CURRENT_ROUTE_NAME.includes('report.')) {
                    $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="5"]').click()
                } else if (CURRENT_ROUTE_NAME.includes('user_management.') || CURRENT_ROUTE_NAME.includes('role_management.')) {
                    $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="9"]').click()
                } else if (CURRENT_ROUTE_NAME.includes('area.') || CURRENT_ROUTE_NAME.includes('credit_term.') || CURRENT_ROUTE_NAME.includes('currency.') || CURRENT_ROUTE_NAME.includes('debtor_type.') || CURRENT_ROUTE_NAME.includes('material_use.') || CURRENT_ROUTE_NAME.includes('inventory_category.') || CURRENT_ROUTE_NAME.includes('promotion.') || CURRENT_ROUTE_NAME.includes('project_type.') || CURRENT_ROUTE_NAME.includes('platform.') || CURRENT_ROUTE_NAME.includes('priority.') || CURRENT_ROUTE_NAME.includes('service.') || CURRENT_ROUTE_NAME.includes('service.') || CURRENT_ROUTE_NAME.includes('uom.') || CURRENT_ROUTE_NAME.includes('warranty_period.')) {
                    $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="2"]').click()
                } else if (CURRENT_ROUTE_NAME.includes('production.') || CURRENT_ROUTE_NAME.includes('production_material.')) {
                    $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="10"]').click()
                }
            }
        })

        function getTimeSection() {
            var currentHour = moment().format("HH");
            var timeSection = "{!! __('Good Morning,') !!}"

            if (currentHour >= 5 && currentHour < 12) {
                timeSection = "{!! __('Good Morning,') !!}"
            } else if (currentHour >= 12 && currentHour < 18) {
                timeSection = "{!! __('Good Afternoon,') !!}"
            } else {
                timeSection = "{!! __('Good Evening,') !!}"
            }
            $('#expanded-sidebar #time-section').text(`${timeSection}`)
        }

        $('#expanded-sidebar .sidebar-menu-trigger, #mobile-sidebar .sidebar-menu-trigger').on('click', function() {
            var id = $(this).data('accordionstriggerid')

            if ($(`.sidebar-accordions[data-accordionid="${id}"]`).hasClass('grid-rows-[1fr]')) {
                $(`.sidebar-accordions[data-accordionid="${id}"]`).removeClass('grid-rows-[1fr] opacity-100')
                $(`.sidebar-accordions[data-accordionid="${id}"]`).addClass('grid-rows-[0fr] opacity-0')
            } else {
                $(`.sidebar-accordions[data-accordionid="${id}"]`).removeClass('grid-rows-[0fr] opacity-0')
                $(`.sidebar-accordions[data-accordionid="${id}"]`).addClass('grid-rows-[1fr] opacity-100')
            }
        })

        $('#collapse-sidebar-btn').on('click', function() {
            $('#expanded-sidebar').toggleClass('max-w-[250px] max-w-0 w-0 opacity-0 -z-10 delay-700')

            $('#collapsed-sidebar').toggleClass('max-w-0 max-w-[250px] -z-10 opacity-0 delay-700')
            localStorage.setItem('is_sidebar_expand', false)
        })

        $('#expand-sidebar-btn').on('click', function() {
            $('#collapsed-sidebar').toggleClass('max-w-0 max-w-[250px] -z-10 opacity-0 delay-700')

            $('#expanded-sidebar').toggleClass('max-w-[250px] max-w-0 w-0 opacity-0 -z-10 delay-700')
            localStorage.setItem('is_sidebar_expand', true)
        })

        $('.expand-sub-menu-triggers').on('mouseenter', function() {
            let type = $(this).data('type')

            $(`.sub-menu-content`).removeClass('max-w-[250px]')
            $(`.sub-menu-content`).addClass('opacity-0 -z-50 invisible')

            $(`.sub-menu-content[data-type="${type}"]`).toggleClass('opacity-0 -z-50 max-w-[250px] invisible')
        })

        $('#collapsed-sidebar').on('mouseleave', function() {
            $(`.sub-menu-content`).removeClass('max-w-[250px]')
            $(`.sub-menu-content`).addClass('opacity-0 -z-50 invisible')
        })

        $('.tooltip-triggers').on('mouseenter', function() {
            $(`.sub-menu-content`).removeClass('max-w-[250px]')
            $(`.sub-menu-content`).addClass('opacity-0 -z-50 invisible')
        })

        function refreshPendingOrdersCount() {
            $.ajax({
                url: '{{ route('pending_order.count') }}',
                method: 'GET',
                success: function(response) {
                    if (response.count > 0) {
                        $('#pending-orders-icon').css('display', 'block');
                    } else {
                        $('#pending-orders-icon').css('display', 'none');
                    }
                },
                error: function() {
                    console.error('Error fetching pending orders count.');
                }
            });
        }

        $(document).ready(function() {
            refreshPendingOrdersCount();
        });

        $(document).on('salePersonAssigned', function() {
            refreshPendingOrdersCount();
        });

        $('.mobile-sidebar-trigger-btn').on('click', function() {
            $('#mobile-sidebar').toggleClass('hidden')
        })

    </script>
@endpush
