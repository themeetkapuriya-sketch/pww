@extends('layouts.app')

@section('title', 'Invoice Builder')

@section('content')
@php
    $clientPlantOptions = [];
    foreach ($clients as $client) {
        if ($client->plants->isNotEmpty()) {
            foreach ($client->plants as $plant) {
                $fullText = $client->company_name . ' — ' . $plant->plant_name . ' (' . $plant->state . ')';
                $clientPlantOptions[] = [
                    'value' => $plant->id,
                    'label' => $fullText,
                    'badge' => $plant->state,
                    'search' => strtolower($fullText . ' ' . ($plant->gst_number ?? '') . ' ' . ($plant->shipping_address ?? '')),
                    'data' => [
                        'state' => $plant->state
                    ]
                ];
            }
        }
    }

    $invoiceProductOptions = [];
    foreach ($finishedGoods as $g) {
        $kgPrice = $g->price_per_kg ?? (($g->unit_weight_kg ?? 0) > 0 ? round($g->selling_price / $g->unit_weight_kg, 2) : 0);
        $prodLabel = $g->product_name . (($g->unit_weight_kg ?? 0) > 0 ? ' (' . number_format($g->unit_weight_kg, 3) . ' Kg)' : '');
        $invoiceProductOptions[] = [
            'value' => 'product_' . $g->id,
            'label' => $prodLabel,
            'search' => strtolower($prodLabel . ' ' . $g->sku),
            'data' => [
                'type' => 'product',
                'price' => $g->selling_price,
                'price-pcs' => $g->selling_price,
                'price-kg' => $kgPrice,
                'weight' => $g->unit_weight_kg ?? 0.000,
                'uom' => $g->uom ?? 'piece'
            ]
        ];
    }

    $invoiceRawMaterialOptions = [];
    if (isset($rawMaterials) && $rawMaterials->isNotEmpty()) {
        foreach ($rawMaterials as $rm) {
            $rmLabel = $rm->material_name . ' (Stock: ' . number_format($rm->current_stock, 1) . ' ' . $rm->unit . ')';
            $invoiceRawMaterialOptions[] = [
                'value' => 'raw_material_' . $rm->id,
                'label' => $rmLabel,
                'search' => strtolower($rmLabel . ' ' . $rm->unit),
                'data' => [
                    'type' => 'raw_material',
                    'price' => '0.00',
                    'price-pcs' => '0.00',
                    'price-kg' => '0.00',
                    'weight' => '1.000',
                    'uom' => $rm->unit ?? 'kg'
                ]
            ];
        }
    }

    $invoiceComboboxHtml = View::make('components.combobox', [
        'name' => 'product_ids[]',
        'placeholder' => 'Select product...',
        'options' => $invoiceProductOptions,
        'required' => true,
    ])->render();

    $rawMaterialComboboxHtml = View::make('components.combobox', [
        'name' => 'product_ids[]',
        'placeholder' => 'Select raw material / scrap...',
        'options' => $invoiceRawMaterialOptions,
        'required' => true,
    ])->render();
