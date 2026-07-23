@extends('layouts.app')

@section('title', 'Products Catalog')

@section('content')
<div class="space-y-6">
    <!-- Page Header with Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight flex items-center">
                <svg class="w-6 h-6 mr-2.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                Products Catalog
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-1">Audit, catalog, and manage completed factory products and corporate selling prices.</p>
        </div>
        <div>
            <button type="button" onclick="toggleCreateProductForm()" 
                    class="btn-primary py-2.5 px-5 text-xs font-bold flex items-center shadow-xs">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                + Add Product
            </button>
        </div>
    </div>

    <!-- INLINE EXPANDABLE FORM 1: Add Product -->
    <div id="createProductFormCard" class="hidden bg-white rounded-2xl shadow-md border-2 border-blue-500/30 p-6 transition-all duration-300">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
            <h3 class="text-base font-bold text-slate-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Catalog New Product
            </h3>
            <button type="button" onclick="toggleCreateProductForm()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times; Close</button>
        </div>
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

            <div class="flex items-center justify-end space-x-3 pt-2">
                <button type="button" onclick="toggleCreateProductForm()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800">Cancel</button>
                <button type="submit" class="btn-primary py-2 px-6 text-xs font-bold">Save Product</button>
            </div>
        </form>
    </div>

    <!-- INLINE EXPANDABLE FORM 2: Edit Product -->
    <div id="editProductFormCard" class="hidden bg-amber-50/70 rounded-2xl shadow-md border-2 border-amber-400 p-6 transition-all duration-300">
        <div class="flex items-center justify-between border-b border-amber-200/80 pb-4 mb-4">
            <h3 class="text-base font-bold text-amber-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Edit Product Details
            </h3>
            <button type="button" onclick="closeEditProductForm()" class="text-amber-700 hover:text-amber-900 text-lg font-bold">&times; Close</button>
        </div>
        <form id="editProductForm" method="POST" class="ajax-form space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Product Model Name</label>
                    <input type="text" name="product_name" id="edit_product_name" required
                           class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">SKU Code (Unique)</label>
                    <input type="text" name="sku" id="edit_product_sku" required
                           class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Opening Stock Inventory</label>
                    <input type="number" name="current_stock" id="edit_product_stock" min="0" required
                           class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Corporate Selling Price (Excl. Tax)</label>
                    <input type="number" name="selling_price" id="edit_product_price" step="0.01" min="0" required
                           class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700">
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-2">
                <button type="button" onclick="closeEditProductForm()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800">Cancel</button>
                <button type="submit" class="btn-primary py-2 px-6 text-xs font-bold">Update Product</button>
            </div>
        </form>
    </div>

    <!-- Products Catalog Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Products Catalog Ledger
        </h3>
        
        <div class="overflow-x-auto">
            <table class="erp-datatable min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-[#4371D7] text-white divide-x divide-white/25">
                    <tr>
                        <th class="px-4 py-3.5 text-center text-xs font-bold uppercase w-12">#</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Product Name</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">SKU Code</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Current Stock</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Selling Price</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach ($finishedGoods as $good)
                        <tr class="hover:bg-slate-50 transition" id="row-prod-{{ $good->id }}">
                            <td class="px-4 py-4 text-center font-bold text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $good->product_name }}</td>
                            <td class="px-6 py-4 text-slate-600 font-medium text-xs font-mono">{{ $good->sku }}</td>
                            <td class="px-6 py-4 text-right font-bold text-slate-800">{{ number_format($good->current_stock) }} units</td>
                            <td class="px-6 py-4 text-right text-slate-700 font-semibold">₹{{ number_format($good->selling_price, 2) }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button type="button" 
                                            title="Edit Product"
                                            onclick="openEditProductForm({{ $good->id }}, '{{ addslashes($good->product_name) }}', '{{ addslashes($good->sku) }}', '{{ $good->current_stock }}', '{{ $good->selling_price }}')"
                                            class="w-8 h-8 p-1.5 inline-flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-xs transition duration-150 transform hover:scale-105">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button type="button" 
                                            title="Delete Product"
                                            onclick="deleteProduct({{ $good->id }}, '{{ addslashes($good->product_name) }}')"
                                            class="w-8 h-8 p-1.5 inline-flex items-center justify-center rounded-lg bg-rose-500 hover:bg-rose-600 text-white shadow-xs transition duration-150 transform hover:scale-105">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
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

<script>
function toggleCreateProductForm() {
    var editCard = document.getElementById('editProductFormCard');
    if (editCard) editCard.classList.add('hidden');
    var card = document.getElementById('createProductFormCard');
    if (card) {
        if (card.classList.contains('hidden')) {
            if (window.resetFormAndErrors) window.resetFormAndErrors('#createProductFormCard form');
            card.classList.remove('hidden');
            card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            if (window.resetFormAndErrors) window.resetFormAndErrors('#createProductFormCard form');
            card.classList.add('hidden');
        }
    }
}

function openEditProductForm(id, name, sku, stock, price) {
    var createCard = document.getElementById('createProductFormCard');
    if (createCard) createCard.classList.add('hidden');

    var form = document.getElementById('editProductForm');
    if (form) form.action = "{{ url('/inventory/goods') }}/" + id;
    if (window.resetFormAndErrors) window.resetFormAndErrors('#editProductFormCard form');

    var nameEl = document.getElementById('edit_product_name');
    if (nameEl) nameEl.value = name;
    var skuEl = document.getElementById('edit_product_sku');
    if (skuEl) skuEl.value = sku;
    var stockEl = document.getElementById('edit_product_stock');
    if (stockEl) stockEl.value = stock;
    var priceEl = document.getElementById('edit_product_price');
    if (priceEl) priceEl.value = price;

    var editCard = document.getElementById('editProductFormCard');
    if (editCard) {
        editCard.classList.remove('hidden');
        editCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function closeEditProductForm() {
    if (window.resetFormAndErrors) window.resetFormAndErrors('#editProductFormCard form');
    var editCard = document.getElementById('editProductFormCard');
    if (editCard) editCard.classList.add('hidden');
}

function deleteProduct(id, name) {
    window.confirmDelete(
        "Delete Product?",
        "Are you sure you want to delete product '" + name + "'?",
        function() {
            $.ajax({
                url: "{{ url('/inventory/goods') }}/" + id,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
                },
                success: function(res) {
                    if (res.success) {
                        $('#row-prod-' + id).fadeOut(300, function() { $(this).remove(); });
                        if (window.showToast) window.showToast('success', res.message);
                    }
                },
                error: function(err) {
                    if (window.showToast) window.showToast('error', 'Failed to delete product.');
                }
            });
        }
    );
}
</script>
@endsection
