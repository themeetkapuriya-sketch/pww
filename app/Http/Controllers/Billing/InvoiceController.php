<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Client;
use App\Models\ClientPlant;
use App\Models\SalesOrder;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;
use App\Services\InvoicePdfService;
use Exception;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    protected InvoicePdfService $pdfService;

    public function __construct(InvoicePdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }
    /**
     * 6. Invoices & Billing.
     */
    public function invoices(Request $request)
    {
        $invoices = Invoice::with(['plant.client', 'items.product', 'items.rawMaterial'])->orderBy('created_at', 'desc')->paginate(50);
        $finishedGoodsInvoices = Invoice::with(['plant.client', 'items.product', 'items.rawMaterial'])
            ->where(function($q) {
                $q->where('invoice_mode', 'finished_goods')->orWhereNull('invoice_mode');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'fg_page');

        $rawMaterialInvoices = Invoice::with(['plant.client', 'items.product', 'items.rawMaterial'])
            ->where('invoice_mode', 'raw_material')
            ->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'rm_page');

        $finishedGoods = Product::all();
        $rawMaterials = RawMaterial::orderBy('material_name')->get();
        $clients = Client::with('plants')->get();

        $prefillOrder = null;
        if ($request->has('order_id')) {
            $prefillOrder = SalesOrder::with(['items.product', 'client', 'plant'])->find($request->input('order_id'));
        }

        $editInvoice = null;
        $editId = $request->input('edit') ?: $request->input('edit_id');
        if ($editId) {
            $editInvoice = Invoice::with(['plant.client', 'items.product', 'items.rawMaterial'])->find($editId);
        }

        return view('pages.invoices', compact(
            'invoices',
            'finishedGoodsInvoices',
            'rawMaterialInvoices',
            'finishedGoods',
            'rawMaterials',
            'clients',
            'prefillOrder',
            'editInvoice'
        ));
    }

    /**
     * Generate Custom Direct Invoice (AJAX).
     */
    public function generateCustomInvoice(Request $request)
    {
        $pIds = $request->input('product_ids', $request->input('finished_good_ids', $request->input('item_keys')));
        $request->merge(['product_ids' => $pIds]);

        $invoiceId = $request->input('invoice_id');
        $invMode = $request->input('invoice_mode', 'finished_goods');

        $validated = $request->validate([
            'invoice_id' => 'nullable|exists:invoices,id',
            'invoice_mode' => 'nullable|string|in:finished_goods,raw_material',
            'invoice_number' => ($invMode === 'raw_material' ? 'nullable|string' : 'required|string|unique:invoices,invoice_number' . ($invoiceId ? ',' . $invoiceId : '')),
            'plant_id' => ($invMode === 'raw_material' ? 'nullable|exists:client_plants,id' : 'required|exists:client_plants,id'),
            'custom_client_name' => ($invMode === 'raw_material' ? 'required|string|max:255' : 'nullable|string|max:255'),
            'custom_buyer_gstin' => 'nullable|string|max:30',
            'gst_rate' => 'nullable|numeric|min:0|max:100',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'invoice_date' => 'nullable|date',
            'vehicle_number' => ['nullable', 'string', 'regex:/^[A-Z]{2}[ -]?[0-9O]{1,2}[ -]?[A-Z]{0,3}[ -]?[0-9O]{1,4}$|^[0-9O]{2}[ -]?BH[ -]?[0-9O]{1,4}[ -]?[A-Z]{1,2}$/i'],
            'due_date' => 'nullable|date',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'required',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'required|numeric|min:0.01',
            'unit_prices' => 'required|array|min:1',
            'unit_prices.*' => 'required|numeric|min:0',
            'billing_uoms' => 'nullable|array',
        ], [
            'vehicle_number.regex' => 'Enter valid vehicle number',
            'custom_client_name.required' => 'Please enter the Buyer / Client Name',
        ]);

        try {
            $invoice = DB::transaction(function () use ($validated, $request, $invoiceId, $invMode) {
                $plantId = !empty($validated['plant_id']) ? $validated['plant_id'] : null;
                $plant = $plantId ? ClientPlant::find($plantId) : null;
                $isGujarat = $plant ? (strcasecmp(trim($plant->state), 'Gujarat') === 0) : true;
                $customGstRate = isset($validated['gst_rate']) ? (float)$validated['gst_rate'] : null;

                // Parse line items (Products vs Raw Materials)
                $parsedItems = [];
                $taxable = 0.00;
                $cgst = 0.00;
                $sgst = 0.00;
                $igst = 0.00;

                foreach ($validated['product_ids'] as $idx => $rawKey) {
                    $qty = (float)$validated['quantities'][$idx];
                    $price = (float)$validated['unit_prices'][$idx];
                    $lineTotal = round($qty * $price, 2);
                    $taxable += $lineTotal;

                    $itemType = ($invMode === 'raw_material') ? 'raw_material' : 'product';
                    $productId = null;
                    $rawMaterialId = null;
                    $itemName = 'Item';
                    $rate = 18.00;
                    $defaultUom = 'Pcs';

                    if (str_starts_with($rawKey, 'raw_material_')) {
                        $itemType = 'raw_material';
                        $rawMaterialId = (int) str_replace('raw_material_', '', $rawKey);
                        $rm = RawMaterial::find($rawMaterialId);
                        if ($rm) {
                            $itemName = $rm->material_name;
                            $defaultUom = $rm->unit ?? 'kg';
                        }
                    } else if (str_starts_with($rawKey, 'product_')) {
                        $itemType = 'product';
                        $productId = (int) str_replace('product_', '', $rawKey);
                        $product = Product::find($productId);
                        if ($product) {
                            $itemName = $product->product_name;
                            $rate = isset($product->gst_rate) ? (float)$product->gst_rate : 18.00;
                            $defaultUom = $product->uom ?? 'piece';
                        }
                    } else {
                        // Numeric ID fallback
                        $numId = (int)$rawKey;
                        if ($invMode === 'raw_material') {
                            $itemType = 'raw_material';
                            $rawMaterialId = $numId;
                            $rm = RawMaterial::find($rawMaterialId);
                            if ($rm) {
                                $itemName = $rm->material_name;
                                $defaultUom = $rm->unit ?? 'kg';
                            }
                        } else {
                            $itemType = 'product';
                            $productId = $numId;
                            $product = Product::find($productId);
                            if ($product) {
                                $itemName = $product->product_name;
                                $rate = isset($product->gst_rate) ? (float)$product->gst_rate : 18.00;
                                $defaultUom = $product->uom ?? 'piece';
                            }
                        }
                    }

                    // Apply custom GST rate if explicitly set in raw material mode
                    if ($customGstRate !== null) {
                        $rate = $customGstRate;
                    }

                    if ($rate > 0) {
                        if ($isGujarat) {
                            $cgst += round($lineTotal * ($rate / 200.0), 2);
                            $sgst += round($lineTotal * ($rate / 200.0), 2);
                        } else {
                            $igst += round($lineTotal * ($rate / 100.0), 2);
                        }
                    }

                    $parsedItems[] = [
                        'type' => $itemType,
                        'product_id' => $productId,
                        'raw_material_id' => $rawMaterialId,
                        'name' => $itemName,
                        'default_uom' => $defaultUom,
                    ];
                }

                $cgst = round($cgst, 2);
                $sgst = round($sgst, 2);
                $igst = round($igst, 2);
                $total = round($taxable + $cgst + $sgst + $igst, 2);
                $invDate = $validated['invoice_date'] ?? date('Y-m-d');
                $dueDate = !empty($validated['due_date']) ? $validated['due_date'] : date('Y-m-d', strtotime($invDate . ' +30 days'));

                $customClientName = ($invMode === 'raw_material') ? ($validated['custom_client_name'] ?? 'Local Buyer') : null;

                if ($invMode === 'raw_material') {
                    if ($invoiceId) {
                        $existingInv = Invoice::find($invoiceId);
                        $finalInvoiceNumber = ($existingInv && str_starts_with($existingInv->invoice_number, 'RMS-'))
                            ? $existingInv->invoice_number
                            : Invoice::generateNextRawMaterialNumber();
                    } else {
                        $finalInvoiceNumber = Invoice::generateNextRawMaterialNumber();
                    }
                } else {
                    $finalInvoiceNumber = $validated['invoice_number'];
                }

                if ($invoiceId) {
                    $invoice = Invoice::findOrFail($invoiceId);
                    
                    // Restore stock before updating
                    foreach ($invoice->items as $oldItem) {
                        if ($oldItem->item_type === 'raw_material' && $oldItem->raw_material_id) {
                            $rm = RawMaterial::find($oldItem->raw_material_id);
                            if ($rm) {
                                $rm->increment('current_stock', (float)$oldItem->quantity);
                            }
                        } else if ($oldItem->product_id) {
                            $product = Product::find($oldItem->product_id);
                            if ($product) {
                                $product->increment('current_stock', (float)$oldItem->quantity);
                            }
                        }
                    }
                    $invoice->items()->delete();

                    $invoice->update([
                        'plant_id' => $plantId,
                        'invoice_mode' => $invMode,
                        'custom_client_name' => $customClientName,
                        'custom_gst_rate' => $customGstRate,
                        'custom_buyer_gstin' => $validated['custom_buyer_gstin'] ?? null,
                        'invoice_number' => $finalInvoiceNumber,
                        'vehicle_number' => $validated['vehicle_number'] ?? null,
                        'invoice_date' => $invDate,
                        'total_taxable_value' => $taxable,
                        'cgst' => $cgst,
                        'sgst' => $sgst,
                        'igst' => $igst,
                        'total_amount' => $total,
                        'due_date' => $dueDate,
                    ]);
                } else {
                    $invoice = Invoice::create([
                        'plant_id' => $plantId,
                        'invoice_mode' => $invMode,
                        'custom_client_name' => $customClientName,
                        'custom_gst_rate' => $customGstRate,
                        'custom_buyer_gstin' => $validated['custom_buyer_gstin'] ?? null,
                        'invoice_number' => $finalInvoiceNumber,
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
                }

                foreach ($parsedItems as $idx => $itemData) {
                    $qty = (float)$validated['quantities'][$idx];
                    $buom = isset($request->billing_uoms[$idx]) ? $request->billing_uoms[$idx] : $itemData['default_uom'];
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'item_type' => $itemData['type'],
                        'product_id' => $itemData['product_id'],
                        'raw_material_id' => $itemData['raw_material_id'],
                        'item_name' => $itemData['name'],
                        'billing_uom' => $buom,
                        'quantity' => $qty,
                        'unit_price' => $validated['unit_prices'][$idx],
                        'total_price' => round($qty * $validated['unit_prices'][$idx], 2),
                    ]);

                    // Automatically deduct inventory stock upon sale
                    if ($itemData['type'] === 'raw_material' && $itemData['raw_material_id']) {
                        $rm = RawMaterial::find($itemData['raw_material_id']);
                        if ($rm) {
                            $rm->decrement('current_stock', $qty);
                        }
                    } else if ($itemData['product_id']) {
                        $product = Product::find($itemData['product_id']);
                        if ($product) {
                            $product->decrement('current_stock', $qty);
                        }
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
            $invoice = Invoice::with('items')->findOrFail($id);
            $invNum = $invoice->invoice_number;

            DB::transaction(function () use ($invoice) {
                Payment::where('invoice_id', $invoice->id)->delete();
                foreach ($invoice->items as $item) {
                    if ($item->item_type === 'raw_material' && $item->raw_material_id) {
                        $rm = RawMaterial::find($item->raw_material_id);
                        if ($rm) {
                            $rm->increment('current_stock', (float)$item->quantity);
                        }
                    } else if ($item->product_id) {
                        $product = Product::find($item->product_id);
                        if ($product) {
                            $product->increment('current_stock', (float)$item->quantity);
                        }
                    }
                }
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
        $invoice = Invoice::with(['plant.client', 'items.product', 'items.rawMaterial'])->findOrFail($id);
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
        $invoice = Invoice::with(['plant.client', 'items.product', 'items.rawMaterial'])->findOrFail($id);
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
        try {
            $invoice = Invoice::with(['plant.client', 'items.product', 'items.rawMaterial'])->findOrFail($id);
            $pdfContent = $this->pdfService->generateInvoicePdf($invoice);

            return response()->streamDownload(
                fn () => print($pdfContent),
                "Invoice-{$invoice->invoice_number}.pdf",
                ['Content-Type' => 'application/pdf']
            );
        } catch (Exception $e) {
            Log::error("Failed to download PDF for invoice ID {$id}: " . $e->getMessage());
            return back()->with('error', 'Unable to generate PDF document. Please try again.');
        }
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

            $invoice = Invoice::with(['plant.client', 'items.product', 'items.rawMaterial'])->findOrFail($id);
            $client = $invoice->client;
            $plant = $invoice->plant;
            $groupedItems = $invoice->items;

            $pdfContent = $this->pdfService->generateInvoicePdf($invoice);

            Mail::to($request->recipient_email)->queue(
                new InvoiceMail($invoice, $request->subject, $request->message_body, $pdfContent, $client, $plant, $groupedItems)
            );

            return response()->json([
                'success' => true,
                'message' => "Invoice #{$invoice->invoice_number} emailed successfully to {$request->recipient_email}!"
            ]);
        } catch (Exception $e) {
            Log::error("Failed to send invoice email for ID {$id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email. ' . $e->getMessage()
            ], 500);
        }
    }
}