@endphp
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Invoice Ledger</h1>
            <p class="text-sm text-slate-500">Review generated invoices or log new custom tax invoices.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="https://ewaybillgst.gov.in/" 
               target="_blank" 
               rel="noopener noreferrer"
               title="Open Official Government E-Way Bill Portal"
               class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-md transition duration-150 flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                <span>E-Way Bill Portal</span>
            </a>
            <button type="button" 
                    id="toggleInvoiceFormBtn"
                    onclick="toggleInlineForm('section-manual-builder', this)" 
                    class="{{ !empty($prefillOrder) ? 'bg-slate-700 hover:bg-slate-800' : 'bg-blue-600 hover:bg-blue-700' }} text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-md transition duration-150 flex items-center space-x-2">
                <svg class="w-4 h-4 transition-transform duration-200" style="{{ !empty($prefillOrder) ? 'transform: rotate(45deg);' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Create Custom Invoice</span>
            </button>
        </div>
    </div>

    <!-- Empty Billing Row Template for JS Cloning -->
    <template id="emptyBillingRowTemplate">
        <div class="billing-row flex items-center space-x-3 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
            <select name="product_ids[]" class="flex-grow bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" required>
                <option value="">Select product...</option>
                @foreach ($finishedGoods as $g)
                    <option value="{{ $g->id }}" data-price="{{ $g->selling_price }}">{{ $g->product_name }}</option>
                @endforeach
            </select>
            <input type="number" name="quantities[]" min="1" placeholder="Qty" class="w-24 bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" required>
            <input type="number" name="unit_prices[]" step="0.01" min="0" placeholder="Price" class="w-32 bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" required>
            <button type="button" class="remove-billing-row-btn text-rose-500 hover:text-rose-600 font-bold px-2 text-sm">✕</button>
        </div>
    </template>

    <!-- Direct Invoice Builder (Expandable Full Width) -->
    <div id="section-manual-builder" class="{{ !empty($prefillOrder) ? '' : 'hidden' }} transition-all duration-300 ease-in-out space-y-6">
        <div id="invoiceFormCard" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col transition-all duration-300">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4 pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-3">
                    <h3 id="invoiceFormTitle" class="text-base font-bold text-slate-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Direct Invoice Itemizer
                    </h3>

                    <!-- Invoice Mode Toggle Pills -->
                    <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200 text-xs font-bold">
                        <button type="button" id="modeBtnFinishedGoods" onclick="switchInvoiceMode('finished_goods')"
                                class="px-3 py-1.5 rounded-lg transition-all shadow-sm bg-blue-600 text-white font-bold">
                            📦 Finished Goods Sale
                        </button>
                        <button type="button" id="modeBtnRawMaterial" onclick="switchInvoiceMode('raw_material')"
                                class="px-3 py-1.5 rounded-lg transition-all text-slate-600 hover:text-slate-900 font-medium">
                            🧱 Raw Material / Scrap Sale
                        </button>
                    </div>
                </div>
                <button type="button" id="invoiceFormCloseBtn" onclick="cancelInvoiceForm()" class="text-xs font-bold text-slate-400 hover:text-slate-600">&times; Close</button>
            </div>
            <form id="customInvoiceForm" action="{{ route('invoice.generate') }}" method="POST" class="ajax-form space-y-4 flex-grow">
                @csrf
                <input type="hidden" name="sales_order_id" id="salesOrderIdHidden" value="{{ $prefillOrder->id ?? '' }}">
                <input type="hidden" name="invoice_id" id="invoiceIdHidden" value="">
                <input type="hidden" name="invoice_mode" id="invoiceModeInput" value="finished_goods">
                
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div id="invoiceNumberContainer" class="md:col-span-3">
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Invoice Number</label>
                        <input type="text" name="invoice_number" value="{{ \App\Models\Invoice::generateNextInvoiceNumber() }}" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-mono">
                    </div>

                    <!-- Registered Client Select (Finished Goods Mode) -->
                    <div id="registeredClientContainer" class="md:col-span-4 relative">
                        <x-combobox name="plant_id"
                                    id="invoiceClientSelect"
                                    label="Select Client & Plant"
                                    placeholder="Search company, plant, or state..."
                                    :options="$clientPlantOptions"
                                    :value="!empty($prefillOrder) ? $prefillOrder->plant_id : ''"
                                    required />
                    </div>

                    <!-- Custom Buyer Name Input (Raw Material Mode) -->
                    <div id="customClientContainer" class="hidden md:col-span-4">
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Buyer / Client Name (Custom)</label>
                        <input type="text" name="custom_client_name" id="customClientNameInput" placeholder="e.g. Maruti Scrap Merchants / Ramesh Traders"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-semibold">
                    </div>

                    <!-- GST Slab Selector (Raw Material Mode) -->
                    <div id="gstRateSlabContainer" class="hidden md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">GST Rate Slab</label>
                        <select name="gst_rate" id="rawMaterialGstRateSelect" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-bold" onchange="onGstRateSelectChange()">
                            <option value="18">18% GST (Standard)</option>
                            <option value="12">12% GST</option>
                            <option value="5">5% GST</option>
                            <option value="0">0% GST (Exempt / Non-GST)</option>
                        </select>
                    </div>

                    <!-- Buyer GSTIN Input (Shows when GST Rate > 0 in Raw Material Mode) -->
                    <div id="customGstinContainer" class="hidden md:col-span-3">
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Buyer GSTIN (Optional)</label>
                        <input type="text" name="custom_buyer_gstin" id="customBuyerGstinInput" placeholder="e.g. 24ABCDE1234F1Z5"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-mono uppercase">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Invoice Date</label>
                        <input type="date" name="invoice_date" value="{{ date('Y-m-d') }}" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>
                    <div id="vehicleNumberContainer" class="md:col-span-3">
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Delivery Vehicle No. <span class="text-rose-500">*</span></label>
                        <input type="text" name="vehicle_number" required placeholder="e.g. GJ-03-BW-1234"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-mono uppercase">
                    </div>
                </div>

                <!-- Templates for item select options -->
                <template id="templateOptionsFinishedGoods">
                    <option value="">Select product...</option>
                    @foreach ($finishedGoods as $g)
                        @php
                            $kgPrice = $g->price_per_kg ?? (($g->unit_weight_kg ?? 0) > 0 ? round($g->selling_price / $g->unit_weight_kg, 2) : 0);
                        @endphp
                        <option value="product_{{ $g->id }}" data-type="product" data-price="{{ $g->selling_price }}" data-price-pcs="{{ $g->selling_price }}" data-price-kg="{{ $kgPrice }}" data-weight="{{ $g->unit_weight_kg ?? 0.000 }}" data-uom="{{ $g->uom ?? 'piece' }}">
                            {{ $g->product_name }} @if(($g->unit_weight_kg ?? 0) > 0)({{ number_format($g->unit_weight_kg, 3) }} Kg)@endif
                        </option>
                    @endforeach
                </template>

                <template id="templateOptionsRawMaterials">
                    <option value="">Select raw material / scrap...</option>
                    @if(isset($rawMaterials) && $rawMaterials->isNotEmpty())
                        @foreach ($rawMaterials as $rm)
                            <option value="raw_material_{{ $rm->id }}" data-type="raw_material" data-price="0.00" data-price-pcs="0.00" data-price-kg="0.00" data-weight="1.000" data-uom="{{ $rm->unit ?? 'kg' }}">
                                {{ $rm->material_name }} (Stock: {{ number_format($rm->current_stock, 1) }} {{ $rm->unit }})
                            </option>
                        @endforeach
                    @endif
                </template>

                <!-- Items rows container -->
                <div class="border-t border-slate-200 pt-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Billing Line Items</label>
                        <button type="button" id="addBillingRowBtn" class="text-blue-600 hover:text-blue-700 text-xs font-bold flex items-center">
                            + Add Row
                        </button>
                    </div>

                    <div id="billingRowsContainer" class="space-y-2">
                        @if(!empty($prefillOrder) && $prefillOrder->items->isNotEmpty())
                            @foreach($prefillOrder->items as $it)
                                <div class="billing-row flex items-center space-x-2 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                                    <div class="flex-grow">
                                        <x-combobox name="product_ids[]"
                                                    placeholder="Select product..."
                                                    :options="$invoiceProductOptions"
                                                    :value="'product_' . $it->product_id"
                                                    required />
                                    </div>
                                    <select name="billing_uoms[]" class="billing-uom-select w-24 shrink-0 bg-white border border-slate-200 rounded-xl py-2 px-2 text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="Pcs">Pcs</option>
                                        <option value="Kg">Kg</option>
                                    </select>
                                    <input type="number" name="quantities[]" step="any" min="0.01" value="{{ (float)$it->quantity }}" placeholder="Qty" class="w-20 bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 font-bold" required>
                                    <input type="number" name="unit_prices[]" step="0.01" min="0" value="{{ number_format((float)str_replace(',', '', $it->unit_price), 2, '.', '') }}" placeholder="Price" class="w-28 bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 font-bold" required>
                                    <button type="button" class="remove-billing-row-btn text-rose-500 hover:text-rose-600 font-bold px-2 text-sm">✕</button>
                                </div>
                            @endforeach
                        @else
                            <div class="billing-row flex items-center space-x-2 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                                <div class="flex-grow">
                                    <x-combobox name="product_ids[]"
                                                placeholder="Select product..."
                                                :options="$invoiceProductOptions"
                                                required />
                                </div>
                                <select name="billing_uoms[]" class="billing-uom-select w-24 shrink-0 bg-white border border-slate-200 rounded-xl py-2 px-2 text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="Pcs">Pcs</option>
                                    <option value="Kg">Kg</option>
                                </select>
                                <input type="number" name="quantities[]" step="any" min="0.01" placeholder="Qty" class="w-20 bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 font-bold" required>
                                <input type="number" name="unit_prices[]" step="0.01" min="0" placeholder="Price" class="w-28 bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 font-bold" required>
                                <button type="button" class="remove-billing-row-btn text-rose-500 hover:text-rose-600 font-bold px-2 text-sm">✕</button>
                            </div>
                        @endif
                    </div>

                    <!-- Inline Tax & Round Off Summary Bar under Billing Line Items -->
                    <div class="mt-4 p-4 bg-slate-50 border border-slate-200 rounded-xl">
                        <div class="flex flex-wrap items-center justify-between gap-4 text-xs font-semibold text-slate-600">
                            <div>
                                <span>Taxable Subtotal: </span>
                                <span class="font-bold text-slate-800 text-sm ml-1" id="live-taxable">₹0.00</span>
                            </div>
                            <div id="cgst-sgst-box" class="flex items-center space-x-3">
                                <span>CGST (9%): <strong class="text-slate-800 text-sm ml-1" id="live-cgst">₹0.00</strong></span>
                                <span>SGST (9%): <strong class="text-slate-800 text-sm ml-1" id="live-sgst">₹0.00</strong></span>
                            </div>
                            <div id="igst-box" class="hidden">
                                <span>IGST (18%): <strong class="text-slate-800 text-sm ml-1" id="live-igst">₹0.00</strong></span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <label class="inline-flex items-center cursor-pointer select-none text-slate-700 font-bold">
                                    <input type="checkbox" id="roundOffCheckbox" checked onchange="recalculateCustomInvoice()" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 mr-1.5">
                                    <span>Round Off</span>
                                </label>
                                <span class="text-xs text-slate-500 italic" id="live-roundoff">(+₹0.00)</span>
                            </div>
                            <div class="bg-blue-600 text-white px-4 py-2 rounded-xl flex items-center space-x-2 shadow-xs ml-auto">
                                <span class="text-xs uppercase font-bold tracking-wider">Grand Total (Inc. GST):</span>
                                <span class="text-base font-black font-mono" id="live-total">₹0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" id="cancelInvoiceBtn" onclick="cancelInvoiceForm()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold py-2.5 px-5 rounded-xl transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" id="invoiceSubmitBtn" class="btn-primary py-2.5 px-6 text-sm font-bold shadow-xs">
                        Generate & Save Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Ledger Section with Tabs -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 mb-4 border-b border-slate-200 gap-3">
            <h3 class="text-base font-bold text-slate-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                Sales & Invoices Ledger
            </h3>
            
            <!-- Tabs Switcher -->
            <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200 text-xs font-bold">
                <button type="button" id="ledgerTabBtnFG" onclick="switchLedgerTab('finished_goods')" class="px-3.5 py-1.5 rounded-lg transition-all shadow-sm bg-blue-600 text-white flex items-center font-bold">
                    <span class="mr-1.5">📦</span> Finished Goods Invoices
                    <span class="ml-2 bg-white/20 text-white px-2 py-0.5 rounded-full text-[10px]">{{ $finishedGoodsInvoices->total() }}</span>
                </button>
                <button type="button" id="ledgerTabBtnRM" onclick="switchLedgerTab('raw_material')" class="px-3.5 py-1.5 rounded-lg transition-all text-slate-600 hover:text-slate-900 flex items-center font-medium">
                    <span class="mr-1.5">🧱</span> Raw Material & Scrap Sales
                    <span class="ml-2 bg-amber-200 text-amber-900 px-2 py-0.5 rounded-full text-[10px]">{{ $rawMaterialInvoices->total() }}</span>
                </button>
            </div>
        </div>

        <!-- Table 1: Finished Goods Invoices Datatable -->
        <div id="ledgerTabFinishedGoods" class="overflow-x-auto w-full max-w-full">
            <table class="erp-datatable divide-y divide-slate-200 text-xs" style="min-width: 1100px; width: 100%;">
                <thead class="bg-[#EDF4FA] text-black divide-x divide-slate-200">
                    <tr>
                        <th class="px-2 py-2.5 text-center text-[11px] font-bold uppercase w-8">#</th>
                        <th class="px-2.5 py-2.5 text-left text-[11px] font-bold uppercase">Invoice No</th>
                        <th class="px-2.5 py-2.5 text-left text-[11px] font-bold uppercase">Invoice Date</th>
                        <th class="px-2.5 py-2.5 text-center text-[11px] font-bold uppercase">Vehicle No</th>
                        <th class="px-2.5 py-2.5 text-left text-[11px] font-bold uppercase">Destination</th>
                        <th class="px-2.5 py-2.5 text-right text-[11px] font-bold uppercase">Taxable Value</th>
                        <th class="px-2.5 py-2.5 text-right text-[11px] font-bold uppercase">CGST+SGST</th>
                        <th class="px-2.5 py-2.5 text-right text-[11px] font-bold uppercase">IGST</th>
                        <th class="px-2.5 py-2.5 text-right text-[11px] font-bold uppercase">Total Amount</th>
                        <th class="px-2 py-2.5 text-center text-[11px] font-bold uppercase">Status</th>
                        <th class="px-2 py-2.5 text-center text-[11px] font-bold uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($finishedGoodsInvoices as $inv)
                        @php
                            $pName = $inv->plant ? $inv->plant->plant_name : ($inv->custom_client_name ?? 'HQ / Custom');
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-2 py-2 text-center font-bold text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-2.5 py-2 font-semibold text-slate-800">
                                <a href="{{ route('invoice.preview', $inv->id) }}" class="text-blue-600 hover:text-blue-800 font-bold hover:underline">
                                    {{ $inv->invoice_number }}
                                </a>
                            </td>
                            <td class="px-2.5 py-2 text-slate-600 font-medium whitespace-nowrap">
                                {{ $inv->invoice_date ? $inv->invoice_date->format('d M Y') : \Carbon\Carbon::parse($inv->created_at)->format('d M Y') }}
                            </td>
                            <td class="px-2.5 py-2 text-center">
                                @if(!empty($inv->vehicle_number))
                                    <span class="font-mono font-bold text-slate-800 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200 text-[10px] uppercase">{{ $inv->vehicle_number }}</span>
                                @else
                                    <span class="text-slate-400 font-medium text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-2.5 py-2 text-slate-600">{{ $pName }}</td>
                            <td class="px-2.5 py-2 text-right text-slate-700">₹{{ format_indian($inv->total_taxable_value, 2) }}</td>
                            <td class="px-2.5 py-2 text-right text-slate-600">
                                @if ($inv->cgst > 0)
                                    ₹{{ format_indian($inv->cgst + $inv->sgst, 2) }}
                                    <span class="text-[9px] block text-slate-400">(9% + 9%)</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-2.5 py-2 text-right text-slate-600">
                                @if ($inv->igst > 0)
                                    ₹{{ format_indian($inv->igst, 2) }}
                                    <span class="text-[9px] block text-slate-400">(18%)</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-2.5 py-2 text-right font-bold text-slate-800">₹{{ format_indian($inv->total_amount, 2) }}</td>
                            <td class="px-2 py-2 text-center">
                                @if(($inv->payment_status ?? 'unpaid') === 'paid')
                                    <span class="px-2 py-0.5 rounded-full text-[9.5px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs">
                                        PAID
                                    </span>
                                @elseif(($inv->payment_status ?? 'unpaid') === 'partially_paid')
                                    <button type="button" 
                                            onclick="openInvoicePaymentModal({{ $inv->id }}, '{{ $inv->invoice_number }}', {{ $inv->remaining_balance }})"
                                            title="Click to record next payment for this invoice"
                                            class="px-2 py-0.5 rounded-full text-[9.5px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-300 hover:bg-amber-200 transition cursor-pointer shadow-2xs">
                                        PARTIAL (₹{{ format_indian($inv->remaining_balance, 0) }} DUE)
                                    </button>
                                @else
                                    <button type="button" 
                                            onclick="openInvoicePaymentModal({{ $inv->id }}, '{{ $inv->invoice_number }}', {{ $inv->remaining_balance }})"
                                            title="Click to record payment for this invoice"
                                            class="px-2 py-0.5 rounded-full text-[9.5px] font-extrabold uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-300 hover:bg-rose-200 transition cursor-pointer shadow-2xs">
                                        UNPAID
                                    </button>
                                @endif
                            </td>
                            <td class="px-2 py-2 text-center space-x-1 whitespace-nowrap">
                                <a href="{{ route('invoice.preview', $inv->id) }}" 
                                   title="Preview Invoice"
                                   class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white shadow-2xs transition duration-150 transform hover:scale-105">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                                <a href="{{ route('invoice.print', $inv->id) }}" 
                                   target="_blank"
                                   title="Print Invoice"
                                   class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-sky-500 hover:bg-sky-600 text-white shadow-2xs transition duration-150 transform hover:scale-105">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                </a>
                                <button type="button" 
                                        title="Edit Invoice Details"
                                        onclick="window.editInvoiceRecord({{ $inv->id }})"
                                        class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-2xs transition duration-150 transform hover:scale-105">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <button type="button" 
                                        title="Delete Invoice"
                                        onclick="window.deleteInvoiceRecord({{ $inv->id }}, '{{ addslashes($inv->invoice_number) }}')"
                                        class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-rose-500 hover:bg-rose-600 text-white shadow-2xs transition duration-150 transform hover:scale-105">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <x-empty-state title="No Invoices Found" subtitle="There are no finished goods invoices recorded yet." colspan="11" />
                    @endforelse
                </tbody>
            </table>
            <div class="mt-4">
                {{ $finishedGoodsInvoices->links() }}
            </div>
        </div>

        <!-- Table 2: Raw Material & Scrap Sales Datatable -->
        <div id="ledgerTabRawMaterial" class="hidden overflow-x-auto w-full max-w-full">
            <table class="erp-datatable divide-y divide-slate-200 text-xs" style="min-width: 1000px; width: 100%;">
                <thead class="bg-amber-600 text-white divide-x divide-white/25">
                    <tr>
                        <th class="px-2 py-2.5 text-center text-[11px] font-bold uppercase w-8">#</th>
                        <th class="px-2.5 py-2.5 text-left text-[11px] font-bold uppercase">Sale Date</th>
                        <th class="px-2.5 py-2.5 text-left text-[11px] font-bold uppercase">Buyer Name</th>
                        <th class="px-2.5 py-2.5 text-left text-[11px] font-bold uppercase">Material & Items Sold</th>
                        <th class="px-2.5 py-2.5 text-center text-[11px] font-bold uppercase">GST Slab</th>
                        <th class="px-2.5 py-2.5 text-right text-[11px] font-bold uppercase">Taxable Value</th>
                        <th class="px-2.5 py-2.5 text-right text-[11px] font-bold uppercase">Total Tax</th>
                        <th class="px-2.5 py-2.5 text-right text-[11px] font-bold uppercase">Total Amount</th>
                        <th class="px-2 py-2.5 text-center text-[11px] font-bold uppercase">Status</th>
                        <th class="px-2 py-2.5 text-center text-[11px] font-bold uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($rawMaterialInvoices as $rmInv)
                        @php
                            $buyerName = $rmInv->custom_client_name ?: ($rmInv->plant ? $rmInv->plant->plant_name : 'Direct Buyer');
                            $itemsSummary = [];
                            foreach($rmInv->items as $item) {
                                $name = $item->item_name ?: ($item->rawMaterial->material_name ?? ($item->product->product_name ?? 'Material'));
                                $uom = $item->billing_uom ?? 'Kg';
                                $itemsSummary[] = $name . ' (' . (float)$item->quantity . ' ' . $uom . ')';
                            }
                            $itemsText = implode(', ', $itemsSummary);
                            $gstRate = ($rmInv->custom_gst_rate !== null) ? $rmInv->custom_gst_rate : 18;
                        @endphp
                        <tr class="hover:bg-amber-50/40 transition">
                            <td class="px-2 py-2 text-center font-bold text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-2.5 py-2 text-slate-600 font-medium whitespace-nowrap">
                                {{ $rmInv->invoice_date ? $rmInv->invoice_date->format('d M Y') : \Carbon\Carbon::parse($rmInv->created_at)->format('d M Y') }}
                            </td>
                            <td class="px-2.5 py-2 font-bold text-slate-800">{{ $buyerName }}</td>
                            <td class="px-2.5 py-2 text-slate-700 max-w-xs truncate" title="{{ $itemsText }}">
                                {{ $itemsText }}
                            </td>
                            <td class="px-2.5 py-2 text-center font-bold text-amber-800">
                                <span class="bg-amber-100 text-amber-900 px-2 py-0.5 rounded-md border border-amber-300 text-[10px]">
                                    {{ $gstRate }}% GST
                                </span>
                            </td>
                            <td class="px-2.5 py-2 text-right text-slate-700">₹{{ format_indian($rmInv->total_taxable_value, 2) }}</td>
                            <td class="px-2.5 py-2 text-right text-slate-600">₹{{ format_indian($rmInv->total_tax, 2) }}</td>
                            <td class="px-2.5 py-2 text-right font-bold text-slate-800">₹{{ format_indian($rmInv->total_amount, 2) }}</td>
                            <td class="px-2 py-2 text-center">
                                @if(($rmInv->payment_status ?? 'unpaid') === 'paid')
                                    <span class="px-2 py-0.5 rounded-full text-[9.5px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs">
                                        PAID
                                    </span>
                                @elseif(($rmInv->payment_status ?? 'unpaid') === 'partially_paid')
                                    <button type="button" 
                                            onclick="openInvoicePaymentModal({{ $rmInv->id }}, '{{ $rmInv->invoice_number }}', {{ $rmInv->remaining_balance }})"
                                            title="Click to record next payment for this raw material sale"
                                            class="px-2 py-0.5 rounded-full text-[9.5px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-300 hover:bg-amber-200 transition cursor-pointer shadow-2xs">
                                        PARTIAL (₹{{ format_indian($rmInv->remaining_balance, 0) }} DUE)
                                    </button>
                                @else
                                    <button type="button" 
                                            onclick="openInvoicePaymentModal({{ $rmInv->id }}, '{{ $rmInv->invoice_number }}', {{ $rmInv->remaining_balance }})"
                                            title="Click to record payment for this raw material sale"
                                            class="px-2 py-0.5 rounded-full text-[9.5px] font-extrabold uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-300 hover:bg-rose-200 transition cursor-pointer shadow-2xs">
                                        UNPAID
                                    </button>
                                @endif
                            </td>
                            <td class="px-2 py-2 text-center space-x-1 whitespace-nowrap">
                                <a href="{{ route('invoice.print', $rmInv->id) }}" 
                                   target="_blank"
                                   title="Print Sale Bill / PDF"
                                   class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-sky-500 hover:bg-sky-600 text-white shadow-2xs transition duration-150 transform hover:scale-105">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                </a>
                                <button type="button" 
                                        title="Edit Sale Record"
                                        onclick="window.editInvoiceRecord({{ $rmInv->id }})"
                                        class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-2xs transition duration-150 transform hover:scale-105">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <button type="button" 
                                        title="Delete Sale Record"
                                        onclick="window.deleteInvoiceRecord({{ $rmInv->id }}, '{{ addslashes($rmInv->invoice_number) }}')"
                                        class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-rose-500 hover:bg-rose-600 text-white shadow-2xs transition duration-150 transform hover:scale-105">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
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
                                    <p class="text-xs text-slate-400">There are no raw material or scrap sales recorded yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-4">
                {{ $rawMaterialInvoices->links() }}
            </div>
        </div>
    </div>

