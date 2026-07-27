<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RawMaterial;
use App\Models\Product;

class InventoryController extends Controller
{
    /**
     * 2. Inventory Management.
     */
    public function inventory(Request $request)
    {
        $tab = $request->input('tab', 'materials');
        
        if ($tab === 'materials') {
            $rawMaterials = RawMaterial::orderBy('material_name')->paginate(20);
            return view('pages.inventory', compact('rawMaterials', 'tab'));
        }

        $finishedGoods = Product::orderBy('product_name')->paginate(20);
        return view('pages.inventory', compact('finishedGoods', 'tab'));
    }

    /**
     * Create Raw Material (AJAX).
     */
    public function storeRawMaterial(Request $request)
    {
        $validated = $request->validate([
            'material_name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'current_stock' => 'nullable|numeric|min:0',
            'safety_threshold' => 'required|numeric|min:0',
            'average_purchase_price' => 'required|numeric|min:0',
        ]);
        $addedQty = (float) $request->input('current_stock', 0);
        $validated['current_stock'] = $addedQty;

        // Auto-restock if material already exists
        $existing = RawMaterial::where('material_name', $validated['material_name'])->first();

        if ($existing) {
            $existing->current_stock += $addedQty;
            $existing->safety_threshold = $validated['safety_threshold'];
            $existing->average_purchase_price = $validated['average_purchase_price'];
            $existing->unit = $validated['unit'];
            $existing->save();

            return response()->json([
                'success' => true,
                'message' => "Restocked " . number_format($addedQty, 2) . " {$existing->unit} for '{$existing->material_name}'! Updated Total Stock: " . number_format($existing->current_stock, 2) . " {$existing->unit}.",
                'data' => $existing
            ]);
        }

        $material = RawMaterial::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Raw Material '{$material->material_name}' logged successfully!",
            'data' => $material
        ]);
    }

    /**
     * Update Raw Material Item (AJAX).
     */
    public function updateRawMaterial(Request $request, $id)
    {
        $material = RawMaterial::findOrFail($id);

        $validated = $request->validate([
            'material_name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'safety_threshold' => 'required|numeric|min:0',
            'average_purchase_price' => 'required|numeric|min:0',
        ]);

        $material->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Raw Material '{$material->material_name}' updated successfully!",
            'data' => $material
        ]);
    }

    /**
     * Delete Raw Material Item (AJAX).
     */
    public function deleteRawMaterial($id)
    {
        $material = RawMaterial::findOrFail($id);
        $name = $material->material_name;
        $material->delete();

        return response()->json([
            'success' => true,
            'message' => "Raw Material '{$name}' deleted successfully!"
        ]);
    }

    /**
     * Create Finished Good (AJAX).
     */
    public function storeFinishedGood(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku|max:100',
            'hsn_code' => 'required|string|max:50',
            'uom' => 'required|string|max:50',
            'unit_weight_kg' => 'nullable|numeric|min:0',
            'current_stock' => 'nullable|integer|min:0',
            'selling_price' => 'required|numeric|min:0',
            'price_per_kg' => 'nullable|numeric|min:0',
            'gst_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $validated['current_stock'] = $request->input('current_stock', 0);
        $validated['uom'] = $request->input('uom', 'piece');
        $validated['unit_weight_kg'] = $request->input('unit_weight_kg', 0.000);
        $validated['price_per_kg'] = $request->filled('price_per_kg') ? $request->input('price_per_kg') : null;
        $validated['gst_rate'] = $request->input('gst_rate', 18.00);

        $good = Product::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Product '{$good->product_name}' (GST {$good->gst_rate}%) cataloged successfully!",
            'data' => $good
        ]);
    }

    /**
     * Update Finished Good Product (AJAX).
     */
    public function updateFinishedGood(Request $request, $id)
    {
        $good = Product::findOrFail($id);

        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku,' . $id,
            'hsn_code' => 'required|string|max:50',
            'uom' => 'required|string|max:50',
            'unit_weight_kg' => 'nullable|numeric|min:0',
            'current_stock' => 'nullable|integer|min:0',
            'selling_price' => 'required|numeric|min:0',
            'price_per_kg' => 'nullable|numeric|min:0',
            'gst_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        if (!array_key_exists('current_stock', $validated) || is_null($validated['current_stock'])) {
            unset($validated['current_stock']);
        }
        if (!isset($validated['gst_rate'])) {
            $validated['gst_rate'] = 18.00;
        }
        if (!isset($validated['unit_weight_kg'])) {
            $validated['unit_weight_kg'] = 0.000;
        }
        $validated['price_per_kg'] = $request->filled('price_per_kg') ? $request->input('price_per_kg') : null;

        $good->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Product '{$good->product_name}' updated successfully!",
            'data' => $good
        ]);
    }

    /**
     * Delete Finished Good Product (AJAX).
     */
    public function deleteFinishedGood($id)
    {
        $good = Product::findOrFail($id);
        $name = $good->product_name;
        $good->delete();

        return response()->json([
            'success' => true,
            'message' => "Product '{$name}' deleted successfully!"
        ]);
    }
}
