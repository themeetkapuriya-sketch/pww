<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $customSubject;
    public $messageBody;
    public $pdfContent;
    public $client;
    public $plant;
    public $groupedItems;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice, string $customSubject, string $messageBody, string $pdfContent, $client = null, $plant = null, $groupedItems = null)
    {
        $this->invoice = $invoice;
        $this->customSubject = $customSubject;
        $this->messageBody = $messageBody;
        $this->pdfContent = $pdfContent;
        $this->client = $client;
        $this->plant = $plant;
        $this->groupedItems = $groupedItems ?: collect();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->customSubject ?: "Invoice #{$this->invoice->invoice_number} from Praful Welding Works",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice_email',
            with: [
                'invoice' => $this->invoice,
                'messageBody' => $this->messageBody,
                'client' => $this->client,
                'plant' => $this->plant,
                'groupedItems' => $this->groupedItems,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, "Invoice-{$this->invoice->invoice_number}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
