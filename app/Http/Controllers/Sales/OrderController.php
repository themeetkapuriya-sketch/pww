<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Client;
use App\Models\ClientPlant;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * 5.5 Sales Orders / Order Management.
     */
    public function orders(Request $request)
    {
        $orders = SalesOrder::with(['client', 'plant', 'items.product'])->orderBy('created_at', 'desc')->paginate(20);
        $clients = Client::with('plants')->orderBy('company_name')->get();
        $finishedGoods = Product::orderBy('product_name')->get();
        return view('pages.orders', compact('orders', 'clients', 'finishedGoods'));
    }

    /**
     * Store Sales Order (AJAX).
     */
    public function storeOrder(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'plant_id' => 'required|exists:client_plants,id',
            'po_number' => 'nullable|string|max:100',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'required|exists:products,id',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'required|integer|min:1',
            'unit_prices' => 'required|array|min:1',
            'unit_prices.*' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $order = DB::transaction(function () use ($validated) {
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
                SalesOrderItem::create([
                    'sales_order_id' => $order->id,
                    'product_id' => $pid,
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
            'data' => $order
        ]);
    }

    /**
     * Update Sales Order Status (AJAX).
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $order = SalesOrder::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,in_production,ready_for_dispatch,dispatched,cancelled',
        ]);

        $order->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => "Order '{$order->order_number}' status updated to '" . strtoupper(str_replace('_', ' ', $order->status)) . "'!",
            'data' => $order
        ]);
    }

    /**
     * Delete Sales Order (AJAX).
     */
    public function deleteOrder($id)
    {
        $order = SalesOrder::findOrFail($id);
        $orderNum = $order->order_number;

        DB::transaction(function () use ($order) {
            $order->items()->delete();
            $order->delete();
        });

        return response()->json([
            'success' => true,
            'message' => "Sales Order '{$orderNum}' deleted successfully!"
        ]);
    }
}
