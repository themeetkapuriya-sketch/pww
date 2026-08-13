<?php

namespace App\Services;

use App\Models\Product;
use App\Models\RawMaterial;
use Illuminate\Support\Facades\Schema;

class InventoryAlertService
{
    /**
     * Get summary of low stock items across Raw Materials and Products.
     */
    public static function getLowStockSummary(): array
    {
        try {
            if (! Schema::hasTable('raw_materials') || ! Schema::hasTable('products')) {
                return ['total_count' => 0, 'raw_materials' => [], 'products' => []];
            }

            // 1. Fetch low raw materials (where current_stock <= safety_threshold or <= 50kg if threshold not set)
            $lowRawMaterials = RawMaterial::where(function ($query) {
                $query->whereRaw('current_stock <= safety_threshold')
                    ->orWhere(function ($sub) {
                        $sub->where('safety_threshold', '<=', 0)
                            ->where('current_stock', '<=', 50);
                    });
            })
                ->orderBy('current_stock', 'asc')
                ->take(6)
                ->get();

            // 2. Fetch low finished products (where alerts_enabled is true, safety_threshold > 0, and current_stock <= safety_threshold)
            $lowProducts = Product::where('alerts_enabled', true)
                ->where(function ($q) {
                    $q->whereRaw('current_stock <= safety_threshold')
                        ->where('safety_threshold', '>', 0);
                })
                ->orderBy('current_stock', 'asc')
                ->take(6)
                ->get();

            $totalCount = $lowRawMaterials->count() + $lowProducts->count();

            return [
                'total_count' => $totalCount,
                'raw_materials' => $lowRawMaterials,
                'products' => $lowProducts,
            ];
        } catch (\Throwable $e) {
            return ['total_count' => 0, 'raw_materials' => [], 'products' => []];
        }
    }
}
