<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class ProductChild extends Model
{
    use HasFactory, SoftDeletes;

    const LOCATION_WAREHOUSE = 'warehouse';
    const LOCATION_FACTORY = 'factory';

    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date;
    }

    public function saleProductChild() {
        return $this->hasMany(SaleProductChild::class, 'product_children_id');
    }

    public function assignedTo() {
        $sp_child = $this->saleProductChild()->orderBy('id', 'desc')->first();

        if ($sp_child != null) {
            return $sp_child->saleProduct->sale;
        }
        return null;
    }

    public function generateSku($parent_prefix, array $existing_skus = []): string {
        $init_idx = 1;
        $sku = null;
        $min_char = 4;

        while (true) {
            $str_init_idx = (string)$init_idx; 
            while (strlen($str_init_idx) < $min_char) { // make it $min_char digits
                $str_init_idx = '0' . $str_init_idx;
            }
            $sku = $parent_prefix . $str_init_idx;

            if (!in_array($sku, $existing_skus)) {
                break;
            }
            $init_idx++;

            if (strlen(str_replace('9', '', $str_init_idx)) == 0) { // When idx is only have character '9'
                $min_char++;
            }
        }

        return $sku;
    }
}
