@extends('layouts.app')

@section('title', 'Products Catalog')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Products Catalog</h1>
            <p class="text-sm text-slate-500">Audit and catalog completed products.</p>
        </div>
        <div>
            <button type="button" onclick="toggleProductForm()" class="btn-primary py-2.5 px-5 text-xs font-bold shadow-xs flex items-center">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span id="btnProductToggleText">Add Product</span>
            </button>
        </div>
    </div>

    <!-- 1. COLLAPSIBLE PRODUCT FORM -->
    <div id="productCard" class="hidden bg-white rounded-2xl shadow-sm border border-slate-200 p-6 transition-all duration-300">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100/60">
            <h3 id="productTitle" class="text-base font-bold text-slate-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-[#4371D7]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Add Product
            </h3>
            <button type="button" id="productCloseBtn" onclick="toggleProductForm(false)" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition cursor-pointer">&times; Close</button>
        </div>

        <form id="productForm" action="{{ route('inventory.goods.store') }}" method="POST" class="ajax-form space-y-4">
            @csrf
            <input type="hidden" name="_method" id="product_form_method" value="POST">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Product Model Name</label>
                    <input type="text" id="good_name" name="product_name" placeholder="e.g. Balaji Wire Rack 3-Tier" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">SKU Code (Unique)</label>
                    <input type="text" id="good_sku" name="sku" placeholder="e.g. WR-3T-BALAJI" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">HSN Code</label>
                    <input type="text" id="good_hsn" name="hsn_code" placeholder="e.g. 73089090"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">UOM (Primary Unit)</label>
                    <input type="text" id="good_uom" name="uom" placeholder="e.g. piece, kg, box" value="piece" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Unit Weight (Kg/Pcs)</label>
                    <input type="number" id="good_unit_weight_kg" name="unit_weight_kg" step="0.001" min="0" placeholder="e.g. 14.500" value="0.000"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Price / Piece (₹)</label>
                    <input type="number" id="good_price" name="selling_price" step="0.01" min="0" placeholder="e.g. 1850.00" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Price / Kg (₹)</label>
                    <input type="number" id="good_price_per_kg" name="price_per_kg" step="0.01" min="0" placeholder="Optional (e.g. 125.00)"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">GST Rate (%)</label>
                    <select id="good_gst_rate" name="gst_rate" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                        <option value="18.00" selected>18% (Standard GST)</option>
                        <option value="12.00">12% (Reduced Tax)</option>
                        <option value="5.00">5% (Essential Goods)</option>
                        <option value="28.00">28% (Luxury / Heavy Equipment)</option>
                        <option value="0.00">0% (Exempt Goods)</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-2">
                <button type="button" onclick="toggleProductForm(false)" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold py-2.5 px-5 rounded-xl transition cursor-pointer">Cancel</button>
                <button type="submit" id="productSubmitBtn" class="btn-primary py-2.5 px-6 text-sm font-bold bg-[#2563EB] hover:bg-blue-700 text-white rounded-xl shadow-xs">
                    Save Product
                </button>
            </div>
        </form>
    </div>

    <!-- 2. RECORDS LIST UNDERNEATH -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Products Catalog
        </h3>
        
        <div class="overflow-x-auto w-full max-w-full">
            <table class="erp-datatable min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-[#EDF4FA] text-black divide-x divide-slate-200">
                    <tr>
                        <th class="px-4 py-3.5 text-center text-xs font-bold uppercase w-12">#</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Product Name</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">SKU</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">HSN Code</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">UOM</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">Unit Weight</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">GST Rate</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Current Stock</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Selling Prices</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase w-28">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($finishedGoods as $good)
                        <tr class="hover:bg-slate-50 transition" id="row-good-{{ $good->id }}">
                            <td class="px-4 py-4 text-center font-bold text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $good->product_name }}</td>
                            <td class="px-6 py-4 text-slate-600 font-medium text-xs">{{ $good->sku }}</td>
                            <td class="px-6 py-4 text-center text-slate-600 font-mono text-xs">{{ $good->hsn_code ?? '-' }}</td>
                            <td class="px-6 py-4 text-center text-slate-600 capitalize text-xs font-semibold">{{ $good->uom }}</td>
                            <td class="px-6 py-4 text-center text-slate-600 font-mono text-xs">{{ number_format($good->unit_weight_kg, 3) }} kg</td>
                            <td class="px-6 py-4 text-center text-slate-600 font-mono text-xs font-semibold">{{ number_format($good->gst_rate, 0) }}%</td>
                            <td class="px-6 py-4 text-right font-bold text-blue-700 font-mono text-xs">{{ number_format($good->current_stock) }} {{ $good->uom }}</td>
                            <td class="px-6 py-4 text-right text-slate-800 font-mono text-xs">
                                <span class="font-bold">₹{{ number_format($good->selling_price, 2) }}</span>/pcs
                                @if(!empty($good->price_per_kg))
                                    <span class="block text-[10px] text-slate-500">(₹{{ number_format($good->price_per_kg, 2) }}/kg)</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <button type="button" 
                                            onclick="openEditProductModal({{ $good->id }}, '{{ addslashes($good->product_name) }}', '{{ $good->sku }}', '{{ $good->hsn_code ?? '' }}', '{{ $good->uom }}', {{ $good->current_stock }}, {{ $good->selling_price }}, {{ $good->gst_rate }}, {{ $good->unit_weight_kg }}, {{ $good->price_per_kg ?? 'null' }})"
                                            class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-xs transition duration-150 transform hover:scale-105"
                                            title="Edit Product Details">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    <button type="button" 
                                            onclick="deleteProduct({{ $good->id }}, '{{ addslashes($good->product_name) }}')"
                                            class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-rose-500 hover:bg-rose-600 text-white shadow-xs transition duration-150 transform hover:scale-105"
                                            title="Delete Product">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="10" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="w-10 h-10 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <p class="text-sm font-bold text-slate-600">No Records Found</p>
                                    <p class="text-xs text-slate-400">There are no products recorded in the catalog yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function resetProductForm() {
    const form = document.getElementById('productForm');
    if (!form) return;
    form.action = "{{ route('inventory.goods.store') }}";
    document.getElementById('product_form_method').value = "POST";
    
    const card = document.getElementById('productCard');
    if (card) card.className = 'bg-white rounded-2xl shadow-sm border border-slate-200 p-6 transition-all duration-300';

    document.getElementById('productTitle').className = 'text-base font-bold text-slate-800 flex items-center';
    document.getElementById('productTitle').innerHTML = `
        <svg class="w-5 h-5 mr-2 text-[#4371D7]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        Add Product
    `;
    document.getElementById('productCloseBtn').className = 'text-xs font-bold text-slate-400 hover:text-slate-600';

    document.querySelectorAll('#productForm input, #productForm select').forEach(el => {
        if (el.type !== 'hidden') {
            el.className = 'w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium';
        }
    });

    document.getElementById('good_name').value = '';
    document.getElementById('good_sku').value = '';
    document.getElementById('good_hsn').value = '';
    document.getElementById('good_uom').value = 'piece';
    if (document.getElementById('good_unit_weight_kg')) document.getElementById('good_unit_weight_kg').value = '0.000';
    document.getElementById('good_price').value = '';
    if (document.getElementById('good_price_per_kg')) document.getElementById('good_price_per_kg').value = '';
    if (document.getElementById('good_gst_rate')) document.getElementById('good_gst_rate').value = '18.00';
    
    const btn = document.getElementById('productSubmitBtn');
    btn.innerText = 'Save Product';
    btn.className = 'btn-primary py-2.5 px-6 text-sm font-bold bg-[#2563EB] hover:bg-blue-700 text-white rounded-xl shadow-xs';
}

