@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title>{{ isset($user) ? 'Edit User' : 'Create User' }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ isset($user) ? route('user_management.update', ['user' => $user]) : route('user_management.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="grid grid-cols-3 gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">Picture</x-app.input.label>
                    <x-app.input.file id="picture[]" :hasError="$errors->has('picture')"/>
                    <div class="uploaded-file-preview-container" data-id="picture">
                        <div class="p-y.5 px-1.5 rounded bg-blue-50 mt-2 hidden" id="uploaded-file-template">
                            <a href="" target="_blank" class="text-blue-700 text-xs"></a>
                        </div>
                        @if (isset($user))
                            @foreach ($user->pictures as $att)
                                <div class="p-y.5 px-1.5 rounded bg-blue-50 mt-2 old-preview">
                                    <a href="{{ $att->url }}" target="_blank" class="text-blue-700 text-xs">{{ $att->src }}</a>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <x-input-error :messages="$errors->first('picture.*')" class="mt-1" />
                </div>
                @if (isset($user))
                    <div class="flex flex-col">
                        <x-app.input.label id="sku" class="mb-1">Staff ID <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.input name="sku" id="sku" value="{{ isset($user) ? $user->sku : null }}" disabled="true" />
                    </div>
                @endif
                <div class="flex flex-col">
                    <x-app.input.label id="department" class="mb-1">Department <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="department" id="department" :hasError="$errors->has('status')">
                        <option value="">Select a department</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('department', isset($user) ? $user_role_id : null) === $role->id)>{{ $role->name }}</option>
                        @endforeach
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('department')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="name" class="mb-1">Name <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="name" id="name" :hasError="$errors->has('name')" value="{{ old('name', isset($user) ? $user->name : null) }}" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="gender" class="mb-1">Gender <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="gender" id="gender" :hasError="$errors->has('gender')">
                        <option value="">Select a Male/Female</option>
                        <option value="male" @selected(old('gender', isset($user) ? $user->gender : null) == 'male')>Male</option>
                        <option value="female" @selected(old('gender', isset($user) ? $user->gender : null) === 'female')>Female</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('gender')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="address" class="mb-1">Address <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="address" id="address" :hasError="$errors->has('address')" value="{{ old('address', isset($user) ? $user->address : null) }}" />
                    <x-input-error :messages="$errors->get('address')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="city" class="mb-1">City</x-app.input.label>
                    <x-app.input.input name="city" id="city" :hasError="$errors->has('city')" value="{{ old('city', isset($user) ? $user->city : null) }}" />
                    <x-input-error :messages="$errors->get('city')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="state" class="mb-1">State</x-app.input.label>
                    <x-app.input.input name="state" id="state" :hasError="$errors->has('state')" value="{{ old('state', isset($user) ? $user->state : null) }}" />
                    <x-input-error :messages="$errors->get('state')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="zip_code" class="mb-1">Zip Code</x-app.input.label>
                    <x-app.input.input name="zip_code" id="zip_code" :hasError="$errors->has('zip_code')" value="{{ old('zip_code', isset($user) ? $user->zip_code : null) }}" class="int-input" />
                    <x-input-error :messages="$errors->get('zip_code')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="password" class="mb-1">Password @if(!isset($user))<span class="text-sm text-red-500">*</span> @endif</x-app.input.label>
                    <x-app.input.input name="password" id="password" type="password" :hasError="$errors->has('password')" />
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="password_confirmation" class="mb-1">Password Confirmation @if(!isset($user))<span class="text-sm text-red-500">*</span> @endif</x-app.input.label>
                    <x-app.input.input name="password_confirmation" type="password" id="password_confirmation" :hasError="$errors->has('password_confirmation')" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="phone_number" class="mb-1">Phone Number <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="phone_number" id="phone_number" :hasError="$errors->has('phone_number')" value="{{ old('phone_number', isset($user) ? $user->phone_number : null) }}"/>
                    <x-input-error :messages="$errors->get('phone_number')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="email" class="mb-1">Email @if(!isset($user))<span class="text-sm text-red-500">*</span> @endif</x-app.input.label>
                    <x-app.input.input name="email" id="email" type="email" :hasError="$errors->has('email')" value="{{ old('email', isset($user) ? $user->email : null) }}" disabled="{{ isset($user) ? 'disabled' : '' }}"/>
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="website" class="mb-1">Website</x-app.input.label>
                    <x-app.input.input name="website" id="website" :hasError="$errors->has('website')" value="{{ old('website', isset($user) ? $user->website : null) }}"/>
                    <x-input-error :messages="$errors->get('website')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="epf" class="mb-1">EPF No</x-app.input.label>
                    <x-app.input.input name="epf" id="epf" :hasError="$errors->has('epf')" value="{{ old('epf', isset($user) ? $user->epf : null) }}"/>
                    <x-input-error :messages="$errors->get('epf')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="status" class="mb-1">Status <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                        <option value="">Select a Active/Inactive</option>
                        <option value="1" @selected(old('status', isset($user) ? $user->is_active : null) == 1)>Active</option>
                        <option value="0" @selected(old('status', isset($user) ? $user->is_active : null) === 0)>Inactive</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                </div>
                <div class="flex flex-col col-span-2">
                    <x-app.input.label id="remark" class="mb-1">Remark</x-app.input.label>
                    <x-app.input.input name="remark" id="remark" :hasError="$errors->has('remark')" value="{{ old('remark', isset($user) ? $user->remark : null) }}" />
                    <x-input-error :messages="$errors->get('remark')" class="mt-1" />
                </div>
            </div>
            <div class="mt-8 flex justify-end">
                <x-app.button.submit>{{ isset($user) ? 'Update User' : 'Create New User' }}</x-app.button.submit>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        $('input[name="picture[]"]').on('change', function() {
            let files = $(this).prop('files');

            $('.uploaded-file-preview-container[data-id="picture"]').find('.old-preview').remove()
        
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                
                let clone = $('#uploaded-file-template')[0].cloneNode(true);
                $(clone).find('a').text(file.name)
                $(clone).find('a').attr('href', URL.createObjectURL(file))
                $(clone).addClass('old-preview')
                $(clone).removeClass('hidden')
                $(clone).removeAttr('id')

                $('.uploaded-file-preview-container[data-id="picture"]').append(clone)
                $('.uploaded-file-preview-container[data-id="picture"]').removeClass('hidden')
            }
        })
    </script>
@endpush