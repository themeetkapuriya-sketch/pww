<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SalesOrderResource;
use App\Models\Client;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Setting;
use App\Services\RolePermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * 5.5 Sales Orders / Order Management.
     */
    public function orders(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = SalesOrder::with(['client', 'plant', 'items.product.billOfMaterials.rawMaterial']);

        if ($status !== 'all' && ! empty($status)) {
            if ($status === 'dispatched') {
                $query->whereIn('status', ['dispatched', 'completed']);
            } else {
                $query->where('status', $status);
            }
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20)->appends($request->query());
        $clients = Client::with('plants')->orderBy('company_name')->get();
        $rawFinishedGoods = Product::orderBy('product_name')->get();
        $finishedGoods = ProductResource::collection($rawFinishedGoods);

        $stats = [
            'total' => SalesOrder::count(),
            'pending' => SalesOrder::pending()->count(),
            'in_production' => SalesOrder::inProduction()->count(),
            'ready' => SalesOrder::ready()->count(),
            'completed' => SalesOrder::completed()->count(),
        ];

        $salesOrders360Map = [];
        foreach ($orders as $ord) {
            $salesOrders360Map[$ord->id] = (new SalesOrderResource($ord))->resolve();
        }

        return view('pages.orders', compact('orders', 'clients', 'finishedGoods', 'stats', 'status', 'salesOrders360Map'));
    }

    /**
     * Store Sales Order (AJAX).
     */
    public function storeOrder(Request $request)
    {
        if ($res = RolePermissionService::authorizeAction($request, 'action_insert')) {
            return $res;
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'plant_id' => 'required|exists:client_plants,id',
            'po_number' => 'nullable|string|max:100',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'required|exists:products,id',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'required|numeric|min:0.01',
            'unit_prices' => 'required|array|min:1',
            'unit_prices.*' => 'required|numeric|min:0',
            'billing_uoms' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
        ]);

        $order = DB::transaction(function () use ($validated, $request) {
            $totalAmount = 0.00;
            foreach ($validated['product_ids'] as $idx => $pid) {
                $totalAmount += $validated['quantities'][$idx] * $validated['unit_prices'][$idx];
            }

            $order = SalesOrder::create([
                'order_number' => SalesOrder::generateNextOrderNumber(),
                'po_number' => $validated['po_number'] ?? null,
                'client_id' => $validated['client_id'],
                'plant_id' => $validated['plant_id'],
                'order_date' => $validated['order_date'],
                'delivery_date' => $validated['delivery_date'] ?? null,
                'status' => 'pending',
                'total_amount' => round($totalAmount, 2),
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['product_ids'] as $idx => $pid) {
                $buom = isset($request->billing_uoms[$idx]) ? $request->billing_uoms[$idx] : 'Pcs';
                SalesOrderItem::create([
                    'sales_order_id' => $order->id,
                    'product_id' => $pid,
                    'billing_uom' => $buom,
                    'quantity' => $validated['quantities'][$idx],
                    'unit_price' => $validated['unit_prices'][$idx],
                    'total_price' => round($validated['quantities'][$idx] * $validated['unit_prices'][$idx], 2),
                ]);
            }

            return $order;
        });

        return response()->json([
            'success' => true,
            'message' => "Sales Order '{$order->order_number}' created successfully!",
            'data' => $order,
        ]);
    }

    /**
     * Update Sales Order (AJAX).
     */
    public function updateOrder(Request $request, $id)
    {
        if ($res = RolePermissionService::authorizeAction($request, 'action_update')) {
            return $res;
        }

        $order = SalesOrder::findOrFail($id);

        if (in_array($order->status, ['dispatched', 'completed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Dispatched/Completed Sales Orders cannot be edited.',
            ], 422);
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'plant_id' => 'required|exists:client_plants,id',
            'po_number' => 'nullable|string|max:100',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'required|exists:products,id',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'required|numeric|min:0.01',
            'unit_prices' => 'required|array|min:1',
            'unit_prices.*' => 'required|numeric|min:0',
            'billing_uoms' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($order, $validated, $request) {
            $totalAmount = 0.00;
            foreach ($validated['product_ids'] as $idx => $pid) {
                $totalAmount += $validated['quantities'][$idx] * $validated['unit_prices'][$idx];
            }

            $order->update([
                'po_number' => $validated['po_number'] ?? null,
                'client_id' => $validated['client_id'],
                'plant_id' => $validated['plant_id'],
                'order_date' => $validated['order_date'],
                'delivery_date' => $validated['delivery_date'] ?? null,
                'total_amount' => round($totalAmount, 2),
                'notes' => $validated['notes'] ?? null,
            ]);

            // Replace line items
            $order->items()->delete();

            foreach ($validated['product_ids'] as $idx => $pid) {
                $buom = isset($request->billing_uoms[$idx]) ? $request->billing_uoms[$idx] : 'Pcs';
                SalesOrderItem::create([
                    'sales_order_id' => $order->id,
                    'product_id' => $pid,
                    'billing_uom' => $buom,
                    'quantity' => $validated['quantities'][$idx],
                    'unit_price' => $validated['unit_prices'][$idx],
                    'total_price' => round($validated['quantities'][$idx] * $validated['unit_prices'][$idx], 2),
                ]);
            }

            $order->autoPromoteIfStockAvailable();
        });

        return response()->json([
            'success' => true,
            'message' => "Sales Order '{$order->order_number}' updated successfully!",
            'data' => $order,
        ]);
    }

    /**
     * Update Sales Order Status (AJAX).
     */
    public function updateOrderStatus(Request $request, $id)
    {
        if ($res = RolePermissionService::authorizeAction($request, 'action_update')) {
            return $res;
        }

        $order = SalesOrder::findOrFail($id);

        if (in_array($order->status, ['dispatched', 'completed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Dispatched / Completed orders cannot be reverted to pending or modified.',
            ], 422);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,in_production,ready_for_dispatch,dispatched,cancelled',
        ]);

        $requestedStatus = $validated['status'];

        $trackStock = Setting::isStockEnabled();

        if ($trackStock && in_array($requestedStatus, ['ready_for_dispatch', 'dispatched', 'completed'])) {
            $deficits = $order->getStockDeficitDetails();
            if (! empty($deficits)) {
                $deficitMsgs = array_map(function ($d) {
                    return "'{$d['product_name']}' (Requires {$d['required_quantity']}, Current Stock: {$d['current_stock']} - Short by {$d['missing_quantity']})";
                }, $deficits);

                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock to mark as '.strtoupper(str_replace('_', ' ', $requestedStatus)).'. Shortage: '.implode('; ', $deficitMsgs),
                ], 422);
            }
        }

        $order->update(['status' => $requestedStatus]);

        if ($trackStock && in_array($requestedStatus, ['dispatched', 'completed'])) {
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->decrement('current_stock', $item->quantity);
                }
            }
        }

        $autoPromoted = false;
        if (in_array($requestedStatus, ['in_production', 'pending'])) {
            $autoPromoted = $order->autoPromoteIfStockAvailable();
        }

        $statusText = strtoupper(str_replace('_', ' ', $order->status));
        $message = "Order '{$order->order_number}' status updated to '{$statusText}'!";
        if ($autoPromoted) {
            $message = "Order '{$order->order_number}' has required stock available & was automatically marked READY FOR DISPATCH!";
        }

        $hasStock = $order->hasSufficientStock();
        $deficits = $trackStock ? $order->getStockDeficitDetails() : [];

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $order,
            'status' => $order->status,
            'has_stock' => $hasStock,
            'track_stock' => $trackStock,
            'deficits' => $deficits,
        ]);
    }

    /**
     * Delete Sales Order (AJAX).
     */
    public function deleteOrder($id)
    {
        if ($res = RolePermissionService::authorizeAction(request(), 'action_delete')) {
            return $res;
        }

        $order = SalesOrder::findOrFail($id);
        $orderNum = $order->order_number;

        DB::transaction(function () use ($order) {
            $order->items()->delete();
            $order->delete();
        });

        return response()->json([
            'success' => true,
            'message' => "Sales Order '{$orderNum}' deleted successfully!",
        ]);
    }

    /**
     * Get 360° Order Details with MRP and FG Stock Status (AJAX API).
     */
    public function orderDetails($id)
    {
        $order = SalesOrder::with(['client', 'plant', 'items.product.billOfMaterials.rawMaterial'])->findOrFail($id);

        return new SalesOrderResource($order);
    }

    /**
     * Display printable Factory Job Card / Work Order (A4 view).
     */
    public function showJobCard($id)
    {
        $order = SalesOrder::with(['client', 'plant', 'items.product.billOfMaterials.rawMaterial'])->findOrFail($id);
        $mrpData = $order->calculateRawMaterialRequirements();
        $fgStatus = $order->getFinishedGoodsStockStatus();

        return view('pages.orders.job_card', compact('order', 'mrpData', 'fgStatus'));
    }
}
