@extends('layouts.app')

@section('title', 'Inventory Audit')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            @if ($tab === 'materials')
                <h1 class="text-2xl font-bold text-slate-800">Raw Materials Inventory Audit</h1>
                <p class="text-sm text-slate-500">Track, manage, and audit factory raw material supplies.</p>
            @else
                <h1 class="text-2xl font-bold text-slate-800">Products Catalog</h1>
                <p class="text-sm text-slate-500">Audit and catalog completed products.</p>
            @endif
        </div>
        <div>
            @if ($tab === 'materials')
                <button type="button" onclick="toggleMaterialForm()" class="btn-primary py-2.5 px-5 text-xs font-bold shadow-xs flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span id="btnMaterialToggleText">+ Add Raw Material</span>
                </button>
            @else
                <button type="button" onclick="toggleProductForm()" class="btn-primary py-2.5 px-5 text-xs font-bold shadow-xs flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span id="btnProductToggleText">+ Add Product</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Active Tab Content -->
    @if ($tab === 'materials')
        
        <!-- 1. COLLAPSIBLE RAW MATERIAL FORM -->
        <div id="rawMaterialCard" class="hidden bg-white rounded-2xl shadow-sm border border-slate-200 p-6 transition-all duration-300">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100/60">
                <h3 id="rawMaterialTitle" class="text-base font-bold text-slate-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-[#4371D7]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Add Raw Material
                </h3>
                <button type="button" id="rawMaterialCloseBtn" onclick="toggleMaterialForm(false)" class="text-xs font-bold text-slate-400 hover:text-slate-600">&times; Close</button>
            </div>

            <form id="rawMaterialForm" action="{{ route('inventory.materials.store') }}" method="POST" class="ajax-form space-y-4">
                @csrf
                <input type="hidden" name="_method" id="material_form_method" value="POST">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Material Name</label>
                        <input type="text" id="mat_name" name="material_name" placeholder="e.g. Iron Wire Coils (5mm)" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Measurement Unit</label>
                        <select id="mat_unit" name="unit" required
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                            <option value="kg" selected>kg (Kilograms)</option>
                            <option value="liter">liter (Liters)</option>
                            <option value="meter">meter (Meters)</option>
                            <option value="packet">packet (Packets)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div id="material_stock_wrapper">
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Initial Quantity / Stock</label>
                        <input type="number" id="mat_stock" name="current_stock" step="0.0001" min="0" placeholder="e.g. 15000"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Safety Threshold Alert Limit</label>
                        <input type="number" id="mat_threshold" name="safety_threshold" step="0.0001" min="0" placeholder="e.g. 2000" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Purchase Price (₹)</label>
                        <input type="number" id="mat_price" name="average_purchase_price" step="0.01" min="0" placeholder="e.g. 85.00" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" onclick="toggleMaterialForm(false)" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700">Cancel</button>
                    <button type="submit" id="materialSubmitBtn" class="btn-primary py-2.5 px-6 text-sm font-bold bg-[#4371D7] hover:bg-blue-700 text-white rounded-xl shadow-xs">
                        Create Raw Material
                    </button>
                </div>
            </form>
        </div>

        <!-- 2. RECORDS LIST UNDERNEATH -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Raw Materials Ledger
            </h3>
            
            <div class="overflow-x-auto w-full max-w-full">
                <table class="erp-datatable min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-[#4371D7] text-white divide-x divide-white/25">
                        <tr>
                            <th class="px-4 py-3.5 text-center text-xs font-bold uppercase w-12">#</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Material Name</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Current Stock</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Safety Threshold Limit</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Purchase Price</th>
                            <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">Status</th>
                            <th class="px-6 py-3.5 text-center text-xs font-bold uppercase w-28">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($rawMaterials as $mat)
                            @php $isLow = $mat->current_stock < $mat->safety_threshold; @endphp
                            <tr class="hover:bg-slate-50 transition" id="row-mat-{{ $mat->id }}">
                                <td class="px-4 py-4 text-center font-bold text-slate-500">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-800">{{ $mat->material_name }}</td>
                                <td class="px-6 py-4 text-right font-medium text-slate-700">{{ number_format($mat->current_stock, 2) }} {{ $mat->unit }}</td>
                                <td class="px-6 py-4 text-right text-slate-500">{{ number_format($mat->safety_threshold, 1) }} {{ $mat->unit }}</td>
                                <td class="px-6 py-4 text-right text-slate-700">₹{{ number_format($mat->average_purchase_price, 2) }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if ($isLow)
                                        <span class="px-2.5 py-0.5 bg-rose-50 border border-rose-200 text-rose-700 text-[10px] rounded font-bold uppercase tracking-wider animate-pulse">Low Stock</span>
                                    @else
                                        <span class="px-2.5 py-0.5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] rounded font-bold uppercase tracking-wider">OK</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center space-x-1.5">
                                        <button type="button" 
                                                onclick="openEditMaterialModal({{ $mat->id }}, '{{ addslashes($mat->material_name) }}', '{{ $mat->unit }}', {{ $mat->safety_threshold }}, {{ $mat->average_purchase_price }})"
                                                class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-xs transition duration-150 transform hover:scale-105"
                                                title="Edit Raw Material">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                        <button type="button" 
                                                onclick="deleteMaterial({{ $mat->id }}, '{{ addslashes($mat->material_name) }}')"
                                                class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-rose-500 hover:bg-rose-600 text-white shadow-xs transition duration-150 transform hover:scale-105"
                                                title="Delete Raw Material">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    @else
        
        <!-- 1. COLLAPSIBLE PRODUCT FORM -->
        <div id="productCard" class="hidden bg-white rounded-2xl shadow-sm border border-slate-200 p-6 transition-all duration-300">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100/60">
                <h3 id="productTitle" class="text-base font-bold text-slate-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-[#4371D7]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Add Product
                </h3>
                <button type="button" id="productCloseBtn" onclick="toggleProductForm(false)" class="text-xs font-bold text-slate-400 hover:text-slate-600">&times; Close</button>
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

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">UOM (Unit of Measurement)</label>
                        <input type="text" id="good_uom" name="uom" placeholder="e.g. piece, kg, box" value="piece" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Selling Price (Excl. Tax)</label>
                        <input type="number" id="good_price" name="selling_price" step="0.01" min="0" placeholder="e.g. 1850.00" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
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
                    <button type="button" onclick="toggleProductForm(false)" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700">Cancel</button>
                    <button type="submit" id="productSubmitBtn" class="btn-primary py-2.5 px-6 text-sm font-bold bg-[#4371D7] hover:bg-blue-700 text-white rounded-xl shadow-xs">
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
                    <thead class="bg-[#4371D7] text-white divide-x divide-white/25">
                        <tr>
                            <th class="px-4 py-3.5 text-center text-xs font-bold uppercase w-12">#</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Product Name</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">SKU</th>
                            <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">HSN Code</th>
                            <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">UOM</th>
                            <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">GST Rate</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Current Stock</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Selling Price</th>
                            <th class="px-6 py-3.5 text-center text-xs font-bold uppercase w-28">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($finishedGoods as $good)
                            <tr class="hover:bg-slate-50 transition" id="row-good-{{ $good->id }}">
                                <td class="px-4 py-4 text-center font-bold text-slate-500">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-800">{{ $good->product_name }}</td>
                                <td class="px-6 py-4 text-slate-600 font-medium text-xs">{{ $good->sku }}</td>
                                <td class="px-6 py-4 text-center text-slate-600 font-mono text-xs">{{ $good->hsn_code ?? '-' }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-0.5 rounded-lg bg-blue-50 text-blue-700 text-xs font-bold uppercase tracking-wider">{{ $good->uom ?? 'piece' }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold">{{ number_format($good->gst_rate ?? 18, 0) }}% GST</span>
                                </td>
                                <td class="px-6 py-4 text-right font-medium text-slate-700">{{ $good->current_stock }} {{ $good->uom ?? 'piece' }}</td>
                                <td class="px-6 py-4 text-right font-bold text-slate-850">₹{{ number_format($good->selling_price, 2) }}</td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center space-x-1.5">
                                        <button type="button" 
                                                onclick="openEditProductModal({{ $good->id }}, '{{ addslashes($good->product_name) }}', '{{ addslashes($good->sku) }}', '{{ addslashes($good->hsn_code ?? '') }}', '{{ $good->uom ?? 'piece' }}', {{ $good->current_stock ?? 0 }}, {{ $good->selling_price }}, {{ $good->gst_rate ?? 18.00 }})"
                                                class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-xs transition duration-150 transform hover:scale-105"
                                                title="Edit Product">
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
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    @endif
</div>

<script>
// --- RAW MATERIAL HANDLERS ---
function resetMaterialForm() {
    const form = document.getElementById('rawMaterialForm');
    if (!form) return;
    form.action = "{{ route('inventory.materials.store') }}";
    document.getElementById('material_form_method').value = "POST";
    
    const card = document.getElementById('rawMaterialCard');
    if (card) card.className = 'bg-white rounded-2xl shadow-sm border border-slate-200 p-6 transition-all duration-300';

    document.getElementById('rawMaterialTitle').className = 'text-base font-bold text-slate-800 flex items-center';
    document.getElementById('rawMaterialTitle').innerHTML = `
        <svg class="w-5 h-5 mr-2 text-[#4371D7]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        Add Raw Material
    `;
    document.getElementById('rawMaterialCloseBtn').className = 'text-xs font-bold text-slate-400 hover:text-slate-600';

    document.querySelectorAll('#rawMaterialForm input, #rawMaterialForm select').forEach(el => {
        if (el.type !== 'hidden') {
            el.className = 'w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium';
        }
    });

    document.getElementById('mat_name').value = '';
    document.getElementById('mat_unit').value = 'kg';
    document.getElementById('mat_threshold').value = '';
    document.getElementById('mat_price').value = '';
    if (document.getElementById('mat_stock')) document.getElementById('mat_stock').value = '';
    if (document.getElementById('material_stock_wrapper')) document.getElementById('material_stock_wrapper').style.display = 'block';
    
    const btn = document.getElementById('materialSubmitBtn');
    btn.innerText = 'Create Raw Material';
    btn.className = 'btn-primary py-2.5 px-6 text-sm font-bold bg-[#4371D7] hover:bg-blue-700 text-white rounded-xl shadow-xs';
}

function toggleMaterialForm(showExplicit = null) {
    const card = document.getElementById('rawMaterialCard');
    if (!card) return;
    const isHidden = card.classList.contains('hidden');
    const shouldShow = showExplicit !== null ? showExplicit : isHidden;
    
    if (shouldShow) {
        if (isHidden) resetMaterialForm();
        card.classList.remove('hidden');
        card.scrollIntoView({ behavior: 'smooth' });
    } else {
        card.classList.add('hidden');
    }
}

function openEditMaterialModal(id, name, unit, threshold, price) {
    const card = document.getElementById('rawMaterialCard');
    if (!card) return;
    
    document.getElementById('rawMaterialForm').action = `/inventory/materials/${id}`;
    document.getElementById('material_form_method').value = "PUT";
    
    card.className = 'bg-[#FFFDF5] rounded-2xl shadow-sm border-2 border-amber-300 p-6 transition-all duration-300';
    document.getElementById('rawMaterialTitle').className = 'text-base font-bold text-amber-900 flex items-center';
    document.getElementById('rawMaterialTitle').innerHTML = `Edit Raw Material`;
    document.getElementById('rawMaterialCloseBtn').className = 'text-xs font-bold text-amber-700 hover:text-amber-900';

    document.querySelectorAll('#rawMaterialForm input, #rawMaterialForm select').forEach(el => {
        if (el.type !== 'hidden') {
            el.className = 'w-full bg-white border border-amber-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700 font-medium';
        }
    });

    document.getElementById('mat_name').value = name;
    document.getElementById('mat_unit').value = unit;
    document.getElementById('mat_threshold').value = threshold;
    document.getElementById('mat_price').value = price;
    if (document.getElementById('material_stock_wrapper')) document.getElementById('material_stock_wrapper').style.display = 'none';
    
    const btn = document.getElementById('materialSubmitBtn');
    btn.innerText = 'Update Material';
    btn.className = 'btn-primary py-2.5 px-6 text-sm font-bold bg-[#4371D7] hover:bg-blue-700 text-white rounded-xl shadow-xs';
    
    card.classList.remove('hidden');
    card.scrollIntoView({ behavior: 'smooth' });
}

function deleteMaterial(id, name) {
    if (!confirm(`Are you sure you want to delete Raw Material '${name}'?`)) return;

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    $.ajax({
        url: `/inventory/materials/${id}`,
        method: 'DELETE',
        data: { _token: token },
        success: function(res) {
            if (window.showToast) window.showToast('success', res.message);
            $(`#row-mat-${id}`).fadeOut(300, function() { $(this).remove(); });
        },
        error: function(xhr) {
            alert(xhr.responseJSON?.message || 'Failed to delete raw material.');
        }
    });
}

// --- PRODUCT CATALOG HANDLERS ---
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
    document.getElementById('good_price').value = '';
    if (document.getElementById('good_gst_rate')) document.getElementById('good_gst_rate').value = '18.00';
    
    const btn = document.getElementById('productSubmitBtn');
    btn.innerText = 'Save Product';
    btn.className = 'btn-primary py-2.5 px-6 text-sm font-bold bg-[#4371D7] hover:bg-blue-700 text-white rounded-xl shadow-xs';
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

function openEditProductModal(id, name, sku, hsn, uom, stock, price, gstRate = 18.00) {
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
    document.getElementById('good_price').value = price;
    if (document.getElementById('good_gst_rate')) document.getElementById('good_gst_rate').value = parseFloat(gstRate).toFixed(2);
    
    const btn = document.getElementById('productSubmitBtn');
    btn.innerText = 'Update Product';
    btn.className = 'btn-primary py-2.5 px-6 text-sm font-bold bg-[#4371D7] hover:bg-blue-700 text-white rounded-xl shadow-xs';

    card.classList.remove('hidden');
    card.scrollIntoView({ behavior: 'smooth' });
}

function deleteProduct(id, name) {
    if (!confirm(`Are you sure you want to delete Product '${name}'?`)) return;

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
</script>
@endsection
