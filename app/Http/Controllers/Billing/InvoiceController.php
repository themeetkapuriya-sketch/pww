<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Client;
use App\Models\ClientPlant;
use App\Models\SalesOrder;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;

class InvoiceController extends Controller
{
    /**
     * 6. Invoices & Billing.
     */
    public function invoices(Request $request)
    {
        $invoices = Invoice::with(['plant.client', 'items.product'])->orderBy('created_at', 'desc')->paginate(20);
        $finishedGoods = Product::all();
        $clients = Client::with('plants')->get();

        $prefillOrder = null;
        if ($request->has('order_id')) {
            $prefillOrder = SalesOrder::with(['items.product', 'client', 'plant'])->find($request->input('order_id'));
        }

        return view('pages.invoices', compact('invoices', 'finishedGoods', 'clients', 'prefillOrder'));
    }

    /**
     * Generate Custom Direct Invoice (AJAX).
     */
    public function generateCustomInvoice(Request $request)
    {
        $pIds = $request->input('product_ids', $request->input('finished_good_ids'));
        $request->merge(['product_ids' => $pIds]);

        $validated = $request->validate([
            'invoice_number' => 'required|string|unique:invoices,invoice_number',
            'plant_id' => 'required|exists:client_plants,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'invoice_date' => 'nullable|date',
            'vehicle_number' => ['nullable', 'string', 'regex:/^[A-Z]{2}[ -]?[0-9O]{1,2}[ -]?[A-Z]{0,3}[ -]?[0-9O]{1,4}$|^[0-9O]{2}[ -]?BH[ -]?[0-9O]{1,4}[ -]?[A-Z]{1,2}$/i'],
            'due_date' => 'nullable|date',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'required|exists:products,id',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'required|integer|min:1',
            'unit_prices' => 'required|array|min:1',
            'unit_prices.*' => 'required|numeric|min:0',
            'billing_uoms' => 'nullable|array',
        ], [
            'vehicle_number.regex' => 'Enter valid vehicle number',
        ]);

        try {
            $invoice = DB::transaction(function () use ($validated, $request) {
                $plant = ClientPlant::findOrFail($validated['plant_id']);
                $isGujarat = strcasecmp(trim($plant->state), 'Gujarat') === 0;

                // Calculate taxable subtotal
                $taxable = 0.00;
                $cgst = 0.00;
                $sgst = 0.00;
                $igst = 0.00;

                foreach ($validated['product_ids'] as $idx => $fgId) {
                    $qty = (int)$validated['quantities'][$idx];
                    $price = (float)$validated['unit_prices'][$idx];
                    $lineTotal = round($qty * $price, 2);
                    $taxable += $lineTotal;

                    $product = Product::find($fgId);
                    $rate = ($product && isset($product->gst_rate)) ? (float)$product->gst_rate : 18.00;

                    if ($isGujarat) {
                        $cgst += round($lineTotal * ($rate / 200.0), 2);
                        $sgst += round($lineTotal * ($rate / 200.0), 2);
                    } else {
                        $igst += round($lineTotal * ($rate / 100.0), 2);
                    }
                }

                $cgst = round($cgst, 2);
                $sgst = round($sgst, 2);
                $igst = round($igst, 2);
                $total = round($taxable + $cgst + $sgst + $igst, 2);
                $invDate = $validated['invoice_date'] ?? date('Y-m-d');
                $dueDate = !empty($validated['due_date']) ? $validated['due_date'] : date('Y-m-d', strtotime($invDate . ' +30 days'));

                $invoice = Invoice::create([
                    'plant_id' => $plant->id,
                    'invoice_number' => $validated['invoice_number'],
                    'vehicle_number' => $validated['vehicle_number'] ?? null,
                    'invoice_date' => $invDate,
                    'total_taxable_value' => $taxable,
                    'cgst' => $cgst,
                    'sgst' => $sgst,
                    'igst' => $igst,
                    'total_amount' => $total,
                    'payment_status' => 'unpaid',
                    'paid_amount' => 0.00,
                    'due_date' => $dueDate,
                    'created_at' => $invDate . ' ' . now()->format('H:i:s'),
                ]);

                foreach ($validated['product_ids'] as $idx => $fgId) {
                    $qty = (int)$validated['quantities'][$idx];
                    $buom = isset($request->billing_uoms[$idx]) ? $request->billing_uoms[$idx] : 'Pcs';
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $fgId,
                        'billing_uom' => $buom,
                        'quantity' => $qty,
                        'unit_price' => $validated['unit_prices'][$idx],
                        'total_price' => round($qty * $validated['unit_prices'][$idx], 2),
                    ]);

                    // Automatically deduct finished goods stock upon sale
                    $product = Product::find($fgId);
                    if ($product) {
                        $product->decrement('current_stock', $qty);
                    }
                }

                if (!empty($validated['sales_order_id'])) {
                    SalesOrder::where('id', $validated['sales_order_id'])->update(['status' => 'dispatched']);
                }

                return $invoice;
            });

