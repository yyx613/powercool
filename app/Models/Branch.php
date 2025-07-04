<?php

namespace App\Models;

use DateTimeInterface;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    const LOCATION_EVERY = 0;
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
        $loc = null;
        if ($location != null) {
            $loc = $location;
        } else if (isSuperAdmin() || getCurrentUserBranch() == null) {
            $loc = Session::get('as_branch');
        } else {
            $loc = Auth::user()->branch->location;
        }
        
        if ((int)$loc === Branch::LOCATION_EVERY) {
            throw new Exception("Branch is not allow");
        }

        self::create([
            'object_type' => $type,
            'object_id' => $id,
            'location' => $loc,
        ]);
    }

    public function keyToLabel(int $key) {
        switch ($key) {
            case self::LOCATION_EVERY:
                return 'Every';
            case self::LOCATION_KL:
                return 'Kuala Lumpur';
            case self::LOCATION_PENANG:
                return 'Penang';
        }
    }
}
