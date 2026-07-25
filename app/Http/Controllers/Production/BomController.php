<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\BillOfMaterial;

class BomController extends Controller
{
    /**
     * 3. Bill of Materials (BOM).
     */
    public function bom()
    {
        $finishedGoods = Product::with('billOfMaterials.rawMaterial')->get();
        $rawMaterials = RawMaterial::all();
        return view('pages.bom', compact('finishedGoods', 'rawMaterials'));
    }

    /**
     * Store BOM Item (AJAX).
     */
    public function storeBom(Request $request)
    {
        $productId = $request->input('product_id', $request->input('finished_good_id'));
        $request->merge(['product_id' => $productId]);

        // Support multi-row component array submission
        if ($request->has('raw_material_ids') && is_array($request->input('raw_material_ids'))) {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'raw_material_ids' => 'required|array|min:1',
                'raw_material_ids.*' => 'required|exists:raw_materials,id',
                'required_quantities' => 'required|array|min:1',
                'required_quantities.*' => 'required|numeric|min:0.0001',
                'waste_percentages' => 'required|array|min:1',
                'waste_percentages.*' => 'required|numeric|min:0',
            ]);

            $savedCount = 0;
            foreach ($validated['raw_material_ids'] as $idx => $matId) {
                if (empty($matId)) continue;
                $reqQty = (float) ($validated['required_quantities'][$idx] ?? 0);
                $waste = (float) ($validated['waste_percentages'][$idx] ?? 0);
                if ($reqQty <= 0) continue;

                BillOfMaterial::updateOrCreate(
                    [
                        'product_id' => $validated['product_id'],
                        'raw_material_id' => $matId,
                    ],
                    [
                        'required_quantity' => $reqQty,
                        'waste_percentage' => $waste,
                    ]
                );
                $savedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully assigned {$savedCount} BOM raw material components!",
            ]);
        }

        // Single component fallback
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'raw_material_id' => 'required|exists:raw_materials,id',
            'required_quantity' => 'required|numeric|min:0.0001',
            'waste_percentage' => 'required|numeric|min:0',
        ]);

        $bom = BillOfMaterial::updateOrCreate(
            [
                'product_id' => $validated['product_id'],
                'raw_material_id' => $validated['raw_material_id'],
            ],
            [
                'required_quantity' => $validated['required_quantity'],
                'waste_percentage' => $validated['waste_percentage'],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "BOM component mapping assigned successfully!",
            'data' => $bom
        ]);
    }

    /**
     * Update BOM Item (AJAX).
     */
    public function updateBom(Request $request, $id)
    {
        $bom = BillOfMaterial::findOrFail($id);

        $validated = $request->validate([
            'required_quantity' => 'required|numeric|min:0.0001',
            'waste_percentage' => 'required|numeric|min:0',
        ]);

        $bom->update([
            'required_quantity' => $validated['required_quantity'],
            'waste_percentage' => $validated['waste_percentage'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'BOM component updated successfully!',
            'data' => $bom->load('rawMaterial')
        ]);
    }

    /**
     * Delete BOM Item (AJAX).
     */
    public function deleteBom($id)
    {
        $bom = BillOfMaterial::findOrFail($id);
        $bom->delete();

        return response()->json([
            'success' => true,
            'message' => 'BOM raw material component removed successfully!'
        ]);
    }
}
