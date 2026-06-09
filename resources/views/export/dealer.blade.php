<table>
    <tr>
        <td>Code</td>
        <td>Name</td>
        <td>Company Name</td>
        <td>Company Group</td>
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
