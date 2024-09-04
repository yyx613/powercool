<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    const LOCATION_KL = 1;
    const LOCATION_PENANG = 2;

    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date;
    }

    public function assign($type, $id, $location=null) {
        self::create([
            'object_type' => $type,
            'object_id' => $id,
            'location' => $location != null ? $location : (isSuperAdmin() ? Session::get('as_branch') : Auth::user()->branch->location),
        ]);
    }
}
