@if(session()->has('success'))
    <div class="mb-4">
        <x-app.alert.success>
            <strong>{{ __('Success !') }}</strong> {{ session('success') }}
        </x-app.alert.success>
    </div>
@endif

@if(session()->has('warning'))
    <div class="mb-4">
        <x-app.alert.warning>
            <strong>{{ __('Warning !') }}</strong> {{ session('warning') }}
        </x-app.alert.warning>
    </div>
@endif

@if(session()->has('error'))
    <div class="mb-4">
        <x-app.alert.error>
            <strong>{{ __('Error !') }}</strong> {{ session('error') }}
        </x-app.alert.error>
    </div>
@endif