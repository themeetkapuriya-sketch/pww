<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\AuditLogService;
use App\Services\RolePermissionService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Products Catalog Page.
     */
    public function index(Request $request)
    {
        $finishedGoods = Product::orderBy('product_name')->paginate(20);

        return view('pages.product', compact('finishedGoods'));
    }

    /**
     * Create Finished Good Product (AJAX).
     */
    public function store(Request $request)
    {
        if ($res = RolePermissionService::authorizeAction($request, 'action_insert')) {
            return $res;
        }

        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku|max:100',
            'hsn_code' => 'required|string|max:50',
            'uom' => 'required|string|max:50',
            'unit_weight_kg' => 'nullable|numeric|min:0',
            'current_stock' => 'nullable|integer|min:0',
            'safety_threshold' => 'nullable|integer|min:0',
            'selling_price' => 'required|numeric|min:0',
            'price_per_kg' => 'nullable|numeric|min:0',
            'gst_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $validated['current_stock'] = $request->input('current_stock', 0);
        $validated['safety_threshold'] = (int) $request->input('safety_threshold', 10);
        $validated['alerts_enabled'] = $validated['safety_threshold'] > 0;
        $validated['uom'] = $request->input('uom', 'piece');
        $validated['unit_weight_kg'] = $request->input('unit_weight_kg', 0.000);
        $validated['price_per_kg'] = $request->filled('price_per_kg') ? $request->input('price_per_kg') : null;
        $validated['gst_rate'] = $request->input('gst_rate', 18.00);

        $existingGood = Product::where('product_name', $validated['product_name'])->first();
        if ($existingGood) {
            return response()->json([
                'success' => false,
                'message' => "A product with name '{$validated['product_name']}' already exists in the catalog!",
                'errors' => ['product_name' => ["A product with name '{$validated['product_name']}' already exists in the catalog!"]],
            ], 422);
        }

        $good = Product::create($validated);

        AuditLogService::log('Inventory', 'created', "Cataloged new product '{$good->product_name}' (SKU: {$good->sku}) at ₹".number_format($good->selling_price, 2));

        return response()->json([
            'success' => true,
            'message' => "Product '{$good->product_name}' (GST {$good->gst_rate}%) cataloged successfully!",
            'data' => $good,
        ]);
    }

    /**
     * Update Finished Good Product (AJAX).
     */
    public function update(Request $request, $id)
    {
        if ($res = RolePermissionService::authorizeAction($request, 'action_update')) {
            return $res;
        }

        $good = Product::findOrFail($id);

        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku,'.$id,
            'hsn_code' => 'required|string|max:50',
            'uom' => 'required|string|max:50',
            'unit_weight_kg' => 'nullable|numeric|min:0',
            'current_stock' => 'nullable|integer|min:0',
            'safety_threshold' => 'nullable|integer|min:0',
            'selling_price' => 'required|numeric|min:0',
            'price_per_kg' => 'nullable|numeric|min:0',
            'gst_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        if (! array_key_exists('current_stock', $validated) || is_null($validated['current_stock'])) {
            unset($validated['current_stock']);
        }
        if ($request->has('safety_threshold')) {
            $validated['safety_threshold'] = (int) $request->input('safety_threshold', 10);
            $validated['alerts_enabled'] = $validated['safety_threshold'] > 0;
        }
        if (! isset($validated['gst_rate'])) {
            $validated['gst_rate'] = 18.00;
        }
        if (! isset($validated['unit_weight_kg'])) {
            $validated['unit_weight_kg'] = 0.000;
        }
        $validated['price_per_kg'] = $request->filled('price_per_kg') ? $request->input('price_per_kg') : null;

        $oldPrice = $good->selling_price;
        $good->update($validated);

        $desc = "Updated product '{$good->product_name}'";
        if ((float) $oldPrice !== (float) $good->selling_price) {
            $desc .= ' selling price from ₹'.number_format($oldPrice, 2).' to ₹'.number_format($good->selling_price, 2);
        }

        AuditLogService::log('Inventory', 'updated', $desc);

        return response()->json([
            'success' => true,
            'message' => "Product '{$good->product_name}' updated successfully!",
            'data' => $good,
        ]);
    }

    /**
     * Delete Finished Good Product (AJAX).
     */
    public function destroy($id)
    {
        if ($res = RolePermissionService::authorizeAction(request(), 'action_delete')) {
            return $res;
        }

        $good = Product::findOrFail($id);
        $name = $good->product_name;
        $good->delete();

        AuditLogService::log('Inventory', 'deleted', "Deleted product '{$name}' from catalog.");

        return response()->json([
            'success' => true,
            'message' => "Product '{$name}' deleted successfully!",
        ]);
    }
}
