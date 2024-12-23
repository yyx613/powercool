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
class Dealer extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }

    public function branch()
    {
        return $this->morphOne(Branch::class, 'object');
    }

    public function generateSku(): string
    {
        $sku = null;
        $staring_num = 1;

        while (true) {
            $digits = (string) $staring_num;

            while (strlen($digits) < 3) { // Make 3 digits
                $digits = '0'.$digits;
            }
            $sku = strtoupper('500-'.$digits);

            $exists = self::withoutGlobalScope(BranchScope::class)->where(DB::raw('BINARY `sku`'), $sku)->exists();

            if (! $exists) {
                break;
            }
            $staring_num++;
        }

        return $sku;
    }
}
