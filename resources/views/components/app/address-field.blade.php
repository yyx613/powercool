@props([
    'title' => '',
])

<div>
    <div class="mb-4">
        <span class="text-md font-semibold">New {{ $title }}</span>
        <p class="text-sm text-slate-500 leading-none">{{ __('Enter these field to create a new ' . $title . '.') }}</p>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-8 w-full">
        <div class="flex flex-col">
            <x-app.input.label id="address1" class="mb-1">{{ __('Address 1') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.input name="address1" id="address1" :hasError="$errors->has('address1')" />
            <x-app.message.error id="address1_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="address2" class="mb-1">{{ __('Address 2') }} </x-app.input.label>
            <x-app.input.input name="address2" id="address2" :hasError="$errors->has('address2')" />
            <x-app.message.error id="address2_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="address3" class="mb-1">{{ __('Address 3') }} </x-app.input.label>
            <x-app.input.input name="address3" id="address3" :hasError="$errors->has('address3')" />
            <x-app.message.error id="address3_err" />
        </div>
        <div class="flex flex-col"> <x-app.input.label id="address4" class="mb-1">{{ __('Address 4') }}
            </x-app.input.label>
            <x-app.input.input name="address4" id="addres4" :hasError="$errors->has('address4')" />
            <x-app.message.error id="address4_err" />
        </div>
    </div>
</div>
