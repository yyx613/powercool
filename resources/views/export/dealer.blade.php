<table>
    <tr>
        <td>Code</td>
        <td>Name</td>
    </tr>
    @foreach ($dealers as $d)
        <tr>
            <td>{{ $d->sku }}</td>
            <td>{{ $d->name }}</td>
        </tr>
    @endforeach
</table>
