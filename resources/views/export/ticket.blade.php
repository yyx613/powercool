<table>
    <tr>
        <td>Code</td>
        <td>Subject</td>
        <td>Body</td>
        <td>Customer</td>
        <td>Status</td>
        <td>Sale Order / Invoice</td>
        <td>Product</td>
        <td>Product Child</td>
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
