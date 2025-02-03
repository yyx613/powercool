@inject('production', 'App\Models\Production')

<table>
    <tr>
        <td>Code</td>
        <td>Name</td>
        <td>Description</td>
        <td>State Date</td>
        <td>Due Date</td>
        <td>Product</td>
        <td>Status</td>
        <td>Assigned Order ID</td>
        <td>Priority</td>
        <td>Assigned Staff</td>
        <td>Milestones</td>
        <td>Remark</td>
    </tr>
    @foreach ($productions as $p)
        @php
            $staff = $p->users->pluck('name')->toArray();
            $ms = $p->milestones->pluck('name')->toArray();
        @endphp
        <tr>
            <td>{{ $p->sku }}</td>
            <td>{{ $p->name }}</td>
            <td>{{ $p->desc }}</td>
            <td>{{ $p->start_date }}</td>
            <td>{{ $p->due_date }}</td>
            <td>{{ $p->product->sku ?? null }}</td>
            <td>{{ $p->status == $production::STATUS_TO_DO ? 'To Do' : ($p->status == $production::STATUS_DOING ? 'Doing' : ($p->status == $production::STATUS_COMPLETED ? 'Completed' : ($p->status == $production::STATUS_TRANSFERRED ? 'Transferred' : '')))  }}</td>
            <td>{{ $p->sale->sku ?? null }}</td>
            <td>{{ $p->priority->sku ?? null }}</td>
            <td>{{ join(', ', $staff) }}</td>
            <td>{{ join(', ', $ms) }}</td>
            <td>{{ $p->remark }}</td>
        </tr>
    @endforeach
</table>
