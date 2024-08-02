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
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="3">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M23,22H3a1,1,0,0,1-1-1V1A1,1,0,0,0,0,1V21a3,3,0,0,0,3,3H23a1,1,0,0,0,0-2Z"/><path d="M15,20a1,1,0,0,0,1-1V12a1,1,0,0,0-2,0v7A1,1,0,0,0,15,20Z"/><path d="M7,20a1,1,0,0,0,1-1V12a1,1,0,0,0-2,0v7A1,1,0,0,0,7,20Z"/><path d="M19,20a1,1,0,0,0,1-1V7a1,1,0,0,0-2,0V19A1,1,0,0,0,19,20Z"/><path d="M11,20a1,1,0,0,0,1-1V7a1,1,0,0,0-2,0V19A1,1,0,0,0,11,20Z"/></svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">Sale</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="3">
                        <div class="overflow-hidden">
                            <ul>
                                <li>
                                    <a href="{{ route('quotation.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'quotation.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Quotation</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('sale_order.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'sale_order.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Sale Order</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('task.sale.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'task.sale.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">Invoice</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>
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
                <li>
                    <a href="{{ route('ticket.index') }}" class="p-2 flex items-center rounded-md {{ str_contains(Route::currentRouteName(), 'ticket.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M16,0h-.13a2.02,2.02,0,0,0-1.941,1.532,2,2,0,0,1-3.858,0A2.02,2.02,0,0,0,8.13,0H8A5.006,5.006,0,0,0,3,5V21a3,3,0,0,0,3,3H8.13a2.02,2.02,0,0,0,1.941-1.532,2,2,0,0,1,3.858,0A2.02,2.02,0,0,0,15.87,24H18a3,3,0,0,0,3-3V5A5.006,5.006,0,0,0,16,0Zm2,22-2.143-.063A4,4,0,0,0,8.13,22H6a1,1,0,0,1-1-1V17H7a1,1,0,0,0,0-2H5V5A3,3,0,0,1,8,2l.143.063A4.01,4.01,0,0,0,12,5a4.071,4.071,0,0,0,3.893-3H16a3,3,0,0,1,3,3V15H17a1,1,0,0,0,0,2h2v4A1,1,0,0,1,18,22Z"/><path d="M13,15H11a1,1,0,0,0,0,2h2a1,1,0,0,0,0-2Z"/></svg>
                        <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">Ticket</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('customer.index') }}" class="p-2 flex items-center rounded-md {{ str_contains(Route::currentRouteName(), 'customer.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                        <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,16a4,4,0,1,1,4-4A4,4,0,0,1,12,16Zm0-6a2,2,0,1,0,2,2A2,2,0,0,0,12,10Zm6,13A6,6,0,0,0,6,23a1,1,0,0,0,2,0,4,4,0,0,1,8,0,1,1,0,0,0,2,0ZM18,8a4,4,0,1,1,4-4A4,4,0,0,1,18,8Zm0-6a2,2,0,1,0,2,2A2,2,0,0,0,18,2Zm6,13a6.006,6.006,0,0,0-6-6,1,1,0,0,0,0,2,4,4,0,0,1,4,4,1,1,0,0,0,2,0ZM6,8a4,4,0,1,1,4-4A4,4,0,0,1,6,8ZM6,2A2,2,0,1,0,8,4,2,2,0,0,0,6,2ZM2,15a4,4,0,0,1,4-4A1,1,0,0,0,6,9a6.006,6.006,0,0,0-6,6,1,1,0,0,0,2,0Z"/></svg>
                        <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">Customer</span>
                    </a>
                </li>
                <li>
                    <div class="transition-all duration-500 delay-75 cursor-pointer flex items-center justify-between sidebar-menu-trigger" data-accordionstriggerid="2">
                        <button class="p-2 flex items-center rounded-md w-full">
                            <svg class="h-5 w-5 flex-none fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M15,6c0-3.309-2.691-6-6-6S3,2.691,3,6s2.691,6,6,6,6-2.691,6-6Zm-6,4c-2.206,0-4-1.794-4-4s1.794-4,4-4,4,1.794,4,4-1.794,4-4,4Zm-.008,4.938c.068,.548-.32,1.047-.869,1.116-3.491,.436-6.124,3.421-6.124,6.946,0,.552-.448,1-1,1s-1-.448-1-1c0-4.531,3.386-8.37,7.876-8.93,.542-.069,1.047,.32,1.116,.869Zm13.704,4.195l-.974-.562c.166-.497,.278-1.019,.278-1.572s-.111-1.075-.278-1.572l.974-.562c.478-.276,.642-.888,.366-1.366-.277-.479-.887-.644-1.366-.366l-.973,.562c-.705-.794-1.644-1.375-2.723-1.594v-1.101c0-.552-.448-1-1-1s-1,.448-1,1v1.101c-1.079,.22-2.018,.801-2.723,1.594l-.973-.562c-.48-.277-1.09-.113-1.366,.366-.276,.479-.112,1.09,.366,1.366l.974,.562c-.166,.497-.278,1.019-.278,1.572s.111,1.075,.278,1.572l-.974,.562c-.478,.276-.642,.888-.366,1.366,.186,.321,.521,.5,.867,.5,.169,0,.341-.043,.499-.134l.973-.562c.705,.794,1.644,1.375,2.723,1.594v1.101c0,.552,.448,1,1,1s1-.448,1-1v-1.101c1.079-.22,2.018-.801,2.723-1.594l.973,.562c.158,.091,.33,.134,.499,.134,.346,0,.682-.179,.867-.5,.276-.479,.112-1.09-.366-1.366Zm-5.696,.866c-1.654,0-3-1.346-3-3s1.346-3,3-3,3,1.346,3,3-1.346,3-3,3Z"/></svg>
                            <span class="block text-base ml-4 flex-1 whitespace-nowrap text-left leading-tight text-white">Management</span>
                        </button>
                    </div>
                    <div class="grid grid-rows-[0fr] opacity-0 transition-all duration-500 sidebar-accordions" data-accordionid="2">
                        <div class="overflow-hidden">
                            <ul>
                                <li>
                                    <a href="{{ route('user_management.index') }}" class="rounded-md p-2 flex items-center {{ str_contains(Route::currentRouteName(), 'user_management.') ? 'bg-blue-600' : 'hover:bg-blue-600' }}">
                                        <span class="block text-sm ml-9 flex-1 leading-tight whitespace-nowrap text-white">User</span>
                                    </a>
                                </li>
                                
                            </ul>
                        </div>
                    </div>
                </li>
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
                <button type="button" class="rounded-full p-2.5 hover:bg-gray-100" id="expand-sidebar-btn">
                    <svg class="h-5 w-5 fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><rect y="11" width="24" height="2" rx="1"/><rect y="4" width="24" height="2" rx="1"/><rect y="18" width="24" height="2" rx="1"/></svg>
                </button>
            </div>
            <div class="flex-1 my-4">
                <ul>
                    <li class="expand-sub-menu-triggers" data-type="finance">
                        <button class="p-2.5 flex items-center justify-center rounded-full hover:bg-gray-100">
                            <svg class="h-5 w-5 fill-white" id="Layer_1" height="512" viewBox="0 0 24 24" width="512" xmlns="http://www.w3.org/2000/svg" data-name="Layer 1"><path d="m4 6a2.982 2.982 0 0 1 -2.122-.879l-1.544-1.374a1 1 0 0 1 1.332-1.494l1.585 1.414a1 1 0 0 0 1.456.04l3.604-3.431a1 1 0 0 1 1.378 1.448l-3.589 3.414a2.964 2.964 0 0 1 -2.1.862zm20-2a1 1 0 0 0 -1-1h-10a1 1 0 0 0 0 2h10a1 1 0 0 0 1-1zm-17.9 9.138 3.589-3.414a1 1 0 1 0 -1.378-1.448l-3.6 3.431a1.023 1.023 0 0 1 -1.414 0l-1.59-1.585a1 1 0 0 0 -1.414 1.414l1.585 1.585a3 3 0 0 0 4.226.017zm17.9-1.138a1 1 0 0 0 -1-1h-10a1 1 0 0 0 0 2h10a1 1 0 0 0 1-1zm-17.9 9.138 3.585-3.414a1 1 0 1 0 -1.378-1.448l-3.6 3.431a1 1 0 0 1 -1.456-.04l-1.585-1.414a1 1 0 0 0 -1.332 1.494l1.544 1.374a3 3 0 0 0 4.226.017zm17.9-1.138a1 1 0 0 0 -1-1h-10a1 1 0 0 0 0 2h10a1 1 0 0 0 1-1z"/></svg>
                        </button>
                    </li>
                    @can('user_management')
                    <li>
                        <a href="{{ route('user_management.index') }}" class="relative group tooltip-triggers rounded-full p-2.5 flex items-center justify-center {{ str_contains(Route::currentRouteName(), 'user_management.') ? 'bg-blue-600' : 'hover:bg-gray-100' }}">
                            <svg class="h-5 w-5 {{ str_contains(Route::currentRouteName(), 'user_management.') ? 'fill-red-700' : '' }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M15,6c0-3.309-2.691-6-6-6S3,2.691,3,6s2.691,6,6,6,6-2.691,6-6Zm-6,4c-2.206,0-4-1.794-4-4s1.794-4,4-4,4,1.794,4,4-1.794,4-4,4Zm13,7c0-.552-.09-1.082-.256-1.579l1.82-1.049-.998-1.733-1.823,1.05c-.706-.797-1.662-1.368-2.743-1.589v-2.101h-2v2.101c-1.082,.221-2.037,.792-2.743,1.589l-1.823-1.05-.998,1.733,1.82,1.049c-.166,.497-.256,1.027-.256,1.579s.09,1.082,.256,1.579l-1.82,1.049,.998,1.733,1.823-1.05c.706,.797,1.662,1.368,2.743,1.589v2.101h2v-2.101c1.082-.221,2.037-.792,2.743-1.589l1.823,1.05,.998-1.733-1.82-1.049c.166-.497,.256-1.027,.256-1.579Zm-5,3c-1.654,0-3-1.346-3-3s1.346-3,3-3,3,1.346,3,3-1.346,3-3,3ZM5,14h3v2h-3c-1.654,0-3,1.346-3,3v5H0v-5c0-2.757,2.243-5,5-5Z"/></svg>
                            <!-- Tooltip -->
                            <div class="absolute top-0 transition-all duration-500 left-0 opacity-0 invisible group-hover:visible group-hover:left-12 group-hover:opacity-100 rounded py-1.5 px-3 bg-white shadow h-full flex items-center border">
                                <span class="text-sm leading-tight font-semibold text-black whitespace-nowrap">User Management</span>
                            </div>
                        </a>
                    </li>
                    @endcan
                    @can('role_management')
                    <li>
                        <a href="{{ route('role_management.index') }}" class="relative group tooltip-triggers rounded-full p-2.5 flex items-center justify-center {{ str_contains(Route::currentRouteName(), 'role_management.') ? 'bg-blue-600' : 'hover:bg-gray-100' }}">
                            <svg class="h-5 w-5 {{ str_contains(Route::currentRouteName(), 'role_management.') ? 'fill-red-700' : '' }}" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                <path d="m23.169,16.111l-3.698-3.712c-.657-.658-1.599-.983-2.518-.856l-3.073.405c-1.1.146-1.962,1.055-2.05,2.16l-.238,2.979c-.069.875.246,1.733.865,2.355l3.708,3.723c.535.532,1.244.825,1.998.825.004,0,.008,0,.012,0,.759-.003,1.47-.302,2.003-.842l3.005-3.044c1.092-1.105,1.084-2.898-.014-3.993Zm-1.409,2.589l-3.005,3.043c-.157.159-.366.247-.588.248h-.003c-.222,0-.43-.087-.584-.24l-3.705-3.72c-.207-.207-.312-.493-.288-.785l.238-2.979c.014-.172.148-.313.319-.336,0,0,3.158-.414,3.201-.414.264,0,.522.106.71.295l3.7,3.714c.324.322.326.85.005,1.175Zm-3.76-2.2c0,.828-.672,1.5-1.5,1.5s-1.5-.672-1.5-1.5.672-1.5,1.5-1.5,1.5.672,1.5,1.5Zm-10-4.5c3.309,0,6-2.691,6-6S11.309,0,8,0,2,2.691,2,6s2.691,6,6,6Zm0-10c2.206,0,4,1.794,4,4s-1.794,4-4,4-4-1.794-4-4,1.794-4,4-4Zm2,13c0,.553-.448,1-1,1-3.86,0-7,3.141-7,7,0,.553-.448,1-1,1s-1-.447-1-1c0-4.963,4.038-9,9-9,.552,0,1,.447,1,1Z"/>
                            </svg>
                            <!-- Tooltip -->
                            <div class="absolute top-0 transition-all duration-500 left-0 opacity-0 invisible group-hover:visible group-hover:left-12 group-hover:opacity-100 rounded py-1.5 px-3 bg-white shadow h-full flex items-center border">
                                <span class="text-sm leading-tight font-semibold text-black whitespace-nowrap">Role Management</span>
                            </div>
                        </a>
                    </li>
                    @endcan
                </ul>
            </div>
            <div class="flex items-center justify-between">
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="p-2.5">
                        <svg class="h-5 w-5 fill-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M22.763,10.232l-4.95-4.95L16.4,6.7,20.7,11H6.617v2H20.7l-4.3,4.3,1.414,1.414,4.95-4.95a2.5,2.5,0,0,0,0-3.536Z"/><path d="M10.476,21a1,1,0,0,1-1,1H3a1,1,0,0,1-1-1V3A1,1,0,0,1,3,2H9.476a1,1,0,0,1,1,1V8.333h2V3a3,3,0,0,0-3-3H3A3,3,0,0,0,0,3V21a3,3,0,0,0,3,3H9.476a3,3,0,0,0,3-3V15.667h-2Z"/></svg>
                    </button>
                </form>
            </div>
        </div>
        <!-- Finance -->
        <div class="absolute top-0 left-14 shadow-[10px_0px_15px_#00000010] bg-white h-full py-4 px-2 border-x opacity-0 -z-50 invisible transition-all duration-300 max-w-0 sub-menu-content" data-type="finance">
            <div class="mb-4 p-2 border-b">
                <h6 class="text-lg font-semibold whitespace-nowrap">Finance</h6>
            </div>
            <ul>
                <li>
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
                if (CURRENT_ROUTE_NAME.includes('finance.')) {
                    $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="1"]').click()
                } else if (CURRENT_ROUTE_NAME.includes('master_data.')) {
                    $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="2"]').click()
                } else if (CURRENT_ROUTE_NAME.includes('invoice.')) {
                    $('#expanded-sidebar .sidebar-menu-trigger[data-accordionstriggerid="3"]').click()
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