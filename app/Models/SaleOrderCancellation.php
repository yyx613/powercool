<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class SaleOrderCancellation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sale_order_cancellation';
    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date;
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function salePerson() {
        return $this->belongsTo(User::class, 'sale_person_id');
    }

    /**
     * Type 1 - cancel
     * Type 2 - convert
     * Type 3 - cancel from converted
     */
    public static function calCancellation(Sale $sale, int $type) {
        $sps = SaleProduct::where('sale_id', $sale->id)->get();
        $bulk = [];
        $now = now();

        for ($i=0; $i < count($sps); $i++) {
            if ($type == 1) {
                $bulk[] = [
                    'sale_id' => $sale->id,
                    'saleperson_id' => $sale->sale_id,
                    'product_id' => $sps[$i]->product_id,
                    'qty' => $sps[$i]->qty,
                ];
            } else if ($type == 2) {
                $remaining_sps_qty_to_on_hold = $sps[$i]->qty;

                $sochs = self::where([
                    ['on_hold_sale_id', null],
                    ['saleperson_id', $sale->sale_id],
                    ['product_id', $sps[$i]->product_id],
                ])->get();

                for ($j=0; $j < count($sochs); $j++) { 
                    $count_to_sell = $sochs[$j]->toSellCount();

                    if ($count_to_sell <= 0) {
                        continue;
                    }

                    $bulk[] = [
                        'sale_id' => $sale->id,
                        'on_hold_sale_id' => $sochs[$j]->sale_id,
                        'saleperson_id' => $sale->sale_id,
                        'product_id' => $sps[$i]->product_id,
                        'qty' => $remaining_sps_qty_to_on_hold,
                        'extra' => $count_to_sell - $remaining_sps_qty_to_on_hold < 0 ? abs($count_to_sell - $remaining_sps_qty_to_on_hold) : null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $remaining_sps_qty_to_on_hold = $remaining_sps_qty_to_on_hold - $count_to_sell;

                    if ($remaining_sps_qty_to_on_hold <= 0) {
                        break;
                    } 
                }
            } else if ($type == 3) {
                self::where([
                    ['saleperson_id', $sale->sale_id],
                    ['product_id', $sps[$i]->product_id],
                    ['sale_id', $sale->id]
                ])
                ->delete();
            }
        }

        if (count($bulk) > 0) {
            if ($type == 1) {
                self::upsert($bulk, ['product_id', 'saleperson_id'], ['sale_id', 'qty']);
            } else if ($type == 2) {
                self::insert($bulk);
            }
        }
    }

    private function toSellCount(): int {
        $count = $this->qty - $this->onHoldCount();

        return $count <= 0 ? 0 : $count;
    }
    
    private function onHoldCount(): int {
        return self::where('on_hold_sale_id', $this->sale_id)->sum('qty');
    }
}
