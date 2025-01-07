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
class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_LOCAL = 1;

    const TYPE_OVERSEA = 2;

    protected $guarded = [];

    protected $casts = [
        'under_warranty' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }

    public function pictures()
    {
        return $this->morphMany(Attachment::class, 'object')->orderBy('id', 'desc');
    }

    public function msicCode()
    {
        return $this->belongsTo(MsicCode::class, 'msic_id');
    }

    public function branch()
    {
        return $this->morphOne(Branch::class, 'object');
    }

    public function creditTerms()
    {
        return $this->morphMany(ObjectCreditTerm::class, 'object')->orderBy('id', 'desc');
    }

    public function generateSku(string $company_first_alphabet): string
    {
        $sku = null;
        $staring_num = 1;

        while (true) {
            $digits = (string) $staring_num;

            while (strlen($digits) < 3) { // Make 3 digits
                $digits = '0'.$digits;
            }
            $sku = strtoupper('400-'.$company_first_alphabet.$digits);

            $exists = self::withoutGlobalScope(BranchScope::class)->where(DB::raw('BINARY `sku`'), $sku)->exists();

            if (! $exists) {
                break;
            }
            $staring_num++;
        }

        return $sku;
    }
}
