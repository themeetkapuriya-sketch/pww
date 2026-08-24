<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\RawMaterial;
use App\Models\Setting;
use App\Services\AuditLogService;
use App\Services\RolePermissionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseController extends Controller
{
    /**
     * Purchase Ledger Page.
     */
    public function purchases(Request $request)
    {
        $purchases = Purchase::with('rawMaterial')->orderBy('purchase_date', 'desc')->orderBy('id', 'desc')->paginate(20);
        $rawMaterials = RawMaterial::orderBy('material_name')->get();
        $prefillMaterialId = $request->query('prefill_raw_material');
        $prefillQty = $request->query('prefill_qty');
        $prefillPrice = $request->query('prefill_price');

        return view('pages.purchases', compact('purchases', 'rawMaterials', 'prefillMaterialId', 'prefillQty', 'prefillPrice'));
    }

    /**
     * Store Purchase Record (AJAX).
     */
    public function storePurchase(Request $request)
    {
        if ($res = RolePermissionService::authorizeAction($request, 'action_insert')) {
            return $res;
        }

        $validated = $request->validate([
            'bill_number' => 'nullable|string|max:100',
            'vendor_name' => 'required|string|max:255',
            'purchase_type' => 'required|string|max:100',
            'raw_material_id' => 'nullable|exclude_unless:purchase_type,raw_material|required_if:purchase_type,raw_material|exists:raw_materials,id',
            'item_name' => 'nullable|string|max:255',
            'quantity' => 'nullable|numeric|min:0.0001',
            'unit' => 'nullable|string|max:50',
            'total_amount' => 'required|numeric|min:0',
            'gst_rate' => 'required|numeric|in:0,5,12,18,28',
            'purchase_date' => 'required|date',
            'payment_status' => 'nullable|string|in:paid,unpaid,partially_paid',
        ]);

        if (\App\Services\FinancialYearService::isFinancialYearLocked($validated['purchase_date'])) {
            $fy = \App\Services\FinancialYearService::getFinancialYearForDate($validated['purchase_date']);

            return response()->json([
                'success' => false,
                'message' => "Financial Year {$fy} is LOCKED for tax audit compliance. Creating purchases in locked periods is disabled.",
                'errors' => ["Financial Year {$fy} is locked."],
            ], 422);
        }

        $gstRate = (float) $validated['gst_rate'];
        $totalAmt = (float) $validated['total_amount'];

        $status = $validated['payment_status'] ?? 'paid';
        if (empty($status)) {
            $status = 'paid';
        }
        $validated['payment_status'] = $status;

        if ($status === 'paid') {
            $validated['paid_amount'] = $totalAmt;
        } elseif ($status === 'unpaid') {
            $validated['paid_amount'] = 0.00;
        }

        if ($gstRate > 0) {
            $taxableAmount = round($totalAmt / (1 + ($gstRate / 100)), 2);
            $validated['gst_amount'] = round($totalAmt - $taxableAmount, 2);
        } else {
            $validated['gst_amount'] = 0.00;
        }

        if (empty($validated['quantity'])) {
            $validated['quantity'] = 1.0;
        }

        if ($validated['purchase_type'] === 'raw_material' && ! empty($validated['raw_material_id'])) {
            $material = RawMaterial::find($validated['raw_material_id']);
            if ($material) {
                if (empty($validated['item_name'])) {
                    $validated['item_name'] = $material->material_name;
                }
                if (empty($validated['unit'])) {
                    $validated['unit'] = $material->unit;
                }
            }
        }

        if (empty($validated['item_name'])) {
            $typeLabels = [
                'office_assets' => 'Office Assets & Electronics',
                'machinery' => 'Machinery & Capital Equipment',
                'factory_spares' => 'Welding Gas & Machinery Spare Parts',
                'supplies' => 'Factory Consumables & Tools',
                'vehicle_transport' => 'Vehicle & Freight Expenses',
                'others' => 'Miscellaneous Purchase',
            ];
            $validated['item_name'] = $typeLabels[$validated['purchase_type']] ?? 'Purchased Item';
        }
        $duplicateCheck = Purchase::where('vendor_name', $validated['vendor_name'])
            ->where('purchase_type', $validated['purchase_type'])
            ->where('total_amount', $totalAmt)
            ->whereDate('purchase_date', $validated['purchase_date'])
            ->where(function ($q) use ($validated) {
                if (! empty($validated['bill_number'])) {
                    $q->where('bill_number', $validated['bill_number']);
                }
            })
            ->exists();

        if ($duplicateCheck) {
            return response()->json([
                'success' => false,
                'message' => 'An identical purchase entry already exists for this vendor, date, and amount!',
                'errors' => ['vendor_name' => ['An identical purchase entry already exists for this vendor, date, and amount!']],
            ], 422);
        }

        $purchase = DB::transaction(function () use ($validated) {
            $pur = Purchase::create($validated);

            $trackStock = Setting::isStockEnabled();
            if ($validated['purchase_type'] === 'raw_material' && ! empty($validated['raw_material_id'])) {
                /** @var RawMaterial|null $material */
                $material = RawMaterial::find($validated['raw_material_id']);
                if ($material) {
                    if ($trackStock) {
                        $material->current_stock += (float) $validated['quantity'];
                        $material->save();
                    }
                    $material->recalculateAveragePurchasePrice();
                }
            }

            return $pur;
        });

        AuditLogService::log('Purchases', 'created', "Logged purchase from '{$purchase->vendor_name}' for '{$purchase->item_name}' (Amount: ₹".number_format($purchase->total_amount, 2).')');

        $stockMsg = Setting::isStockEnabled()
            ? ' Stock & accounting updated.' : '';

        return response()->json([
            'success' => true,
            'message' => "Purchase record '{$purchase->item_name}' logged successfully!{$stockMsg}",
            'data' => $purchase,
        ]);
    }

    /**
     * Update Purchase Record (AJAX).
     */
    public function updatePurchase(Request $request, $id)
    {
        if ($res = RolePermissionService::authorizeAction($request, 'action_update')) {
            return $res;
        }

        $validated = $request->validate([
            'bill_number' => 'nullable|string|max:100',
            'vendor_name' => 'required|string|max:255',
            'purchase_type' => 'required|string|max:100',
            'raw_material_id' => 'nullable|exclude_unless:purchase_type,raw_material|required_if:purchase_type,raw_material|exists:raw_materials,id',
            'item_name' => 'nullable|string|max:255',
            'quantity' => 'nullable|numeric|min:0.0001',
            'unit' => 'nullable|string|max:50',
            'total_amount' => 'required|numeric|min:0',
            'gst_rate' => 'required|numeric|in:0,5,12,18,28',
            'purchase_date' => 'required|date',
            'payment_status' => 'nullable|string|in:paid,unpaid,partially_paid',
        ]);

        if (\App\Services\FinancialYearService::isFinancialYearLocked($validated['purchase_date'])) {
            $fy = \App\Services\FinancialYearService::getFinancialYearForDate($validated['purchase_date']);

            return response()->json([
                'success' => false,
                'message' => "Financial Year {$fy} is LOCKED for tax audit compliance. Updating purchases in locked periods is disabled.",
                'errors' => ["Financial Year {$fy} is locked."],
            ], 422);
        }

        $gstRate = (float) $validated['gst_rate'];
        $totalAmt = (float) $validated['total_amount'];

        $status = $validated['payment_status'] ?? 'paid';
        if (empty($status)) {
            $status = 'paid';
        }
        $validated['payment_status'] = $status;

        if ($status === 'paid') {
            $validated['paid_amount'] = $totalAmt;
        } elseif ($status === 'unpaid') {
            $validated['paid_amount'] = 0.00;
        }

        if ($gstRate > 0) {
            $taxableAmount = round($totalAmt / (1 + ($gstRate / 100)), 2);
            $validated['gst_amount'] = round($totalAmt - $taxableAmount, 2);
        } else {
            $validated['gst_amount'] = 0.00;
        }

        if (empty($validated['quantity'])) {
            $validated['quantity'] = 1.0;
        }

        if ($validated['purchase_type'] === 'raw_material' && ! empty($validated['raw_material_id'])) {
            $material = RawMaterial::find($validated['raw_material_id']);
            if ($material) {
                if (empty($validated['item_name'])) {
                    $validated['item_name'] = $material->material_name;
                }
                if (empty($validated['unit'])) {
                    $validated['unit'] = $material->unit;
                }
            }
        }

        if (empty($validated['item_name'])) {
            $typeLabels = [
                'office_assets' => 'Office Assets & Electronics',
                'machinery' => 'Machinery & Capital Equipment',
                'factory_spares' => 'Welding Gas & Machinery Spare Parts',
                'supplies' => 'Factory Consumables & Tools',
                'vehicle_transport' => 'Vehicle & Freight Expenses',
                'others' => 'Miscellaneous Purchase',
            ];
            $validated['item_name'] = $typeLabels[$validated['purchase_type']] ?? 'Purchased Item';
        }
        if (empty($validated['unit'])) {
            $validated['unit'] = 'pcs';
        }

        try {
            $purchase = Purchase::findOrFail($id);
            $oldRmId = $purchase->purchase_type === 'raw_material' ? $purchase->raw_material_id : null;
            $oldQty = (float) $purchase->quantity;

            $purchase->update($validated);

            $trackStock = Setting::isStockEnabled();
            $newRmId = $purchase->purchase_type === 'raw_material' ? $purchase->raw_material_id : null;
            $newQty = (float) $purchase->quantity;

            if ($trackStock) {
                if ($oldRmId && $newRmId && $oldRmId == $newRmId) {
                    $qtyDiff = $newQty - $oldQty;
                    if ($qtyDiff != 0) {
                        $mat = RawMaterial::find($newRmId);
                        if ($mat) {
                            $mat->current_stock = max(0, (float) $mat->current_stock + $qtyDiff);
                            $mat->save();
                        }
                    }
                } else {
                    if ($oldRmId) {
                        $oldMat = RawMaterial::find($oldRmId);
                        if ($oldMat) {
                            $oldMat->current_stock = max(0, (float) $oldMat->current_stock - $oldQty);
                            $oldMat->save();
                            $oldMat->recalculateAveragePurchasePrice();
                        }
                    }
                    if ($newRmId) {
                        $newMat = RawMaterial::find($newRmId);
                        if ($newMat) {
                            $newMat->current_stock = max(0, (float) $newMat->current_stock + $newQty);
                            $newMat->save();
                        }
                    }
                }
            }

            if ($newRmId) {
                /** @var RawMaterial|null $material */
                $material = RawMaterial::find($newRmId);
                if ($material) {
                    $material->recalculateAveragePurchasePrice();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Purchase entry updated successfully!',
                'data' => $purchase,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to update purchase: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update purchase. Please try again.',
                'errors' => ['error' => [$e->getMessage()]],
            ], 500);
        }
    }

    /**
     * Delete Purchase Record (AJAX).
     */
    public function deletePurchase($id)
    {
        if ($res = RolePermissionService::authorizeAction(request(), 'action_delete')) {
            return $res;
        }

        try {
            $purchase = Purchase::findOrFail($id);

            if (\App\Services\FinancialYearService::isFinancialYearLocked($purchase->purchase_date)) {
                $fy = \App\Services\FinancialYearService::getFinancialYearForDate($purchase->purchase_date);

                return response()->json([
                    'success' => false,
                    'message' => "Financial Year {$fy} is LOCKED for tax audit compliance. Deleting purchases from locked periods is disabled.",
                    'errors' => ["Financial Year {$fy} is locked."],
                ], 422);
            }

            $item = $purchase->item_name ?? 'Purchase Bill';

            DB::transaction(function () use ($purchase) {
                Payment::where('purchase_id', $purchase->id)->delete();

                $matId = $purchase->raw_material_id;
                $isRm = $purchase->purchase_type === 'raw_material';
                $purchasedQty = (float) $purchase->quantity;

                $purchase->delete();

                $trackStock = Setting::isStockEnabled();
                if ($isRm && $matId) {
                    /** @var RawMaterial|null $material */
                    $material = RawMaterial::find($matId);
                    if ($material) {
                        if ($trackStock) {
                            $material->current_stock = max(0, (float) $material->current_stock - $purchasedQty);
                            $material->save();
                        }
                        $material->recalculateAveragePurchasePrice();
                    }
                }
            });

            AuditLogService::log('Purchases', 'deleted', "Deleted purchase record '{$item}'");

            return response()->json([
                'success' => true,
                'message' => "Purchase record '{$item}' deleted successfully!",
            ]);
        } catch (Exception $e) {
            Log::error('Failed to delete purchase record: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete purchase record. Please try again.',
            ], 500);
        }
    }

    /**
     * Record payment to vendor.
     */
    public function recordPurchasePayment(Request $request, $id)
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
            $purchase = Purchase::findOrFail($id);
            $amount = (float) $validated['amount'];
            $remaining = (float) $purchase->remaining_balance;

            if ($amount > ($remaining + 0.01)) {
                return response()->json([
                    'success' => false,
                    'errors' => ['amount' => ['Payout amount (₹'.number_format($amount, 2).') cannot exceed remaining bill balance (₹'.number_format($remaining, 2).').']],
                ], 422);
            }

            DB::transaction(function () use ($purchase, $validated, $amount) {
                Payment::create([
                    'payment_number' => Payment::generatePaymentNumber('paid'),
                    'payment_type' => 'paid',
                    'purchase_id' => $purchase->id,
                    'amount' => $amount,
                    'payment_date' => $validated['payment_date'],
                    'payment_method' => $validated['payment_method'],
                    'account_type' => $validated['account_type'],
                    'reference_number' => $validated['reference_number'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]);

                $newPaidAmount = round((float) $purchase->paid_amount + $amount, 2);
                $totalAmount = (float) $purchase->total_amount;

                $newStatus = 'partially_paid';
                if ($newPaidAmount >= ($totalAmount - 0.01)) {
                    $newPaidAmount = $totalAmount;
                    $newStatus = 'paid';
                }

                $purchase->update([
                    'paid_amount' => $newPaidAmount,
                    'payment_status' => $newStatus,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Vendor payout of ₹'.number_format($amount, 2).' recorded successfully!',
            ]);
        } catch (Exception $e) {
            Log::error('Failed to record vendor payout: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'errors' => ['Failed to record vendor payout. Please try again.'],
            ], 500);
        }
    }
}
