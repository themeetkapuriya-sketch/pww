<?php

namespace App\Services;

use App\Models\SalesOrder;
use Illuminate\Support\Facades\Schema;

class ActiveOrderAlertService
{
    /**
     * Get active sales orders (excluding dispatched, completed, and cancelled) with production progress.
     */
    public static function getActiveOrdersSummary(): array
    {
        try {
            if (! Schema::hasTable('sales_orders') || ! Schema::hasTable('sales_order_items')) {
                return [
                    'total_count' => 0,
                    'in_production_count' => 0,
                    'ready_count' => 0,
                    'pending_count' => 0,
                    'orders' => collect(),
                ];
            }

            $orders = SalesOrder::with(['client.plants', 'plant', 'items.product'])
                ->whereNotIn('status', ['dispatched', 'completed', 'cancelled'])
                ->orderBy('delivery_date', 'asc')
                ->orderBy('id', 'desc')
                ->take(10)
                ->get();

            $totalCount = $orders->count();
            $inProductionCount = $orders->where('status', 'in_production')->count();
            $readyCount = $orders->where('status', 'ready_for_dispatch')->count();
            $pendingCount = $orders->whereIn('status', ['pending', 'confirmed'])->count();

            return [
                'total_count' => $totalCount,
                'in_production_count' => $inProductionCount,
                'ready_count' => $readyCount,
                'pending_count' => $pendingCount,
                'orders' => $orders,
            ];
        } catch (\Throwable $e) {
            return [
                'total_count' => 0,
                'in_production_count' => 0,
                'ready_count' => 0,
                'pending_count' => 0,
                'orders' => collect(),
            ];
        }
    }
}
