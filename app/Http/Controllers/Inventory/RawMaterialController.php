<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RawMaterial;

class RawMaterialController extends Controller
{
    /**
     * Raw Materials Inventory Audit Page.
     */
    public function index(Request $request)
    {
        $rawMaterials = RawMaterial::orderBy('material_name')->paginate(20);
        return view('pages.rawmaterial', compact('rawMaterials'));
    }

    /**
     * Create / Restock Raw Material (AJAX).
     */
    public function store(Request $request)
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
    public function update(Request $request, $id)
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
    public function destroy($id)
    {
        $material = RawMaterial::findOrFail($id);
        $name = $material->material_name;
        $material->delete();

        return response()->json([
            'success' => true,
            'message' => "Raw Material '{$name}' deleted successfully!"
        ]);
    }
}
