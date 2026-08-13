<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Spatie\Browsershot\Browsershot;
use Throwable;

class InvoicePdfService
{
    /**
     * Generate binary PDF content for an Invoice instance using Spatie Browsershot.
     *
     * @throws Throwable
     */
    public function generateInvoicePdf(Invoice $invoice): string
    {
        try {
            $client = $invoice->client;
            $plant = $invoice->plant;
            $groupedItems = $invoice->items;
            $isPdf = true;

            $html = View::make('pages.invoice_print', compact(
                'invoice',
                'client',
                'plant',
                'groupedItems',
                'isPdf'
            ))->render();

            return $this->renderHtmlToPdf($html);
        } catch (Throwable $e) {
            Log::error('Invoice PDF Generation Error', [
                'invoice_id' => $invoice->id ?? null,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Render an arbitrary Blade view to binary PDF using Browsershot.
     *
     * @throws Throwable
     */
    public function renderViewToPdf(string $viewName, array $data = []): string
    {
        try {
            $data['isPdf'] = true;
            $html = View::make($viewName, $data)->render();

            return $this->renderHtmlToPdf($html);
        } catch (Throwable $e) {
            Log::error('View PDF Generation Error', [
                'view' => $viewName,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Convert raw HTML string into PDF binary using Headless Chrome (Browsershot).
     */
    public function renderHtmlToPdf(string $html): string
    {
        $browsershot = Browsershot::html($html)
            ->paperSize(210, 297, 'mm')
            ->margins(5, 6, 5, 6, 'mm')
            ->showBackground()
            ->emulateMedia('print')
            ->noSandbox()
            ->dismissDialogs()
            ->waitUntil('domcontentloaded')
            ->setOption('protocolTimeout', 60000)
            ->setOption('args', [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-gpu',
                '--disable-dev-shm-usage',
                '--no-first-run',
                '--no-zygote',
                '--single-process',
                '--disable-extensions',
                '--disable-features=IsolateOrigins,site-per-process',
            ]);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $nodeBinary = 'C:\\Program Files\\nodejs\\node.exe';
            $npmBinary = 'C:\\Program Files\\nodejs\\npm.cmd';

            if (file_exists($nodeBinary)) {
                $browsershot->setNodeBinary($nodeBinary);
            }
            if (file_exists($npmBinary)) {
                $browsershot->setNpmBinary($npmBinary);
            }
        }

        return $browsershot->pdf();
    }
}
