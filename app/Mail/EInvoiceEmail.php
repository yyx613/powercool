<?php

namespace App\Mail;

use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\DebitNote;
use App\Models\EInvoice;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EInvoiceEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $customer;
    public $invoice;
    public $path;
    public $company;

    public function __construct($customer, $invoice, $path,  $company)
    {
        $this->customer = $customer;
        $this->invoice = $invoice;
        $this->path = $path;
        $this->company = $company;
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'invoice.email',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        if ($this->invoice instanceof EInvoice) {
            $subject = 'E-Invoice From '.$this->company;
            $type = 'E-Invoice';
        } elseif ($this->invoice instanceof CreditNote) {
            $subject = 'Credit Note From '.$this->company;
            $type = 'Credit Note';
        } elseif ($this->invoice instanceof DebitNote) {
            $subject = 'Debit Note From '.$this->company;
            $type = 'Debit Note';
        }
        
        $filePath = $this->path;
        return $this->view('invoice.email')
                    ->subject($subject)
                    ->with([
                        'type' => $type,
                        'uuid' => $this->invoice->uuid,
                        'date' => $this->invoice->created_at,
                        'company' => $this->company,
                        'customer' => $this->customer
                    ])
                    ->attach($filePath, [
                        'mime' => 'application/pdf',
                    ]);
    }
}
