<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EInvoice extends Model
{
    use HasFactory;
    protected $fillable = ['invoice_id', 'uuid', 'status'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function creditNotes()
    {
        return $this->belongsToMany(CreditNote::class, 'credit_note_e_invoice', 'einvoice_id', 'credit_note_id');
    }

    public function debitNotes()
    {
        return $this->belongsToMany(DebitNote::class, 'debit_note_e_invoice', 'einvoice_id', 'debit_note_id');
    }
}
