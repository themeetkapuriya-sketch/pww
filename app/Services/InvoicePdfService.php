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
            ->setNodeModulePath(base_path('node_modules'))
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
            // 1. Auto-discover Node.js executable on Windows
            $nodeCandidates = array_filter([
                env('NODE_BINARY'),
                'C:\\Program Files\\nodejs\\node.exe',
                'C:\\Program Files (x86)\\nodejs\\node.exe',
                getenv('LOCALAPPDATA') ? getenv('LOCALAPPDATA').'\\Programs\\nodejs\\node.exe' : null,
                getenv('APPDATA') ? getenv('APPDATA').'\\npm\\node.exe' : null,
            ]);
            foreach ($nodeCandidates as $nodePath) {
                if (file_exists($nodePath)) {
                    $browsershot->setNodeBinary($nodePath);
                    break;
                }
            }

            // 2. Auto-discover NPM executable on Windows
            $npmCandidates = array_filter([
                env('NPM_BINARY'),
                'C:\\Program Files\\nodejs\\npm.cmd',
                'C:\\Program Files (x86)\\nodejs\\npm.cmd',
                getenv('LOCALAPPDATA') ? getenv('LOCALAPPDATA').'\\Programs\\nodejs\\npm.cmd' : null,
                getenv('APPDATA') ? getenv('APPDATA').'\\npm\\npm.cmd' : null,
            ]);
            foreach ($npmCandidates as $npmPath) {
                if (file_exists($npmPath)) {
                    $browsershot->setNpmBinary($npmPath);
                    break;
                }
            }

            // 3. Auto-discover native Windows Chrome/Edge browser for 100% offline reliability
            $chromeCandidates = array_filter([
                env('CHROME_PATH'),
                'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
                'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
                getenv('LOCALAPPDATA') ? getenv('LOCALAPPDATA').'\\Google\\Chrome\\Application\\chrome.exe' : null,
                'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
                'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
            ]);
            foreach ($chromeCandidates as $chromePath) {
                if (file_exists($chromePath)) {
                    $browsershot->setChromePath($chromePath);
                    break;
                }
            }
        }

        return $browsershot->pdf();
    }
}
