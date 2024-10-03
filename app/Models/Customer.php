<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([BranchScope::class])]
class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'under_warranty' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date;
    }

    public function pictures() {
        return $this->morphMany(Attachment::class, 'object')->orderBy('id', 'desc');
    }

    public function locations() {
        return $this->hasMany(CustomerLocation::class);
    }

    public function branch() {
        return $this->morphOne(Branch::class, 'object');
    }

    public function creditTerms() {
        return $this->morphMany(ObjectCreditTerm::class, 'object')->orderBy('id', 'desc');
    }
}
