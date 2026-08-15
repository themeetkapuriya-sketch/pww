@extends('layouts.app')

@section('title', 'Purchase Ledger')

@section('content')
@php
    $purchaseTypeOptions = \App\Services\CategoryService::getPurchaseComboboxOptions();

    $trackStockEnabled = (\App\Models\Setting::get('track_stock', 'true') === 'true');
    $rawMaterialOptions = [];
    foreach ($rawMaterials as $mat) {
        $specText = $mat->specification ? " [{$mat->specification}]" : '';
        $rawMaterialOptions[] = [
            'value' => (string)$mat->id,
            'label' => $mat->material_name . $specText . ($trackStockEnabled ? ' (Stock: ' . number_format($mat->current_stock, 1) . ' ' . $mat->unit . ')' : ''),
            'search' => strtolower($mat->material_name . ' ' . $mat->specification . ' ' . $mat->material_category . ' ' . $mat->unit),
            'data' => [
                'name' => $mat->material_name,
                'unit' => $mat->unit,
            ]
        ];
    }
@endphp
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Purchase Ledger</h1>
            <p class="text-sm text-slate-500">Record all factory purchases including raw materials, machinery, tools, and vendor bills.</p>
        </div>
        <button type="button" 
                onclick="toggleInlineForm('purchaseFormContainer', this)" 
                class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-md transition duration-150 flex items-center space-x-2">
            <svg class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Record New Purchase</span>
        </button>
    </div>

    <!-- 1. Log Purchase Bill Form (Expandable) -->
    <div id="purchaseFormContainer" class="hidden transition-all duration-300 ease-in-out">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Record Purchase Invoice / Bill
            </h3>
            <button type="button" onclick="toggleInlineForm('purchaseFormContainer', document.querySelector('button[onclick*=\'purchaseFormContainer\']'))" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition cursor-pointer">&times; Close</button>
        </div>
        <form action="{{ route('purchases.store') }}" method="POST" class="ajax-form space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-combobox name="purchase_type"
                            id="purchaseTypeSelect"
                            label="Purchase Category"
                            placeholder="Search or type purchase category..."
                            :options="$purchaseTypeOptions"
                            value=""
                            :allowCustom="true"
                            required />
                <div id="rawMaterialSelectContainer">
                    <x-combobox name="raw_material_id"
                                id="rawMaterialSelect"
                                label="Raw Material Sub-Category (Select to Restock)"
                                placeholder="Select Existing Raw Material..."
                                :options="$rawMaterialOptions" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Vendor / Supplier Name</label>
                    <input type="text" name="vendor_name" placeholder="e.g. TATA Steel Ltd" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Bill / Invoice No. (Optional)</label>
                    <input type="text" name="bill_number" placeholder="e.g. INV-2026-9041"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-mono">
                </div>

                <div id="itemNameInputContainer">
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Item Description / Name</label>
                    <input type="text" name="item_name" id="itemNameInput" placeholder="e.g. Spot Welding Machine 25kVA"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>

                <div id="qtyUnitContainer" class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Qty</label>
                        <input type="number" name="quantity" id="quantityInput" step="0.0001" min="0.0001" placeholder="e.g. 5000"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Unit</label>
                        <input type="text" name="unit" id="unitInput" placeholder="e.g. kg" value="kg"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Total Bill Amount (₹)</label>
                    <input type="number" name="total_amount" step="0.01" min="0" placeholder="e.g. 425000.00" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">GST Rate Slab (%)</label>
                    <select name="gst_rate" id="gstRateSelect" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-bold">
                        <option value="0">0% (GST Exempt / Nil)</option>
                        <option value="5">5% GST</option>
                        <option value="12">12% GST</option>
                        <option value="18" selected>18% GST (Standard)</option>
                        <option value="28">28% GST</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Payment Status</label>
                    <select name="payment_status" id="paymentStatusSelect"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-bold">
                        <option value="paid" selected>✓ Paid (Fully Settled)</option>
                        <option value="unpaid">⏳ Unpaid (Pending Bill)</option>
                        <option value="partially_paid">⚡ Partially Paid</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Purchase Date</label>
                    <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-2">
                <button type="button" onclick="toggleInlineForm('purchaseFormContainer', document.querySelector('button[onclick*=\'purchaseFormContainer\']'))" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold py-2.5 px-5 rounded-xl transition cursor-pointer">Cancel</button>
                <button type="submit" class="btn-primary py-2.5 px-6 text-sm font-bold shadow-xs">
                    Log Purchase Entry
                </button>
            </div>
        </form>
    </div>
