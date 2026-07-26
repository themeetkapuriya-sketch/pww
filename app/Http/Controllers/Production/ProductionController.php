<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductionLog;
use App\Models\Product;
use App\Models\StaffProfile;
use App\Models\User;
use App\Services\ProductionService;
use App\Exceptions\InsufficientStockException;
use Exception;

class ProductionController extends Controller
{
    protected $productionService;

    public function __construct(ProductionService $productionService)
    {
        $this->productionService = $productionService;
    }

    /**
     * 4. Production Logs.
     */
    public function production()
    {
        $productionLogs = ProductionLog::with('product', 'recordedByUser')->orderBy('production_date', 'desc')->paginate(20);
        $finishedGoods = Product::all();
        $staffProfiles = StaffProfile::all();
        $users = User::all();
        return view('pages.production', compact('productionLogs', 'finishedGoods', 'staffProfiles', 'users'));
    }

    /**
     * Log a production run (AJAX).
     */
    public function logProduction(Request $request)
    {
        $productId = $request->input('product_id', $request->input('finished_good_id'));
        $request->merge(['product_id' => $productId]);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity_manufactured' => 'required|integer|min:1',
            'quantity_rejected' => 'required|integer|min:0',
            'recorded_by' => 'nullable|exists:users,id',
            'production_date' => 'required|date',
            'labor' => 'nullable|array',
        ]);

        $recordedBy = !empty($validated['recorded_by']) ? $validated['recorded_by'] : auth()->id();
        if (!$recordedBy) {
            $firstUser = User::first();
            $recordedBy = $firstUser ? $firstUser->id : 1;
        }

        try {
            $laborData = [];
            if (!empty($validated['labor'])) {
                foreach ($validated['labor'] as $profileId => $units) {
                    if ($units > 0) {
                        $laborData[] = [
                            'staff_profile_id' => $profileId,
                            'units_completed' => (int) $units
                        ];
                    }
                }
            }

            $log = $this->productionService->logProduction(
                $validated['product_id'],
                $validated['quantity_manufactured'],
                $validated['quantity_rejected'],
                $recordedBy,
                $validated['production_date'],
                $laborData
            );

            return response()->json([
                'success' => true,
                'message' => "Production batch #{$log->id} logged. Stock auto-deductions processed!",
                'data' => $log
            ]);
        } catch (InsufficientStockException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => [
                    'quantity_manufactured' => [$e->getMessage()]
                ]
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Execution failed: ' . $e->getMessage(),
                'errors' => [
                    'quantity_manufactured' => ['Execution failed: ' . $e->getMessage()]
                ]
            ], 422);
        }
    }

    /**
     * Update Production Log (AJAX).
     */
    public function updateProductionLog(Request $request, $id)
    {
        $log = ProductionLog::findOrFail($id);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity_manufactured' => 'required|integer|min:1',
            'quantity_rejected' => 'required|integer|min:0',
            'production_date' => 'required|date',
        ]);

        // Adjust finished product stock difference
        if ($log->product_id == $validated['product_id']) {
            $diff = $validated['quantity_manufactured'] - $log->quantity_manufactured;
            if ($diff != 0) {
                $product = Product::find($validated['product_id']);
                if ($product) {
                    $product->current_stock = max(0, $product->current_stock + $diff);
                    $product->save();
                }
            }
        }

        $log->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Production batch #{$log->id} updated successfully!",
            'data' => $log
        ]);
    }

    /**
     * Delete Production Log (AJAX).
     */
    public function deleteProductionLog($id)
    {
        $log = ProductionLog::findOrFail($id);
        $batchId = $log->id;

        // Deduct manufactured qty from product stock upon deletion
        $product = Product::find($log->product_id);
        if ($product) {
            $product->current_stock = max(0, $product->current_stock - $log->quantity_manufactured);
            $product->save();
        }

        $log->laborLogs()->delete();
        $log->delete();

        return response()->json([
            'success' => true,
            'message' => "Production batch #{$batchId} deleted successfully!"
        ]);
    }
}
