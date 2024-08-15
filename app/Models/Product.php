<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_PRODUCT = 1;
    const TYPE_RAW_MATERIAL = 2;

    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date;
    }

    public function image() {
        return $this->morphOne(Attachment::class, 'object')->orderBy('id', 'desc');
    }

    public function category() {
        return $this->belongsTo(InventoryCategory::class, 'inventory_category_id');
    }

    public function children() {
        return $this->hasMany(ProductChild::class);
    }

    public function totalStockCount($product_id) {
        return ProductChild::where('product_id', $product_id)->count();
    }

    public function reservedStockCount($product_id) {
        $ids = ProductChild::where('product_id', $product_id)->pluck('id');
        
        $spc = SaleProductChild::whereIn('product_children_id', $ids)->distinct('product_children_id')->get();

        $count = 0;
        for ($i=0; $i < count($spc); $i++) { 
            $sale = $spc[$i]->saleProduct->sale;

            if ($sale->status != Sale::STATUS_CONVERTED || ($sale->type == Sale::TYPE_SO && $sale->convert_to != null)) {
                $count++;
            }
        }
        return $count;
    }

    public function generateSku(): string {
        $sku = null;
        
        while (true) {
            $sku = 'P' . now()->format('ym') . generateRandomAlphabet();

            $exists = self::where(DB::raw('BINARY `sku`'), $sku)->exists();

            if (!$exists) {
                break;
            }
        }

        return $sku;
    }
}