</div>

<script>
(function() {
    // Dynamic builder rows & Live Tax Calculations
    var billingRowsContainer = document.getElementById('billingRowsContainer');
    var addBillingRowBtn = document.getElementById('addBillingRowBtn');
    var manualPlantSelect = document.getElementById('manualPlantSelect');
    
    if (!billingRowsContainer || !addBillingRowBtn) return;

    window.rawInvoiceComboboxTpl = @json($invoiceComboboxHtml);
    window.rawMaterialComboboxTpl = @json($rawMaterialComboboxHtml);

    // Add Row (prevent double-fire with flag)
    var _addRowPending = false;
    addBillingRowBtn.addEventListener('click', function(e) {
        if (_addRowPending) return;
        _addRowPending = true;
        setTimeout(function() { _addRowPending = false; }, 300);

        const modeInp = document.getElementById('invoiceModeInput');
        const currentTpl = (modeInp && modeInp.value === 'raw_material') ? window.rawMaterialComboboxTpl : window.rawInvoiceComboboxTpl;

        const row = document.createElement('div');
        row.className = 'billing-row flex items-center space-x-2 bg-slate-50 p-2.5 rounded-xl border border-slate-200';
        row.innerHTML = `
            <div class="flex-grow">
                ${currentTpl}
            </div>
            <select name="billing_uoms[]" class="billing-uom-select w-24 shrink-0 bg-white border border-slate-200 rounded-xl py-2 px-2 text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="Pcs">Pcs</option>
                <option value="Kg">Kg</option>
            </select>
            <input type="number" name="quantities[]" step="any" min="0.01" placeholder="Qty" class="w-20 bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 font-bold" required>
            <input type="number" name="unit_prices[]" step="0.01" min="0" placeholder="Price" class="w-28 bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 font-bold" required>
            <button type="button" class="remove-billing-row-btn text-rose-500 hover:text-rose-600 font-bold px-2 text-sm">✕</button>
        `;
        billingRowsContainer.appendChild(row);
        recalculateCustomInvoice();
    });

    function ensureAndSelectUom(uomSelect, rawUom) {
        if (!uomSelect || !rawUom) return;
        let formattedUom = rawUom.trim();
        if (formattedUom.toLowerCase() === 'kilogram' || formattedUom.toLowerCase() === 'kg') {
            formattedUom = 'Kg';
        } else if (formattedUom.toLowerCase() === 'piece' || formattedUom.toLowerCase() === 'pcs') {
            formattedUom = 'Pcs';
        }

        let found = false;
        for (let i = 0; i < uomSelect.options.length; i++) {
            if (uomSelect.options[i].value.toLowerCase() === formattedUom.toLowerCase()) {
                uomSelect.selectedIndex = i;
                found = true;
                break;
            }
        }
        if (!found) {
            const opt = document.createElement('option');
            opt.value = formattedUom;
            opt.textContent = formattedUom;
            uomSelect.appendChild(opt);
            uomSelect.value = formattedUom;
        }
    }

    // Event delegation on billingRowsContainer — auto-fill price on product or UOM select
    billingRowsContainer.addEventListener('change', function(e) {
        if (e.target.name === 'product_ids[]' || e.target.name === 'billing_uoms[]' || e.target.classList.contains('billing-uom-select')) {
            const row = e.target.closest('.billing-row');
            if (row) {
                const prodInput = row.querySelector('[name="product_ids[]"]');
                const uomSelect = row.querySelector('.billing-uom-select');
                const priceInput = row.querySelector('input[name="unit_prices[]"]');
                const wrapper = row.querySelector('.combobox-wrapper');

                if (prodInput && wrapper) {
                    const val = prodInput.value;
                    const opt = wrapper.querySelector(`.combobox-option[data-value="${val}"]`);
                    if (opt) {
                        // Auto-fetch measurement unit when product/material changes
                        if (e.target.name === 'product_ids[]' && uomSelect && opt.dataset.uom) {
                            ensureAndSelectUom(uomSelect, opt.dataset.uom);
                        }

                        if (priceInput) {
                            const uomVal = uomSelect ? uomSelect.value : 'Pcs';
                            const priceKg = parseFloat(opt.dataset.priceKg || '0');
                            const priceNormal = parseFloat(opt.dataset.pricePcs || opt.dataset.price || '0');

                            if (uomVal === 'Kg' && priceKg > 0) {
                                priceInput.value = priceKg.toFixed(2);
                            } else if (priceNormal > 0) {
                                priceInput.value = priceNormal.toFixed(2);
                            } else {
                                priceInput.value = '';
                                priceInput.placeholder = '0.00';
                            }
                            priceInput.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    } else {
                        if (priceInput) {
                            priceInput.value = '';
                            priceInput.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    }
                }
            }
            recalculateCustomInvoice();
        }
    });

    billingRowsContainer.addEventListener('input', function(e) {
        if (e.target.name === 'quantities[]' || e.target.name === 'unit_prices[]') {
            recalculateCustomInvoice();
        }
    });

    billingRowsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-billing-row-btn')) {
            if (billingRowsContainer.querySelectorAll('.billing-row').length > 1) {
                e.target.closest('.billing-row').remove();
                recalculateCustomInvoice();
            } else {
                if (window.showToast) window.showToast('warning', 'At least one product row is required.');
            }
        }
    });

    function parseCleanFloat(val) {
        if (val === null || val === undefined) return 0;
        const str = val.toString().replace(/,/g, '').trim();
        const num = parseFloat(str);
        return isNaN(num) ? 0 : num;
    }

    // Dynamic tax calculation engine
    function recalculateCustomInvoice() {
        const container = document.getElementById('billingRowsContainer');
        if (!container) return;
        const rows = container.querySelectorAll('.billing-row');
        let totalTaxable = 0.00;
        
        rows.forEach(row => {
            const qtyInput = row.querySelector('input[name="quantities[]"]');
            const priceInput = row.querySelector('input[name="unit_prices[]"]');
            const qty = parseCleanFloat(qtyInput ? qtyInput.value : 0);
            const price = parseCleanFloat(priceInput ? priceInput.value : 0);
            totalTaxable += qty * price;
        });
        
        // Resolve destination state directly from selected client plant option
        let state = 'Gujarat';
        const clientSelect = document.getElementById('invoiceClientSelect');

        if (clientSelect && clientSelect.value) {
            let selectedOpt = null;
            if (clientSelect.selectedIndex >= 0 && clientSelect.options[clientSelect.selectedIndex]) {
                selectedOpt = clientSelect.options[clientSelect.selectedIndex];
            }
            if (!selectedOpt || !selectedOpt.value) {
                try {
                    selectedOpt = clientSelect.querySelector('option[value="' + CSS.escape(clientSelect.value) + '"]');
                } catch(e) {}
            }
            if (selectedOpt && selectedOpt.getAttribute('data-state')) {
                state = selectedOpt.getAttribute('data-state');
            }
        }

        const isGujarat = state.toLowerCase().trim() === 'gujarat';
        const invMode = (document.getElementById('invoiceModeInput') ? document.getElementById('invoiceModeInput').value : 'finished_goods');
        
        let cgst = 0.00;
        let sgst = 0.00;
        let igst = 0.00;
        
        if (totalTaxable > 0) {
            if (invMode === 'raw_material') {
                const gstSelect = document.getElementById('rawMaterialGstRateSelect');
                const rate = gstSelect ? parseFloat(gstSelect.value) : 18.00;
                if (rate > 0) {
                    cgst = Math.round(totalTaxable * (rate / 200.0) * 100) / 100;
                    sgst = Math.round(totalTaxable * (rate / 200.0) * 100) / 100;
                }
            } else {
                if (isGujarat) {
                    cgst = Math.round(totalTaxable * 0.09 * 100) / 100;
                    sgst = Math.round(totalTaxable * 0.09 * 100) / 100;
                } else {
                    igst = Math.round(totalTaxable * 0.18 * 100) / 100;
                }
            }
        }
        
        const exactTotal = totalTaxable + cgst + sgst + igst;
        let finalTotal = exactTotal;
        let roundOffDiff = 0.00;

        const roundOffCheckbox = document.getElementById('roundOffCheckbox');
        if (roundOffCheckbox && roundOffCheckbox.checked) {
            finalTotal = Math.round(exactTotal);
            roundOffDiff = finalTotal - exactTotal;
        }

        const cgstSgstBox = document.getElementById('cgst-sgst-box');
        const igstBox = document.getElementById('igst-box');

        if (isGujarat) {
            if (cgstSgstBox) cgstSgstBox.classList.remove('hidden');
            if (igstBox) igstBox.classList.add('hidden');
        } else {
            if (cgstSgstBox) cgstSgstBox.classList.add('hidden');
            if (igstBox) igstBox.classList.remove('hidden');
        }
        
        // Update DOM
        const elTaxable = document.getElementById('live-taxable');
        const elCgst = document.getElementById('live-cgst');
        const elSgst = document.getElementById('live-sgst');
        const elIgst = document.getElementById('live-igst');
        const elRoundOff = document.getElementById('live-roundoff');
        const elTotal = document.getElementById('live-total');

        if (elTaxable) elTaxable.innerText = '₹' + totalTaxable.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (elCgst) elCgst.innerText = '₹' + cgst.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (elSgst) elSgst.innerText = '₹' + sgst.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (elIgst) elIgst.innerText = '₹' + igst.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (elRoundOff) {
            const sign = roundOffDiff >= 0 ? '+' : '';
            elRoundOff.innerText = '(' + sign + '₹' + roundOffDiff.toFixed(2) + ')';
        }
        if (elTotal) elTotal.innerText = '₹' + finalTotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    window.recalculateCustomInvoice = recalculateCustomInvoice;

    billingRowsContainer.addEventListener('input', recalculateCustomInvoice);
    billingRowsContainer.addEventListener('change', recalculateCustomInvoice);

    // Initialize calculation on page load
    recalculateCustomInvoice();

    // Auto recalculate on combobox change
    document.addEventListener('change', function(e) {
        if (e.target && (e.target.name === 'plant_id' || e.target.name === 'product_ids[]' || e.target.name === 'quantities[]' || e.target.name === 'unit_prices[]' || e.target.name === 'billing_uoms[]')) {
            if (typeof window.recalculateCustomInvoice === 'function') {
                window.recalculateCustomInvoice();
            }
        }
    });
})();
</script>

