@extends('layouts.app')

@section('title', 'Products Catalog')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Products Catalog</h1>
            <p class="text-sm text-slate-500">Audit and catalog completed products.</p>
        </div>
    </div>

    <!-- 1. Add Product Form -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Add Product
        </h3>
        <form action="{{ route('inventory.goods.store') }}" method="POST" class="ajax-form space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Product Model Name</label>
                    <input type="text" name="product_name" placeholder="e.g. Balaji Wire Rack 3-Tier" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">SKU Code (Unique)</label>
                    <input type="text" name="sku" placeholder="e.g. WR-3T-BALAJI" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Opening Stock Inventory</label>
                    <input type="number" name="current_stock" min="0" placeholder="e.g. 50" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Corporate Selling Price (Excl. Tax)</label>
                    <input type="number" name="selling_price" step="0.01" min="0" placeholder="e.g. 1850.00" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>
            </div>

            <button type="submit" class="btn-primary py-2.5 px-6 text-sm font-bold">
                Save Product
            </button>
        </form>
    </div>

    <!-- 2. Products Catalog Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Products Catalog
        </h3>
        
        <div class="overflow-x-auto">
            <table class="erp-datatable min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-[#4371D7] text-white divide-x divide-white/25">
                    <tr>
                        <th class="px-4 py-3.5 text-center text-xs font-bold uppercase w-12">#</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Product Name</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">SKU</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Current Stock</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Selling Price</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach ($finishedGoods as $good)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-4 text-center font-bold text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $good->product_name }}</td>
                            <td class="px-6 py-4 text-slate-600 font-medium text-xs">{{ $good->sku }}</td>
                            <td class="px-6 py-4 text-right font-bold text-slate-800">{{ number_format($good->current_stock) }} units</td>
                            <td class="px-6 py-4 text-right text-slate-700">₹{{ number_format($good->selling_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $finishedGoods->links() }}
        </div>
    </div>
</div>
@endsection
