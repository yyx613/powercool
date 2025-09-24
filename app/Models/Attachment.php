<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attachment extends Model
{
    use HasFactory, SoftDeletes;

    const TASK_PATH = 'public/attachments/task';
    const CUSTOMER_PATH = 'public/attachments/customer';
    const SUPPLIER_PATH = 'public/attachments/supplier';
    const USER_PATH = 'public/attachments/user';
    const TICKET_PATH = 'public/attachments/ticket';
    const TASK_MILESTONE_PATH = 'public/attachments/task_milestone';
    const TASK_MILESTONE_INVENTORY_PATH = 'public/attachments/task_milestone_inventory';
    const PRODUCT_PATH = 'public/attachments/product';

    protected $guarded = [];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];
    protected $appends = ['url'];

    public function getUrlAttribute() {
        $path = '/public/storage';
        if (config('app.env') == 'local') {
            $path = '/storage';
        }

        switch ($this->object_type) {
            case Task::class:
                return config('app.url') . str_replace('public', $path, self::TASK_PATH) . '/' . $this->src;
            case Customer::class:
                return config('app.url') . str_replace('public', $path, self::CUSTOMER_PATH) . '/' . $this->src;
            case Supplier::class:
                return config('app.url') . str_replace('public', $path, self::SUPPLIER_PATH) . '/' . $this->src;
            case User::class:
                return config('app.url') . str_replace('public', $path, self::USER_PATH) . '/' . $this->src;
            case Ticket::class:
                return config('app.url') . str_replace('public', $path, self::TICKET_PATH) . '/' . $this->src;
            case TaskMilestone::class:
                return config('app.url') . str_replace('public', $path, self::TASK_MILESTONE_PATH) . '/' . $this->src;
            case TaskMilestoneInventory::class:
                return config('app.url') . str_replace('public', $path, self::TASK_MILESTONE_INVENTORY_PATH) . '/' . $this->src;
            case Product::class:
                return config('app.url') . str_replace('public', $path, self::PRODUCT_PATH) . '/' . $this->src;
        }
    }
}