</div>

    <!-- 2. EDIT FORM AT THE TOP (Revealed when Edit clicked) -->
    <div id="editPurchaseCardContainer" class="hidden transition-all duration-300 ease-in-out">
        <div class="bg-amber-50/50 rounded-2xl shadow-sm border border-amber-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-amber-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Edit Purchase Record Details
                </h3>
                <button type="button" onclick="closeEditPurchaseCard()" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition cursor-pointer">&times; Close</button>
            </div>
            <form id="editPurchaseForm" method="POST" class="ajax-form space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-combobox name="purchase_type"
                                id="edit_purchase_type"
                                label="Purchase Category"
                                placeholder="Search or type purchase category..."
                                :options="$purchaseTypeOptions"
                                :allowCustom="true"
                                required />
                    <div id="edit_rawMaterialSelectContainer">
                        <x-combobox name="raw_material_id"
                                    id="edit_raw_material_id"
                                    label="Raw Material Sub-Category"
                                    placeholder="Select Existing Raw Material..."
                                    :options="$rawMaterialOptions" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Vendor / Supplier Name</label>
                        <input type="text" name="vendor_name" id="edit_vendor_name" required
                               class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Bill / Invoice No. (Optional)</label>
                        <input type="text" name="bill_number" id="edit_bill_number"
                               class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Item Description / Name</label>
                        <input type="text" name="item_name" id="edit_item_name"
                               class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Qty</label>
                            <input type="number" name="quantity" id="edit_quantity" step="0.0001" min="0.0001"
                                   class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Unit</label>
                            <input type="text" name="unit" id="edit_unit"
                                   class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Total Bill Amount (₹)</label>
                        <input type="number" name="total_amount" id="edit_total_amount" step="0.01" min="0" required
                               class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700 font-bold">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">GST Rate Slab (%)</label>
                        <select name="gst_rate" id="edit_gst_rate" required
                                class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700 font-bold">
                            <option value="0">0% (GST Exempt / Nil)</option>
                            <option value="5">5% GST</option>
                            <option value="12">12% GST</option>
                            <option value="18">18% GST (Standard)</option>
                            <option value="28">28% GST</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Payment Status</label>
                        <select name="payment_status" id="edit_payment_status"
                                class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700 font-bold">
                            <option value="paid">✓ Paid (Fully Settled)</option>
                            <option value="unpaid">⏳ Unpaid (Pending Bill)</option>
                            <option value="partially_paid">⚡ Partially Paid</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Purchase Date</label>
                        <input type="date" name="purchase_date" id="edit_purchase_date" required
                               class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700">
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" onclick="closeEditPurchaseCard()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold py-2.5 px-5 rounded-xl transition cursor-pointer">Cancel</button>
                    <button type="submit" class="btn-primary py-2 px-6 text-xs font-bold">Update Purchase Entry</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. Purchase Bills Ledger Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Purchase History & Bills Ledger
        </h3>
        
        <div class="overflow-x-auto w-full max-w-full">
            <table class="erp-datatable min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-[#EDF4FA] text-black divide-x divide-slate-200">
                    <tr>
                        <th class="px-4 py-3.5 text-center text-xs font-bold uppercase w-12">#</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Date</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Vendor / Supplier</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Category</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Item / Machinery Name</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Quantity</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">GST Slab</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Total Bill (₹)</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">Payment Status</th>
                        <th class="px-4 py-3.5 text-center text-xs font-bold uppercase w-24">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($purchases as $pur)
                        <tr id="row-pur-{{ $pur->id }}" class="hover:bg-slate-50 transition">
                            <td class="px-4 py-4 text-center font-bold text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 text-slate-600 font-medium text-xs">{{ $pur->purchase_date->format('d M Y') }}</td>
                            <td class="px-6 py-4 font-bold text-slate-800">
                                {{ $pur->vendor_name }}
                                @if($pur->bill_number)
                                    <div class="text-[10px] text-slate-400 font-mono">Bill #: {{ $pur->bill_number }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($pur->purchase_type === 'raw_material')
                                    <span class="px-2.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 text-[10px] rounded font-bold uppercase">Raw Material</span>
                                @elseif($pur->purchase_type === 'office_assets')
                                    <span class="px-2.5 py-0.5 bg-cyan-50 text-cyan-700 border border-cyan-200 text-[10px] rounded font-bold uppercase">Office Assets</span>
                                @elseif($pur->purchase_type === 'machinery')
                                    <span class="px-2.5 py-0.5 bg-purple-50 text-purple-700 border border-purple-200 text-[10px] rounded font-bold uppercase">Machinery / Capital</span>
                                @elseif($pur->purchase_type === 'factory_spares')
                                    <span class="px-2.5 py-0.5 bg-indigo-50 text-indigo-700 border border-indigo-200 text-[10px] rounded font-bold uppercase">Gas & Spares</span>
                                @elseif($pur->purchase_type === 'supplies')
                                    <span class="px-2.5 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 text-[10px] rounded font-bold uppercase">Supplies & Tools</span>
                                @elseif($pur->purchase_type === 'vehicle_transport')
                                    <span class="px-2.5 py-0.5 bg-teal-50 text-teal-700 border border-teal-200 text-[10px] rounded font-bold uppercase">Vehicle & Freight</span>
                                @else
                                    <span class="px-2.5 py-0.5 bg-slate-100 text-slate-700 border border-slate-200 text-[10px] rounded font-bold uppercase">{{ ucwords(str_replace('_', ' ', $pur->purchase_type)) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $pur->item_name }}</td>
                            <td class="px-6 py-4 text-right font-medium text-slate-700">{{ number_format($pur->quantity, 2) }} {{ $pur->unit }}</td>
                            <td class="px-6 py-4 text-right text-slate-700 font-medium">
                                <span class="font-bold text-blue-600">{{ number_format($pur->gst_rate, 0) }}%</span>
                                <div class="text-[10px] text-slate-400">₹{{ number_format($pur->gst_amount, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-slate-900">₹{{ number_format($pur->total_amount, 2) }}</td>
                            <td class="px-6 py-4 text-center pur-payment-cell">
                                @if(($pur->payment_status ?? 'paid') === 'paid')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs">
                                        PAID
                                    </span>
                                @elseif(($pur->payment_status ?? 'paid') === 'partially_paid')
                                    <button type="button" 
                                            onclick="openVendorPaymentModal({{ $pur->id }}, '{{ addslashes($pur->vendor_name) }}', {{ $pur->remaining_balance }})"
                                            title="Click to record vendor payment"
                                            class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-300 hover:bg-amber-200 transition cursor-pointer shadow-2xs">
                                        PARTIAL (₹{{ number_format($pur->remaining_balance, 0) }} DUE)
                                    </button>
                                @else
                                    <button type="button" 
                                            onclick="openVendorPaymentModal({{ $pur->id }}, '{{ addslashes($pur->vendor_name) }}', {{ $pur->remaining_balance }})"
                                            title="Click to record vendor payment"
                                            class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-300 hover:bg-rose-200 transition cursor-pointer shadow-2xs">
                                        UNPAID
                                    </button>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-2">
                                    <button type="button" 
                                            title="Edit Purchase Record"
                                            onclick="openEditPurchaseForm({{ $pur->id }}, '{{ $pur->purchase_type }}', '{{ $pur->raw_material_id ?? '' }}', '{{ addslashes($pur->vendor_name) }}', '{{ addslashes($pur->bill_number ?? '') }}', '{{ addslashes($pur->item_name) }}', {{ $pur->quantity }}, '{{ $pur->unit }}', {{ $pur->total_amount }}, {{ (int)$pur->gst_rate }}, '{{ $pur->purchase_date->format('Y-m-d') }}', '{{ $pur->payment_status ?? 'paid' }}')"
                                            class="w-8 h-8 p-1.5 inline-flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-xs transition duration-150 transform hover:scale-105">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button type="button" 
                                            title="Delete Purchase Record"
                                            onclick="deletePurchaseRecord({{ $pur->id }}, '{{ addslashes($pur->vendor_name) }}')"
                                            class="w-8 h-8 p-1.5 inline-flex items-center justify-center rounded-lg bg-rose-500 hover:bg-rose-600 text-white shadow-xs transition duration-150 transform hover:scale-105">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
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
                                    <p class="text-xs text-slate-400">There are no purchase bills recorded yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $purchases->links() }}
        </div>
    </div>
</div>

<!-- Record Vendor Payment Modal -->
<div id="recordVendorPaymentModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" onclick="closeVendorPaymentModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Record Vendor Payment</h3>
                    <p class="text-xs text-slate-500 font-medium">Supplier: <span class="text-purple-600 font-bold" id="modalVendorName"></span> | Dues Remaining: <span class="text-rose-600 font-bold">₹<span id="modalVendorRemainingText">0.00</span></span></p>
                </div>
                <button type="button" onclick="closeVendorPaymentModal()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>

            <form action="" method="POST" onsubmit="submitVendorPayment(event)">
                @csrf
                <input type="hidden" id="modalPurchaseId" name="purchase_id">

                <div class="p-6 space-y-4 text-xs">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-600 uppercase mb-1">Payout Amount (₹)</label>
                            <input type="number" step="0.01" min="0.01" name="amount" id="modalVendorPayAmount" required
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 text-slate-800 font-extrabold">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-600 uppercase mb-1">Payment Date</label>
                            <input type="date" name="payment_date" id="modalVendorPayDate" required
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 text-slate-800 font-medium">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-600 uppercase mb-1">Payment Method</label>
                            <select name="payment_method" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500 text-slate-800 font-bold">
                                <option value="bank_transfer">Bank Transfer (NEFT/RTGS)</option>
                                <option value="cheque">Cheque</option>
                                <option value="upi">UPI / Online</option>
                                <option value="cash">Cash</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-600 uppercase mb-1">Account Source</label>
                            <select name="account_type" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500 text-slate-800 font-bold">
                                <option value="bank">Bank Account</option>
                                <option value="cash">Cash in Hand</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Reference / UTR / Cheque No.</label>
                        <input type="text" name="reference_number" placeholder="e.g. UTR-SUPPLIER-9901"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500 text-slate-800 font-mono">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Internal Notes</label>
                        <textarea name="notes" rows="2" placeholder="Optional notes for vendor ledger..."
                                  class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500 text-slate-800"></textarea>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-3 border-t border-slate-100 flex justify-end space-x-3">
                    <button type="button" onclick="closeVendorPaymentModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800">Cancel</button>
                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white py-2 px-5 text-xs font-bold rounded-xl shadow-xs transition">
                        Record Vendor Payout
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    window.toggleInlineForm = function(containerId, btn) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        const isHidden = container.classList.contains('hidden');
        if (isHidden) {
            container.classList.remove('hidden');
            if (btn) {
                btn.classList.replace('bg-blue-600', 'bg-slate-700');
                btn.classList.replace('hover:bg-blue-700', 'hover:bg-slate-800');
                const icon = btn.querySelector('svg');
                if (icon) icon.style.transform = 'rotate(45deg)';
            }
        } else {
            container.classList.add('hidden');
            if (btn) {
                btn.classList.replace('bg-slate-700', 'bg-blue-600');
                btn.classList.replace('hover:bg-slate-800', 'hover:bg-blue-700');
                const icon = btn.querySelector('svg');
                if (icon) icon.style.transform = 'rotate(0deg)';
            }
            const form = container.querySelector('form');
            if (form) {
                form.reset();
                form.querySelectorAll('.combobox-wrapper').forEach(w => {
                    if (window.ERPComboboxManager) window.ERPComboboxManager.clear(w);
                });
                if (typeof window.handlePurchaseTypeChange === 'function') window.handlePurchaseTypeChange();
            }
            if (window.history && window.history.replaceState) {
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        }
    };

    window.handleRawMaterialSelectChange = function() {
        const hiddenInp = document.getElementById('rawMaterialSelect_hidden') || document.getElementById('rawMaterialSelect');
        if (!hiddenInp) return;
        const wrapper = hiddenInp.closest('.combobox-wrapper');
        if (!wrapper) return;
        const val = hiddenInp.value;
        const opt = wrapper.querySelector(`.combobox-option[data-value="${CSS.escape(val)}"]`);
        const itemNameInput = document.getElementById('itemNameInput');
        const unitInput = document.getElementById('unitInput');
        if (opt && opt.dataset.name && itemNameInput) {
            itemNameInput.value = opt.dataset.name;
        }
        if (opt && opt.dataset.unit && unitInput) {
            unitInput.value = opt.dataset.unit;
        }
    };

    window.handleEditRawMaterialSelectChange = function() {
        const hiddenInp = document.getElementById('edit_raw_material_id_hidden') || document.getElementById('edit_raw_material_id');
        if (!hiddenInp) return;
        const wrapper = hiddenInp.closest('.combobox-wrapper');
        if (!wrapper) return;
        const val = hiddenInp.value;
        const opt = wrapper.querySelector(`.combobox-option[data-value="${CSS.escape(val)}"]`);
        const editItemName = document.getElementById('edit_item_name');
        const editUnit = document.getElementById('edit_unit');
        if (opt && opt.dataset.name && editItemName) {
            editItemName.value = opt.dataset.name;
        }
        if (opt && opt.dataset.unit && editUnit) {
            editUnit.value = opt.dataset.unit;
        }
    };

    window.handlePurchaseTypeChange = function() {
        const hiddenInp = document.getElementById('purchaseTypeSelect_hidden') || document.getElementById('purchaseTypeSelect');
        const searchInp = document.getElementById('purchaseTypeSelect_search');
        const val = (hiddenInp ? hiddenInp.value : '') || (searchInp ? searchInp.value : '');
        const type = val.toLowerCase().trim();

        const isRawMaterial = type === '' || type === 'raw_material' || type.includes('raw_material') || type.includes('raw material');

        const rmHiddenInp = document.getElementById('rawMaterialSelect_hidden') || document.getElementById('rawMaterialSelect');
        const rmSearchInp = document.getElementById('rawMaterialSelect_search');
        const rmWrapper = rmHiddenInp ? rmHiddenInp.closest('.combobox-wrapper') : null;
        const itemNameInput = document.getElementById('itemNameInput');
        const quantityInput = document.getElementById('quantityInput');
        const unitInput = document.getElementById('unitInput');

        if (isRawMaterial) {
            if (rmHiddenInp) rmHiddenInp.disabled = false;
            if (rmSearchInp) {
                rmSearchInp.disabled = false;
                rmSearchInp.classList.remove('bg-slate-100', 'opacity-50', 'cursor-not-allowed');
            }
            if (rmWrapper) rmWrapper.classList.remove('pointer-events-none', 'opacity-50');

            if (quantityInput) {
                quantityInput.disabled = false;
                quantityInput.classList.remove('bg-slate-100', 'opacity-50', 'cursor-not-allowed');
                quantityInput.classList.add('bg-slate-50');
            }
            if (unitInput) {
                unitInput.disabled = false;
                unitInput.classList.remove('bg-slate-100', 'opacity-50', 'cursor-not-allowed');
                unitInput.classList.add('bg-slate-50');
            }
            if (itemNameInput) {
                itemNameInput.disabled = false;
                itemNameInput.classList.remove('bg-slate-100', 'opacity-50', 'cursor-not-allowed');
                itemNameInput.classList.add('bg-slate-50');
            }

            if (rmHiddenInp && rmHiddenInp.value) {
                window.handleRawMaterialSelectChange();
            }
        } else {
            if (rmHiddenInp) {
                rmHiddenInp.disabled = true;
                rmHiddenInp.value = '';
            }
            if (rmSearchInp) {
                rmSearchInp.disabled = true;
                rmSearchInp.value = '';
                rmSearchInp.classList.add('bg-slate-100', 'opacity-50', 'cursor-not-allowed');
            }
            if (rmWrapper) {
                rmWrapper.classList.add('pointer-events-none', 'opacity-50');
                if (window.ERPComboboxManager) window.ERPComboboxManager.syncDisplay(rmWrapper);
            }

            if (quantityInput) {
                quantityInput.disabled = true;
                quantityInput.value = '';
                quantityInput.classList.remove('bg-slate-50');
                quantityInput.classList.add('bg-slate-100', 'opacity-50', 'cursor-not-allowed');
            }
            if (unitInput) {
                unitInput.disabled = true;
                unitInput.value = '';
                unitInput.classList.remove('bg-slate-50');
                unitInput.classList.add('bg-slate-100', 'opacity-50', 'cursor-not-allowed');
            }
            if (itemNameInput) {
                itemNameInput.disabled = false;
                itemNameInput.required = true;
                itemNameInput.classList.remove('bg-slate-100', 'opacity-50', 'cursor-not-allowed');
                itemNameInput.classList.add('bg-slate-50');
            }
        }
    };

    window.openEditPurchaseForm = function(id, type, rawMaterialId, vendorName, billNumber, itemName, qty, unit, totalAmount, gstRate, purchaseDate, paymentStatus) {
        const createForm = document.getElementById('purchaseFormContainer');
        if (createForm && !createForm.classList.contains('hidden')) {
            createForm.classList.add('hidden');
        }

        const editCard = document.getElementById('editPurchaseCardContainer');
        const form = document.getElementById('editPurchaseForm');

        form.action = "{{ url('/purchases') }}/" + id;
        
        const ptInp = document.getElementById('edit_purchase_type_hidden') || document.getElementById('edit_purchase_type');
        if (ptInp) {
            ptInp.value = type;
            const ptWrapper = ptInp.closest('.combobox-wrapper');
            if (ptWrapper && window.ERPComboboxManager) window.ERPComboboxManager.syncDisplay(ptWrapper);
        }

        const rmInp = document.getElementById('edit_raw_material_id_hidden') || document.getElementById('edit_raw_material_id');
        if (rmInp) {
            rmInp.value = rawMaterialId || '';
            const rmWrapper = rmInp.closest('.combobox-wrapper');
            if (rmWrapper && window.ERPComboboxManager) window.ERPComboboxManager.syncDisplay(rmWrapper);
        }

        document.getElementById('edit_vendor_name').value = vendorName;
        document.getElementById('edit_bill_number').value = billNumber || '';
        document.getElementById('edit_item_name').value = itemName;
        document.getElementById('edit_quantity').value = qty;
        document.getElementById('edit_unit').value = unit;
        document.getElementById('edit_total_amount').value = totalAmount;
        document.getElementById('edit_gst_rate').value = gstRate;
        document.getElementById('edit_purchase_date').value = purchaseDate;
        if (document.getElementById('edit_payment_status')) {
            document.getElementById('edit_payment_status').value = paymentStatus || 'paid';
        }

        window.handleEditPurchaseTypeChange();

        editCard.classList.remove('hidden');
        editCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    };

    window.closeEditPurchaseCard = function() {
        const editCard = document.getElementById('editPurchaseCardContainer');
        if (editCard) editCard.classList.add('hidden');
    };

    window.handleEditPurchaseTypeChange = function() {
        const ptInp = document.getElementById('edit_purchase_type_hidden') || document.getElementById('edit_purchase_type');
        const searchInp = document.getElementById('edit_purchase_type_search');
        const val = (ptInp ? ptInp.value : '') || (searchInp ? searchInp.value : '');
        const type = val.toLowerCase().trim();

        const isRawMaterial = type === '' || type === 'raw_material' || type.includes('raw_material') || type.includes('raw material');

        const rmHiddenInp = document.getElementById('edit_raw_material_id_hidden') || document.getElementById('edit_raw_material_id');
        const rmSearchInp = document.getElementById('edit_raw_material_id_search');
        const itemNameInput = document.getElementById('edit_item_name');
        const quantityInput = document.getElementById('edit_quantity');
        const unitInput = document.getElementById('edit_unit');

        if (isRawMaterial) {
            if (rmHiddenInp) rmHiddenInp.disabled = false;
            if (rmSearchInp) {
                rmSearchInp.disabled = false;
                rmSearchInp.classList.remove('bg-slate-100', 'opacity-50', 'cursor-not-allowed');
            }
            if (quantityInput) {
                quantityInput.disabled = false;
                quantityInput.classList.remove('bg-slate-100', 'opacity-50', 'cursor-not-allowed');
                quantityInput.classList.add('bg-white');
            }
            if (unitInput) {
                unitInput.disabled = false;
                unitInput.classList.remove('bg-slate-100', 'opacity-50', 'cursor-not-allowed');
                unitInput.classList.add('bg-white');
            }
            if (itemNameInput) {
                itemNameInput.disabled = false;
                itemNameInput.classList.remove('bg-slate-100', 'opacity-50', 'cursor-not-allowed');
                itemNameInput.classList.add('bg-white');
            }
        } else {
            if (rmHiddenInp) {
                rmHiddenInp.disabled = true;
                rmHiddenInp.value = '';
            }
            if (rmSearchInp) {
                rmSearchInp.disabled = true;
                rmSearchInp.value = '';
                rmSearchInp.classList.add('bg-slate-100', 'opacity-50', 'cursor-not-allowed');
            }
            if (quantityInput) {
                quantityInput.disabled = true;
                quantityInput.value = '';
                quantityInput.classList.remove('bg-white');
                quantityInput.classList.add('bg-slate-100', 'opacity-50', 'cursor-not-allowed');
            }
            if (unitInput) {
                unitInput.disabled = true;
                unitInput.value = '';
                unitInput.classList.remove('bg-white');
                unitInput.classList.add('bg-slate-100', 'opacity-50', 'cursor-not-allowed');
            }
            if (itemNameInput) {
                itemNameInput.disabled = false;
                itemNameInput.required = true;
                itemNameInput.classList.remove('bg-slate-100', 'opacity-50', 'cursor-not-allowed');
                itemNameInput.classList.add('bg-white');
            }
        }
    };

    window.deletePurchaseRecord = function(id, name) {
        window.confirmDelete(
            "Delete Purchase Record?",
            "Are you sure you want to delete purchase bill from '" + name + "'? This action cannot be undone.",
            function() {
                if (typeof $ !== 'undefined') {
                    $.ajax({
                        url: "{{ url('/purchases') }}/" + id,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function(res) {
                            if (res.success) {
                                if (window.ERPTableHelper) {
                                    window.ERPTableHelper.removeRow('#row-pur-' + id);
                                } else {
                                    $('#row-pur-' + id).fadeOut(300, function() { $(this).remove(); });
                                }
                                if (window.showToast) window.showToast('success', res.message);
                            }
                        },
                        error: function(err) {
                            const msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Failed to delete purchase record.';
                            if (window.showToast) window.showToast('error', msg);
                        }
                    });
                }
            }
        );
    };

    document.addEventListener('change', function(e) {
        if (e.target && (e.target.id === 'purchaseTypeSelect_hidden' || e.target.id === 'purchaseTypeSelect_search' || e.target.id === 'purchaseTypeSelect')) {
            window.handlePurchaseTypeChange();
        }
        if (e.target && (e.target.id === 'rawMaterialSelect_hidden' || e.target.id === 'rawMaterialSelect_search' || e.target.id === 'rawMaterialSelect')) {
            window.handleRawMaterialSelectChange();
        }
        if (e.target && (e.target.id === 'edit_purchase_type_hidden' || e.target.id === 'edit_purchase_type_search' || e.target.id === 'edit_purchase_type')) {
            window.handleEditPurchaseTypeChange();
        }
        if (e.target && (e.target.id === 'edit_raw_material_id_hidden' || e.target.id === 'edit_raw_material_id_search' || e.target.id === 'edit_raw_material_id')) {
            window.handleEditRawMaterialSelectChange();
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target && (e.target.id === 'purchaseTypeSelect_search' || e.target.id === 'purchaseTypeSelect')) {
            window.handlePurchaseTypeChange();
        }
        if (e.target && (e.target.id === 'edit_purchase_type_search' || e.target.id === 'edit_purchase_type')) {
            window.handleEditPurchaseTypeChange();
        }
    });

    (function initPurchasePrefill() {
        function runPrefill() {
            if (typeof window.handlePurchaseTypeChange === 'function') {
                window.handlePurchaseTypeChange();
            }

            const urlParams = new URLSearchParams(window.location.search);
            const matId = urlParams.get('material_id') || urlParams.get('prefill_raw_material') || "{{ $prefillMaterialId ?? '' }}";
            const prefillQty = urlParams.get('qty') || urlParams.get('prefill_qty') || "{{ $prefillQty ?? '' }}";
            const prefillPrice = urlParams.get('price') || urlParams.get('prefill_price') || "{{ $prefillPrice ?? '' }}";

            if (urlParams.has('open') || matId) {
                const formContainer = document.getElementById('purchaseFormContainer');
                if (formContainer) {
                    formContainer.classList.remove('hidden');
                    formContainer.scrollIntoView({ behavior: 'smooth' });
                }

                const typeSelect = document.getElementById('purchaseTypeSelect_hidden') || document.getElementById('purchaseTypeSelect');
                if (typeSelect) {
                    typeSelect.value = 'raw_material';
                    const wrapper = typeSelect.closest('.combobox-wrapper');
                    if (wrapper && window.ERPComboboxManager) {
                        window.ERPComboboxManager.syncDisplay(wrapper);
                    }
                    if (typeof window.handlePurchaseTypeChange === 'function') {
                        window.handlePurchaseTypeChange();
                    }
                }

                if (matId) {
                    const matSelect = document.getElementById('rawMaterialSelect_hidden') || document.getElementById('rawMaterialSelect');
                    if (matSelect) {
                        matSelect.value = matId;
                        const wrapper = matSelect.closest('.combobox-wrapper');
                        if (wrapper && window.ERPComboboxManager) {
                            window.ERPComboboxManager.syncDisplay(wrapper);
                        }
                        matSelect.dispatchEvent(new Event('change', { bubbles: true }));
                        if (typeof window.handleRawMaterialSelectChange === 'function') {
                            window.handleRawMaterialSelectChange();
                        }
                    }
                }

                if (prefillQty && parseFloat(prefillQty) > 0) {
                    const qtyInput = document.getElementById('quantityInput');
                    if (qtyInput) {
                        qtyInput.value = prefillQty;
                    }
                }

                if (prefillQty && prefillPrice && parseFloat(prefillQty) > 0 && parseFloat(prefillPrice) > 0) {
                    const totalInput = document.querySelector('input[name="total_amount"]');
                    if (totalInput && (!totalInput.value || parseFloat(totalInput.value) <= 0)) {
                        totalInput.value = (parseFloat(prefillQty) * parseFloat(prefillPrice)).toFixed(2);
                    }
                }

                if (window.history && window.history.replaceState) {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            }
        }

        runPrefill();
        document.addEventListener('DOMContentLoaded', runPrefill);
    })();

    window.openVendorPaymentModal = function(id, vendorName, remainingBalance) {
        document.getElementById('modalPurchaseId').value = id;
        document.getElementById('modalVendorName').innerText = vendorName;
        document.getElementById('modalVendorRemainingText').innerText = parseFloat(remainingBalance).toFixed(2);
        document.getElementById('modalVendorPayAmount').value = parseFloat(remainingBalance).toFixed(2);
        document.getElementById('modalVendorPayAmount').max = parseFloat(remainingBalance);
        document.getElementById('modalVendorPayDate').value = new Date().toISOString().split('T')[0];
        
        document.getElementById('recordVendorPaymentModal').classList.remove('hidden');
    };

    window.closeVendorPaymentModal = function() {
        document.getElementById('recordVendorPaymentModal').classList.add('hidden');
    };

    window.submitVendorPayment = function(e) {
        e.preventDefault();
        const purId = document.getElementById('modalPurchaseId').value;
        const $submitBtn = $(e.target).find('button[type="submit"]');
        if (window.setButtonLoading) window.setButtonLoading($submitBtn, true, 'Recording...');

        const formData = new FormData(e.target);
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        fetch(`/purchases/${purId}/record-payment`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(response => {
            if (window.setButtonLoading) window.setButtonLoading($submitBtn, false);
            closeVendorPaymentModal();
            if (window.showToast) {
                window.showToast('success', response.message || 'Vendor payment recorded successfully!');
            }
            const $row = $(`#row-pur-${purId}`);
            if ($row.length) {
                $row.find('.pur-payment-cell').html(`
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs">
                        PAID
                    </span>
                `);
                if (window.ERPTableHelper) window.ERPTableHelper.highlightRow($row);
            }
            if (window.clearPageCache) window.clearPageCache();
        })
        .catch(err => {
            if (window.setButtonLoading) window.setButtonLoading($submitBtn, false);
            if (window.showToast) window.showToast('error', 'Failed to record vendor payment.');
            else alert('Failed to record vendor payment.');
        });
    };
</script>
@endsection
