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
        'is_active' => 'integer',
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

    public function costs() {
        return $this->hasMany(ProductCost::class);
    }

    public function uomUnit() {
        return $this->belongsTo(UOM::class, 'uom');
    }

    public function branch() {
        return $this->morphOne(Branch::class, 'object');
    }

    public function serviceHistories() {
        return $this->morphMany(InventoryServiceHistory::class, 'object')->orderBy('id', 'desc');
    }

    public function childrenWithoutAssigned($production_id) {
        $involved_pc_ids = getInvolvedProductChild($production_id);

        return $this->children()->whereNull('status')->whereNotIn('id', $involved_pc_ids)->get();
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

    public function isRawMaterial(): bool {
        return $this->is_sparepart !== null && $this->is_sparepart == false;
    }

    public function warehouseAvailableStock() {
        return $this->warehouseStock() - $this->warehouseReservedStock() - $this->warehouseOnHoldStock();
    }

    public function warehouseStock() {
        if ($this->isRawMaterial()) {
            return $this->qty;
        }

        return ProductChild::whereNull('status')
            ->where('location', ProductChild::LOCATION_WAREHOUSE)
            ->where('product_id', $this->id)
            ->count();
    }

    public function warehouseReservedStock() {
        if ($this->isRawMaterial()) {
            // Check in Production
            $count = ProductionMilestoneMaterial::where('product_id', $this->id)->where('on_hold', false)->sum('qty');
            // Check in Task
            $count += TaskMilestoneInventory::where('inventory_type', Product::class)->where('inventory_id', $this->id)->value('qty');

            return $count;
        }

        $ids = ProductChild::whereNull('status')
            ->where('location', ProductChild::LOCATION_WAREHOUSE)
            ->where('product_id', $this->id)
            ->pluck('id');

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

        // Check in Task
        $count += TaskMilestoneInventory::where('inventory_type', ProductChild::class)
            ->whereIn('inventory_id', $ids)
            ->count();

        return $count;
    }

    public function warehouseOnHoldStock() {
        if ($this->isRawMaterial()) {
            return ProductionMilestoneMaterial::where('product_id', $this->id)->where('on_hold', true)->sum('qty');
        }

        $ids = ProductChild::whereNull('status')->where('location', ProductChild::LOCATION_WAREHOUSE)->where('product_id', $this->id)->pluck('id');

        // Check in Production
        return ProductionMilestoneMaterial::where('on_hold', true)->whereIn('product_child_id', $ids)->count();
    }

    public function productionStock() {
        if ($this->isRawMaterial()) {
            return 0;
        }

        return ProductChild::whereNull('status')->where('location', ProductChild::LOCATION_FACTORY)->where('product_id', $this->id)->count();
    }

    public function productionReservedStock() {
        if ($this->isRawMaterial()) {
            return 0;
        }

        $ids = ProductChild::whereNull('status')->where('location', ProductChild::LOCATION_FACTORY)->where('product_id', $this->id)->pluck('id');

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

    public function isLowStock(): bool {
        if ($this->low_stock_threshold != null && $this->qty <= $this->low_stock_threshold) {
            return true;
        }
        return false;
    }
}
