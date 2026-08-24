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

        <form id="productForm" action="{{ route('inventory.goods.store') }}" method="POST" class="ajax-form space-y-4" data-redirect="/product">
            @csrf
            <input type="hidden" name="_method" id="product_form_method" value="POST">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Product Model Name</label>
                    <input type="text" id="good_name" name="product_name" placeholder="e.g. Balaji Wire Rack 3-Tier" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">SKU Code <span class="text-slate-400 font-normal lowercase">(optional)</span></label>
                    <input type="text" id="good_sku" name="sku" placeholder="e.g. WR-3T-BALAJI (Optional)"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">HSN Code</label>
                    <input type="text" id="good_hsn" name="hsn_code" placeholder="e.g. 73089090"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">UOM (Unit) <span class="text-rose-500">*</span></label>
                    <select id="good_uom" name="uom" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium cursor-pointer">
                        <option value="pcs" selected>Pieces (pcs)</option>
                        <option value="kg">Kilograms (kg)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Weight (Kg/Pcs)</label>
                    <input type="number" id="good_unit_weight_kg" name="unit_weight_kg" step="0.001" min="0" placeholder="e.g. 14.500" value="0.000"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Price / Pcs (₹)</label>
                    <input type="number" id="good_price" name="selling_price" step="0.01" min="0" placeholder="e.g. 1850.00" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Price / Kg (₹)</label>
                    <input type="number" id="good_price_per_kg" name="price_per_kg" step="0.01" min="0" placeholder="Optional (125)"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">GST Rate (%)</label>
                    <select id="good_gst_rate" name="gst_rate" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                        <option value="18.00" selected>18% (Standard)</option>
                        <option value="12.00">12% (Reduced)</option>
                        <option value="5.00">5% (Essential)</option>
                        <option value="28.00">28% (Luxury)</option>
                        <option value="0.00">0% (Exempt)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Min Stock Alert</label>
                    <input type="number" id="good_safety_threshold" name="safety_threshold" min="0" placeholder="e.g. 10" value="10"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium font-mono"
                           title="Set to 0 to disable alerts for this item">
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
            <svg class="w-5 h-5 mr-2 text-[#4371D7]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            Finished Goods Master Table
        </h3>
        
        <div class="overflow-x-auto w-full max-w-full">
            <table class="erp-datatable divide-y divide-slate-200 text-sm" style="min-width: 1100px; width: 100%;">
                <thead class="bg-[#EDF4FA] text-black divide-x divide-slate-200">
                    <tr>
                        <th class="px-4 py-3.5 text-center text-xs font-bold uppercase w-12">#</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Product Name</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">SKU</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">HSN Code</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">UOM</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">Unit Weight</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">GST Rate</th>
                        @if(\App\Models\Setting::get('track_stock', 'true') === 'true')
                            <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Current Stock</th>
                        @endif
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Selling Prices</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">Last Updated</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase w-28">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($finishedGoods as $good)
                        @php 
                            $trackStock = (\App\Models\Setting::get('track_stock', 'true') === 'true');
                            $threshold = $good->safety_threshold ?? 10;
                            $isLowStock = $trackStock && ($good->current_stock <= $threshold && $threshold > 0);
                        @endphp
                        <tr class="hover:bg-slate-50 transition {{ $isLowStock ? 'bg-rose-50/40' : '' }}" id="row-good-{{ $good->id }}">
                            <td class="px-4 py-4 text-center font-bold text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-800" id="name-cell-{{ $good->id }}">
                                <span class="product-name-text">{{ $good->product_name }}</span>
                                <span class="low-stock-badge ml-1.5 px-1.5 py-0.5 bg-rose-100 text-rose-700 font-bold text-[10px] rounded {{ $isLowStock ? '' : 'hidden' }}">Low Stock</span>
                            </td>
                            <td class="px-6 py-4 text-slate-600 font-medium text-xs">{{ $good->sku ?: '-' }}</td>
                            <td class="px-6 py-4 text-center text-slate-600 font-mono text-xs">{{ $good->hsn_code ?? '-' }}</td>
                            <td class="px-6 py-4 text-center text-slate-600 capitalize text-xs font-semibold">{{ $good->uom }}</td>
                            <td class="px-6 py-4 text-center text-slate-600 font-mono text-xs">{{ number_format($good->unit_weight_kg, 3) }} kg</td>
                            <td class="px-6 py-4 text-center text-slate-600 font-mono text-xs font-semibold">{{ number_format($good->gst_rate, 0) }}%</td>
                            @if(\App\Models\Setting::get('track_stock', 'true') === 'true')
                                <td class="px-6 py-4 text-right font-bold text-blue-700 font-mono text-xs" id="stock-val-{{ $good->id }}">{{ number_format($good->current_stock) }} {{ $good->uom }}</td>
                            @endif
                            <td class="px-6 py-4 text-right text-slate-800 font-mono text-xs">
                                <span class="font-bold">₹{{ number_format($good->selling_price, 2) }}</span>/pcs
                                @if(!empty($good->price_per_kg))
                                    <span class="block text-[10px] text-slate-500">(₹{{ number_format($good->price_per_kg, 2) }}/kg)</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center text-slate-600 text-xs">
                                @if($good->updated_at)
                                    <span class="font-semibold text-slate-700 block">{{ $good->updated_at->format('d M Y') }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $good->updated_at->format('h:i A') }}</span>
                                @else
                                    <span class="text-slate-400 italic">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    @if(\App\Models\Setting::get('track_stock', 'true') === 'true')
                                        <button type="button" 
                                                id="btn-adjust-{{ $good->id }}"
                                                onclick="openStockAdjustmentModal({{ $good->id }}, '{{ addslashes($good->product_name) }}', {{ $good->current_stock }}, '{{ $good->uom }}')"
                                                class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition duration-150 transform hover:scale-105"
                                                title="Adjust Stock Quantity">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                                        </button>
                                    @endif
                                    <button type="button" 
                                            onclick="openEditProductModal({{ $good->id }}, '{{ addslashes($good->product_name) }}', '{{ addslashes($good->sku ?? '') }}', '{{ $good->hsn_code ?? '' }}', '{{ $good->uom }}', {{ $good->current_stock }}, {{ $good->selling_price }}, {{ $good->gst_rate }}, {{ $good->unit_weight_kg }}, {{ $good->price_per_kg ?? 'null' }}, {{ $good->safety_threshold ?? 10 }})"
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
                            <td colspan="11" class="px-6 py-12 text-center text-slate-400">
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
    const goodUomEl = document.getElementById('good_uom');
    if (goodUomEl && goodUomEl.options.length) goodUomEl.selectedIndex = 0;
    if (document.getElementById('good_unit_weight_kg')) document.getElementById('good_unit_weight_kg').value = '0.000';
    document.getElementById('good_price').value = '';
    if (document.getElementById('good_price_per_kg')) document.getElementById('good_price_per_kg').value = '';
    if (document.getElementById('good_gst_rate')) document.getElementById('good_gst_rate').value = '18.00';
    if (document.getElementById('good_safety_threshold')) document.getElementById('good_safety_threshold').value = '10';
    
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

