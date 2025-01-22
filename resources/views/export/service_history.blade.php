<table>
    <tr>
        <td>Serial No</td>
        <td>Task ID</td>
        <td>Technician</td>
    </tr>
    @foreach ($pcs as $pc)
        <tr>
            <td>{{ $pc->sku }}</td>
            <td>{{ $pc->taskMilestoneInventory->taskMilestone->task->sku }}</td>
            <td>{{ $pc->stockOutTo->sku ?? null }}</td>
        </tr>
    @endforeach
</table>
