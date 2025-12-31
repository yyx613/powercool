<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

#[ScopedBy([BranchScope::class])]
class Target extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'amount_to_collect' => 'double',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date;
    }

    public function salesperson() {
        return $this->belongsTo(User::class, 'sale_id')
            ->withoutGlobalScope(BranchScope::class);
    }
    
    public function branch() {
        return $this->morphOne(Branch::class, 'object');
    }

    public static function getReachedAmount($target_id) {
        $target = Target::find($target_id);
        if (!$target) {
            return 0;
        }

        $salesperson_sale_agents_ids = $target->salesperson->salesAgents->pluck('id')->toArray();

        $total_amount = 0;

        $invoices = Invoice::select('invoices.*')
            ->whereNull('invoices.deleted_at')
            ->whereIn('sales.sale_id', $salesperson_sale_agents_ids)
            ->whereNot('delivery_orders.status', DeliveryOrder::STATUS_APPROVAL_PENDING)
            ->whereBetween('invoices.created_at', [\Carbon\Carbon::parse($target->date)->startOfMonth(), \Carbon\Carbon::parse($target->date)->endOfMonth()])  
            ->leftJoin('delivery_orders', 'invoices.id', '=', 'delivery_orders.invoice_id')
            ->leftJoin('sales', DB::raw('FIND_IN_SET(delivery_orders.id, sales.convert_to)'), '>', DB::raw("'0'"))
            ->leftJoin('customers', 'customers.id', '=', 'sales.customer_id')
            ->groupBy('invoices.id')
            ->get();

        foreach ($invoices as $invoice) {
            $total_amount += Invoice::getTotal($invoice->id);
        }

        return $total_amount;
    }
}
