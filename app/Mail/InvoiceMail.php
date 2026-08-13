<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Services\InvoicePdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable implements ShouldQueue
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
    public function __construct(Invoice $invoice, string $customSubject, string $messageBody, ?string $pdfContent = null, $client = null, $plant = null, $groupedItems = null)
    {
        $this->invoice = $invoice;
        $this->customSubject = $customSubject;
        $this->messageBody = $messageBody;
        $this->pdfContent = $pdfContent ? base64_encode($pdfContent) : null;
        $this->client = $client;
        $this->plant = $plant;
        $this->groupedItems = $groupedItems ?: null;
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
        $this->invoice->loadMissing(['plant.client', 'items.product', 'items.rawMaterial']);

        $this->client = $this->client ?? $this->invoice->client ?? $this->invoice->plant?->client;
        $this->plant = $this->plant ?? $this->invoice->plant;

        if (empty($this->groupedItems) || (is_countable($this->groupedItems) && count($this->groupedItems) === 0)) {
            $this->groupedItems = $this->invoice->items ?? collect();
        }

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
        $this->invoice->loadMissing(['plant.client', 'items.product', 'items.rawMaterial']);

        $pdfData = $this->pdfContent
            ? base64_decode($this->pdfContent)
            : app(InvoicePdfService::class)->generateInvoicePdf($this->invoice);

        return [
            Attachment::fromData(fn () => $pdfData, "Invoice-{$this->invoice->invoice_number}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
