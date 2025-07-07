<?php

namespace App\Mail;

use App\Modules\Sales\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $customMessage;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice, $customMessage = null)
    {
        $this->invoice = $invoice;
        $this->customMessage = $customMessage;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'فاتورة رقم ' . $this->invoice->invoice_number . ' - MaxCon ERP',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.invoice',
            with: [
                'invoice' => $this->invoice,
                'customMessage' => $this->customMessage,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            // Optionally attach PDF version of invoice
            // Attachment::fromPath($this->invoice->getPdfPath())
            //     ->as('invoice-' . $this->invoice->invoice_number . '.pdf')
            //     ->withMime('application/pdf'),
        ];
    }
}