function openEditProductModal(id, name, sku, hsn, uom, stock, price, gstRate = 18.00, unitWeight = 0.000, pricePerKg = '', safetyThreshold = 10) {
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
    document.getElementById('good_sku').value = sku || '';
    document.getElementById('good_hsn').value = hsn;
    const goodUomEl = document.getElementById('good_uom');
    if (goodUomEl && uom) {
        let found = false;
        for (let i = 0; i < goodUomEl.options.length; i++) {
            if (goodUomEl.options[i].value.toLowerCase() === String(uom).toLowerCase()) {
                goodUomEl.selectedIndex = i;
                found = true;
                break;
            }
        }
        if (!found) {
            const opt = document.createElement('option');
            opt.value = uom;
            opt.textContent = uom;
            goodUomEl.appendChild(opt);
            goodUomEl.value = uom;
        }
    }
    if (document.getElementById('good_unit_weight_kg')) document.getElementById('good_unit_weight_kg').value = parseFloat(unitWeight).toFixed(3);
    document.getElementById('good_price').value = price;
    if (document.getElementById('good_price_per_kg')) document.getElementById('good_price_per_kg').value = (pricePerKg !== '' && pricePerKg !== null && pricePerKg !== undefined) ? parseFloat(pricePerKg).toFixed(2) : '';
    if (document.getElementById('good_gst_rate')) document.getElementById('good_gst_rate').value = parseFloat(gstRate).toFixed(2);
    if (document.getElementById('good_safety_threshold')) document.getElementById('good_safety_threshold').value = safetyThreshold;
    
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
                    if (window.ERPTableHelper) {
                        window.ERPTableHelper.removeRow(`#row-good-${id}`);
                    } else {
                        $(`#row-good-${id}`).fadeOut(300, function() { $(this).remove(); });
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Failed to delete product.';
                    if (window.showToast) window.showToast('error', msg);
                    else alert(msg);
                }
            });
        }
    );
}

