@extends('layouts.app')
@section('title', 'Role Management')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('role_management.index') }}">{{ isset($role) ? __('Edit Role - ') . $role->name : __('Create Role') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <div class="p-6 rounded-md shadow bg-white">
        <form action="{{ isset($role) ? route('role_management.update', ['role' => $role]) : route('role_management.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="flex flex-col">
                <x-app.input.label id="name" class="mb-1">{{ __('Name') }}</x-app.input.label>
                <x-app.input.input name="name" id="name" :hasError="$errors->has('name')" value="{{ old('name', isset($role) ? $role->name : null) }}" />
            </div>

            @php
                $approval_examples = [
                    'approval.production_material_transfer_request' => [
                        'Production requests material transfer between warehouses',
                        'Production requests transfer of finished goods to warehouse',
                    ],
                    'approval.type_quotation' => [
                        'Salesperson submits a quotation for manager approval',
                        'Quotation with special discount requires approval before sending to customer',
                    ],
                    'approval.type_sale_order' => [
                        'Sale order generated from approved quotation needs final sign-off',
                        'Sale order with modified terms requires re-approval',
                    ],
                    'approval.type_delivery_order' => [
                        'Delivery order created from sale order needs logistics approval',
                        'Partial delivery requires approval before dispatch',
                    ],
                    'approval.type_customer' => [
                        'New debtor registration submitted for approval',
                        'Customer credit term change request (e.g. 30 days to 60 days)',
                        'Customer deletion request needs approval before removal',
                    ],
                    'approval.type_payment_record' => [
                        'Payment record edit request (e.g. correcting payment amount)',
                        'Payment record deletion request needs approval',
                    ],
                    'approval.type_raw_material_request' => [
                        'Raw material request cancellation needs approval before voiding',
                    ],
                ];
            @endphp
            <div class="border-t border-gray-300 mt-6 pt-4">
                <div class="mb-5">
                    <h3 class="text-lg font-bold">{{ __('Permissions') }}</h3>
                    <p class="text-sm text-slate-500">{{ __('By selecting create, edit, and delete permission will automatically select view permission.') }}</p>
                </div>
                @foreach($permissions_group as $group => $children)
                    <div class="mb-4">
                        @php
                            $group_label = join(' ', explode('.', $group));
                            $group_label = join(' ', explode('_', $group_label));
                        @endphp
                        <h4 class="mb-2 capitalize text-sm font-semibold">{{ __($group_label)}}</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($children as $permission)
                                @php
                                    $cat = $group;
                                    $labels = explode('.', $permission->name);
                                    $action_label = $labels[count($labels) - 1];
                                    $action_label = join(' ', explode('_', $action_label));
                                @endphp
                                <label for="{{ $permission->name }}" data-group="{{ $cat }}" class="permission-selector cursor-pointer border py-2 px-3 rounded flex flex-col w-full max-w-[200px] transition-colors {{ isset($role_permissions) && in_array($permission->name, $role_permissions) ? 'bg-blue-50 border-blue-300' : 'bg-white border-gray-200' }}">
                                    <span class="text-sm text-slate-700 font-semibold mb-1 leading-tight capitalize">{{ __($action_label) }}</span>
                                    @if(!empty($permission_descriptions[$permission->name]))
                                        <span class="text-xs text-slate-400 leading-tight mb-1">{{ __($permission_descriptions[$permission->name]) }}</span>
                                    @endif
                                    @if(isset($approval_examples[$permission->name]))
                                        <button type="button" class="approval-examples-toggle flex items-center gap-1 text-xs text-blue-500 hover:text-blue-700 mt-1 self-start" onclick="event.stopPropagation(); event.preventDefault(); toggleExamples(this);">
                                            <svg class="w-3 h-3 transition-transform duration-200 chevron-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            {{ __('Examples') }}
                                        </button>
                                        <div class="approval-examples-list hidden mt-1">
                                            <ul class="list-disc list-inside text-xs text-slate-500 leading-relaxed space-y-0.5">
                                                @foreach($approval_examples[$permission->name] as $example)
                                                    <li>{{ __($example) }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <input type="checkbox" id="{{ $permission->name }}" name="{{ $permission->name }}" value="{{ $permission->name }}" class="sr-only" {{ isset($role_permissions) && in_array($permission->name, $role_permissions) ? 'checked' : '' }}>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-6 flex justify-end">
                <x-app.button.submit>{{ __('Save and Update') }}</x-app.button.submit>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        // Apply background color to already-checked cards on page load
        $('.permission-selector').each(function() {
            const checkbox = $(this).find('input[type="checkbox"]');
            if (checkbox.is(':checked')) {
                $(this).removeClass('bg-white border-gray-200').addClass('bg-blue-50 border-blue-300');
            }
        });

        // Toggle background color on checkbox change
        $('.permission-selector input[type="checkbox"]').on('change', function() {
            const label = $(this).closest('.permission-selector');
            if ($(this).is(':checked')) {
                label.removeClass('bg-white border-gray-200').addClass('bg-blue-50 border-blue-300');
            } else {
                label.removeClass('bg-blue-50 border-blue-300').addClass('bg-white border-gray-200');
            }
        });

        // Toggle approval examples expand/collapse
        window.toggleExamples = function(btn) {
            const list = $(btn).siblings('.approval-examples-list');
            const chevron = $(btn).find('.chevron-icon');
            list.toggleClass('hidden');
            chevron.toggleClass('rotate-90');
        };

        $('.permission-selector').on('click', function(e) {
            let permissionName = $(this).attr('for')
            let group = $(this).data('group')

            // Automatically select view permission if create/edit permission is selected
            if (permissionName.includes('.create') || permissionName.includes('.edit') || permissionName.includes('.delete')) {
                const viewCheckbox = $(`input[name="${ group }.view"]`);
                viewCheckbox.prop('checked', true);
                viewCheckbox.closest('.permission-selector').removeClass('bg-white border-gray-200').addClass('bg-blue-50 border-blue-300');
            } else if (permissionName.includes('.view') && !permissionName.includes('.view_record') && (
                $(`input[name="${ group }.create"]`).is(':checked') ||
                $(`input[name="${ group }.edit"]`).is(':checked') ||
                $(`input[name="${ group }.delete"]`).is(':checked')
            )) {
                e.preventDefault()
            }
        })
    </script>
@endpush
