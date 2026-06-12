<table>
    <tr>
        <td>{{ __('Code') }}</td>
        <td>{{ __('Subject') }}</td>
        <td>{{ __('Body') }}</td>
        <td>{{ __('Customer') }}</td>
        <td>{{ __('Status') }}</td>
        <td>{{ __('Sale Order / Invoice') }}</td>
        <td>{{ __('Product') }}</td>
        <td>{{ __('Product Child') }}</td>
    </tr>
    @foreach ($tickets as $t)
        <tr>
            <td>{{ $t->sku }}</td>
            <td>{{ $t->subject }}</td>
            <td>{{ $t->body }}</td>
            <td>{{ $t->customer->sku ?? null }}</td>
            <td>{{ $t->is_active ? 'Active' : 'Inactive' }}</td>
            <td>{{ $t->new_so_inv }}</td>
            <td>{{ $t->product }}</td>
            <td>{{ $t->product_children }}</td>
        </tr>
    @endforeach
</table>
