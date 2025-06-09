<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductChild extends Model
{
    use HasFactory, SoftDeletes;

    const LOCATION_WAREHOUSE = 'warehouse';
    const LOCATION_FACTORY = 'factory';

    const STATUS_STOCK_OUT = 1;
    const STATUS_IN_TRANSIT = 2;
    const STATUS_TO_BE_RECEIVED = 3;
    const STATUS_RECEIVED = 4;
    const STATUS_PENDING_APPROVAL = 5;
    const STATUS_TRANSFER_APPROVED = 6;
    const STATUS_BROKEN = 7;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }

    public function parent()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function saleProductChild()
    {
        return $this->hasMany(SaleProductChild::class, 'product_children_id');
    }

    public function productionMilestoneChildren()
    {
        return $this->hasOne(ProductionMilestoneMaterial::class, 'product_child_id');
    }

    public function taskMilestoneInventory()
    {
        return $this->morphOne(TaskMilestoneInventory::class, 'inventory');
    }

    public function stockOutBy()
    {
        return $this->belongsTo(User::class, 'stock_out_by')->withoutGlobalScope(BranchScope::class);
    }

    public function transferredBy()
    {
        return $this->belongsTo(User::class, 'transfer_by');
    }

    public function serviceHistories()
    {
        return $this->morphMany(InventoryServiceReminder::class, 'object')->orderBy('id', 'desc');
    }

    public function stockOutTo(): MorphTo
    {
        return $this->morphTo('stock_out_to');
    }

    public function assignedTo()
    {
        // In QUO/SO/DO
        $sp_child = $this->saleProductChild()->orderBy('id', 'desc')->first();

        if ($sp_child != null) {
            $sale = $sp_child->saleProduct->sale;

            if ($sale->type == Sale::TYPE_SO && $sale->convert_to != null) {
                $dopc = DeliveryOrderProductChild::where('product_children_id', $this->id)->first();
                if ($dopc != null) {
                    return $dopc->doProduct->do;
                }

                $spc = SaleProductChild::where('product_children_id', $this->id)->first();
                if ($spc != null) {
                    return $spc->saleProduct->sale;
                }

                if (str_contains($sale->convert_to, ',')) {
                    $dos = DeliveryOrder::whereIn('id', explode(',', $sale->convert_to))->get();

                    return $dos;
                }

                return DeliveryOrder::where('id', $sale->convert_to)->first();
            } elseif ($sale->status != Sale::STATUS_CONVERTED) {
                return $sale;
            }
        }

        // In Production
        $pm_child = $this->productionMilestoneChildren()->orderBy('id', 'desc')->first();
        if ($pm_child != null) {
            return $pm_child->productionMilestone->production;
        }

        // In Task
        $task_child = $this->taskMilestoneInventory()->orderBy('id', 'desc')->first();
        if ($task_child != null) {
            return $task_child->taskMilestone->task;
        }

        return null;
    }

    public function generateSku($parent_prefix): string
    {
        $init_idx = 1;
        $sku = null;
        $min_char = 6;
        $existing_skus = self::pluck('sku')->toArray();

        while (true) {
            $str_init_idx = (string) $init_idx;
            while (strlen($str_init_idx) < $min_char) { // make it $min_char digits
                $str_init_idx = '0'.$str_init_idx;
            }
            $sku = $parent_prefix . '-' . now()->format('ymd') . '-' . $str_init_idx;

            if (! in_array($sku, $existing_skus)) {
                break;
            }

            if (strlen(str_replace('9', '', $str_init_idx)) == 0) { // When idx is only have character '9'
                $min_char++;
            }

            $init_idx++;
        }

        return $sku;
    }
}
