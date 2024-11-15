<?php

namespace App\Mail;

use App\Models\Customer;
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

    public function __construct(Customer $customer, Invoice $invoice)
    {
        $this->customer = $customer;
        $this->invoice = $invoice;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'E Invoice Email',
        );
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
        $filePath = storage_path('app/public/e-invoices/pdf/' . 'XML-INV12345.pdf');
        return $this->view('invoice.email')
                    ->subject('您的发票')
                    ->attach($filePath, [
                        'as' => '发票-' . $this->invoice->filename,
                        'mime' => 'application/pdf',
                    ]);
    }
}
