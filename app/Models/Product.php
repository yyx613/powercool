<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[ScopedBy([BranchScope::class])]
class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const TYPE_PRODUCT = 1;
    public const TYPE_RAW_MATERIAL = 2;

    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }

    public function image()
    {
        return $this->morphOne(Attachment::class, 'object')->orderBy('id', 'desc');
    }

    public function category()
    {
        return $this->belongsTo(InventoryCategory::class, 'inventory_category_id');
    }

    public function children()
    {
        return $this->hasMany(ProductChild::class);
    }

    public function branch()
    {
        return $this->morphOne(Branch::class, 'object');
    }

    public function childrenWithoutAssigned($production_id)
    {
        // Not in production
        $pm_ids = ProductionMilestone::where('production_id', $production_id)->pluck('id');
        $pmm_ids = ProductionMilestoneMaterial::whereNotIn('production_milestone_id', $pm_ids)->pluck('product_child_id')->toArray();
        // Exclude converted sale
        $sale_ids = Sale::where('status', Sale::STATUS_CONVERTED)->pluck('id');
        $converted_sp_ids = SaleProduct::whereIn('sale_id', $sale_ids)->pluck('id');

        $assigned_pc_ids = SaleProductChild::distinct()
                ->whereNotIn('sale_product_id', $converted_sp_ids)
                ->pluck('product_children_id')
                ->toArray();

        return $this->children()->whereNull('status')->whereNotIn('id', $assigned_pc_ids)->whereNotIn('id', $pmm_ids)->get();
    }

    public function materialUse()
    {
        return $this->hasOne(MaterialUse::class);
    }

    public function getQtyAttribute($val)
    {
        if ($this->type == self::TYPE_PRODUCT || ($this->type == self::TYPE_RAW_MATERIAL && (bool)$this->is_sparepart == true)) {
            return ProductChild::where('product_id', $this->id)->count();
        }
        return $val;
    }

    public function warehouseAvailableStock($product_id)
    {
        return $this->warehouseStock($product_id) - $this->warehouseReservedStock($product_id) - $this->warehouseOnHoldStock($product_id);
    }

    public function warehouseStock($product_id)
    {
        return ProductChild::whereNull('status')->where('location', ProductChild::LOCATION_WAREHOUSE)->where('product_id', $product_id)->count();
    }

    public function warehouseReservedStock($product_id)
    {
        $ids = ProductChild::whereNull('status')->where('location', ProductChild::LOCATION_WAREHOUSE)->where('product_id', $product_id)->pluck('id');
        // Check in QUO/SO/DO
        $spc = SaleProductChild::whereIn('product_children_id', $ids)->distinct('product_children_id')->get();

        $count = 0;
        for ($i = 0; $i < count($spc); $i++) {
            $sale = $spc[$i]->saleProduct->sale;

            if ($sale->status != Sale::STATUS_CONVERTED || ($sale->type == Sale::TYPE_SO && $sale->convert_to != null)) {
                $count++;
            }
        }

        // Check in Production
        $count += ProductionMilestoneMaterial::where('on_hold', false)->whereIn('product_child_id', $ids)->count();

        return $count;
    }

    public function warehouseOnHoldStock($product_id)
    {
        $ids = ProductChild::whereNull('status')->where('location', ProductChild::LOCATION_WAREHOUSE)->where('product_id', $product_id)->pluck('id');

        // Check in Production
        return ProductionMilestoneMaterial::where('on_hold', true)->whereIn('product_child_id', $ids)->count();
    }

    public function productionStock($product_id)
    {
        return ProductChild::whereNull('status')->where('location', ProductChild::LOCATION_FACTORY)->where('product_id', $product_id)->count();
    }

    public function productionReservedStock($product_id)
    {
        $ids = ProductChild::whereNull('status')->where('location', ProductChild::LOCATION_FACTORY)->where('product_id', $product_id)->pluck('id');

        // Check in QUO/SO/DO
        $spc = SaleProductChild::whereIn('product_children_id', $ids)->distinct('product_children_id')->get();

        $count = 0;
        for ($i = 0; $i < count($spc); $i++) {
            $sale = $spc[$i]->saleProduct->sale;

            if ($sale->status != Sale::STATUS_CONVERTED || ($sale->type == Sale::TYPE_SO && $sale->convert_to != null)) {
                $count++;
            }
        }

        // Check in Production
        $count += ProductionMilestoneMaterial::whereIn('product_child_id', $ids)->count();

        return $count;
    }

    public function generateSku(): string
    {
        $sku = null;

        while (true) {
            $sku = 'P' . now()->format('ym') . generateRandomAlphabet();

            $exists = self::withoutGlobalScope(BranchScope::class)->where(DB::raw('BINARY `sku`'), $sku)->exists();

            if (!$exists) {
                break;
            }
        }

        return $sku;
    }

    public function isLowStock(): bool
    {
        if ($this->low_stock_threshold != null && $this->qty <= $this->low_stock_threshold) {
            return true;
        }
        return false;
    }
}
