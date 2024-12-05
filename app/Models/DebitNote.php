<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DebitNote extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function einvoices()
    {
        return $this->belongsToMany(EInvoice::class, 'debit_note_e_invoice', 'debit_note_id', 'einvoice_id');
    }

    public function consolidatedEInvoices()
    {
        return $this->belongsToMany(ConsolidatedEInvoice::class, 'debit_note_con_e_invoice', 'debit_note_id', 'con_einvoice_id');
    }

    public function generateSku(): string {
        $sku = null;
        
        while (true) {
            $sku = 'DN' . now()->format('ym') . generateRandomAlphabet();

            $exists = self::withoutGlobalScope(BranchScope::class)->where(DB::raw('BINARY `sku`'), $sku)->exists();

            if (!$exists) {
                break;
            }
        }

        return $sku;
    }
}