function toggleProductForm(showExplicit = null) {
    const card = document.getElementById('productCard');
    if (!card) return;
    const isHidden = card.classList.contains('hidden');
    const shouldShow = showExplicit !== null ? showExplicit : isHidden;
    
    if (shouldShow) {
        if (isHidden) resetProductForm();
        card.classList.remove('hidden');
        card.scrollIntoView({ behavior: 'smooth' });
    } else {
        card.classList.add('hidden');
    }
}

function openEditProductModal(id, name, sku, hsn, uom, stock, price, gstRate = 18.00, unitWeight = 0.000, pricePerKg = '') {
    const card = document.getElementById('productCard');
    if (!card) return;

    document.getElementById('productForm').action = `/inventory/goods/${id}`;
    document.getElementById('product_form_method').value = "PUT";
    
    card.className = 'bg-[#FFFDF5] rounded-2xl shadow-sm border-2 border-amber-300 p-6 transition-all duration-300';
    document.getElementById('productTitle').className = 'text-base font-bold text-amber-900 flex items-center';
    document.getElementById('productTitle').innerHTML = `Edit Product Details`;
    document.getElementById('productCloseBtn').className = 'text-xs font-bold text-amber-700 hover:text-amber-900';

    document.querySelectorAll('#productForm input, #productForm select').forEach(el => {
        if (el.type !== 'hidden') {
            el.className = 'w-full bg-white border border-amber-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700 font-medium';
        }
    });

    document.getElementById('good_name').value = name;
    document.getElementById('good_sku').value = sku;
    document.getElementById('good_hsn').value = hsn;
    document.getElementById('good_uom').value = uom;
    if (document.getElementById('good_unit_weight_kg')) document.getElementById('good_unit_weight_kg').value = parseFloat(unitWeight).toFixed(3);
    document.getElementById('good_price').value = price;
    if (document.getElementById('good_price_per_kg')) document.getElementById('good_price_per_kg').value = (pricePerKg !== '' && pricePerKg !== null && pricePerKg !== undefined) ? parseFloat(pricePerKg).toFixed(2) : '';
    if (document.getElementById('good_gst_rate')) document.getElementById('good_gst_rate').value = parseFloat(gstRate).toFixed(2);
    
    const btn = document.getElementById('productSubmitBtn');
    btn.innerText = 'Update Product';
    btn.className = 'btn-primary py-2.5 px-6 text-sm font-bold bg-[#2563EB] hover:bg-blue-700 text-white rounded-xl shadow-xs';

    card.classList.remove('hidden');
    card.scrollIntoView({ behavior: 'smooth' });
}

function deleteProduct(id, name) {
    window.confirmDelete(
        'Delete Product?',
        `Are you sure you want to delete Product '${name}'? This action cannot be undone.`,
        function() {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            $.ajax({
                url: `/inventory/goods/${id}`,
                method: 'DELETE',
                data: { _token: token },
                success: function(res) {
                    if (window.showToast) window.showToast('success', res.message);
                    $(`#row-good-${id}`).fadeOut(300, function() { $(this).remove(); });
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.message || 'Failed to delete product.');
                }
            });
        }
    );
}
</script>
@endsection
