<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreditNote extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function einvoices()
    {
        return $this->belongsToMany(EInvoice::class, 'credit_note_e_invoice', 'credit_note_id', 'einvoice_id');
    }

    public function consolidatedEInvoice()
    {
        return $this->belongsToMany(ConsolidatedEInvoice::class, 'credit_note_con_e_invoice', 'credit_note_id', 'con_einvoice_id');
    }

    public function generateSku(): string {
        $sku = null;
        
        while (true) {
            $sku = 'CN' . now()->format('ym') . generateRandomAlphabet();

            $exists = self::withoutGlobalScope(BranchScope::class)->where(DB::raw('BINARY `sku`'), $sku)->exists();

            if (!$exists) {
                break;
            }
        }

        return $sku;
    }
}
