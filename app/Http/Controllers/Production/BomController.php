<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\BillOfMaterial;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Services\RolePermissionService;
use Illuminate\Http\Request;

class BomController extends Controller
{
    /**
     * 3. Bill of Materials (BOM).
     */
    public function bom()
    {
        $finishedGoods = Product::with('billOfMaterials.rawMaterial')->orderByDesc('id')->get();
        $rawMaterials = RawMaterial::orderByDesc('id')->get();

        return view('pages.bom', compact('finishedGoods', 'rawMaterials'));
    }

    /**
     * Store BOM Item (AJAX).
     */
    public function storeBom(Request $request)
    {
        if ($res = RolePermissionService::authorizeAction($request, 'action_insert')) {
            return $res;
        }

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
                'unit_rates' => 'nullable|array',
                'unit_rates.*' => 'nullable|numeric|min:0',
            ]);

            // If full formula edit mode, remove components that were deleted from the form
            if ($request->input('replace_mode') == '1' || $request->boolean('replace_mode')) {
                BillOfMaterial::where('product_id', $validated['product_id'])
                    ->whereNotIn('raw_material_id', array_filter($validated['raw_material_ids']))
                    ->delete();
            }

            $savedCount = 0;
            foreach ($validated['raw_material_ids'] as $idx => $matId) {
                if (empty($matId)) {
                    continue;
                }
                $reqQty = (float) ($validated['required_quantities'][$idx] ?? 0);
                $waste = (float) ($validated['waste_percentages'][$idx] ?? 0);
                $rate = isset($validated['unit_rates'][$idx]) && $validated['unit_rates'][$idx] !== '' && $validated['unit_rates'][$idx] !== null
                    ? (float) $validated['unit_rates'][$idx]
                    : null;

                if ($reqQty <= 0) {
                    continue;
                }

                BillOfMaterial::updateOrCreate(
                    [
                        'product_id' => $validated['product_id'],
                        'raw_material_id' => $matId,
                    ],
                    [
                        'required_quantity' => $reqQty,
                        'waste_percentage' => $waste,
                        'unit_rate' => null,
                    ]
                );
                $savedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully saved {$savedCount} BOM raw material components!",
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
                'unit_rate' => null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'BOM component mapping assigned successfully!',
            'data' => $bom,
        ]);
    }

    /**
     * Update BOM Item (AJAX).
     */
    public function updateBom(Request $request, $id)
    {
        if ($res = RolePermissionService::authorizeAction($request, 'action_update')) {
            return $res;
        }

        $bom = BillOfMaterial::findOrFail($id);

        $validated = $request->validate([
            'required_quantity' => 'required|numeric|min:0.0001',
            'waste_percentage' => 'required|numeric|min:0',
        ]);

        $bom->update([
            'required_quantity' => $validated['required_quantity'],
            'waste_percentage' => $validated['waste_percentage'],
            'unit_rate' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'BOM component updated successfully!',
            'data' => $bom->load('rawMaterial'),
        ]);
    }

    /**
     * Delete BOM Item (AJAX).
     */
    public function deleteBom($id)
    {
        if ($res = RolePermissionService::authorizeAction(request(), 'action_delete')) {
            return $res;
        }

        $bom = BillOfMaterial::findOrFail($id);
        $bom->delete();

        return response()->json([
            'success' => true,
            'message' => 'BOM raw material component removed successfully!',
        ]);
    }
}
