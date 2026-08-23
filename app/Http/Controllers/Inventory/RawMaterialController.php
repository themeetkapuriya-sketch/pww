<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\RawMaterial;
use App\Models\StockAdjustment;
use App\Services\AuditLogService;
use App\Services\RolePermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RawMaterialController extends Controller
{
    /**
     * Raw Materials Inventory Audit Page.
     */
    public function index(Request $request)
    {
        $selectedCategory = $request->query('category', 'all');
        $query = RawMaterial::with(['latestPurchase', 'purchases'])->orderByDesc('id');

        if ($selectedCategory && $selectedCategory !== 'all') {
            $query->where('material_category', $selectedCategory);
        }

        $rawMaterials = $query->get();

        // Calculate counts for each category
        $countsRaw = RawMaterial::selectRaw('material_category, count(*) as count')
            ->groupBy('material_category')
            ->pluck('count', 'material_category')
            ->toArray();

        $materialCategories = \App\Services\CategoryService::getMaterialCategories();
        $totalCount = RawMaterial::count();
        $categoryCounts = ['all' => $totalCount];
        foreach ($materialCategories as $cat) {
            $k = $cat['key'];
            $categoryCounts[$k] = $countsRaw[$k] ?? 0;
        }
        if (isset($countsRaw[''])) {
            $categoryCounts['other'] = ($categoryCounts['other'] ?? 0) + $countsRaw[''];
        }

        $lowStockMaterials = $rawMaterials->filter(fn ($m) => (float) $m->current_stock < (float) $m->safety_threshold);

        return view('pages.rawmaterial', compact('rawMaterials', 'selectedCategory', 'categoryCounts', 'materialCategories', 'lowStockMaterials'));
    }

    /**
     * Create / Register New Raw Material (AJAX).
     */
    public function store(Request $request)
    {
        if ($res = RolePermissionService::authorizeAction($request, 'action_insert')) {
            return $res;
        }

        $validated = $request->validate([
            'material_name' => 'required|string|max:255',
            'material_category' => 'nullable|string|max:50',
            'specification' => 'nullable|string|max:255',
            'unit' => 'required|string|max:50',
            'current_stock' => 'nullable|numeric|min:0',
            'safety_threshold' => 'required|numeric|min:0',
            'average_purchase_price' => 'nullable|numeric|min:0',
        ]);
        $addedQty = (float) $request->input('current_stock', 0);
        $validated['current_stock'] = $addedQty;
        $customRate = $request->filled('average_purchase_price') ? (float) $request->input('average_purchase_price') : 0.0;
        $validated['average_purchase_price'] = $customRate;

        // Auto-restock if material already exists
        /** @var RawMaterial|null $existing */
        $existing = RawMaterial::where('material_name', $validated['material_name'])->first();

        if ($existing) {
            $existing->current_stock += $addedQty;
            $existing->safety_threshold = $validated['safety_threshold'];
            if (! empty($validated['material_category'])) {
                $existing->material_category = $validated['material_category'];
            }
            if (! empty($validated['specification'])) {
                $existing->specification = $validated['specification'];
            }
            if ($customRate > 0) {
                $existing->average_purchase_price = $customRate;
            } elseif (! $request->filled('average_purchase_price')) {
                $existing->recalculateAveragePurchasePrice();
            }
            $existing->unit = $validated['unit'];
            $existing->save();

            return response()->json([
                'success' => true,
                'message' => 'Restocked '.number_format($addedQty, 2)." {$existing->unit} for '{$existing->material_name}'! Updated Total Stock: ".number_format((float) $existing->current_stock, 2)." {$existing->unit}.",
                'data' => $existing,
            ]);
        }

        $material = RawMaterial::create($validated);
        if ($customRate <= 0) {
            $material->recalculateAveragePurchasePrice();
        }

        return response()->json([
            'success' => true,
            'message' => "Raw Material '{$material->material_name}' registered successfully!",
            'data' => $material,
        ]);
    }

    /**
     * Update Raw Material Item Details (AJAX).
     */
    public function update(Request $request, $id)
    {
        if ($res = RolePermissionService::authorizeAction($request, 'action_update')) {
            return $res;
        }

        $material = RawMaterial::findOrFail($id);

        $validated = $request->validate([
            'material_name' => 'required|string|max:255',
            'material_category' => 'nullable|string|max:50',
            'specification' => 'nullable|string|max:255',
            'unit' => 'required|string|max:50',
            'safety_threshold' => 'required|numeric|min:0',
            'average_purchase_price' => 'nullable|numeric|min:0',
        ]);

        if ($request->filled('average_purchase_price') && (float) $request->input('average_purchase_price') > 0) {
            $validated['average_purchase_price'] = (float) $request->input('average_purchase_price');
        } elseif ($request->has('average_purchase_price') && ! $request->filled('average_purchase_price')) {
            // Revert back to live calculated purchase average
            $material->recalculateAveragePurchasePrice();
            unset($validated['average_purchase_price']);
        }

        $material->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Raw Material '{$material->material_name}' updated successfully!",
            'data' => $material,
        ]);
    }

    /**
     * Physical Stock Adjustment / Audit Voucher (AJAX).
     */
    public function adjustStock(Request $request, $id)
    {
        if ($res = RolePermissionService::authorizeAction($request, 'action_update')) {
            return $res;
        }

        $material = RawMaterial::findOrFail($id);

        $validated = $request->validate([
            'new_stock' => 'required|numeric|min:0',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $previousStock = (float) $material->current_stock;
        $newStock = (float) $validated['new_stock'];
        $varianceQty = $newStock - $previousStock;
        $reason = $validated['reason'];
        $notes = $validated['notes'] ?? null;

        $material->current_stock = $newStock;
        $material->save();

        $user = Auth::user();
        StockAdjustment::create([
            'raw_material_id' => $material->id,
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'Admin',
            'previous_stock' => $previousStock,
            'new_stock' => $newStock,
            'variance_qty' => $varianceQty,
            'reason' => $reason,
            'notes' => $notes,
            'adjusted_at' => now(),
        ]);

        $diffSign = $varianceQty >= 0 ? '+' : '';
        $formattedDiff = $diffSign.number_format($varianceQty, 2)." {$material->unit}";
        AuditLogService::log('Inventory', 'updated', "Stock adjustment for '{$material->material_name}': {$previousStock} -> {$newStock} ({$formattedDiff}). Reason: {$reason}");

        return response()->json([
            'success' => true,
            'message' => "Stock adjusted for '{$material->material_name}' to ".number_format($newStock, 2)." {$material->unit} ({$formattedDiff})!",
            'data' => [
                'material_id' => $material->id,
                'current_stock' => $material->current_stock,
                'unit' => $material->unit,
                'safety_threshold' => $material->safety_threshold,
                'is_low' => $material->current_stock < $material->safety_threshold,
                'formatted_stock' => number_format($material->current_stock, 2).' '.$material->unit,
            ],
        ]);
    }

    /**
     * Delete Raw Material Item (AJAX).
     */
    public function destroy($id)
    {
        if ($res = RolePermissionService::authorizeAction(request(), 'action_delete')) {
            return $res;
        }

        $material = RawMaterial::findOrFail($id);
        $name = $material->material_name;
        $material->delete();

        return response()->json([
            'success' => true,
            'message' => "Raw Material '{$name}' deleted successfully!",
        ]);
    }
}