            return response()->json([
                'success' => true,
                'message' => "Custom Tax Invoice '{$invoice->invoice_number}' logged successfully!",
                'data' => $invoice
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Failed to log invoice: ' . $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Record payment against an invoice.
     */
    public function recordInvoicePayment(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:bank_transfer,cheque,upi,cash',
            'account_type' => 'required|in:bank,cash',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $invoice = Invoice::with('plant.client')->findOrFail($id);
            $clientId = $invoice->plant ? $invoice->plant->client_id : null;
            $plantId = $invoice->plant_id;

            $amount = (float) $validated['amount'];
            $remaining = (float) $invoice->remaining_balance;

            if ($amount > ($remaining + 0.01)) {
                return response()->json([
                    'success' => false,
                    'errors' => ['amount' => ["Payment amount (₹" . number_format($amount, 2) . ") cannot exceed remaining invoice balance (₹" . number_format($remaining, 2) . ")."]]
                ], 422);
            }

            DB::transaction(function () use ($invoice, $validated, $amount, $clientId, $plantId) {
                Payment::create([
                    'payment_number' => Payment::generatePaymentNumber('received'),
                    'payment_type' => 'received',
                    'invoice_id' => $invoice->id,
                    'client_id' => $clientId,
                    'plant_id' => $plantId,
                    'amount' => $amount,
                    'payment_date' => $validated['payment_date'],
                    'payment_method' => $validated['payment_method'],
                    'account_type' => $validated['account_type'],
                    'reference_number' => $validated['reference_number'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]);

                $newPaidAmount = round((float)$invoice->paid_amount + $amount, 2);
                $totalAmount = (float)$invoice->total_amount;

                $newStatus = 'partially_paid';
                if ($newPaidAmount >= ($totalAmount - 0.01)) {
                    $newPaidAmount = $totalAmount;
                    $newStatus = 'paid';
                }

                $invoice->update([
                    'paid_amount' => $newPaidAmount,
                    'payment_status' => $newStatus,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => "Payment of ₹" . number_format($amount, 2) . " recorded successfully for Invoice '{$invoice->invoice_number}'!"
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Failed to record payment: ' . $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Mark an invoice as Paid (Quick action).
     */
    public function payInvoice($id)
    {
        try {
            $invoice = Invoice::with('plant.client')->findOrFail($id);
            $remaining = (float) $invoice->remaining_balance;

            if ($remaining <= 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Invoice '{$invoice->invoice_number}' is already fully paid."
                ]);
            }

            $clientId = $invoice->plant ? $invoice->plant->client_id : null;
            $plantId = $invoice->plant_id;

            DB::transaction(function () use ($invoice, $remaining, $clientId, $plantId) {
                Payment::create([
                    'payment_number' => Payment::generatePaymentNumber('received'),
                    'payment_type' => 'received',
                    'invoice_id' => $invoice->id,
                    'client_id' => $clientId,
                    'plant_id' => $plantId,
                    'amount' => $remaining,
                    'payment_date' => date('Y-m-d'),
                    'payment_method' => 'bank_transfer',
                    'account_type' => 'bank',
                    'notes' => 'Quick marked as fully paid',
                ]);

                $invoice->update([
                    'payment_status' => 'paid',
                    'paid_amount' => $invoice->total_amount,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => "Invoice '{$invoice->invoice_number}' marked as fully paid successfully!"
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Failed to update payment status: ' . $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Delete an invoice (AJAX).
     */
    public function deleteInvoice($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);
            $invNum = $invoice->invoice_number;

            DB::transaction(function () use ($invoice) {
                Payment::where('invoice_id', $invoice->id)->delete();
                $invoice->items()->delete();
                $invoice->delete();
            });

            return response()->json([
                'success' => true,
                'message' => "Invoice '{$invNum}' deleted successfully!"
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Failed to delete invoice: ' . $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Print / Save PDF representation of the invoice.
     */
    public function printInvoice($id)
    {
        $invoice = Invoice::with(['plant.client', 'items.product'])->findOrFail($id);
        $client = $invoice->client;
        $plant = $invoice->plant;
        $groupedItems = $invoice->items;

        return view('pages.invoice_print', compact('invoice', 'client', 'plant', 'groupedItems'));
    }

    /**
     * Preview Invoice page.
     */
    public function previewInvoice($id)
    {
        $invoice = Invoice::with(['plant.client', 'items.product'])->findOrFail($id);
        $client = $invoice->client;
        $plant = $invoice->plant;
        $groupedItems = $invoice->items;

        return view('pages.invoice_preview', compact('invoice', 'client', 'plant', 'groupedItems'));
    }

    /**
     * Download Invoice as PDF document.
     */
    public function downloadInvoicePdf($id)
    {
        $invoice = Invoice::with(['plant.client', 'items.product'])->findOrFail($id);
        $client = $invoice->client;
        $plant = $invoice->plant;
        $groupedItems = $invoice->items;

        $isPdf = true;
        $pdf = Pdf::loadView('pages.invoice_print', compact('invoice', 'client', 'plant', 'groupedItems', 'isPdf'));
        return $pdf->download("Invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Send Invoice Email to recipient with attached PDF.
     */
    public function sendInvoiceEmail(Request $request, $id)
    {
        try {
            $request->validate([
                'recipient_email' => 'required|email',
                'subject' => 'required|string|max:255',
                'message_body' => 'required|string',
            ]);

            $invoice = Invoice::with(['plant.client', 'items.product'])->findOrFail($id);
            $client = $invoice->client;
            $plant = $invoice->plant;
            $groupedItems = $invoice->items;

            $isPdf = true;
            $pdfContent = Pdf::loadView('pages.invoice_print', compact('invoice', 'client', 'plant', 'groupedItems', 'isPdf'))
                ->setOption([
                    'isRemoteEnabled' => false,
                    'isFontSubsettingEnabled' => true,
                    'dpi' => 96
                ])
                ->output();

            Mail::to($request->recipient_email)->queue(
                new InvoiceMail($invoice, $request->subject, $request->message_body, $pdfContent, $client, $plant, $groupedItems)
            );

            return response()->json([
                'success' => true,
                'message' => "Invoice #{$invoice->invoice_number} emailed successfully to {$request->recipient_email}!"
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }
}
