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
            <x-app.input.label id="address" class="mb-1">{{ __('Address') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.input name="address" id="address" :hasError="$errors->has('address')" />
            <x-app.message.error id="address_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="city" class="mb-1">{{ __('City') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.input name="city" id="city" :hasError="$errors->has('city')" />
            <x-app.message.error id="city_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="state" class="mb-1">{{ __('State') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.input name="state" id="state" :hasError="$errors->has('state')" />
            <x-app.message.error id="state_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="zip_code" class="mb-1">{{ __('Zip Code') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.input name="zip_code" id="zip_code" :hasError="$errors->has('zip_code')" class="int-input" />
            <x-app.message.error id="zip_code_err" />
        </div>
    </div>
</div>