<script>
    // Invoice data registry
    window.erpInvoicesMap = window.erpInvoicesMap || {};
    @foreach ($invoices as $inv)
        @php
            $itemsArray = [];
            foreach ($inv->items as $it) {
                $itemKey = ($it->item_type === 'raw_material') ? ('raw_material_' . $it->raw_material_id) : ('product_' . ($it->product_id ?? $it->finished_good_id));
                $itemsArray[] = [
                    'key' => $itemKey,
                    'item_type' => $it->item_type ?? 'product',
                    'product_id' => $it->product_id ?? $it->finished_good_id,
                    'raw_material_id' => $it->raw_material_id,
                    'billing_uom' => $it->billing_uom,
                    'quantity' => $it->quantity,
                    'unit_price' => $it->unit_price
                ];
            }
            $invPlantId = $inv->plant_id ?? '';
        @endphp
        window.erpInvoicesMap[{{ $inv->id }}] = {
            id: {{ $inv->id }},
            invoice_mode: @json($inv->invoice_mode ?? 'finished_goods'),
            custom_client_name: @json($inv->custom_client_name),
            custom_gst_rate: @json($inv->custom_gst_rate),
            custom_buyer_gstin: @json($inv->custom_buyer_gstin),
            invoice_number: @json($inv->invoice_number),
            client_id: @json($inv->client_id),
            plant_id: @json($invPlantId),
            due_date: @json($inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('Y-m-d') : date('Y-m-d')),
            items: @json($itemsArray)
        };
    @endforeach

    @if(isset($editInvoice) && $editInvoice)
        @php
            $inv = $editInvoice;
            $itemsArray = [];
            foreach ($inv->items as $it) {
                $itemKey = ($it->item_type === 'raw_material') ? ('raw_material_' . $it->raw_material_id) : ('product_' . ($it->product_id ?? $it->finished_good_id));
                $itemsArray[] = [
                    'key' => $itemKey,
                    'item_type' => $it->item_type ?? 'product',
                    'product_id' => $it->product_id ?? $it->finished_good_id,
                    'raw_material_id' => $it->raw_material_id,
                    'billing_uom' => $it->billing_uom,
                    'quantity' => $it->quantity,
                    'unit_price' => $it->unit_price
                ];
            }
            $invPlantId = $inv->plant_id ?? '';
        @endphp
        window.erpInvoicesMap[{{ $inv->id }}] = {
            id: {{ $inv->id }},
            invoice_mode: @json($inv->invoice_mode ?? 'finished_goods'),
            custom_client_name: @json($inv->custom_client_name),
            custom_gst_rate: @json($inv->custom_gst_rate),
            custom_buyer_gstin: @json($inv->custom_buyer_gstin),
            invoice_number: @json($inv->invoice_number),
            client_id: @json($inv->client_id),
            plant_id: @json($invPlantId),
            due_date: @json($inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('Y-m-d') : date('Y-m-d')),
            items: @json($itemsArray)
        };
    @endif

    window.switchLedgerTab = function(tab) {
        const tabFG = document.getElementById('ledgerTabFinishedGoods');
        const tabRM = document.getElementById('ledgerTabRawMaterial');
        const btnFG = document.getElementById('ledgerTabBtnFG');
        const btnRM = document.getElementById('ledgerTabBtnRM');

        if (tab === 'raw_material') {
            if (tabFG) tabFG.classList.add('hidden');
            if (tabRM) tabRM.classList.remove('hidden');
            if (btnFG) btnFG.className = 'px-3.5 py-1.5 rounded-lg transition-all text-slate-600 hover:text-slate-900 flex items-center font-medium';
            if (btnRM) btnRM.className = 'px-3.5 py-1.5 rounded-lg transition-all shadow-sm bg-amber-500 text-white flex items-center font-bold';
        } else {
            if (tabFG) tabFG.classList.remove('hidden');
            if (tabRM) tabRM.classList.add('hidden');
            if (btnFG) btnFG.className = 'px-3.5 py-1.5 rounded-lg transition-all shadow-sm bg-blue-600 text-white flex items-center font-bold';
            if (btnRM) btnRM.className = 'px-3.5 py-1.5 rounded-lg transition-all text-slate-600 hover:text-slate-900 flex items-center font-medium';
        }
    };

    window.onGstRateSelectChange = function() {
        const gstSelect = document.getElementById('rawMaterialGstRateSelect');
        const gstinContainer = document.getElementById('customGstinContainer');
        const mode = document.getElementById('invoiceModeInput') ? document.getElementById('invoiceModeInput').value : 'finished_goods';

        if (mode === 'raw_material' && gstSelect && gstinContainer) {
            const val = parseInt(gstSelect.value || '0');
            if (val > 0) {
                gstinContainer.classList.remove('hidden');
            } else {
                gstinContainer.classList.add('hidden');
                const input = document.getElementById('customBuyerGstinInput');
                if (input) input.value = '';
            }
        } else if (gstinContainer) {
            gstinContainer.classList.add('hidden');
        }

        if (typeof window.recalculateCustomInvoice === 'function') {
            window.recalculateCustomInvoice();
        }
    };

    window.switchInvoiceMode = function(mode) {
        if (!mode) mode = 'finished_goods';
        try { sessionStorage.setItem('pww_invoice_mode', mode); } catch(e) {}
        
        const input = document.getElementById('invoiceModeInput');
        const btnFG = document.getElementById('modeBtnFinishedGoods');
        const btnRM = document.getElementById('modeBtnRawMaterial');
        
        const regClient = document.getElementById('registeredClientContainer');
        const custClient = document.getElementById('customClientContainer');
        const gstSlab = document.getElementById('gstRateSlabContainer');
        const clientSelect = document.getElementById('invoiceClientSelect');
        const custInput = document.getElementById('customClientNameInput');
        
        if (input) {
            input.value = mode;
            input.setAttribute('value', mode);
        }

        const targetTpl = (mode === 'raw_material') ? window.rawMaterialComboboxTpl : window.rawInvoiceComboboxTpl;

        const container = document.getElementById('billingRowsContainer');
        if (container) {
            container.querySelectorAll('.billing-row').forEach(row => {
                const flexGrow = row.querySelector('.flex-grow');
                if (flexGrow) {
                    flexGrow.innerHTML = targetTpl;
                }
            });
        }
        
        const invNumContainer = document.getElementById('invoiceNumberContainer');
        const vehNumContainer = document.getElementById('vehicleNumberContainer');
        
        const invNumInput = document.querySelector('input[name="invoice_number"]');
        if (mode === 'raw_material') {
            if (btnFG) btnFG.className = 'px-3 py-1.5 rounded-lg transition-all text-slate-600 hover:text-slate-900 font-medium';
            if (btnRM) btnRM.className = 'px-3 py-1.5 rounded-lg transition-all shadow-sm bg-amber-500 text-white font-bold';
            
            if (regClient) regClient.classList.add('hidden');
            if (custClient) custClient.classList.remove('hidden');
            if (gstSlab) gstSlab.classList.remove('hidden');
            if (invNumContainer) invNumContainer.classList.add('hidden');
            if (vehNumContainer) vehNumContainer.classList.add('hidden');
            
            if (clientSelect) clientSelect.removeAttribute('required');
            if (invNumInput) invNumInput.removeAttribute('required');
            if (custInput) custInput.setAttribute('required', 'required');
            switchLedgerTab('raw_material');
        } else {
            if (btnFG) btnFG.className = 'px-3 py-1.5 rounded-lg transition-all shadow-sm bg-blue-600 text-white font-bold';
            if (btnRM) btnRM.className = 'px-3 py-1.5 rounded-lg transition-all text-slate-600 hover:text-slate-900 font-medium';
            
            if (regClient) regClient.classList.remove('hidden');
            if (custClient) custClient.classList.add('hidden');
            if (gstSlab) gstSlab.classList.add('hidden');
            if (invNumContainer) invNumContainer.classList.remove('hidden');
            if (vehNumContainer) vehNumContainer.classList.remove('hidden');
            
            if (clientSelect) clientSelect.setAttribute('required', 'required');
            if (invNumInput) invNumInput.setAttribute('required', 'required');
            if (custInput) custInput.removeAttribute('required');
            switchLedgerTab('finished_goods');
        }

        onGstRateSelectChange();
    };

    function setToggleButtonState(isOpen) {
        const btn = document.getElementById('toggleInvoiceFormBtn');
        if (!btn) return;
        if (isOpen) {
            btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            btn.classList.add('bg-slate-700', 'hover:bg-slate-800');
            const icon = btn.querySelector('svg');
            if (icon) icon.style.transform = 'rotate(45deg)';
        } else {
            btn.classList.remove('bg-slate-700', 'hover:bg-slate-800');
            btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
            const icon = btn.querySelector('svg');
            if (icon) icon.style.transform = 'rotate(0deg)';
        }
    }

    window.toggleInlineForm = function(containerId, btn) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        const isHidden = container.classList.contains('hidden');
        if (isHidden) {
            resetInvoiceForm();
            container.classList.remove('hidden');
            setToggleButtonState(true);
        } else {
            resetInvoiceForm();
            container.classList.add('hidden');
            setToggleButtonState(false);
        }
    };

    // Cancel button handler — reset form and close
    window.cancelInvoiceForm = function() {
        resetInvoiceForm();
        const container = document.getElementById('section-manual-builder');
        if (container) container.classList.add('hidden');
        setToggleButtonState(false);
    };

    // Shared form reset helper
    window.resetInvoiceForm = function() {
        const card = document.getElementById('invoiceFormCard');
        if (card) card.className = 'bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col transition-all duration-300';

        const title = document.getElementById('invoiceFormTitle');
        if (title) {
            title.className = 'text-base font-bold text-slate-800 flex items-center';
            title.innerHTML = `
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Direct Invoice Itemizer
            `;
        }

        const closeBtn = document.getElementById('invoiceFormCloseBtn');
        if (closeBtn) closeBtn.className = 'text-xs font-bold text-slate-400 hover:text-slate-600';

        const $form = $('#customInvoiceForm');
        if ($form.length) {
            $form[0].reset();
            $form.find('input[name="invoice_id"]').val('');
            $form.find('input[name="sales_order_id"]').val('');
            $form.find('input[name="invoice_number"]').val('{{ \App\Models\Invoice::generateNextInvoiceNumber() }}');
            $form.find('input[name="invoice_date"]').val('{{ date("Y-m-d") }}');
            $form.find('input[name="vehicle_number"]').val('');
            $form.find('input[name="custom_client_name"]').val('');
            $form.find('input[name="custom_buyer_gstin"]').val('');

            $form.find('select#invoiceClientSelect').val('');
            if (typeof window.syncDirectClientDisplay === 'function') {
                window.syncDirectClientDisplay();
            }
        }

        // Preserve active mode on reset
        let activeMode = 'finished_goods';
        try { activeMode = sessionStorage.getItem('pww_invoice_mode') || 'finished_goods'; } catch(e) {}
        switchInvoiceMode(activeMode);

        const currentTpl = (activeMode === 'raw_material') ? window.rawMaterialComboboxTpl : window.rawInvoiceComboboxTpl;

        // Reset billing rows to single clean empty row from template
        const container = document.getElementById('billingRowsContainer');
        if (container) {
            container.innerHTML = `
                <div class="billing-row flex items-center space-x-2 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                    <div class="flex-grow">
                        ${currentTpl}
                    </div>
                    <select name="billing_uoms[]" class="billing-uom-select w-24 shrink-0 bg-white border border-slate-200 rounded-xl py-2 px-2 text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Pcs">Pcs</option>
                        <option value="Kg">Kg</option>
                    </select>
                    <input type="number" name="quantities[]" step="any" min="0.01" placeholder="Qty" class="w-20 bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 font-bold" required>
                    <input type="number" name="unit_prices[]" step="0.01" min="0" placeholder="Price" class="w-28 bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 font-bold" required>
                    <button type="button" class="remove-billing-row-btn text-rose-500 hover:text-rose-600 font-bold px-2 text-sm">✕</button>
                </div>
            `;
        }

        // Reset submit button text
        const submitBtn = document.getElementById('invoiceSubmitBtn');
        if (submitBtn) {
            submitBtn.textContent = 'Generate & Save Invoice';
            submitBtn.className = 'btn-primary py-2.5 px-6 text-sm font-bold shadow-xs';
        }

        // Recalculate live totals (will show ₹0.00)
        if (typeof window.recalculateCustomInvoice === 'function') {
            window.recalculateCustomInvoice();
        }
    };

    // Global edit function
    window.editInvoiceRecord = function(id) {
        // Clean URL query parameters so refreshing page doesn't re-trigger edit mode
        if (window.history && window.history.replaceState && (window.location.search.includes('edit=') || window.location.search.includes('edit_id='))) {
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
        }

        const invoice = window.erpInvoicesMap[id];
        if (!invoice) {
            console.error('Invoice record not found for id:', id);
            return;
        }

        // Force-open the form (don't toggle)
        const formContainer = document.getElementById('section-manual-builder');
        if (formContainer) {
            formContainer.classList.remove('hidden');
            setToggleButtonState(true);
        }

        // Warm Amber Edit Styling
        const card = document.getElementById('invoiceFormCard');
        if (card) card.className = 'bg-[#FFFDF5] rounded-2xl shadow-sm border-2 border-amber-300 p-6 flex flex-col transition-all duration-300';

        const title = document.getElementById('invoiceFormTitle');
        if (title) {
            title.className = 'text-base font-bold text-amber-900 flex items-center';
            title.innerHTML = `
                <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                Edit Tax Invoice #${invoice.invoice_number}
            `;
        }

        const closeBtn = document.getElementById('invoiceFormCloseBtn');
        if (closeBtn) closeBtn.className = 'text-xs font-bold text-amber-700 hover:text-amber-900';

        // Change submit button text to "Update Invoice"
        const submitBtn = document.getElementById('invoiceSubmitBtn');
        if (submitBtn) {
            submitBtn.textContent = 'Update Invoice';
            submitBtn.className = 'btn-primary py-2.5 px-6 text-sm font-bold bg-[#2563EB] hover:bg-blue-700 text-white rounded-xl shadow-xs';
        }

        const $form = $('#customInvoiceForm');
        if ($form.length) {
            $form.find('input[name="invoice_id"]').val(invoice.id);
            $form.find('input[name="invoice_number"]').val(invoice.invoice_number);
            
            if (invoice.invoice_mode === 'raw_material' || invoice.custom_client_name) {
                switchInvoiceMode('raw_material');
                $('#customClientNameInput').val(invoice.custom_client_name || '');
                if (invoice.custom_gst_rate !== null && invoice.custom_gst_rate !== undefined) {
                    $('#rawMaterialGstRateSelect').val(parseInt(invoice.custom_gst_rate));
                }
                $('#customBuyerGstinInput').val(invoice.custom_buyer_gstin || '');
                onGstRateSelectChange();
            } else {
                switchInvoiceMode('finished_goods');
                if (invoice.plant_id) {
                    $form.find('select#invoiceClientSelect').val(invoice.plant_id);
                    if (typeof window.syncDirectClientDisplay === 'function') {
                        window.syncDirectClientDisplay();
                    }
                }
            }

            if (invoice.due_date) {
                $form.find('input[name="due_date"]').val(invoice.due_date);
            }
            $form.find('input:not([type="hidden"]):not([name="quantities[]"]):not([name="unit_prices[]"]), select:not([name="product_ids[]"]):not([name="billing_uoms[]"]):not(.billing-uom-select):not(#invoiceClientSelect), textarea').each(function() {
                if (!this.disabled) {
                    this.className = 'w-full bg-white border border-amber-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700 font-medium';
                }
            });
            if (window.invoiceClientTomSelect && window.invoiceClientTomSelect.control) {
                window.invoiceClientTomSelect.control.style.backgroundColor = '#ffffff';
                window.invoiceClientTomSelect.control.style.borderColor = '#fde68a';
            }
        }

        const container = document.getElementById('billingRowsContainer');
        if (container && invoice.items && invoice.items.length > 0) {
            container.innerHTML = '';

            invoice.items.forEach(item => {
                const isRm = (item.item_type === 'raw_material') || (invoice.invoice_mode === 'raw_material');
                const currentTpl = isRm ? window.rawMaterialComboboxTpl : window.rawInvoiceComboboxTpl;

                const row = document.createElement('div');
                row.className = 'billing-row flex items-center space-x-2 bg-amber-50/50 p-2.5 rounded-xl border border-amber-200';
                row.innerHTML = `
                    <div class="flex-grow">
                        ${currentTpl}
                    </div>
                    <select name="billing_uoms[]" class="billing-uom-select w-24 shrink-0 bg-white border border-amber-200 rounded-xl py-2 px-2 text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <option value="Pcs">Pcs</option>
                        <option value="Kg">Kg</option>
                    </select>
                    <input type="number" name="quantities[]" value="${parseFloat(item.quantity)}" step="any" min="0.01" placeholder="Qty" class="w-20 bg-white border border-amber-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-900 font-bold" required>
                    <input type="number" name="unit_prices[]" value="${parseFloat(item.unit_price).toFixed(2)}" step="0.01" min="0" placeholder="Price" class="w-28 bg-white border border-amber-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-900 font-bold" required>
                    <button type="button" class="remove-billing-row-btn text-rose-500 hover:text-rose-600 font-bold px-2 text-sm">✕</button>
                `;
                const itemVal = item.key || (item.item_type === 'raw_material' ? ('raw_material_' + item.raw_material_id) : ('product_' + (item.product_id || item.finished_good_id)));
                const hiddenInp = row.querySelector('.combobox-hidden-input');
                if (hiddenInp) hiddenInp.value = itemVal;
                const wrapper = row.querySelector('.combobox-wrapper');
                if (wrapper && window.ERPComboboxManager) window.ERPComboboxManager.syncDisplay(wrapper);
                if (row.querySelector('select[name="billing_uoms[]"]')) {
                    const uomSel = row.querySelector('select[name="billing_uoms[]"]');
                    ensureAndSelectUom(uomSel, item.billing_uom || (isRm ? 'Kg' : 'Pcs'));
                }
                container.appendChild(row);
            });
            
            if (typeof window.recalculateCustomInvoice === 'function') {
                window.recalculateCustomInvoice();
            }
        }

        const formElem = document.getElementById('customInvoiceForm');
        if (formElem) {
            formElem.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    };

    // Quit Edit mode handler (now uses shared resetInvoiceForm)
    window.quitEditMode = function() {
        resetInvoiceForm();
    };

    // Global delete invoice handler
    window.deleteInvoiceRecord = function(id, invoiceNumber) {
        window.confirmDelete(
            'Delete Invoice?',
            `Are you sure you want to permanently delete Invoice '${invoiceNumber}'? This action cannot be undone!`,
            function() {
                const token = $('meta[name="csrf-token"]').attr('content') || '';
                $.ajax({
                    url: `/invoices/${id}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    success: async function(response) {
                        if (window.showToast) {
                            window.showToast('success', response.message || 'Invoice deleted successfully!');
                        }
                        await window.loadPage(window.location.href);
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to delete invoice.';
                        if (window.showToast) {
                            window.showToast('error', msg);
                        } else {
                            alert(msg);
                        }
                    }
                });
            }
        );
    };
</script>

<script>
    (function() {
        const urlParams = new URLSearchParams(window.location.search);
        const modeParam = urlParams.get('mode');
        if (modeParam === 'raw_material') {
            if (typeof window.switchInvoiceMode === 'function') {
                window.switchInvoiceMode('raw_material');
            }
            if (window.history && window.history.replaceState) {
                const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
            }
        } else {
            if (typeof window.switchInvoiceMode === 'function') {
                window.switchInvoiceMode('finished_goods');
            }
        }
        const editId = urlParams.get('edit') || urlParams.get('edit_id');
        if (editId && typeof window.editInvoiceRecord === 'function') {
            setTimeout(function() {
                window.editInvoiceRecord(editId);
                if (window.history && window.history.replaceState) {
                    const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                    window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
                }
            }, 100);
        } else {
            setTimeout(function() {
                if (typeof window.recalculateCustomInvoice === 'function') {
                    window.recalculateCustomInvoice();
                }
            }, 50);
        }
    })();
</script>
@endsection
