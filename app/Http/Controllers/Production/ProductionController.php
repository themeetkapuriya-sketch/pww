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
            'recorded_by' => 'required|exists:users,id',
            'production_date' => 'required|date',
            'labor' => 'nullable|array',
        ]);

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
                $validated['recorded_by'],
                $validated['production_date'],
                $laborData
            );

            return response()->json([
                'success' => true,
                'message' => "Production batch {$log->id} logged. Stock auto-deductions processed!",
                'data' => $log
            ]);
        } catch (InsufficientStockException $e) {
            return response()->json([
                'success' => false,
                'errors' => [$e->getMessage()]
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Execution failed: ' . $e->getMessage()]
            ], 500);
        }
    }
}
