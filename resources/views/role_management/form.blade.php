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
                                <label for="{{ $permission->name }}" data-group="{{ $cat }}" class="permission-selector cursor-pointer border border-gray-200 py-2 px-3 rounded flex flex-col w-full max-w-[150px]">
                                    <span class="text-sm text-slate-500 mb-2 leading-tight capitalize">{{ __($action_label) }}</span>
                                    <div class="relative inline-flex items-center">
                                        <input type="checkbox" id="{{ $permission->name }}" name="{{ $permission->name }}" value="{{ $permission->name }}" class="sr-only peer" {{ isset($role_permissions) && in_array($permission->name, $role_permissions) ? 'checked' : '' }}>
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                                    </div>
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
        $('.permission-selector').on('click', function(e) {
            let permissionName = $(this).attr('for')
            let group = $(this).data('group')

            // Automatically select view permission if create/edit permission is selected
            if (permissionName.includes('.create') || permissionName.includes('.edit') || permissionName.includes('.delete')) {
                $(`input[name="${ group }.view"]`).prop('checked', true)
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
