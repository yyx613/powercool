<table>
    <tr>
        <td>{{ __('Serial No') }}</td>
        <td>{{ __('Task ID') }}</td>
        <td>{{ __('Technician') }}</td>
    </tr>
    @foreach ($pcs as $pc)
        <tr>
            <td>{{ $pc->sku }}</td>
            <td>{{ $pc->taskMilestoneInventory->taskMilestone->task->sku }}</td>
            <td>{{ $pc->stockOutTo->sku ?? null }}</td>
        </tr>
    @endforeach
</table>
