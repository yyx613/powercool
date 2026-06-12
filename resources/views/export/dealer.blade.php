<table>
    <tr>
        <td>{{ __('Code') }}</td>
        <td>{{ __('Name') }}</td>
        <td>{{ __('Company Name') }}</td>
        <td>{{ __('Company Group') }}</td>
    </tr>
    @foreach ($dealers as $d)
        <tr>
            <td>{{ $d->sku }}</td>
            <td>{{ $d->name }}</td>
            <td>{{ $d->company_name }}</td>
            <td>{{ $d->company_group == 1 ? 'Power Cool' : ($d->company_group == 2 ? 'Hi-Ten' : null) }}</td>
        </tr>
    @endforeach
</table>