@if(\App\Models\Setting::get('track_stock', 'true') === 'true')
function openStockAdjustmentModal(id, name, currentStock, uom) {
    document.getElementById('adjustProductId').value = id;
    document.getElementById('modalAdjustProductName').innerText = name;
    document.getElementById('modalAdjustCurrentStock').innerText = `${currentStock} ${uom}`;
    document.getElementById('modalAdjustUom').innerText = uom;
    document.getElementById('adjustQuantityInput').value = currentStock;
    
    // Default to set_total
    const radioSetTotal = document.querySelector('input[name="adjustment_type"][value="set_total"]');
    if (radioSetTotal) radioSetTotal.checked = true;
    updateAdjustmentTypeUI();

    document.getElementById('stockAdjustmentModal').classList.remove('hidden');
}

function closeStockAdjustmentModal() {
    document.getElementById('stockAdjustmentModal').classList.add('hidden');
    document.getElementById('stockAdjustmentForm').reset();
}

function updateAdjustmentTypeUI() {
    const selected = document.querySelector('input[name="adjustment_type"]:checked')?.value || 'set_total';
    const label = document.getElementById('modalQtyLabel');
    const input = document.getElementById('adjustQuantityInput');
    
    document.querySelectorAll('input[name="adjustment_type"]').forEach(radio => {
        const parent = radio.closest('label');
        if (radio.checked) {
            parent.className = 'flex flex-col items-center justify-center p-2.5 border-2 border-blue-600 bg-blue-50/60 rounded-xl cursor-pointer text-center select-none text-xs font-bold text-blue-700 transition shadow-2xs';
        } else {
            parent.className = 'flex flex-col items-center justify-center p-2.5 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 text-center select-none text-xs font-bold text-slate-700 transition';
        }
    });

    if (selected === 'set_total') {
        if (label) label.innerText = 'New Total Stock Count';
        if (input) input.placeholder = '0';
    } else if (selected === 'add_stock') {
        if (label) label.innerText = 'Quantity to Add (+)';
        if (input) input.placeholder = 'e.g. 50';
    } else if (selected === 'reduce_stock') {
        if (label) label.innerText = 'Quantity to Deduct (-)';
        if (input) input.placeholder = 'e.g. 20';
    }
}

