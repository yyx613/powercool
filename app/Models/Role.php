<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $guarded = [];

    const SUPERADMIN = 1;
    const SALE = 2;
    const TECHNICIAN = 3;
    const DRIVER = 4;
    const PRODUCTION_WORKER = 5;
    const PRODUCTION_SUPERVISOR = 6;
    const PRODUCTION_ASSISTANT = 7;
    const WAREHOUSE = 8;
    const FINANCE = 9;
    const SALE_COORDINATOR = 13;
}
