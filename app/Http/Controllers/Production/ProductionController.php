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
        if ($res = \App\Services\RolePermissionService::authorizeAction($request, 'action_insert')) return $res;

        $recordedBy = $request->input('recorded_by', auth()->id());
        if (!$recordedBy) {
            $firstUser = User::first();
            $recordedBy = $firstUser ? $firstUser->id : 1;
        }

        // Multi-Item Batch Submission Support
        if ($request->has('items') && is_array($request->input('items')) && count($request->input('items')) > 0) {
            $validated = $request->validate([
                'production_date' => 'required|date',
                'recorded_by' => 'nullable|exists:users,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity_manufactured' => 'required|integer|min:1',
                'items.*.quantity_rejected' => 'nullable|integer|min:0',
                'labor' => 'nullable|array',
            ]);

            $loggedCount = 0;
            $logs = [];

            try {
                \Illuminate\Support\Facades\DB::beginTransaction();

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

                foreach ($validated['items'] as $item) {
                    $prodId = $item['product_id'];
                    $qtyMfg = (int) $item['quantity_manufactured'];
                    $qtyRej = isset($item['quantity_rejected']) ? (int) $item['quantity_rejected'] : 0;

                    $log = $this->productionService->logProduction(
                        $prodId,
                        $qtyMfg,
                        $qtyRej,
                        $recordedBy,
                        $validated['production_date'],
                        $laborData
                    );

                    $logs[] = $log;
                    $loggedCount++;
                }

                \Illuminate\Support\Facades\DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => "Successfully logged {$loggedCount} production run(s)! Stock auto-deductions processed.",
                    'data' => $logs
                ]);
            } catch (InsufficientStockException $e) {
                \Illuminate\Support\Facades\DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => ['items' => [$e->getMessage()]]
                ], 422);
            } catch (Exception $e) {
                \Illuminate\Support\Facades\DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Execution failed: ' . $e->getMessage(),
                    'errors' => ['items' => ['Execution failed: ' . $e->getMessage()]]
                ], 422);
            }
        }

        // Single Item Fallback
        $productId = $request->input('product_id', $request->input('finished_good_id'));
        $request->merge(['product_id' => $productId]);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity_manufactured' => 'required|integer|min:1',
            'quantity_rejected' => 'nullable|integer|min:0',
            'recorded_by' => 'nullable|exists:users,id',
            'production_date' => 'required|date',
            'labor' => 'nullable|array',
        ]);

        $duplicateCheck = ProductionLog::where('product_id', $validated['product_id'])
            ->where('quantity_manufactured', $validated['quantity_manufactured'])
            ->where('quantity_rejected', $validated['quantity_rejected'])
            ->whereDate('production_date', $validated['production_date'])
            ->exists();

        if ($duplicateCheck) {
            return response()->json([
                'success' => false,
                'message' => 'An identical production log already exists for this product, date, and quantity!',
                'errors' => ['quantity_manufactured' => ['An identical production log already exists for this product, date, and quantity!']]
            ], 422);
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
        if ($res = \App\Services\RolePermissionService::authorizeAction($request, 'action_update')) return $res;

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
        if ($res = \App\Services\RolePermissionService::authorizeAction(request(), 'action_delete')) return $res;

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