function submitStockAdjustment(e) {
    e.preventDefault();
    const id = document.getElementById('adjustProductId').value;
    const form = document.getElementById('stockAdjustmentForm');
    const formData = new FormData(form);
    const $submitBtn = $(form).find('button[type="submit"]');

    if (window.setButtonLoading) window.setButtonLoading($submitBtn, true, 'Updating...');

    fetch(`/inventory/goods/${id}/adjust`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json().then(data => ({ status: res.status, body: data })))
    .then(({ status, body }) => {
        if (window.setButtonLoading) window.setButtonLoading($submitBtn, false);
        if (status === 200 && body.success) {
            closeStockAdjustmentModal();
            if (window.showToast) window.showToast('success', body.message);

            const productId = body.product_id || id;
            const newStock = body.new_stock;
            const uom = body.uom || 'piece';
            const threshold = body.safety_threshold !== undefined ? body.safety_threshold : 10;
            const isLow = (newStock <= threshold && threshold > 0);

            // 1. Update stock display in table cell
            const stockCell = document.getElementById(`stock-val-${productId}`);
            if (stockCell) {
                stockCell.innerText = `${Number(newStock).toLocaleString()} ${uom}`;
                stockCell.classList.add('bg-blue-50/80');
                setTimeout(() => stockCell.classList.remove('bg-blue-50/80'), 1200);
            }

            // 2. Update Row Highlight & Low Stock badge
            const row = document.getElementById(`row-good-${productId}`);
            if (row) {
                if (isLow) row.classList.add('bg-rose-50/40');
                else row.classList.remove('bg-rose-50/40');
            }

            const nameCell = document.getElementById(`name-cell-${productId}`);
            if (nameCell) {
                const badge = nameCell.querySelector('.low-stock-badge');
                if (badge) {
                    if (isLow) badge.classList.remove('hidden');
                    else badge.classList.add('hidden');
                }
            }

            // 3. Update Adjust Button onclick handler with updated stock
            const btn = document.getElementById(`btn-adjust-${productId}`);
            if (btn) {
                const prodNameSafe = (body.product_name || '').replace(/'/g, "\\'");
                btn.setAttribute('onclick', `openStockAdjustmentModal(${productId}, '${prodNameSafe}', ${newStock}, '${uom}')`);
            }
        } else {
            const msg = body.message || 'Failed to adjust stock.';
            if (window.showToast) window.showToast('error', msg);
            else alert(msg);
        }
    })
    .catch(err => {
        if (window.setButtonLoading) window.setButtonLoading($submitBtn, false);
        if (window.showToast) window.showToast('error', 'Network error while adjusting stock.');
        else alert('Network error while adjusting stock.');
    });
}
@endif
</script>

@if(\App\Models\Setting::get('track_stock', 'true') === 'true')
<!-- Stock Adjustment Modal -->
<div id="stockAdjustmentModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden border border-slate-200 animate-in fade-in zoom-in duration-200">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-bold">
                    ⚡
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Adjust Product Stock</h3>
                    <p class="text-xs text-slate-500 font-medium truncate max-w-[240px]" id="modalAdjustProductName">-</p>
                </div>
            </div>
            <button type="button" onclick="closeStockAdjustmentModal()" class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer">&times;</button>
        </div>

        <form id="stockAdjustmentForm" onsubmit="submitStockAdjustment(event)" class="p-6 space-y-4">
            @csrf
            <input type="hidden" id="adjustProductId" name="product_id" value="">

            <!-- Current Stock Display Box -->
            <div class="p-3.5 bg-blue-50/70 border border-blue-200 rounded-xl flex items-center justify-between text-xs">
                <span class="font-bold text-blue-900">Current Recorded Stock:</span>
                <span class="font-mono font-black text-sm text-blue-700" id="modalAdjustCurrentStock">0</span>
            </div>

            <!-- Adjustment Action Type -->
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Adjustment Mode</label>
                <div class="grid grid-cols-3 gap-2">
                    <label class="flex flex-col items-center justify-center p-2.5 border-2 border-blue-600 bg-blue-50/60 rounded-xl cursor-pointer text-center select-none text-xs font-bold text-blue-700 transition shadow-2xs">
                        <input type="radio" name="adjustment_type" value="set_total" checked class="sr-only" onchange="updateAdjustmentTypeUI()">
                        <span class="text-base mb-0.5">🎯</span>
                        <span>Set Total</span>
                    </label>
                    <label class="flex flex-col items-center justify-center p-2.5 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 text-center select-none text-xs font-bold text-slate-700 transition">
                        <input type="radio" name="adjustment_type" value="add_stock" class="sr-only" onchange="updateAdjustmentTypeUI()">
                        <span class="text-base mb-0.5">➕</span>
                        <span>Add (+)</span>
                    </label>
                    <label class="flex flex-col items-center justify-center p-2.5 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 text-center select-none text-xs font-bold text-slate-700 transition">
                        <input type="radio" name="adjustment_type" value="reduce_stock" class="sr-only" onchange="updateAdjustmentTypeUI()">
                        <span class="text-base mb-0.5">➖</span>
                        <span>Deduct (-)</span>
                    </label>
                </div>
            </div>

            <!-- Quantity Input -->
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1" id="modalQtyLabel">New Total Stock Count</label>
                <div class="relative">
                    <input type="number" id="adjustQuantityInput" name="quantity" min="0" required placeholder="0"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm font-mono font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <span class="absolute right-3 top-2.5 text-xs font-bold text-slate-400" id="modalAdjustUom">piece</span>
                </div>
            </div>

            <!-- Reason Dropdown -->
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Reason for Adjustment <span class="text-rose-500">*</span></label>
                <select name="reason" id="adjustReasonSelect" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="Physical Count / Audit Correction">📋 Physical Count / Audit Correction</option>
                    <option value="Damaged in Warehouse / Scrapped">💥 Damaged in Warehouse / Scrapped</option>
                    <option value="Sample / Trial Dispatch">🚚 Sample / Trial Dispatch</option>
                    <option value="Initial Opening Stock Setup">📦 Initial Opening Stock Setup</option>
                    <option value="Other / Manual Correction">✏️ Other / Manual Correction</option>
                </select>
            </div>

            <!-- Remarks / Notes (Optional) -->
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Remarks / Notes (Optional)</label>
                <input type="text" name="notes" placeholder="e.g. Counted during monthly warehouse audit"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeStockAdjustmentModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit" id="submitAdjustStockBtn" class="btn-primary py-2.5 px-5 text-xs font-bold shadow-xs">
                    Confirm Stock Update
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
