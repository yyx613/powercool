<!-- Expand -->
<aside class="max-w-[250px] w-full bg-blue-900 transition-all duration-700 delay-700" id="expanded-sidebar">
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
        <div class="flex-1 overflow-y-auto px-2 my-4">
            <ul>
                <li>
                    <a href="{{ route('dashboard.index') }}" class="p-2 flex items-center rounded-md {{ str_contains(Route::currentRouteName(), 'dashboard.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                            <path d="M14,12c0,1.019-.308,1.964-.832,2.754l-2.875-2.875c-.188-.188-.293-.442-.293-.707V7.101c2.282,.463,4,2.48,4,4.899Zm-6-.414V7.101c-2.55,.518-4.396,2.976-3.927,5.767,.325,1.934,1.82,3.543,3.729,3.992,1.47,.345,2.86,.033,3.952-.691l-3.169-3.169c-.375-.375-.586-.884-.586-1.414Zm11-4.586h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c.553,0,1-.448,1-1s-.447-1-1-1Zm0,4h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c.553,0,1-.448,1-1s-.447-1-1-1Zm0,4h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c.553,0,1-.448,1-1s-.447-1-1-1Zm5-7v8c0,2.757-2.243,5-5,5H5c-2.757,0-5-2.243-5-5V8C0,5.243,2.243,3,5,3h14c2.757,0,5,2.243,5,5Zm-2,0c0-1.654-1.346-3-3-3H5c-1.654,0-3,1.346-3,3v8c0,1.654,1.346,3,3,3h14c1.654,0,3-1.346,3-3V8Z"/>
                        </svg>
                        <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">Dashboard</span>
                    </a>
                </li>
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="4">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M19.5,16c0,.553-.447,1-1,1h-2c-.553,0-1-.447-1-1s.447-1,1-1h2c.553,0,1,.447,1,1Zm4.5-1v5c0,2.206-1.794,4-4,4H4c-2.206,0-4-1.794-4-4v-5c0-2.206,1.794-4,4-4h1V4C5,1.794,6.794,0,9,0h6c2.206,0,4,1.794,4,4v7h1c2.206,0,4,1.794,4,4ZM7,11h10V4c0-1.103-.897-2-2-2h-6c-1.103,0-2,.897-2,2v7Zm-3,11h7V13H4c-1.103,0-2,.897-2,2v5c0,1.103,.897,2,2,2Zm18-7c0-1.103-.897-2-2-2h-7v9h7c1.103,0,2-.897,2-2v-5Zm-14.5,0h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1s-.447-1-1-1ZM14,5c0-.553-.447-1-1-1h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1Z"/></svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">Inventory</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="4">
                        <div class="overflow-hidden">
                            <ul>
                                @can('inventory.summary.view')
                                <li>
                                    <a href="{{ route('inventory_summary.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'inventory_summary.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Summary</span>
                                    </a>
                                </li>
                                @endcan
                                @can('inventory.category.view')
                                <li>
                                    <a href="{{ route('inventory_category.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'inventory_category.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Category</span>
                                    </a>
                                </li>
                                @endcan
                                @can('inventory.product.view')
                                <li>
                                    <a href="{{ route('product.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'product.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Product</span>
                                    </a>
                                </li>
                                @endcan
                                @can('inventory.raw_material.view')
                                <li>
                                    <a href="{{ route('raw_material.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'raw_material.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Raw Material</span>
                                    </a>
                                </li>
                                @endcan
                                @can('grn.view')
                                <li>
                                    <a href="{{ route('grn.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'grn.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">GRN</span>
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="3">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M23,22H3a1,1,0,0,1-1-1V1A1,1,0,0,0,0,1V21a3,3,0,0,0,3,3H23a1,1,0,0,0,0-2Z"/><path d="M15,20a1,1,0,0,0,1-1V12a1,1,0,0,0-2,0v7A1,1,0,0,0,15,20Z"/><path d="M7,20a1,1,0,0,0,1-1V12a1,1,0,0,0-2,0v7A1,1,0,0,0,7,20Z"/><path d="M19,20a1,1,0,0,0,1-1V7a1,1,0,0,0-2,0V19A1,1,0,0,0,19,20Z"/><path d="M11,20a1,1,0,0,0,1-1V7a1,1,0,0,0-2,0V19A1,1,0,0,0,11,20Z"/></svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">Sale</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="3">
                        <div class="overflow-hidden">
                            <ul>
                                @can('sale.quotation.view')
                                <li>
                                    <a href="{{ route('quotation.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'quotation.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Quotation</span>
                                    </a>
                                </li>
                                @endcan
                                @can('sale.sale_order.view')
                                <li>
                                    <a href="{{ route('sale_order.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'sale_order.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Sale Order</span>
                                    </a>
                                </li>
                                @endcan
                                @can('sale.delivery_order.view')
                                <li>
                                    <a href="{{ route('delivery_order.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'delivery_order.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Delivery Order</span>
                                    </a>
                                </li>
                                @endcan
                                @can('sale.invoice.view')
                                <li>
                                    <a href="{{ route('invoice.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'invoice.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Invoice</span>
                                    </a>
                                </li>
                                @endcan
                                @can('sale.target.view')
                                <li>
                                    <a href="{{ route('target.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'target.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Target</span>
                                    </a>
                                </li>
                                @endcan
                                @can('sale.billing.view')
                                <li>
                                    <a href="{{ route('billing.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'billing.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Billing</span>
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </div>
                </li>
                @can('task.view')
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="1">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg class="h-5 w-5 flex-none fill-white" id="Layer_1" height="512" viewBox="0 0 24 24" width="512" xmlns="http://www.w3.org/2000/svg" data-name="Layer 1"><path d="m4 6a2.982 2.982 0 0 1 -2.122-.879l-1.544-1.374a1 1 0 0 1 1.332-1.494l1.585 1.414a1 1 0 0 0 1.456.04l3.604-3.431a1 1 0 0 1 1.378 1.448l-3.589 3.414a2.964 2.964 0 0 1 -2.1.862zm20-2a1 1 0 0 0 -1-1h-10a1 1 0 0 0 0 2h10a1 1 0 0 0 1-1zm-17.9 9.138 3.589-3.414a1 1 0 1 0 -1.378-1.448l-3.6 3.431a1.023 1.023 0 0 1 -1.414 0l-1.59-1.585a1 1 0 0 0 -1.414 1.414l1.585 1.585a3 3 0 0 0 4.226.017zm17.9-1.138a1 1 0 0 0 -1-1h-10a1 1 0 0 0 0 2h10a1 1 0 0 0 1-1zm-17.9 9.138 3.585-3.414a1 1 0 1 0 -1.378-1.448l-3.6 3.431a1 1 0 0 1 -1.456-.04l-1.585-1.414a1 1 0 0 0 -1.332 1.494l1.544 1.374a3 3 0 0 0 4.226.017zm17.9-1.138a1 1 0 0 0 -1-1h-10a1 1 0 0 0 0 2h10a1 1 0 0 0 1-1z"/></svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">Task</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="1">
                        <div class="overflow-hidden">
                            <ul>
                                <li>
                                    <a href="{{ route('task.driver.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'task.driver.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Driver</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('task.technician.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'task.technician.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Technician</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('task.sale.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'task.sale.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Sale</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>
                @endcan
                @can('production.view')
                <li>
                    <a href="{{ route('production.index') }}" class="p-2 flex items-center rounded-md {{ str_contains(Route::currentRouteName(), 'production.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                            <path d="m22.97,6.251c-.637-.354-1.415-.331-2.1.101l-4.87,3.649v-2.001c0-.727-.395-1.397-1.03-1.749-.637-.354-1.416-.331-2.1.101l-4.87,3.649V2c.553,0,1-.448,1-1s-.447-1-1-1H1C.447,0,0,.448,0,1s.447,1,1,1v17c0,2.757,2.243,5,5,5h13c2.757,0,5-2.243,5-5v-11c0-.727-.395-1.397-1.03-1.749Zm-.97,12.749c0,1.654-1.346,3-3,3H6c-1.654,0-3-1.346-3-3V2h3v9.991c0,.007,0,.014,0,.02v5.989c0,.552.447,1,1,1s1-.448,1-1v-5.5l6-4.5v4c0,.379.214.725.553.895s.743.134,1.047-.094l6.4-4.8v11Zm-8-2v1c0,.552-.448,1-1,1h-1c-.552,0-1-.448-1-1v-1c0-.552.448-1,1-1h1c.552,0,1,.448,1,1Zm2,1v-1c0-.552.448-1,1-1h1c.552,0,1,.448,1,1v1c0,.552-.448,1-1,1h-1c-.552,0-1-.448-1-1Z"/>
                        </svg>
                        <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">Production</span>
                    </a>
                </li>
                @endcan
                @can('ticket.view')
                <li>
                    <a href="{{ route('ticket.index') }}" class="p-2 flex items-center rounded-md {{ str_contains(Route::currentRouteName(), 'ticket.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M16,0h-.13a2.02,2.02,0,0,0-1.941,1.532,2,2,0,0,1-3.858,0A2.02,2.02,0,0,0,8.13,0H8A5.006,5.006,0,0,0,3,5V21a3,3,0,0,0,3,3H8.13a2.02,2.02,0,0,0,1.941-1.532,2,2,0,0,1,3.858,0A2.02,2.02,0,0,0,15.87,24H18a3,3,0,0,0,3-3V5A5.006,5.006,0,0,0,16,0Zm2,22-2.143-.063A4,4,0,0,0,8.13,22H6a1,1,0,0,1-1-1V17H7a1,1,0,0,0,0-2H5V5A3,3,0,0,1,8,2l.143.063A4.01,4.01,0,0,0,12,5a4.071,4.071,0,0,0,3.893-3H16a3,3,0,0,1,3,3V15H17a1,1,0,0,0,0,2h2v4A1,1,0,0,1,18,22Z"/><path d="M13,15H11a1,1,0,0,0,0,2h2a1,1,0,0,0,0-2Z"/></svg>
                        <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">Ticket</span>
                    </a>
                </li>
                @endcan
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="5">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                <path d="m22.204,1.162c-1.141-.952-2.634-1.343-4.098-1.081l-3.822.695c-.913.167-1.706.634-2.284,1.289-.578-.655-1.371-1.123-2.285-1.289L5.894.082C4.433-.181,2.938.21,1.796,1.162c-1.142.953-1.796,2.352-1.796,3.839v12.792c0,2.417,1.727,4.486,4.106,4.919l6.284,1.143c.534.097,1.072.146,1.61.146s1.076-.048,1.61-.146l6.285-1.143c2.379-.433,4.105-2.502,4.105-4.919V5.001c0-1.487-.655-2.886-1.796-3.839Zm-11.204,20.766c-.084-.012-6.536-1.184-6.536-1.184-1.428-.26-2.464-1.501-2.464-2.952V5.001c0-.892.393-1.731,1.078-2.303.545-.455,1.223-.697,1.919-.697.179,0,.36.016.54.049l3.821.695c.952.173,1.643,1.001,1.643,1.968v17.216Zm11-4.135c0,1.451-1.036,2.692-2.463,2.952,0,0-6.452,1.171-6.537,1.184V4.712c0-.967.691-1.794,1.642-1.968l3.821-.695c.878-.161,1.773.076,2.459.648.685.572,1.078,1.411,1.078,2.303v12.792ZM8.984,6.224c-.088.483-.509.821-.983.821-.059,0-3.18-.562-3.18-.562-.543-.099-.904-.619-.805-1.163.099-.543.615-.901,1.163-.805l3,.545c.543.099.904.619.805,1.163Zm0,3.955c-.088.483-.509.821-.983.821-.059,0-3.18-.562-3.18-.562-.543-.099-.904-.619-.805-1.163.099-.543.615-.903,1.163-.805l3,.545c.543.099.904.619.805,1.163Zm0,4c-.088.483-.509.821-.983.821-.059,0-3.18-.562-3.18-.562-.543-.099-.904-.619-.805-1.163.099-.543.615-.902,1.163-.805l3,.545c.543.099.904.619.805,1.163Zm11-8.857c.099.543-.262,1.064-.805,1.163,0,0-3.121.562-3.18.562-.474,0-.895-.338-.983-.821-.099-.543.262-1.064.805-1.163l3-.545c.541-.097,1.064.262,1.163.805Zm0,3.955c.099.543-.262,1.064-.805,1.163,0,0-3.121.562-3.18.562-.474,0-.895-.338-.983-.821-.099-.543.262-1.064.805-1.163l3-.545c.541-.098,1.064.262,1.163.805Zm0,4c.099.543-.262,1.064-.805,1.163,0,0-3.121.562-3.18.562-.474,0-.895-.338-.983-.821-.099-.543.262-1.064.805-1.163l3-.545c.541-.097,1.064.262,1.163.805Zm-2,4.364c.099.543-.262,1.064-.805,1.163,0,0-1.121.198-1.18.198-.474,0-.895-.338-.983-.821-.099-.543.262-1.064.805-1.163l1-.182c.549-.098,1.064.262,1.163.805Zm-11,.221c-.088.483-.509.821-.983.821-.059,0-1.18-.198-1.18-.198-.543-.099-.904-.619-.805-1.163.099-.543.615-.906,1.163-.805l1,.182c.543.099.904.619.805,1.163Z"/>
                            </svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">Report</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="5">
                        <div class="overflow-hidden">
                            <ul>
                                <li>
                                    <a href="{{ route('report.production_report.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'report.production_report.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Production Report</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('report.sales_report.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'report.sales_report.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Sales Report</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('report.stock_report.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'report.stock_report.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Stock Report</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('report.earning_report.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'report.earning_report.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Earning Report</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>
                @can('customer.view')
                <li>
                    <a href="{{ route('customer.index') }}" class="p-2 flex items-center rounded-md {{ str_contains(Route::currentRouteName(), 'customer.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,16a4,4,0,1,1,4-4A4,4,0,0,1,12,16Zm0-6a2,2,0,1,0,2,2A2,2,0,0,0,12,10Zm6,13A6,6,0,0,0,6,23a1,1,0,0,0,2,0,4,4,0,0,1,8,0,1,1,0,0,0,2,0ZM18,8a4,4,0,1,1,4-4A4,4,0,0,1,18,8Zm0-6a2,2,0,1,0,2,2A2,2,0,0,0,18,2Zm6,13a6.006,6.006,0,0,0-6-6,1,1,0,0,0,0,2,4,4,0,0,1,4,4,1,1,0,0,0,2,0ZM6,8a4,4,0,1,1,4-4A4,4,0,0,1,6,8ZM6,2A2,2,0,1,0,8,4,2,2,0,0,0,6,2ZM2,15a4,4,0,0,1,4-4A1,1,0,0,0,6,9a6.006,6.006,0,0,0-6,6,1,1,0,0,0,2,0Z"/></svg>
                        <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">Customer</span>
                    </a>
                </li>
                @endcan
                @can('supplier.view')
                <li>
                    <a href="{{ route('supplier.index') }}" class="p-2 flex items-center rounded-md {{ str_contains(Route::currentRouteName(), 'supplier.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                            <path d="m9,12c3.309,0,6-2.691,6-6S12.309,0,9,0,3,2.691,3,6s2.691,6,6,6Zm0-10c2.206,0,4,1.794,4,4s-1.794,4-4,4-4-1.794-4-4,1.794-4,4-4Zm12,12h-5c-1.654,0-3,1.346-3,3v4c0,1.654,1.346,3,3,3h5c1.654,0,3-1.346,3-3v-4c0-1.654-1.346-3-3-3Zm1,7c0,.552-.449,1-1,1h-5c-.551,0-1-.448-1-1v-4c0-.552.449-1,1-1h5c.551,0,1,.448,1,1v4Zm-2-3c0,.553-.448,1-1,1h-1c-.552,0-1-.447-1-1s.448-1,1-1h1c.552,0,1,.447,1,1Zm-9.351-1.072c.42.358.47.989.112,1.41l-.5.586c-.06.07-.129.132-.207.183-.79.527-1.859.386-2.487-.331l-2.331-2.767c-2.03,1.294-3.237,3.495-3.237,5.886v1.105c0,.553-.448,1-1,1s-1-.447-1-1v-1.105c0-3.075,1.551-5.906,4.148-7.571.846-.542,1.973-.371,2.618.397l2.211,2.625.261-.307c.358-.42.99-.472,1.41-.111Z"/>
                        </svg>
                        <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">Supplier</span>
                    </a>
                </li>
                @endcan
                @can('setting.view')
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="2">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M12,8a4,4,0,1,0,4,4A4,4,0,0,0,12,8Zm0,6a2,2,0,1,1,2-2A2,2,0,0,1,12,14Z"/><path d="M21.294,13.9l-.444-.256a9.1,9.1,0,0,0,0-3.29l.444-.256a3,3,0,1,0-3-5.2l-.445.257A8.977,8.977,0,0,0,15,3.513V3A3,3,0,0,0,9,3v.513A8.977,8.977,0,0,0,6.152,5.159L5.705,4.9a3,3,0,0,0-3,5.2l.444.256a9.1,9.1,0,0,0,0,3.29l-.444.256a3,3,0,1,0,3,5.2l.445-.257A8.977,8.977,0,0,0,9,20.487V21a3,3,0,0,0,6,0v-.513a8.977,8.977,0,0,0,2.848-1.646l.447.258a3,3,0,0,0,3-5.2Zm-2.548-3.776a7.048,7.048,0,0,1,0,3.75,1,1,0,0,0,.464,1.133l1.084.626a1,1,0,0,1-1,1.733l-1.086-.628a1,1,0,0,0-1.215.165,6.984,6.984,0,0,1-3.243,1.875,1,1,0,0,0-.751.969V21a1,1,0,0,1-2,0V19.748a1,1,0,0,0-.751-.969A6.984,6.984,0,0,1,7.006,16.9a1,1,0,0,0-1.215-.165l-1.084.627a1,1,0,1,1-1-1.732l1.084-.626a1,1,0,0,0,.464-1.133,7.048,7.048,0,0,1,0-3.75A1,1,0,0,0,4.79,8.992L3.706,8.366a1,1,0,0,1,1-1.733l1.086.628A1,1,0,0,0,7.006,7.1a6.984,6.984,0,0,1,3.243-1.875A1,1,0,0,0,11,4.252V3a1,1,0,0,1,2,0V4.252a1,1,0,0,0,.751.969A6.984,6.984,0,0,1,16.994,7.1a1,1,0,0,0,1.215.165l1.084-.627a1,1,0,1,1,1,1.732l-1.084.626A1,1,0,0,0,18.746,10.125Z"/></svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">Setting</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="2">
                        <div class="overflow-hidden">
                            <ul>
                                <li>
                                    <a href="{{ route('material_use.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'material_use.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Material Use</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('warranty_period.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'warranty_period.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Warranty Period</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('promotion.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'promotion.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Promotion</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('project_type.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'project_type.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Project Type</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('currency.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'currency.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Currency</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('credit_term.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'credit_term.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Credit Term</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('area.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'area.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Area</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('debtor_type.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'debtor_type.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Debtor Type</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('user_management.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'user_management.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">User Management</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('role_management.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'role_management.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Role Management</span>
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
<aside class="max-w-0 bg-blue-900 -z-10 opacity-0 transition-all duration-700" id="collapsed-sidebar">
    <div class="h-screen py-4 px-2 sticky top-0 z-50">
        <div class="relative flex flex-col h-full">
            <div class="flex items-center justify-center">
                <button type="button" class="rounded-full p-2.5 hover:bg-blue-600" id="expand-sidebar-btn">
                    <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><rect y="11" width="24" height="2" rx="1"/><rect y="4" width="24" height="2" rx="1"/><rect y="18" width="24" height="2" rx="1"/></svg>
                </button>
            </div>
            <div class="flex-1 my-4">
                <ul>
                    <li>
                        <a href="{{ route('dashboard.index') }}" class="relative group tooltip-triggers rounded-full p-2.5 flex items-center justify-center hover:bg-blue-600">
                            <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                <path d="M14,12c0,1.019-.308,1.964-.832,2.754l-2.875-2.875c-.188-.188-.293-.442-.293-.707V7.101c2.282,.463,4,2.48,4,4.899Zm-6-.414V7.101c-2.55,.518-4.396,2.976-3.927,5.767,.325,1.934,1.82,3.543,3.729,3.992,1.47,.345,2.86,.033,3.952-.691l-3.169-3.169c-.375-.375-.586-.884-.586-1.414Zm11-4.586h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c.553,0,1-.448,1-1s-.447-1-1-1Zm0,4h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c.553,0,1-.448,1-1s-.447-1-1-1Zm0,4h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c.553,0,1-.448,1-1s-.447-1-1-1Zm5-7v8c0,2.757-2.243,5-5,5H5c-2.757,0-5-2.243-5-5V8C0,5.243,2.243,3,5,3h14c2.757,0,5,2.243,5,5Zm-2,0c0-1.654-1.346-3-3-3H5c-1.654,0-3,1.346-3,3v8c0,1.654,1.346,3,3,3h14c1.654,0,3-1.346,3-3V8Z"/>
                            </svg>
                            <!-- Tooltip -->
                            <div class="absolute top-0 transition-all duration-500 left-0 opacity-0 invisible group-hover:visible group-hover:left-12 group-hover:opacity-100 rounded py-1.5 px-3 bg-blue-900 shadow h-full flex items-center border">
                                <span class="text-sm leading-tight font-semibold text-white whitespace-nowrap">Dashboard</span>
                            </div>
                        </a>
                    </li>
                    <li class="expand-sub-menu-triggers" data-type="inventory">
                        <button class="p-2.5 flex items-center justify-center rounded-full hover:bg-blue-600">
                            <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M19.5,16c0,.553-.447,1-1,1h-2c-.553,0-1-.447-1-1s.447-1,1-1h2c.553,0,1,.447,1,1Zm4.5-1v5c0,2.206-1.794,4-4,4H4c-2.206,0-4-1.794-4-4v-5c0-2.206,1.794-4,4-4h1V4C5,1.794,6.794,0,9,0h6c2.206,0,4,1.794,4,4v7h1c2.206,0,4,1.794,4,4ZM7,11h10V4c0-1.103-.897-2-2-2h-6c-1.103,0-2,.897-2,2v7Zm-3,11h7V13H4c-1.103,0-2,.897-2,2v5c0,1.103,.897,2,2,2Zm18-7c0-1.103-.897-2-2-2h-7v9h7c1.103,0,2-.897,2-2v-5Zm-14.5,0h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1s-.447-1-1-1ZM14,5c0-.553-.447-1-1-1h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1Z"/></svg>
                        </button>
                    </li>
                    <li class="expand-sub-menu-triggers" data-type="sale">
                        <button class="p-2.5 flex items-center justify-center rounded-full hover:bg-blue-600">
                            <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M23,22H3a1,1,0,0,1-1-1V1A1,1,0,0,0,0,1V21a3,3,0,0,0,3,3H23a1,1,0,0,0,0-2Z"/><path d="M15,20a1,1,0,0,0,1-1V12a1,1,0,0,0-2,0v7A1,1,0,0,0,15,20Z"/><path d="M7,20a1,1,0,0,0,1-1V12a1,1,0,0,0-2,0v7A1,1,0,0,0,7,20Z"/><path d="M19,20a1,1,0,0,0,1-1V7a1,1,0,0,0-2,0V19A1,1,0,0,0,19,20Z"/><path d="M11,20a1,1,0,0,0,1-1V7a1,1,0,0,0-2,0V19A1,1,0,0,0,11,20Z"/></svg>
                        </button>
                    </li>
                    @can('task.view')
                    <li class="expand-sub-menu-triggers" data-type="task">
                        <button class="p-2.5 flex items-center justify-center rounded-full hover:bg-blue-600">
                            <svg class="h-5 w-5 fill-white" id="Layer_1" height="512" viewBox="0 0 24 24" width="512" xmlns="http://www.w3.org/2000/svg" data-name="Layer 1"><path d="m4 6a2.982 2.982 0 0 1 -2.122-.879l-1.544-1.374a1 1 0 0 1 1.332-1.494l1.585 1.414a1 1 0 0 0 1.456.04l3.604-3.431a1 1 0 0 1 1.378 1.448l-3.589 3.414a2.964 2.964 0 0 1 -2.1.862zm20-2a1 1 0 0 0 -1-1h-10a1 1 0 0 0 0 2h10a1 1 0 0 0 1-1zm-17.9 9.138 3.589-3.414a1 1 0 1 0 -1.378-1.448l-3.6 3.431a1.023 1.023 0 0 1 -1.414 0l-1.59-1.585a1 1 0 0 0 -1.414 1.414l1.585 1.585a3 3 0 0 0 4.226.017zm17.9-1.138a1 1 0 0 0 -1-1h-10a1 1 0 0 0 0 2h10a1 1 0 0 0 1-1zm-17.9 9.138 3.585-3.414a1 1 0 1 0 -1.378-1.448l-3.6 3.431a1 1 0 0 1 -1.456-.04l-1.585-1.414a1 1 0 0 0 -1.332 1.494l1.544 1.374a3 3 0 0 0 4.226.017zm17.9-1.138a1 1 0 0 0 -1-1h-10a1 1 0 0 0 0 2h10a1 1 0 0 0 1-1z"/></svg>
                        </button>
                    </li>
                    @endcan
                    @can('production.view')
                    <li>
                        <a href="{{ route('production.index') }}" class="relative group tooltip-triggers rounded-full p-2.5 flex items-center justify-center hover:bg-blue-600">
                            <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                <path d="m22.97,6.251c-.637-.354-1.415-.331-2.1.101l-4.87,3.649v-2.001c0-.727-.395-1.397-1.03-1.749-.637-.354-1.416-.331-2.1.101l-4.87,3.649V2c.553,0,1-.448,1-1s-.447-1-1-1H1C.447,0,0,.448,0,1s.447,1,1,1v17c0,2.757,2.243,5,5,5h13c2.757,0,5-2.243,5-5v-11c0-.727-.395-1.397-1.03-1.749Zm-.97,12.749c0,1.654-1.346,3-3,3H6c-1.654,0-3-1.346-3-3V2h3v9.991c0,.007,0,.014,0,.02v5.989c0,.552.447,1,1,1s1-.448,1-1v-5.5l6-4.5v4c0,.379.214.725.553.895s.743.134,1.047-.094l6.4-4.8v11Zm-8-2v1c0,.552-.448,1-1,1h-1c-.552,0-1-.448-1-1v-1c0-.552.448-1,1-1h1c.552,0,1,.448,1,1Zm2,1v-1c0-.552.448-1,1-1h1c.552,0,1,.448,1,1v1c0,.552-.448,1-1,1h-1c-.552,0-1-.448-1-1Z"/>
                            </svg>
                            <!-- Tooltip -->
                            <div class="absolute top-0 transition-all duration-500 left-0 opacity-0 invisible group-hover:visible group-hover:left-12 group-hover:opacity-100 rounded py-1.5 px-3 bg-blue-900 shadow h-full flex items-center border">
                                <span class="text-sm leading-tight font-semibold text-white whitespace-nowrap">Production</span>
                            </div>
                        </a>
                    </li>
                    @endcan
                    @can('ticket.view')
                    <li>
                        <a href="{{ route('ticket.index') }}" class="relative group tooltip-triggers rounded-full p-2.5 flex items-center justify-center hover:bg-blue-600">
                            <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M16,0h-.13a2.02,2.02,0,0,0-1.941,1.532,2,2,0,0,1-3.858,0A2.02,2.02,0,0,0,8.13,0H8A5.006,5.006,0,0,0,3,5V21a3,3,0,0,0,3,3H8.13a2.02,2.02,0,0,0,1.941-1.532,2,2,0,0,1,3.858,0A2.02,2.02,0,0,0,15.87,24H18a3,3,0,0,0,3-3V5A5.006,5.006,0,0,0,16,0Zm2,22-2.143-.063A4,4,0,0,0,8.13,22H6a1,1,0,0,1-1-1V17H7a1,1,0,0,0,0-2H5V5A3,3,0,0,1,8,2l.143.063A4.01,4.01,0,0,0,12,5a4.071,4.071,0,0,0,3.893-3H16a3,3,0,0,1,3,3V15H17a1,1,0,0,0,0,2h2v4A1,1,0,0,1,18,22Z"/><path d="M13,15H11a1,1,0,0,0,0,2h2a1,1,0,0,0,0-2Z"/></svg>
                            <!-- Tooltip -->
                            <div class="absolute top-0 transition-all duration-500 left-0 opacity-0 invisible group-hover:visible group-hover:left-12 group-hover:opacity-100 rounded py-1.5 px-3 bg-blue-900 shadow h-full flex items-center border">
                                <span class="text-sm leading-tight font-semibold text-white whitespace-nowrap">Ticket</span>
                            </div>
                        </a>
                    </li>
                    @endcan
                    <li class="expand-sub-menu-triggers" data-type="report">
                        <button class="p-2.5 flex items-center justify-center rounded-full hover:bg-blue-600">
                            <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                <path d="m22.204,1.162c-1.141-.952-2.634-1.343-4.098-1.081l-3.822.695c-.913.167-1.706.634-2.284,1.289-.578-.655-1.371-1.123-2.285-1.289L5.894.082C4.433-.181,2.938.21,1.796,1.162c-1.142.953-1.796,2.352-1.796,3.839v12.792c0,2.417,1.727,4.486,4.106,4.919l6.284,1.143c.534.097,1.072.146,1.61.146s1.076-.048,1.61-.146l6.285-1.143c2.379-.433,4.105-2.502,4.105-4.919V5.001c0-1.487-.655-2.886-1.796-3.839Zm-11.204,20.766c-.084-.012-6.536-1.184-6.536-1.184-1.428-.26-2.464-1.501-2.464-2.952V5.001c0-.892.393-1.731,1.078-2.303.545-.455,1.223-.697,1.919-.697.179,0,.36.016.54.049l3.821.695c.952.173,1.643,1.001,1.643,1.968v17.216Zm11-4.135c0,1.451-1.036,2.692-2.463,2.952,0,0-6.452,1.171-6.537,1.184V4.712c0-.967.691-1.794,1.642-1.968l3.821-.695c.878-.161,1.773.076,2.459.648.685.572,1.078,1.411,1.078,2.303v12.792ZM8.984,6.224c-.088.483-.509.821-.983.821-.059,0-3.18-.562-3.18-.562-.543-.099-.904-.619-.805-1.163.099-.543.615-.901,1.163-.805l3,.545c.543.099.904.619.805,1.163Zm0,3.955c-.088.483-.509.821-.983.821-.059,0-3.18-.562-3.18-.562-.543-.099-.904-.619-.805-1.163.099-.543.615-.903,1.163-.805l3,.545c.543.099.904.619.805,1.163Zm0,4c-.088.483-.509.821-.983.821-.059,0-3.18-.562-3.18-.562-.543-.099-.904-.619-.805-1.163.099-.543.615-.902,1.163-.805l3,.545c.543.099.904.619.805,1.163Zm11-8.857c.099.543-.262,1.064-.805,1.163,0,0-3.121.562-3.18.562-.474,0-.895-.338-.983-.821-.099-.543.262-1.064.805-1.163l3-.545c.541-.097,1.064.262,1.163.805Zm0,3.955c.099.543-.262,1.064-.805,1.163,0,0-3.121.562-3.18.562-.474,0-.895-.338-.983-.821-.099-.543.262-1.064.805-1.163l3-.545c.541-.098,1.064.262,1.163.805Zm0,4c.099.543-.262,1.064-.805,1.163,0,0-3.121.562-3.18.562-.474,0-.895-.338-.983-.821-.099-.543.262-1.064.805-1.163l3-.545c.541-.097,1.064.262,1.163.805Zm-2,4.364c.099.543-.262,1.064-.805,1.163,0,0-1.121.198-1.18.198-.474,0-.895-.338-.983-.821-.099-.543.262-1.064.805-1.163l1-.182c.549-.098,1.064.262,1.163.805Zm-11,.221c-.088.483-.509.821-.983.821-.059,0-1.18-.198-1.18-.198-.543-.099-.904-.619-.805-1.163.099-.543.615-.906,1.163-.805l1,.182c.543.099.904.619.805,1.163Z"/>
                            </svg>
                        </button>
                    </li>
                    @can('customer.view')
                    <li>
                        <a href="{{ route('customer.index') }}" class="relative group tooltip-triggers rounded-full p-2.5 flex items-center justify-center hover:bg-blue-600">
                            <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,16a4,4,0,1,1,4-4A4,4,0,0,1,12,16Zm0-6a2,2,0,1,0,2,2A2,2,0,0,0,12,10Zm6,13A6,6,0,0,0,6,23a1,1,0,0,0,2,0,4,4,0,0,1,8,0,1,1,0,0,0,2,0ZM18,8a4,4,0,1,1,4-4A4,4,0,0,1,18,8Zm0-6a2,2,0,1,0,2,2A2,2,0,0,0,18,2Zm6,13a6.006,6.006,0,0,0-6-6,1,1,0,0,0,0,2,4,4,0,0,1,4,4,1,1,0,0,0,2,0ZM6,8a4,4,0,1,1,4-4A4,4,0,0,1,6,8ZM6,2A2,2,0,1,0,8,4,2,2,0,0,0,6,2ZM2,15a4,4,0,0,1,4-4A1,1,0,0,0,6,9a6.006,6.006,0,0,0-6,6,1,1,0,0,0,2,0Z"/></svg>
                            <!-- Tooltip -->
                            <div class="absolute top-0 transition-all duration-500 left-0 opacity-0 invisible group-hover:visible group-hover:left-12 group-hover:opacity-100 rounded py-1.5 px-3 bg-blue-900 shadow h-full flex items-center border">
                                <span class="text-sm leading-tight font-semibold text-white whitespace-nowrap ">Customer</span>
                            </div>
                        </a>
                    </li>
                    @endcan
                    @can('supplier.view')
                    <li>
                        <a href="{{ route('supplier.index') }}" class="relative group tooltip-triggers rounded-full p-2.5 flex items-center justify-center hover:bg-blue-600">
                            <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                <path d="m9,12c3.309,0,6-2.691,6-6S12.309,0,9,0,3,2.691,3,6s2.691,6,6,6Zm0-10c2.206,0,4,1.794,4,4s-1.794,4-4,4-4-1.794-4-4,1.794-4,4-4Zm12,12h-5c-1.654,0-3,1.346-3,3v4c0,1.654,1.346,3,3,3h5c1.654,0,3-1.346,3-3v-4c0-1.654-1.346-3-3-3Zm1,7c0,.552-.449,1-1,1h-5c-.551,0-1-.448-1-1v-4c0-.552.449-1,1-1h5c.551,0,1,.448,1,1v4Zm-2-3c0,.553-.448,1-1,1h-1c-.552,0-1-.447-1-1s.448-1,1-1h1c.552,0,1,.447,1,1Zm-9.351-1.072c.42.358.47.989.112,1.41l-.5.586c-.06.07-.129.132-.207.183-.79.527-1.859.386-2.487-.331l-2.331-2.767c-2.03,1.294-3.237,3.495-3.237,5.886v1.105c0,.553-.448,1-1,1s-1-.447-1-1v-1.105c0-3.075,1.551-5.906,4.148-7.571.846-.542,1.973-.371,2.618.397l2.211,2.625.261-.307c.358-.42.99-.472,1.41-.111Z"/>
                            </svg>
                            <!-- Tooltip -->
                            <div class="absolute top-0 transition-all duration-500 left-0 opacity-0 invisible group-hover:visible group-hover:left-12 group-hover:opacity-100 rounded py-1.5 px-3 bg-blue-900 shadow h-full flex items-center border">
                                <span class="text-sm leading-tight font-semibold text-white whitespace-nowrap ">Supplier</span>
                            </div>
                        </a>
                    </li>
                    @endcan
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
        <!-- Inventory -->
        <div class="absolute top-0 left-14 shadow-[10px_0px_15px_#00000010] bg-blue-900 h-full py-4 px-2 border-l opacity-0 -z-50 invisible transition-all duration-300 max-w-0 min-w-[200px] sub-menu-content" data-type="inventory">
            <div class="mb-4 p-2 border-b">
                <h6 class="text-lg font-semibold whitespace-nowrap text-white">Inventory</h6>
            </div>
            <ul>
                @can('inventory.summary.view')
                <li>
                    <a href="{{ route('inventory_summary.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'inventory_summary.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">Summary</span>
                    </a>
                </li>
                @endcan
                @can('inventory.category.view')
                <li>
                    <a href="{{ route('inventory_category.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'inventory_category.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">Category</span>
                    </a>
                </li>
                @endcan
                @can('inventory.product.view')
                <li>
                    <a href="{{ route('product.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'product.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">Product</span>
                    </a>
                </li>
                @endcan
                @can('inventory.raw_material.view')
                <li>
                    <a href="{{ route('raw_material.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'raw_material.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">Raw Material</span>
                    </a>
                </li>
                @endcan
                @can('grn.view')
                <li>
                    <a href="{{ route('grn.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'grn.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">GRN</span>
                    </a>
                </li>
                @endcan
            </ul>
        </div>
        <!-- Sale -->
        <div class="absolute top-0 left-14 shadow-[10px_0px_15px_#00000010] bg-blue-900 h-full py-4 px-2 border-l opacity-0 -z-50 invisible transition-all duration-300 max-w-0 min-w-[200px] sub-menu-content" data-type="sale">
            <div class="mb-4 p-2 border-b">
                <h6 class="text-lg font-semibold whitespace-nowrap text-white">Sale</h6>
            </div>
            <ul>
                @can('sale.quotation.view')
                <li>
                    <a href="{{ route('quotation.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'quotation.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">Quotation</span>
                    </a>
                </li>
                @endcan
                @can('sale.sale_order.view')
                <li>
                    <a href="{{ route('sale_order.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'sale_order.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">Sale Order</span>
                    </a>
                </li>
                @endcan
                @can('sale.delivery_order.view')
                <li>
                    <a href="{{ route('delivery_order.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'delivery_order.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">Delivery Order</span>
                    </a>
                </li>
                @endcan
                @can('sale.invoice.view')
                <li>
                    <a href="{{ route('invoice.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'invoice.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">Invoice</span>
                    </a>
                </li>
                @endcan
                @can('sale.target.view')
                <li>
                    <a href="{{ route('target.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'target.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">Target</span>
                    </a>
                </li>
                @endcan
                @can('sale.billing.view')
                <li>
                    <a href="{{ route('billing.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'billing.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">Billing</span>
                    </a>
                </li>
                @endcan
            </ul>
        </div>
        <!-- Task -->
        <div class="absolute top-0 left-14 shadow-[10px_0px_15px_#00000010] bg-blue-900 h-full py-4 px-2 border-l opacity-0 -z-50 invisible transition-all duration-300 max-w-0 min-w-[200px] sub-menu-content" data-type="task">
            <div class="mb-4 p-2 border-b">
                <h6 class="text-lg font-semibold whitespace-nowrap text-white">Task</h6>
            </div>
            <ul>
                <li>
                    <a href="{{ route('task.driver.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'task.driver.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">Driver</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('task.technician.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'task.technician.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">Technician</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('task.sale.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'task.sale.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">Sale</span>
                    </a>
                </li>
            </ul>
        </div>
        <!-- Setting -->
        <div class="absolute top-0 left-14 shadow-[10px_0px_15px_#00000010] bg-blue-900 h-full py-4 px-2 border-l opacity-0 -z-50 invisible transition-all duration-300 max-w-0 min-w-[200px] sub-menu-content" data-type="setting">
            <div class="mb-4 p-2 border-b">
                <h6 class="text-lg font-semibold whitespace-nowrap text-white">Setting</h6>
            </div>
            <ul>
                <li>
                    <a href="{{ route('material_use.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'material_use.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">Material Use</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('warranty_period.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'warranty_period.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">Warranty Period</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('promotion.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'promotion.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">Promotion</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('project_type.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'project_type.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">Project Type</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('currency.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'currency.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">Currency</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('credit_term.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'credit_term.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">Credit Term</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('area.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'area.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">Area</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('debtor_type.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'debtor_type.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">Debtor Type</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('user_management.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'user_management.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">User Management</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('role_management.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'role_management.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">Role Management</span>
                    </a>
                </li>
            </ul>
        </div>
        <!-- Report -->
        <div class="absolute top-0 left-14 shadow-[10px_0px_15px_#00000010] bg-blue-900 h-full py-4 px-2 border-l opacity-0 -z-50 invisible transition-all duration-300 max-w-0 min-w-[200px] sub-menu-content" data-type="report">
            <div class="mb-4 p-2 border-b">
                <h6 class="text-lg font-semibold whitespace-nowrap text-white">Report</h6>
            </div>
            <ul>
                <li>
                    <a href="{{ route('report.production_report.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'report.production_report.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">Production Report</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('report.sales_report.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'report.sales_report.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">Sales Report</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('report.stock_report.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'report.stock_report.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">Stock Report</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('report.earning_report.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'report.earning_report.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <span class="block text-sm flex-1 leading-tight whitespace-nowrap text-white">Earning Report</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</aside>

@push('scripts')
    <script>
        CURRENT_ROUTE_NAME = '{{ Route::currentRouteName() }}'
        IS_SIDEBAR_EXPAND = localStorage.getItem('is_sidebar_expand')

        $(document).ready(function(){
            getTimeSection()

            if (IS_SIDEBAR_EXPAND == 'true' || IS_SIDEBAR_EXPAND == null) {
                // if (CURRENT_ROUTE_NAME.includes('finance.')) {
                //     $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="1"]').click()
                // } else if (CURRENT_ROUTE_NAME.includes('master_data.')) {
                //     $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="2"]').click()
                // } else if (CURRENT_ROUTE_NAME.includes('invoice.')) {
                //     $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="3"]').click()
                // }
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
        })

        function getTimeSection() {
            var currentHour = moment().format("HH");
            var timeSection = 'Good Morning'

            if (currentHour >= 5 && currentHour < 12) {
                timeSection = 'Good Morning'
            } else if (currentHour >= 12 && currentHour < 18) {
                timeSection = 'Good Afternoon'
            } else {
                timeSection = 'Goodnight'
            }
            $('#expanded-sidebar #time-section').text(`${timeSection},`)
        }

        $('#expanded-sidebar .sidebar-menu-trigger').on('click', function() {
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
    </script>
@endpush
