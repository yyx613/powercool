<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date;
    }

    public function attachments() {
        return $this->morphMany(Attachment::class, 'object');
    }


    public function generateSku(): string {
        $sku = null;
        
        while (true) {
            $sku = 'T' . now()->format('ym') . generateRandomAlphabet();

            $exists = self::where(DB::raw('BINARY `sku`'), $sku)->exists();

            if (!$exists) {
                break;
            }
        }

        return $sku;
    }
}
