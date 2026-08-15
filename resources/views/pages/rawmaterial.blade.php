@extends('layouts.app')

@section('title', 'Raw Materials Audit')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Raw Materials Inventory Audit</h1>
            <p class="text-sm text-slate-500">Track, manage, and audit factory raw material supplies.</p>
        </div>
        <div>
            <button type="button" onclick="toggleMaterialForm()" class="btn-primary py-2.5 px-5 text-xs font-bold shadow-xs flex items-center">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span id="btnMaterialToggleText">Add Raw Material</span>
            </button>
        </div>
    </div>

    <!-- 1. COLLAPSIBLE RAW MATERIAL FORM -->
    <div id="rawMaterialCard" class="hidden bg-white rounded-2xl shadow-sm border border-slate-200 p-6 transition-all duration-300">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100/60">
            <h3 id="rawMaterialTitle" class="text-base font-bold text-slate-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-[#4371D7]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Add Raw Material
            </h3>
            <button type="button" id="rawMaterialCloseBtn" onclick="toggleMaterialForm(false)" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition cursor-pointer">&times; Close</button>
        </div>

        <form id="rawMaterialForm" action="{{ route('inventory.materials.store') }}" method="POST" class="ajax-form space-y-4" data-redirect="/rawmaterial">
            @csrf
            <input type="hidden" name="_method" id="material_form_method" value="POST">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Material Name <span class="text-rose-500">*</span></label>
                    <input type="text" id="mat_name" name="material_name" placeholder="e.g. MS Round Pipe or Powder Coating" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Material Category / Type</label>
                    <select id="mat_category" name="material_category" onchange="updateSpecPlaceholder(this.value)"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                        <option value="">Select Category (Optional)...</option>
                        @foreach($materialCategories as $cat)
                            <option value="{{ $cat['key'] }}">{{ $cat['icon'] ?? '📦' }} {{ $cat['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Measurement Unit (UOM) <span class="text-rose-500">*</span></label>
                    <input type="text" id="mat_unit" name="unit" list="raw_material_uom_list" placeholder="e.g. kg, meter, piece, roll, sq ft" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                    <datalist id="raw_material_uom_list">
                        <option value="kg">Kilograms (kg)</option>
                        <option value="gram">Grams (g)</option>
                        <option value="tonne">Metric Tonne (MT)</option>
                        <option value="liter">Liters (L)</option>
                        <option value="meter">Meters (m)</option>
                        <option value="feet">Feet (ft)</option>
                        <option value="sq ft">Square Feet (sq ft)</option>
                        <option value="piece">Pieces (pcs)</option>
                        <option value="nos">Numbers (nos)</option>
                        <option value="packet">Packets (pkt)</option>
                        <option value="box">Boxes (box)</option>
                        <option value="bundle">Bundles (bdl)</option>
                        <option value="roll">Rolls (roll)</option>
                        <option value="bag">Bags (bag)</option>
                        <option value="sheet">Sheets (sht)</option>
                    </datalist>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-1">
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Specification / Shade / Size</label>
                    <input type="text" id="mat_spec" name="specification" placeholder="e.g. 12mm OD x 1.5mm or RAL 7035 Grey"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                    <span class="text-[10px] text-slate-400 font-medium" id="mat_spec_hint">Enter pipe size, powder shade code, or gauge thickness</span>
                </div>
                <div id="material_stock_wrapper">
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Initial Quantity / Stock</label>
                    <input type="number" id="mat_stock" name="current_stock" step="0.0001" min="0" placeholder="e.g. 15000"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Safety Threshold Alert Limit <span class="text-rose-500">*</span></label>
                    <input type="number" id="mat_threshold" name="safety_threshold" step="0.0001" min="0" placeholder="e.g. 2000" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-2">
                <button type="button" onclick="toggleMaterialForm(false)" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold py-2.5 px-5 rounded-xl transition cursor-pointer">Cancel</button>
                <button type="submit" id="materialSubmitBtn" class="btn-primary py-2.5 px-6 text-sm font-bold bg-[#2563EB] hover:bg-blue-700 text-white rounded-xl shadow-xs">
                    Create Raw Material
                </button>
            </div>
        </form>
    </div>

    @if(\App\Models\Setting::get('track_stock', 'true') === 'true' && isset($lowStockMaterials) && $lowStockMaterials->isNotEmpty())
        <!-- ⚡ Low Stock Auto-Purchase Reorder Hub -->
        <div class="bg-gradient-to-r from-rose-50 to-amber-50 rounded-2xl shadow-xs border border-rose-200 p-4 transition-all">
            <div class="flex items-center justify-between gap-2 mb-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center text-base font-bold shadow-xs">⚡</span>
                    <div>
                        <h4 class="text-sm font-bold text-rose-900 flex items-center gap-2">
                            <span>Smart Reorder Assistant</span>
                            <span class="px-2 py-0.5 bg-rose-200/80 text-rose-800 rounded-md text-[10px] font-extrabold">{{ $lowStockMaterials->count() }} Critical</span>
                        </h4>
                        <p class="text-xs text-rose-700">These raw materials are running below safety threshold. 1-click launch pre-filled purchase bills.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($lowStockMaterials as $lowMat)
                    @php
                        $suggestedQty = $lowMat->suggested_reorder_quantity;
                        $rate = (float)($lowMat->average_purchase_price ?? 0);
                    @endphp
                    <div class="bg-white rounded-xl border border-rose-200/80 p-3 flex items-center justify-between gap-3 shadow-2xs hover:border-rose-300 transition">
                        <div class="min-w-0">
                            <div class="font-bold text-xs text-slate-800 truncate">{{ $lowMat->material_name }}</div>
                            <div class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                                <span class="font-mono text-rose-600 font-bold">{{ number_format($lowMat->current_stock, 2) }}</span>
                                <span>/ {{ number_format($lowMat->safety_threshold, 2) }} {{ $lowMat->unit }}</span>
                            </div>
                        </div>
                        <a href="{{ route('purchases', ['prefill_raw_material' => $lowMat->id, 'prefill_qty' => $suggestedQty, 'prefill_price' => $rate]) }}"
                           class="shrink-0 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold shadow-xs transition flex items-center gap-1 cursor-pointer">
                            <span>⚡ Reorder</span>
                            <span class="font-mono text-[11px]">({{ number_format($suggestedQty, 0) }})</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 2. RECORDS LIST UNDERNEATH -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2">
            <h3 class="text-base font-bold text-slate-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Raw Materials Ledger
            </h3>
        </div>

        <div class="overflow-x-auto w-full max-w-full">
            <table class="erp-datatable min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-[#EDF4FA] text-black divide-x divide-slate-200">
                    <tr>
                        <th class="px-4 py-3.5 text-center text-xs font-bold uppercase w-12">#</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Material Name & Specification</th>
                        <th class="px-4 py-3.5 text-center text-xs font-bold uppercase">Category</th>
                        @if(\App\Models\Setting::get('track_stock', 'true') === 'true')
                            <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Current Stock</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Safety Threshold Limit</th>
                        @endif
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">Last Restocked Date</th>
                        @if(\App\Models\Setting::get('track_stock', 'true') === 'true')
                            <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">Status</th>
                        @endif
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase w-36">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($rawMaterials as $mat)
                        @php 
                            $isLow = $mat->current_stock < $mat->safety_threshold; 
                            $catInfo = $mat->category_info;
                        @endphp
                        <tr class="hover:bg-slate-50 transition mat-row" id="row-mat-{{ $mat->id }}" data-category="{{ $mat->material_category ?: 'other' }}">
                            <td class="px-4 py-4 text-center font-bold text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800">{{ $mat->material_name }}</div>
                                @if($mat->specification)
                                    <div class="text-xs text-slate-500 font-mono mt-0.5 flex items-center gap-1">
                                        <span class="text-blue-500 text-[10px]">🔹</span>
                                        <span>{{ $mat->specification }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold border bg-slate-50 text-slate-700 border-slate-200">
                                    <span>{{ $catInfo['icon'] ?? '📦' }}</span>
                                    <span>{{ $catInfo['label'] }}</span>
                                </span>
                            </td>
                            @if(\App\Models\Setting::get('track_stock', 'true') === 'true')
                                <td class="px-6 py-4 text-right font-medium text-slate-700 mat-stock-cell">{{ number_format($mat->current_stock, 2) }} {{ $mat->unit }}</td>
                                <td class="px-6 py-4 text-right text-slate-500"><span class="mat-threshold-val hidden">{{ (float)$mat->safety_threshold }}</span>{{ number_format($mat->safety_threshold, 1) }} {{ $mat->unit }}</td>
                            @endif
                            <td class="px-6 py-4 text-center">
                                @if($mat->latestPurchase && $mat->latestPurchase->purchase_date)
                                    <div class="inline-flex flex-col items-center">
                                        <span class="text-xs font-bold text-slate-800">{{ $mat->latestPurchase->purchase_date->format('d M Y') }}</span>
                                        <span class="text-[10px] text-slate-400 font-medium">via Bill #{{ $mat->latestPurchase->bill_number ?: $mat->latestPurchase->id }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 font-medium italic">Initial / Opening</span>
                                @endif
                            </td>
                            @if(\App\Models\Setting::get('track_stock', 'true') === 'true')
                                <td class="px-6 py-4 text-center mat-status-cell">
                                    @if ($isLow)
                                        <span class="px-2.5 py-0.5 bg-rose-50 border border-rose-200 text-rose-700 text-[10px] rounded font-bold uppercase tracking-wider animate-pulse">Low Stock</span>
                                    @else
                                        <span class="px-2.5 py-0.5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] rounded font-bold uppercase tracking-wider">OK</span>
                                    @endif
                                </td>
                            @endif
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <a href="{{ route('purchases', ['prefill_raw_material' => $mat->id, 'prefill_qty' => $mat->suggested_reorder_quantity, 'prefill_price' => $mat->average_purchase_price]) }}"
                                       title="Restock Material (Record Purchase Bill)"
                                       class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition duration-150 transform hover:scale-105">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.5 9.5a8 8 0 0113.86-3.86L20 7m0-3v3h-3M19.5 14.5a8 8 0 01-13.86 3.86L4 17m0 3v-3h3" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9l3 1.7v3.6L12 16l-3-1.7v-3.6L12 9z" />
                                        </svg>
                                    </a>
                                    @if(\App\Models\Setting::get('track_stock', 'true') === 'true')
                                        <button type="button" 
                                                onclick="openStockAdjustmentModal({{ $mat->id }}, '{{ addslashes($mat->material_name) }}', '{{ $mat->unit }}', {{ (float)$mat->current_stock }})"
                                                class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-teal-600 hover:bg-teal-700 text-white shadow-xs transition duration-150 transform hover:scale-105"
                                                title="Physical Stock Adjustment / Audit Voucher">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                                        </button>
                                    @endif
                                    <button type="button" 
                                            onclick="openEditMaterialModal({{ $mat->id }}, '{{ addslashes($mat->material_name) }}', '{{ $mat->material_category }}', '{{ addslashes($mat->specification ?? '') }}', '{{ $mat->unit }}', {{ $mat->safety_threshold }})"
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
                    @empty
                        <tr class="empty-row">
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="w-10 h-10 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <p class="text-sm font-bold text-slate-600">No Records Found</p>
                                    <p class="text-xs text-slate-400">There are no raw materials recorded yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 3. PHYSICAL STOCK AUDIT VOUCHER MODAL -->
<div id="stockAdjustmentModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full border border-slate-200 overflow-hidden transform transition-all">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-xl bg-teal-50 border border-teal-200 flex items-center justify-center text-teal-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Physical Stock Audit Voucher</h3>
                    <p class="text-xs text-slate-500" id="adj_mat_subtitle">Adjust live warehouse stock</p>
                </div>
            </div>
            <button type="button" onclick="closeStockAdjustmentModal()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
        </div>
        <form id="stockAdjustmentForm" onsubmit="return submitStockAdjustment(event);" class="p-6 space-y-4">
            @csrf
            <input type="hidden" id="adj_mat_id" name="material_id">

            <div class="grid grid-cols-2 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-xs">
                <div>
                    <span class="text-slate-500 block mb-0.5 font-medium">Current System Stock</span>
                    <span class="text-sm font-bold text-slate-800" id="adj_current_stock_display">0.00 kg</span>
                </div>
                <div class="text-right">
                    <span class="text-slate-500 block mb-0.5 font-medium">Calculated Variance</span>
                    <span id="adj_variance_badge" class="inline-block px-2 py-0.5 rounded font-bold text-xs bg-slate-200 text-slate-700">0.00 kg</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">
                    Actual Physical Counted Stock <span id="adj_unit_label" class="text-blue-600 lowercase font-bold"></span> *
                </label>
                <input type="number" id="adj_new_stock" name="new_stock" step="0.0001" min="0" required
                       placeholder="Enter physical weighed stock (e.g. 4850)"
                       oninput="calculateAdjustmentVariance()"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 text-slate-800 font-bold">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Reason for Adjustment *</label>
                <select id="adj_reason" name="reason" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 text-slate-700 font-medium">
                    <option value="Physical Count Discrepancy (Periodic Audit)">Monthly / Periodic Physical Count Audit</option>
                    <option value="Cutting, Grinding & Fabrication Waste">Cutting, Grinding & Welding Spatter Loss</option>
                    <option value="Rust, Water, or Handling Damage (Scrapped)">Water, Rust, or Material Damage (Scrapped)</option>
                    <option value="Found Surplus / Unrecorded Material">Found Surplus / Leftover Material Recorded</option>
                    <option value="Weighbridge / Scale Calibration Difference">Weighbridge / Scale Tolerance Difference</option>
                    <option value="Other / Custom Correction">Other / Custom Floor Correction</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Supervisor / Audit Notes <span class="text-slate-400 font-normal text-[10px]">(Optional)</span></label>
                <textarea id="adj_notes" name="notes" rows="2" placeholder="e.g. Floor audit verified on weighbridge scale #2"
                          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-teal-500 text-slate-700"></textarea>
            </div>

            <div class="flex items-center justify-end space-x-2.5 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeStockAdjustmentModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">Cancel</button>
                <button type="submit" id="adj_submit_btn" class="px-5 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center space-x-1.5">
                    <span>Apply Stock Adjustment</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentAdjPreviousStock = 0;
let currentAdjUnit = 'kg';

function updateSpecPlaceholder(cat) {
    const specInput = document.getElementById('mat_spec');
    const hint = document.getElementById('mat_spec_hint');
    if (!specInput) return;

    if (cat === 'powders') {
        specInput.placeholder = 'e.g. RAL 7035 Light Grey Structure / Glossy';
        if (hint) hint.innerText = 'Enter RAL shade code, color name, or texture finish (e.g. RAL 9005 Glossy)';
    } else if (cat === 'pipes') {
        specInput.placeholder = 'e.g. 12mm OD x 1.5mm Thickness (16 Gauge)';
        if (hint) hint.innerText = 'Enter outer diameter (OD), wall thickness, or schedule';
    } else if (cat === 'sheets') {
        specInput.placeholder = 'e.g. 2.0mm Thickness (14 Gauge CRCA)';
        if (hint) hint.innerText = 'Enter sheet thickness, gauge, or steel grade';
    } else if (cat === 'welding') {
        specInput.placeholder = 'e.g. 1.2mm ER70S-6 Wire / Pure Argon';
        if (hint) hint.innerText = 'Enter wire diameter, AWS grade, or gas cylinder volume';
    } else if (cat === 'hardware') {
        specInput.placeholder = 'e.g. M10 x 25mm High Tensile 8.8';
        if (hint) hint.innerText = 'Enter fastener size, thread, and grade';
    } else {
        specInput.placeholder = 'e.g. Dimensions, shade, or model specifications';
        if (hint) hint.innerText = 'Enter material dimensions or specifications';
    }
}

window.filterRawMaterialCategory = function(catKey) {
    $('.mat-category-filter-btn').removeClass('bg-blue-600 text-white shadow-xs').addClass('bg-slate-100 text-slate-700 hover:bg-slate-200');
    $(`#mat_filter_btn_${catKey}`).removeClass('bg-slate-100 text-slate-700 hover:bg-slate-200').addClass('bg-blue-600 text-white shadow-xs');
    
    if ($.fn.DataTable && $.fn.DataTable.isDataTable('.erp-datatable')) {
        const table = $('.erp-datatable').DataTable();
        if (catKey === 'all') {
            $.fn.dataTable.ext.search = [];
            table.draw();
        } else {
            $.fn.dataTable.ext.search = [
                function(settings, data, dataIndex) {
                    const row = table.row(dataIndex).node();
                    const rowCat = $(row).attr('data-category');
                    return rowCat === catKey;
                }
            ];
            table.draw();
        }
    } else {
        if (catKey === 'all') {
            $('.mat-row').show();
        } else {
            $('.mat-row').each(function() {
                const rowCat = $(this).attr('data-category');
                if (rowCat === catKey) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    }
};

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
    document.getElementById('mat_category').value = '';
    document.getElementById('mat_spec').value = '';
    document.getElementById('mat_unit').value = 'kg';
    document.getElementById('mat_threshold').value = '';
    if (document.getElementById('mat_price')) document.getElementById('mat_price').value = '';
    if (document.getElementById('mat_stock')) document.getElementById('mat_stock').value = '';
    if (document.getElementById('material_stock_wrapper')) document.getElementById('material_stock_wrapper').style.display = 'block';
    if (document.getElementById('material_price_wrapper')) document.getElementById('material_price_wrapper').style.display = 'block';
    updateSpecPlaceholder('');
    
    const btn = document.getElementById('materialSubmitBtn');
    btn.innerText = 'Create Raw Material';
    btn.className = 'btn-primary py-2.5 px-6 text-sm font-bold bg-[#2563EB] hover:bg-blue-700 text-white rounded-xl shadow-xs';
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

function openEditMaterialModal(id, name, category, spec, unit, threshold) {
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
    document.getElementById('mat_category').value = category || '';
    document.getElementById('mat_spec').value = spec || '';
    document.getElementById('mat_unit').value = unit;
    document.getElementById('mat_threshold').value = threshold;
    if (document.getElementById('material_stock_wrapper')) document.getElementById('material_stock_wrapper').style.display = 'none';
    if (document.getElementById('material_price_wrapper')) document.getElementById('material_price_wrapper').style.display = 'none';
    updateSpecPlaceholder(category || '');
    
    const btn = document.getElementById('materialSubmitBtn');
    btn.innerText = 'Update Material';
    btn.className = 'btn-primary py-2.5 px-6 text-sm font-bold bg-[#2563EB] hover:bg-blue-700 text-white rounded-xl shadow-xs';
    
    card.classList.remove('hidden');
    card.scrollIntoView({ behavior: 'smooth' });
}

function openStockAdjustmentModal(id, name, unit, currentStock) {
    currentAdjPreviousStock = parseFloat(currentStock) || 0;
    currentAdjUnit = unit || 'kg';
    
    document.getElementById('adj_mat_id').value = id;
    document.getElementById('adj_mat_subtitle').innerText = `Material: ${name}`;
    document.getElementById('adj_unit_label').innerText = `(${currentAdjUnit})`;
    document.getElementById('adj_current_stock_display').innerText = `${currentAdjPreviousStock.toFixed(2)} ${currentAdjUnit}`;
    document.getElementById('adj_new_stock').value = '';
    document.getElementById('adj_notes').value = '';
    document.getElementById('adj_reason').selectedIndex = 0;
    
    calculateAdjustmentVariance();
    
    document.getElementById('stockAdjustmentModal').classList.remove('hidden');
    setTimeout(() => document.getElementById('adj_new_stock').focus(), 100);
}

function closeStockAdjustmentModal() {
    document.getElementById('stockAdjustmentModal').classList.add('hidden');
}

function calculateAdjustmentVariance() {
    const inputVal = document.getElementById('adj_new_stock').value;
    const badge = document.getElementById('adj_variance_badge');
    
    if (inputVal === '' || isNaN(inputVal)) {
        badge.className = 'inline-block px-2 py-0.5 rounded font-bold text-xs bg-slate-200 text-slate-600';
        badge.innerText = 'Enter physical stock';
        return;
    }
    
    const newStock = parseFloat(inputVal);
    const variance = newStock - currentAdjPreviousStock;
    const sign = variance > 0 ? '+' : '';
    
    if (variance > 0) {
        badge.className = 'inline-block px-2 py-0.5 rounded font-bold text-xs bg-emerald-100 text-emerald-800 border border-emerald-300';
        badge.innerText = `${sign}${variance.toFixed(2)} ${currentAdjUnit} (Surplus)`;
    } else if (variance < 0) {
        badge.className = 'inline-block px-2 py-0.5 rounded font-bold text-xs bg-rose-100 text-rose-800 border border-rose-300';
        badge.innerText = `${variance.toFixed(2)} ${currentAdjUnit} (Deficit)`;
    } else {
        badge.className = 'inline-block px-2 py-0.5 rounded font-bold text-xs bg-slate-200 text-slate-700';
        badge.innerText = `0.00 ${currentAdjUnit} (Match)`;
    }
}

function submitStockAdjustment(e) {
    e.preventDefault();
    const matId = document.getElementById('adj_mat_id').value;
    const newStock = document.getElementById('adj_new_stock').value;
    const reason = document.getElementById('adj_reason').value;
    const notes = document.getElementById('adj_notes').value;
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    const btn = document.getElementById('adj_submit_btn');
    
    if (!newStock || isNaN(newStock) || parseFloat(newStock) < 0) {
        alert('Please enter a valid physical stock quantity.');
        return false;
    }
    
    btn.disabled = true;
    if (window.setButtonLoading) window.setButtonLoading(btn, true, 'Applying...');
    
    $.ajax({
        url: `/inventory/materials/${matId}/adjust`,
        method: 'POST',
        data: {
            _token: token,
            new_stock: newStock,
            reason: reason,
            notes: notes
        },
        success: function(res) {
            if (window.setButtonLoading) window.setButtonLoading(btn, false);
            closeStockAdjustmentModal();
            
            if (window.showToast) {
                window.showToast('success', res.message);
            }

            const parsedStock = parseFloat(newStock);
            const $row = $(`#row-mat-${matId}`);
            if ($row.length) {
                const threshold = parseFloat($row.find('.mat-threshold-val').text()) || 0;
                $row.find('.mat-stock-cell').text(`${parsedStock.toFixed(2)} ${currentAdjUnit}`);
                
                const isLow = parsedStock < threshold;
                const $statusCell = $row.find('.mat-status-cell');
                if ($statusCell.length) {
                    if (isLow) {
                        $statusCell.html(`<span class="px-2.5 py-0.5 bg-rose-50 border border-rose-200 text-rose-700 text-[10px] rounded font-bold uppercase tracking-wider animate-pulse">Low Stock</span>`);
                    } else {
                        $statusCell.html(`<span class="px-2.5 py-0.5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] rounded font-bold uppercase tracking-wider">OK</span>`);
                    }
                }
                if (window.ERPTableHelper) window.ERPTableHelper.highlightRow($row);
            }
            
            if (window.clearPageCache) {
                window.clearPageCache();
            }
        },
        error: function(xhr) {
            if (window.setButtonLoading) window.setButtonLoading(btn, false);
            const msg = xhr.responseJSON?.message || 'Failed to apply stock adjustment.';
            if (window.showToast) {
                window.showToast('error', msg);
            } else {
                alert(msg);
            }
        }
    });
    
    return false;
}

function deleteMaterial(id, name) {
    window.confirmDelete(
        'Delete Raw Material?',
        `Are you sure you want to delete Raw Material '${name}'? This action cannot be undone.`,
        function() {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            $.ajax({
                url: `/inventory/materials/${id}`,
                method: 'DELETE',
                data: { _token: token },
                success: function(res) {
                    if (window.showToast) window.showToast('success', res.message);
                    if (window.ERPTableHelper) {
                        window.ERPTableHelper.removeRow(`#row-mat-${id}`);
                    } else {
                        $(`#row-mat-${id}`).fadeOut(300, function() { $(this).remove(); });
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Failed to delete raw material.';
                    if (window.showToast) {
                        window.showToast('error', msg);
                    } else {
                        alert(msg);
                    }
                }
            });
        }
    );
}
</script>
@endsection
