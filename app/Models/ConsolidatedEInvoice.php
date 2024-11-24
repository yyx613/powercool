<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ConsolidatedEInvoice extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class, 'consolidated_e_invoice_invoice');
    }

    public function creditNotes()
    {
        return $this->belongsToMany(CreditNote::class, 'credit_note_con_e_invoice', 'einvoice_id', 'credit_note_id');
    }

    public function debitNotes()
    {
        return $this->belongsToMany(DebitNote::class, 'debit_note_con_e_invoice', 'einvoice_id', 'debit_note_id');
    }

    public function generateSku(): string {
        $sku = null;
        
        while (true) {
            $sku = 'CEI' . now()->format('ym') . generateRandomAlphabet();

            $exists = self::withoutGlobalScope(BranchScope::class)->where(DB::raw('BINARY `sku`'), $sku)->exists();

            if (!$exists) {
                break;
            }
        }

        return $sku;
    }
}
