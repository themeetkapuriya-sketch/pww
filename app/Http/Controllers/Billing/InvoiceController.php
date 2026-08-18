<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Mail\InvoiceMail;
use App\Models\Client;
use App\Models\ClientPlant;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\SalesOrder;
use App\Models\Setting;
use App\Services\AuditLogService;
use App\Services\EwayBillService;
use App\Services\InvoicePdfService;
use App\Services\RolePermissionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
            ->where(function ($q) {
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
            $order = SalesOrder::with(['items.product', 'client', 'plant'])->find($request->input('order_id'));
            if ($order && $order->status !== 'dispatched') {
                $prefillOrder = $order;
            }
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
        $action = $request->filled('invoice_id') ? 'action_update' : 'action_insert';
        if ($res = RolePermissionService::authorizeAction($request, $action)) {
            return $res;
        }

        $pIds = $request->input('product_ids', $request->input('finished_good_ids', $request->input('item_keys')));
        $request->merge(['product_ids' => $pIds]);

        $invoiceId = $request->input('invoice_id');
        $invMode = $request->input('invoice_mode', 'finished_goods');
        $taxType = $request->input('tax_type', 'with_gst');
        $isWithoutGst = ($taxType === 'without_gst') || ($request->input('gst_rate') === '0' || $request->input('gst_rate') === 0);
        $isVehicleRequired = ($invMode !== 'raw_material' && ! $isWithoutGst);

        $validated = $request->validate([
            'invoice_id' => 'nullable|exists:invoices,id',
            'invoice_mode' => 'nullable|string|in:finished_goods,raw_material',
            'tax_type' => 'nullable|string|in:with_gst,without_gst',
            'invoice_number' => ($invMode === 'raw_material' ? 'nullable|string' : 'required|string|unique:invoices,invoice_number'.($invoiceId ? ','.$invoiceId : '')),
            'plant_id' => ($invMode === 'raw_material' ? 'nullable|exists:client_plants,id' : 'required|exists:client_plants,id'),
            'custom_client_name' => ($invMode === 'raw_material' ? 'required|string|max:255' : 'nullable|string|max:255'),
            'custom_buyer_gstin' => 'nullable|string|max:30',
            'gst_rate' => 'nullable|numeric|min:0|max:100',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'invoice_date' => 'nullable|date',
            'vehicle_number' => ($isVehicleRequired
                ? ['required', 'string', 'regex:/^[A-Z]{2}[ -]?[0-9O]{1,2}[ -]?[A-Z]{0,3}[ -]?[0-9O]{1,4}$|^[0-9O]{2}[ -]?BH[ -]?[0-9O]{1,4}[ -]?[A-Z]{1,2}$/i']
                : ['nullable', 'string', 'regex:/^[A-Z]{2}[ -]?[0-9O]{1,2}[ -]?[A-Z]{0,3}[ -]?[0-9O]{1,4}$|^[0-9O]{2}[ -]?BH[ -]?[0-9O]{1,4}[ -]?[A-Z]{1,2}$/i']),
            'due_date' => 'nullable|date',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'required',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'required|numeric|min:0.01',
            'unit_prices' => 'required|array|min:1',
            'unit_prices.*' => 'required|numeric|min:0',
            'billing_uoms' => 'nullable|array',
        ], [
            'vehicle_number.required' => 'Delivery Vehicle Number is required.',
            'vehicle_number.regex' => 'Enter valid vehicle number',
            'custom_client_name.required' => 'Please enter the Buyer / Client Name',
        ]);

        $targetDate = ! empty($validated['invoice_date']) ? $validated['invoice_date'] : date('Y-m-d');
        if (\App\Services\FinancialYearService::isFinancialYearLocked($targetDate)) {
            $fy = \App\Services\FinancialYearService::getFinancialYearForDate($targetDate);

            return response()->json([
                'success' => false,
                'message' => "Financial Year {$fy} is LOCKED for tax audit compliance. Creating or editing invoices in locked periods is disabled.",
                'errors' => ["Financial Year {$fy} is locked."],
            ], 422);
        }

        try {
            $invoice = DB::transaction(function () use ($validated, $request, $invoiceId, $invMode, $isWithoutGst) {
                $plantId = ! empty($validated['plant_id']) ? $validated['plant_id'] : null;
                $plant = $plantId ? ClientPlant::find($plantId) : null;
                $homeState = Setting::get('home_state', 'Gujarat');
                $isHomeState = $plant ? (strcasecmp(trim($plant->state), trim($homeState)) === 0) : true;
                $customGstRate = $isWithoutGst ? 0.00 : (isset($validated['gst_rate']) ? (float) $validated['gst_rate'] : null);

                // Parse line items (Products vs Raw Materials)
                $parsedItems = [];
                $taxable = 0.00;
                $cgst = 0.00;
                $sgst = 0.00;
                $igst = 0.00;

                foreach ($validated['product_ids'] as $idx => $rawKey) {
                    $qty = (float) $validated['quantities'][$idx];
                    $price = (float) $validated['unit_prices'][$idx];
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
                    } elseif (str_starts_with($rawKey, 'product_')) {
                        $itemType = 'product';
                        $productId = (int) str_replace('product_', '', $rawKey);
                        $product = Product::find($productId);
                        if ($product) {
                            $itemName = $product->product_name;
                            $rate = isset($product->gst_rate) ? (float) $product->gst_rate : 18.00;
                            $defaultUom = $product->uom ?? 'piece';
                        }
                    } else {
                        // Numeric ID fallback
                        $numId = (int) $rawKey;
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
                                $rate = isset($product->gst_rate) ? (float) $product->gst_rate : 18.00;
                                $defaultUom = $product->uom ?? 'piece';
                            }
                        }
                    }

                    // Apply custom GST rate if explicitly set in raw material mode
                    if ($customGstRate !== null) {
                        $rate = $customGstRate;
                    }

                    if ($rate > 0) {
                        if ($isHomeState) {
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
                $dueDate = ! empty($validated['due_date']) ? $validated['due_date'] : date('Y-m-d', strtotime($invDate.' +30 days'));

                $vehicleNumber = self::formatVehicleNumber($validated['vehicle_number'] ?? null);

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

                $trackStock = Setting::isStockEnabled();

                if ($invoiceId) {
                    $invoice = Invoice::findOrFail($invoiceId);

                    // Restore stock before updating if stock tracking is enabled
                    if ($trackStock) {
                        foreach ($invoice->items as $oldItem) {
                            if (! empty($oldItem->raw_material_id)) {
                                $rm = RawMaterial::find($oldItem->raw_material_id);
                                if ($rm) {
                                    $rm->increment('current_stock', (float) $oldItem->quantity);
                                }
                            } elseif (! empty($oldItem->product_id)) {
                                $product = Product::find($oldItem->product_id);
                                if ($product) {
                                    $product->increment('current_stock', (float) $oldItem->quantity);
                                }
                            }
                        }
                    }
                    $invoice->items()->delete();

                    $invoice->update([
                        'sales_order_id' => $validated['sales_order_id'] ?? null,
                        'plant_id' => $plantId,
                        'invoice_mode' => $invMode,
                        'custom_client_name' => $customClientName,
                        'custom_gst_rate' => $customGstRate,
                        'custom_buyer_gstin' => $validated['custom_buyer_gstin'] ?? null,
                        'invoice_number' => $finalInvoiceNumber,
                        'vehicle_number' => $vehicleNumber,
                        'invoice_date' => $invDate,
                        'total_taxable_value' => $taxable,
                        'cgst' => $cgst,
                        'sgst' => $sgst,
                        'igst' => $igst,
                        'total_amount' => $total,
                        'due_date' => $dueDate,
                    ]);
                } else {
                    $trackPayments = (Setting::get('track_payments', 'true') === 'true');
                    $initialPaymentStatus = $trackPayments ? 'unpaid' : 'paid';
                    $initialPaidAmount = $trackPayments ? 0.00 : $total;

                    $invoice = Invoice::create([
                        'sales_order_id' => $validated['sales_order_id'] ?? null,
                        'plant_id' => $plantId,
                        'invoice_mode' => $invMode,
                        'custom_client_name' => $customClientName,
                        'custom_gst_rate' => $customGstRate,
                        'custom_buyer_gstin' => $validated['custom_buyer_gstin'] ?? null,
                        'invoice_number' => $finalInvoiceNumber,
                        'vehicle_number' => $vehicleNumber,
                        'invoice_date' => $invDate,
                        'total_taxable_value' => $taxable,
                        'cgst' => $cgst,
                        'sgst' => $sgst,
                        'igst' => $igst,
                        'total_amount' => $total,
                        'payment_status' => $initialPaymentStatus,
                        'paid_amount' => $initialPaidAmount,
                        'due_date' => $dueDate,
                        'created_at' => $invDate.' '.now()->format('H:i:s'),
                    ]);
                }

                foreach ($parsedItems as $idx => $itemData) {
                    $qty = (float) $validated['quantities'][$idx];
                    $buom = $request->input("billing_uoms.{$idx}") ?: $itemData['default_uom'];
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

                    // Automatically deduct inventory stock upon sale if stock tracking is enabled
                    if ($trackStock) {
                        if (! empty($itemData['raw_material_id'])) {
                            $rm = RawMaterial::find($itemData['raw_material_id']);
                            if ($rm) {
                                $rm->decrement('current_stock', $qty);
                            }
                        } elseif (! empty($itemData['product_id'])) {
                            $product = Product::find($itemData['product_id']);
                            if ($product) {
                                $product->decrement('current_stock', $qty);
                            }
                        }
                    }
                }

                // Update sales order status if linked
                if ($invoice->sales_order_id) {
                    SalesOrder::where('id', $invoice->sales_order_id)->update(['status' => 'dispatched']);
                }

                AuditLogService::log('Invoices', $invoiceId ? 'updated' : 'created', "Logged Invoice #{$invoice->invoice_number} (Amount: ₹".number_format($invoice->total_amount, 2).')');

                return $invoice;
            });

            return response()->json([
                'success' => true,
                'message' => $invoiceId ? "Invoice #{$invoice->invoice_number} updated successfully!" : "Custom Tax Invoice '{$invoice->invoice_number}' logged successfully!",
                'data' => $invoice,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to log invoice: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to log invoice: '.$e->getMessage(),
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }

    /**
     * Record payment against an invoice.
     */
    public function recordInvoicePayment(Request $request, $id)
    {
        if ($res = RolePermissionService::authorizeAction($request, 'action_update')) {
            return $res;
        }

        if ($request->has('amount')) {
            $request->merge([
                'amount' => str_replace(',', '', (string) $request->input('amount')),
            ]);
        }

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
                    'errors' => ['amount' => ['Payment amount (₹'.number_format($amount, 2).') cannot exceed remaining invoice balance (₹'.number_format($remaining, 2).').']],
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

                $newPaidAmount = round((float) $invoice->paid_amount + $amount, 2);
                $totalAmount = (float) $invoice->total_amount;

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
                'message' => 'Payment of ₹'.number_format($amount, 2)." recorded successfully for Invoice '{$invoice->invoice_number}'!",
            ]);
        } catch (Exception $e) {
            Log::error('Failed to record invoice payment: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'errors' => ['Failed to record payment. Please try again.'],
            ], 500);
        }
    }

    /**
     * Mark an invoice as Paid (Quick action).
     */
    public function payInvoice($id)
    {
        if ($res = RolePermissionService::authorizeAction(request(), 'action_update')) {
            return $res;
        }

        try {
            $invoice = Invoice::with('plant.client')->findOrFail($id);
            $remaining = (float) $invoice->remaining_balance;

            if ($remaining <= 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Invoice '{$invoice->invoice_number}' is already fully paid.",
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
                'message' => "Invoice '{$invoice->invoice_number}' marked as fully paid successfully!",
            ]);
        } catch (Exception $e) {
            Log::error('Failed to update payment status: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'errors' => ['Failed to update payment status. Please try again.'],
            ], 500);
        }
    }

    /**
     * Delete an invoice (AJAX).
     */
    public function deleteInvoice($id)
    {
        if ($res = RolePermissionService::authorizeAction(request(), 'action_delete')) {
            return $res;
        }

        try {
            $invoice = Invoice::with('items')->findOrFail($id);
            $invNum = $invoice->invoice_number;

            $invDate = $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d') : $invoice->created_at->format('Y-m-d');
            if (\App\Services\FinancialYearService::isFinancialYearLocked($invDate)) {
                $fy = \App\Services\FinancialYearService::getFinancialYearForDate($invDate);

                return response()->json([
                    'success' => false,
                    'message' => "Financial Year {$fy} is LOCKED for tax audit compliance. Deleting invoices from locked periods is disabled.",
                    'errors' => ["Financial Year {$fy} is locked."],
                ], 422);
            }

            DB::transaction(function () use ($invoice, $invNum) {
                Payment::where('invoice_id', $invoice->id)->delete();

                $trackStock = Setting::isStockEnabled();
                if ($trackStock) {
                    foreach ($invoice->items as $item) {
                        if ($item->item_type === 'raw_material' && $item->raw_material_id) {
                            $rm = RawMaterial::find($item->raw_material_id);
                            if ($rm) {
                                $rm->increment('current_stock', (float) $item->quantity);
                            }
                        } elseif ($item->product_id) {
                            $product = Product::find($item->product_id);
                            if ($product) {
                                $product->increment('current_stock', (float) $item->quantity);
                            }
                        }
                    }
                }

                $invoice->items()->delete();
                $invoice->delete();

                AuditLogService::log('Invoices', 'deleted', "Deleted Invoice #{$invNum}".($trackStock ? ' and restored items stock' : ''));
            });

            return response()->json([
                'success' => true,
                'message' => "Invoice '{$invNum}' deleted successfully!",
            ]);
        } catch (Exception $e) {
            Log::error('Failed to delete invoice: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'errors' => ['Failed to delete invoice. Please try again.'],
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
                fn () => print ($pdfContent),
                "Invoice-{$invoice->invoice_number}.pdf",
                ['Content-Type' => 'application/pdf']
            );
        } catch (Exception $e) {
            Log::error("Failed to download PDF for invoice ID {$id}: ".$e->getMessage());

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

            Mail::to($request->recipient_email)->send(
                new InvoiceMail($invoice, $request->subject, $request->message_body)
            );

            return response()->json([
                'success' => true,
                'message' => "Invoice #{$invoice->invoice_number} emailed successfully to {$request->recipient_email}!",
            ]);
        } catch (Exception $e) {
            Log::error("Failed to send invoice email for ID {$id}: ".$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email. Please check your SMTP settings and try again.',
            ], 500);
        }
    }

    /**
     * Format raw vehicle numbers into standard Indian format (e.g., GJO5MA4104 -> GJ-05-MA-4104).
     */
    public static function formatVehicleNumber(?string $val): ?string
    {
        if (empty($val)) {
            return null;
        }

        $clean = preg_replace('/[\s-]/', '', strtoupper(trim($val)));

        // Standard RTO Format: State(2) + District(1-2) + Series(1-3) + Number(1-4)
        if (preg_match('/^([A-Z]{2})([0-9O]{1,2})([A-Z]{1,3})([0-9O]{1,4})$/', $clean, $m)) {
            $dist = str_replace('O', '0', $m[2]);
            if (strlen($dist) === 1) {
                $dist = '0'.$dist;
            }
            $num = str_replace('O', '0', $m[4]);

            return "{$m[1]}-{$dist}-{$m[3]}-{$num}";
        }

        // BH Series Format: Year(2) + BH + Number(1-4) + Series(1-2)
        if (preg_match('/^([0-9O]{2})BH([0-9O]{1,4})([A-Z]{1,2})$/', $clean, $m)) {
            $yr = str_replace('O', '0', $m[1]);
            $num = str_replace('O', '0', $m[2]);

            return "{$yr}-BH-{$num}-{$m[3]}";
        }

        return strtoupper(trim($val));
    }

    /**
     * Download NIC-compliant E-Way Bill JSON file (v1.0.1121) for bulk upload on Govt portal.
     */
    public function downloadEwayJson($id)
    {
        $invoice = Invoice::with(['plant.client', 'items.product', 'items.rawMaterial', 'salesOrder'])->findOrFail($id);
        $payload = EwayBillService::generateJsonPayload($invoice);

        $safeNumber = preg_replace('/[^A-Za-z0-9_-]/', '_', $invoice->invoice_number);
        $filename = "eway_invoice_{$safeNumber}.json";

        AuditLogService::log('Invoices', 'export', "Exported E-Way Bill JSON file for Invoice #{$invoice->invoice_number}");

        return response()->json($payload, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
