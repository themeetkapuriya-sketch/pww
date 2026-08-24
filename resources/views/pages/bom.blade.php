@extends('layouts.app')

@section('title', 'Bill of Materials (BOM)')

@section('content')
@php
    $productOptions = [];
    foreach ($finishedGoods as $good) {
        $skuText = $good->sku ? ' (SKU: ' . $good->sku . ')' : '';
        $productOptions[] = [
            'value' => (string)$good->id,
            'label' => $good->product_name . $skuText,
            'search' => strtolower($good->product_name . ' ' . ($good->sku ?? ''))
        ];
    }

    $rawMaterialOptions = [];
    $rawMaterialRatesMap = [];
    foreach ($rawMaterials as $mat) {
        $rate = (float)($mat->average_purchase_price ?? 0);
        $rawMaterialRatesMap[$mat->id] = [
            'rate' => $rate,
            'unit' => $mat->unit ?? 'kg',
            'name' => $mat->material_name,
        ];
        $specText = $mat->specification ? " [{$mat->specification}]" : '';
        $rawMaterialOptions[] = [
            'value' => (string)$mat->id,
            'label' => $mat->material_name . $specText . ' (' . $mat->unit . ')',
            'search' => strtolower($mat->material_name . ' ' . $mat->specification . ' ' . $mat->material_category . ' ' . $mat->unit),
            'data' => [
                'rate' => $rate,
                'unit' => $mat->unit ?? 'kg',
            ]
        ];
    }
@endphp
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight flex items-center">
                <svg class="w-6 h-6 mr-2.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                Bill of Materials (BOM)
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-1">Define raw material requirements and expected waste multipliers for rack manufacturing.</p>
        </div>
        <div>
            <button type="button" onclick="toggleAddBomForm()" class="btn-primary py-2.5 px-5 text-xs font-bold shadow-xs flex items-center">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Add BOM Formula</span>
            </button>
        </div>
    </div>

    <!-- 1. Add BOM Multi-Row Form (Collapsible Card) -->
    <div id="addBomFormCard" class="hidden bg-white rounded-2xl shadow-md border-2 border-blue-500/30 p-6 transition-all duration-300">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
            <h3 id="bomFormTitleText" class="text-base font-bold text-slate-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Assign Raw Material Components to Product
            </h3>
            <button type="button" onclick="toggleAddBomForm()" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition cursor-pointer">&times; Close</button>
        </div>

        <form action="{{ route('bom.store') }}" method="POST" class="ajax-form space-y-5" data-redirect="/bom">
            @csrf
            <input type="hidden" name="replace_mode" id="bomReplaceModeInput" value="0">
            
            <div class="max-w-md">
                <x-combobox name="product_id"
                            id="bom_product_id"
                            label="Select Target Product"
                            placeholder="Select Product..."
                            :options="$productOptions"
                            required />
            </div>

            <!-- Multi-Row Raw Materials Itemizer -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">Raw Material Components & Waste Allowance</label>
                    <button type="button" onclick="addBomRow()" class="text-xs text-blue-600 hover:text-blue-800 font-bold flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>Add Raw Material Row</span>
                    </button>
                </div>

                <div id="bomRowsContainer" class="space-y-3">
                    <div class="bom-row flex flex-col md:flex-row items-stretch md:items-end gap-2.5 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                        <div class="flex-grow min-w-[200px]">
                            <x-combobox name="raw_material_ids[]"
                                        id="bom_rm_0"
                                        label="Raw Material"
                                        placeholder="Select Raw Material..."
                                        :options="$rawMaterialOptions"
                                        required />
                        </div>
                        <div class="w-full md:w-32 shrink-0">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Rate (₹ / unit)</label>
                            <div class="bom-rate-display h-[38px] px-2.5 bg-white border border-slate-200 rounded-lg flex items-center justify-end font-bold text-xs text-slate-700">₹0.00</div>
                        </div>
                        <div class="w-full md:w-28">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Required Qty</label>
                            <input type="number" name="required_quantities[]" step="0.0001" min="0.0001" placeholder="e.g. 4.5" required
                                   class="bom-qty-input w-full bg-white border border-slate-200 rounded-lg py-2 px-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 h-[38px]">
                        </div>
                        <div class="w-full md:w-24">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Waste (%)</label>
                            <input type="number" name="waste_percentages[]" step="0.01" min="0" value="0" placeholder="e.g. 5%" required
                                   class="bom-waste-input w-full bg-white border border-slate-200 rounded-lg py-2 px-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 h-[38px]">
                        </div>
                        <div class="w-full md:w-28 shrink-0">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Line Cost</label>
                            <div class="bom-line-cost-display h-[38px] px-2.5 bg-white border border-slate-200 rounded-lg flex items-center justify-end font-extrabold text-xs text-slate-800">₹0.00</div>
                        </div>
                        <div class="shrink-0 flex items-center justify-center">
                            <button type="button" title="Remove component row" class="remove-bom-row-btn w-[38px] h-[38px] rounded-xl bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-600 flex items-center justify-center transition duration-150 cursor-pointer shadow-2xs">
                                <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="toggleAddBomForm()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold py-2.5 px-5 rounded-xl transition cursor-pointer">Cancel</button>
                <button type="submit" id="bomSubmitBtn" class="btn-primary py-2.5 px-6 text-xs font-bold">Assign Components to Product</button>
            </div>
        </form>
    </div>
    
    <!-- 2. Edit BOM Multi-Row Form (Dedicated Amber Card Container matching Expenses/Purchases edit forms) -->
    <div id="editBomCardContainer" class="hidden bg-[#FFFDF5] rounded-2xl shadow-md border-2 border-amber-300 p-6 transition-all duration-300">
        <div class="flex items-center justify-between border-b border-amber-200/60 pb-4 mb-4">
            <h3 class="text-base font-bold text-amber-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                <span>Edit BOM Formula:</span>
                <span id="editBomProductTitleText" class="ml-1.5 text-amber-800 font-extrabold"></span>
            </h3>
            <button type="button" onclick="closeEditBomCard()" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition cursor-pointer">&times; Close</button>
        </div>

        <form action="{{ route('bom.store') }}" method="POST" class="ajax-form space-y-5" data-redirect="/bom">
            @csrf
            <input type="hidden" name="replace_mode" value="1">
            <input type="hidden" name="product_id" id="edit_bom_product_id">

            <!-- Multi-Row Raw Materials Itemizer -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-amber-900 uppercase tracking-wider">Raw Material Components & Waste Allowance</label>
                    <button type="button" onclick="addEditBomRow()" class="text-xs text-amber-700 hover:text-amber-900 font-bold flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>Add Raw Material Row</span>
                    </button>
                </div>

                <div id="editBomRowsContainer" class="space-y-3">
                    <!-- Dynamic Rows Injected -->
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-3 border-t border-amber-200/60">
                <button type="button" onclick="closeEditBomCard()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold py-2.5 px-5 rounded-xl transition cursor-pointer">Cancel</button>
                <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold py-2.5 px-6 rounded-xl shadow-xs transition duration-150 cursor-pointer">Update Product Formula</button>
            </div>
        </form>
    </div>

    <!-- 3. BOM List (Products Formula Ledgers) -->
    <div class="space-y-6">
        @forelse ($finishedGoods as $good)
            @php
                $mfgCost = $good->estimated_manufacturing_cost;
                $baseCost = $good->base_material_cost;
                $wasteCost = $good->waste_allowance_cost;
                $sellingPrice = (float)($good->selling_price ?? 0);
                $grossProfit = $good->gross_profit;
                $marginPercent = $good->profit_margin_percentage;
            @endphp
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-base font-bold text-slate-800">{{ $good->product_name }}</h3>
                            @if($good->sku)
                                <span class="text-xs font-mono bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md font-semibold">SKU: {{ $good->sku }}</span>
                            @endif
                            <span class="px-2.5 py-0.5 bg-blue-50 text-blue-700 text-xs font-bold rounded-lg border border-blue-200">
                                {{ $good->billOfMaterials->count() }} ingredients
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Weight: {{ number_format($good->unit_weight_kg, 2) }} kg | UOM: {{ strtoupper($good->uom ?? 'PCS') }}</p>
                    </div>

                    <!-- Cost & Margin KPIs -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <div class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl flex flex-col justify-center min-w-[96px]">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider leading-tight">Est. Unit Cost</span>
                            <span class="text-xs font-extrabold text-slate-800 leading-normal">
                                {{ $mfgCost > 0 ? '₹' . number_format($mfgCost, 2) : '—' }}
                            </span>
                        </div>
                        
                        <div class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl flex flex-col justify-center min-w-[96px]">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider leading-tight">List Price</span>
                            <span class="text-xs font-extrabold text-blue-600 leading-normal">
                                {{ $sellingPrice > 0 ? '₹' . number_format($sellingPrice, 2) : '—' }}
                            </span>
                        </div>

                        <div class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl flex flex-col justify-center min-w-[115px]">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider leading-tight">Gross Margin</span>
                            <div class="flex items-center gap-1.5 leading-normal">
                                @if($good->billOfMaterials->isEmpty() || $mfgCost <= 0)
                                    <span class="text-xs font-semibold text-slate-400 italic">No BOM cost</span>
                                @elseif($sellingPrice > 0)
                                    @if($marginPercent >= 25)
                                        <span class="inline-flex items-center gap-1 text-xs font-extrabold text-emerald-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                            {{ $marginPercent }}%
                                        </span>
                                        <span class="text-[10px] text-emerald-600 font-bold">(+₹{{ number_format($grossProfit, 0) }})</span>
                                    @elseif($marginPercent >= 10)
                                        <span class="inline-flex items-center gap-1 text-xs font-extrabold text-amber-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                            {{ $marginPercent }}%
                                        </span>
                                        <span class="text-[10px] text-amber-600 font-bold">(+₹{{ number_format($grossProfit, 0) }})</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-extrabold text-rose-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
                                            {{ $marginPercent }}%
                                        </span>
                                        <span class="text-[10px] text-rose-600 font-bold">(₹{{ number_format($grossProfit, 0) }})</span>
                                    @endif
                                @else
                                    <span class="text-xs text-slate-400 font-semibold italic">No Price Set</span>
                                @endif
                            </div>
                        </div>

                        <button type="button" 
                                onclick="editFullProductBom({{ $good->id }}, '{{ addslashes($good->product_name) }}', {{ json_encode($good->billOfMaterials->map(fn($b) => ['raw_material_id' => (string)$b->raw_material_id, 'required_quantity' => (float)$b->required_quantity, 'waste_percentage' => (float)$b->waste_percentage, 'unit_rate' => $b->unit_rate])) }})"
                                class="bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold py-2.5 px-3.5 rounded-xl shadow-xs transition duration-150 flex items-center gap-1.5 cursor-pointer shrink-0 ml-1"
                                title="Edit Product Formula">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            <span>Edit Formula</span>
                        </button>
                    </div>
                </div>

                @if ($good->billOfMaterials->isEmpty())
                    <p class="text-xs text-slate-400 py-4 border border-dashed rounded-xl border-slate-200 text-center font-medium">No BOM components assigned yet. Click "Add BOM Formula" above to assign raw materials.</p>
                @else
                    <div class="overflow-x-auto w-full max-w-full">
                        <table class="erp-datatable min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-[#EDF4FA] text-black divide-x divide-slate-200">
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-xs font-bold uppercase">Raw Material</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-bold uppercase">Rate (₹ / unit)</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-bold uppercase">Qty Required</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-bold uppercase">Waste Scrap</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-bold uppercase">Effective Qty</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-bold uppercase">Estimated Cost</th>
                                    <th class="px-4 py-2.5 text-center text-xs font-bold uppercase w-16">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach ($good->billOfMaterials as $bom)
                                    @php
                                        $material = $bom->rawMaterial;
                                        $rate = (float)($bom->effective_rate ?? 0);
                                        $isCustomRate = !is_null($bom->unit_rate) && (float)$bom->unit_rate > 0;
                                        $wasteMultiplier = 1 + ($bom->waste_percentage / 100);
                                        $netConsumption = $bom->required_quantity * $wasteMultiplier;
                                        $lineCost = $netConsumption * $rate;
                                    @endphp
                                    <tr class="hover:bg-slate-50 transition" id="row-bom-{{ $bom->id }}">
                                        <td class="px-4 py-3 font-semibold text-slate-800">
                                            <div>{{ $material->material_name }}</div>
                                            @if($material->specification)
                                                <div class="text-[11px] text-slate-400 font-mono">{{ $material->specification }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium">
                                            <span class="text-slate-800 font-bold">₹{{ number_format($rate, 2) }}</span>
                                            <span class="text-[11px] text-slate-400 font-normal">/ {{ $material->unit }}</span>
                                            @if($material->is_auto_avg)
                                                <span class="text-[9.5px] text-emerald-700 font-bold tracking-wider inline-flex items-center gap-1 bg-emerald-50 border border-emerald-200 rounded-md px-1.5 py-0.5 mt-0.5 ml-auto w-fit" title="Calculated from Purchase ledger entries">
                                                    🔄 Auto Avg
                                                </span>
                                            @else
                                                <span class="text-[9.5px] text-blue-700 font-bold tracking-wider inline-flex items-center gap-1 bg-blue-50 border border-blue-200 rounded-md px-1.5 py-0.5 mt-0.5 ml-auto w-fit" title="Custom Fixed Master Rate">
                                                    🔒 Master Rate
                                                </span>
                                            @endif
                                        </td>
                                        @php
                                            $qtyClean = (float)$bom->required_quantity;
                                            $formattedQty = rtrim(rtrim(number_format($qtyClean, 4), '0'), '.');
                                            $netClean = (float)$netConsumption;
                                            $formattedNet = rtrim(rtrim(number_format($netClean, 4), '0'), '.');
                                            $isKg = strtolower($material->unit ?? '') === 'kg';
                                        @endphp
                                        <td class="px-4 py-3 text-right font-medium whitespace-nowrap">
                                            <span class="text-slate-800 font-bold">{{ $formattedQty }} {{ $material->unit }}</span>
                                            @if($isKg && $qtyClean < 1 && $qtyClean > 0)
                                                <span class="block text-[10px] text-emerald-600 font-bold font-mono">({{ round($qtyClean * 1000, 2) }} g)</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right text-rose-600 font-semibold">+{{ (float)$bom->waste_percentage }}%</td>
                                        <td class="px-4 py-3 text-right font-medium text-slate-800 whitespace-nowrap">
                                            <span class="text-slate-800 font-bold">{{ $formattedNet }} {{ $material->unit }}</span>
                                            @if($isKg && $netClean < 1 && $netClean > 0)
                                                <span class="block text-[10px] text-slate-400 font-medium font-mono">({{ round($netClean * 1000, 2) }} g)</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right font-bold text-slate-900">₹{{ number_format($lineCost, 2) }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center space-x-1.5">
                                                <button type="button" 
                                                        title="Edit Component Quantity & Waste"
                                                        onclick="openEditBomModal({{ $bom->id }}, '{{ addslashes($good->product_name) }}', '{{ addslashes($material->material_name) }}', '{{ (float)$bom->required_quantity }}', '{{ (float)$bom->waste_percentage }}')"
                                                        class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-xs transition duration-150 transform hover:scale-105">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                </button>
                                                <button type="button" 
                                                        title="Remove Component"
                                                        onclick="deleteBomComponent({{ $bom->id }}, '{{ addslashes($material->material_name) }}')"
                                                        class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-rose-500 hover:bg-rose-600 text-white shadow-xs transition duration-150 transform hover:scale-105">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-slate-50 font-bold text-slate-800 border-t border-slate-200">
                                <tr>
                                    <td colspan="5" class="px-4 py-2.5 text-right text-xs uppercase tracking-wider text-slate-600">
                                        Total Estimated Material Unit Cost:
                                    </td>
                                    <td class="px-4 py-2.5 text-right text-sm font-extrabold text-blue-600">
                                        ₹{{ number_format($mfgCost, 2) }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <p class="text-base font-bold text-slate-600">No Records Found</p>
                <p class="text-xs text-slate-400 mt-1">There are no products cataloged to assign Bill of Materials formulas.</p>
            </div>
        @endforelse
    </div>
</div>

<template id="emptyBomRowTemplate">
    <div class="bom-row flex flex-col md:flex-row items-stretch md:items-end gap-2.5 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
        <div class="flex-grow min-w-[200px]">
            <x-combobox name="raw_material_ids[]"
                        label="Raw Material"
                        placeholder="Select Raw Material..."
                        :options="$rawMaterialOptions"
                        required />
        </div>
        <div class="w-full md:w-32 shrink-0">
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Rate (₹ / unit)</label>
            <div class="bom-rate-display h-[38px] px-2.5 bg-white border border-slate-200 rounded-lg flex items-center justify-end font-bold text-xs text-slate-700">₹0.00</div>
        </div>
        <div class="w-full md:w-28">
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Required Qty</label>
            <input type="number" name="required_quantities[]" step="0.0001" min="0.0001" placeholder="e.g. 4.5" required
                   class="bom-qty-input w-full bg-white border border-slate-200 rounded-lg py-2 px-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 h-[38px]">
        </div>
        <div class="w-full md:w-24">
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Waste (%)</label>
            <input type="number" name="waste_percentages[]" step="0.01" min="0" value="0" placeholder="e.g. 5%" required
                   class="bom-waste-input w-full bg-white border border-slate-200 rounded-lg py-2 px-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 h-[38px]">
        </div>
        <div class="w-full md:w-28 shrink-0">
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Line Cost</label>
            <div class="bom-line-cost-display h-[38px] px-2.5 bg-white border border-slate-200 rounded-lg flex items-center justify-end font-extrabold text-xs text-slate-800">₹0.00</div>
        </div>
        <div class="shrink-0 flex items-center justify-center">
            <button type="button" title="Remove component row" class="remove-bom-row-btn w-[38px] h-[38px] rounded-xl bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-600 flex items-center justify-center transition duration-150 cursor-pointer shadow-2xs">
                <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </div>
</template>

<!-- Edit BOM Component Modal Dialog -->
<div id="editBomFormCard" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" onclick="if(event.target === this) closeEditBomModal()">
    <div class="bg-[#FFFDF5] rounded-3xl shadow-2xl border-2 border-amber-300 p-6 sm:p-7 max-w-xl w-full transition-all duration-300">
        <div class="flex items-start justify-between border-b border-amber-200/60 pb-4 mb-5 gap-4">
            <div class="flex items-start gap-3">
                <span class="w-9 h-9 rounded-xl bg-amber-100 border border-amber-300 text-amber-700 flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </span>
                <div>
                    <h3 class="text-base font-extrabold text-amber-950 leading-tight">Edit Component Formula</h3>
                    <div class="text-xs text-amber-800 font-medium flex items-center gap-1.5 flex-wrap mt-1">
                        <span id="edit_bom_prod_name" class="font-bold text-slate-800"></span>
                        <span class="text-amber-500 font-bold">→</span>
                        <span id="edit_bom_mat_name" class="font-bold text-blue-700 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-lg"></span>
                    </div>
                </div>
            </div>
            <button type="button" onclick="closeEditBomModal()" class="w-8 h-8 rounded-xl bg-amber-100/70 hover:bg-amber-200 text-amber-900 flex items-center justify-center text-sm font-extrabold transition cursor-pointer shrink-0 shadow-2xs" title="Close Modal">
                ✕
            </button>
        </div>

        <form id="editBomForm" action="" method="POST" class="ajax-form space-y-4" data-redirect="/bom" data-close-modal="#editBomModal">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Required Qty</label>
                    <input type="number" id="edit_required_quantity" name="required_quantity" step="0.0001" min="0.0001" required
                           class="w-full bg-white border border-amber-200 rounded-xl py-2.5 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-800 font-bold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Waste Factor (%)</label>
                    <input type="number" id="edit_waste_percentage" name="waste_percentage" step="0.01" min="0" required
                           class="w-full bg-white border border-amber-200 rounded-xl py-2.5 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-800 font-bold">
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-amber-200/60 mt-2">
                <button type="button" onclick="closeEditBomModal()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold py-2.5 px-5 rounded-xl transition cursor-pointer">Cancel</button>
                <button type="submit" class="btn-primary py-2.5 px-6 text-sm font-bold bg-[#2563EB] hover:bg-blue-700 text-white rounded-xl shadow-xs">
                    Update Component Formula
                </button>
            </div>
        </form>
    </div>
</div>

<template id="emptyEditBomRowTemplate">
    <div class="bom-row flex flex-col md:flex-row items-stretch md:items-end gap-2.5 bg-amber-50/60 p-3.5 rounded-xl border border-amber-200">
        <div class="flex-grow min-w-[200px]">
            <x-combobox name="raw_material_ids[]"
                        label="Raw Material"
                        placeholder="Select Raw Material..."
                        :options="$rawMaterialOptions"
                        required />
        </div>
        <div class="w-full md:w-32 shrink-0">
            <label class="block text-[10px] font-bold text-amber-900 uppercase mb-1">Rate (₹ / unit)</label>
            <div class="bom-rate-display h-[38px] px-2.5 bg-white border border-amber-200 rounded-lg flex items-center justify-end font-bold text-xs text-slate-700">₹0.00</div>
        </div>
        <div class="w-full md:w-28">
            <label class="block text-[10px] font-bold text-amber-900 uppercase mb-1">Required Qty</label>
            <input type="number" name="required_quantities[]" step="0.0001" min="0.0001" placeholder="e.g. 4.5" required
                   class="bom-qty-input w-full bg-white border border-amber-200 rounded-lg py-2 px-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700 font-bold h-[38px]">
        </div>
        <div class="w-full md:w-24">
            <label class="block text-[10px] font-bold text-amber-900 uppercase mb-1">Waste (%)</label>
            <input type="number" name="waste_percentages[]" step="0.01" min="0" value="0" placeholder="e.g. 5%" required
                   class="bom-waste-input w-full bg-white border border-amber-200 rounded-lg py-2 px-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700 font-bold h-[38px]">
        </div>
        <div class="w-full md:w-28 shrink-0">
            <label class="block text-[10px] font-bold text-amber-900 uppercase mb-1">Line Cost</label>
            <div class="bom-line-cost-display h-[38px] px-2.5 bg-white border border-amber-200 rounded-lg flex items-center justify-end font-extrabold text-xs text-slate-800">₹0.00</div>
        </div>
        <div class="shrink-0 flex items-center justify-center">
            <button type="button" title="Remove component row" class="remove-bom-row-btn w-[38px] h-[38px] rounded-xl bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-600 flex items-center justify-center transition duration-150 cursor-pointer shadow-2xs">
                <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </div>
</template>

<script>
window.rawMaterialRatesMap = @json($rawMaterialRatesMap ?? []);

function toggleAddBomForm() {
    closeEditBomCard();
    const card = document.getElementById('addBomFormCard');
    if (!card) return;
    const isHidden = card.classList.contains('hidden');
    if (isHidden) {
        card.classList.remove('hidden');
        card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
        card.classList.add('hidden');
    }
}

window.resetBomRateToAuto = function(btn) {
    const row = btn.closest('.bom-row') || btn.closest('form') || btn.closest('.grid');
    if (!row) return;
    const rateInp = row.querySelector('.bom-rate-input') || row.querySelector('input[name="unit_rate"]');
    if (rateInp) {
        rateInp.value = '';
        if (row.classList.contains('bom-row')) {
            updateBomRowCost(row);
        }
        if (typeof window.showToast === 'function') {
            window.showToast('info', 'Rate reset to Live Auto Purchase Average!');
        }
    }
};

function updateBomRowCost(row) {
    if (!row) return;
    const rateDisplay = row.querySelector('.bom-rate-display');
    const qtyInp = row.querySelector('.bom-qty-input');
    const wasteInp = row.querySelector('.bom-waste-input');
    const costDisplay = row.querySelector('.bom-line-cost-display');
    const hiddenMatInp = row.querySelector('.combobox-hidden-input');

    const matId = hiddenMatInp ? hiddenMatInp.value : null;
    let liveRate = 0;

    if (matId && window.rawMaterialRatesMap && window.rawMaterialRatesMap[matId]) {
        liveRate = parseFloat(window.rawMaterialRatesMap[matId].rate) || 0;
    } else if (hiddenMatInp && hiddenMatInp.value) {
        const opt = row.querySelector(`.combobox-option[data-value="${CSS.escape(hiddenMatInp.value)}"]`);
        if (opt && opt.dataset.rate) {
            liveRate = parseFloat(opt.dataset.rate) || 0;
        }
    }

    if (rateDisplay) {
        rateDisplay.innerText = liveRate > 0 ? `₹${liveRate.toFixed(2)}` : '₹0.00';
    }

    const qty = parseFloat(qtyInp ? qtyInp.value : 0) || 0;
    const waste = parseFloat(wasteInp ? wasteInp.value : 0) || 0;
    
    const effectiveQty = qty * (1 + (waste / 100));
    const lineCost = effectiveQty * liveRate;
    
    if (costDisplay) {
        costDisplay.innerText = '₹' + lineCost.toFixed(2);
    }
}

function editFullProductBom(productId, productName, components) {
    // Hide create form if open
    const createCard = document.getElementById('addBomFormCard');
    if (createCard) createCard.classList.add('hidden');

    const editCard = document.getElementById('editBomCardContainer');
    const titleText = document.getElementById('editBomProductTitleText');
    const prodInput = document.getElementById('edit_bom_product_id');
    const container = document.getElementById('editBomRowsContainer');

    if (!editCard) return;

    if (titleText) titleText.innerText = productName;
    if (prodInput) prodInput.value = productId;

    if (container) {
        container.innerHTML = '';
        if (components && components.length > 0) {
            components.forEach(comp => {
                addEditBomRowWithData(comp.raw_material_id, comp.required_quantity, comp.waste_percentage);
            });
        } else {
            addEditBomRow();
        }
    }

    editCard.classList.remove('hidden');
    editCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function closeEditBomCard() {
    const editCard = document.getElementById('editBomCardContainer');
    if (editCard) editCard.classList.add('hidden');
}

function addEditBomRowWithData(matId, reqQty, waste) {
    const container = document.getElementById('editBomRowsContainer');
    const template = document.getElementById('emptyEditBomRowTemplate');
    if (container && template) {
        const clone = template.content.cloneNode(true);
        const uniqueId = 'cb_edit_bom_rm_' + Date.now() + '_' + Math.floor(Math.random() * 10000);
        const wrapper = clone.querySelector('.combobox-wrapper');
        if (wrapper) {
            wrapper.id = uniqueId + '_wrapper';
            wrapper.setAttribute('data-combobox-id', uniqueId);
            const hidden = wrapper.querySelector('.combobox-hidden-input');
            const search = wrapper.querySelector('.combobox-search-input');
            const clearBtn = wrapper.querySelector('.combobox-clear-btn');
            const dropdown = wrapper.querySelector('.combobox-dropdown');
            if (hidden) { hidden.id = uniqueId + '_hidden'; hidden.value = matId; }
            if (search) { search.id = uniqueId + '_search'; }
            if (clearBtn) { clearBtn.id = uniqueId + '_clear'; }
            if (dropdown) { dropdown.id = uniqueId + '_dropdown'; }
            
            if (window.ERPComboboxManager) {
                window.ERPComboboxManager.syncDisplay(wrapper);
            }
        }
        
        const qtyInput = clone.querySelector('.bom-qty-input');
        const wasteInput = clone.querySelector('.bom-waste-input');
        
        if (qtyInput) qtyInput.value = parseFloat(reqQty) || reqQty;
        if (wasteInput) wasteInput.value = parseFloat(waste) || waste;

        container.appendChild(clone);
        const newlyAdded = container.lastElementChild;
        updateBomRowCost(newlyAdded);
    }
}

function addEditBomRow() {
    const container = document.getElementById('editBomRowsContainer');
    const template = document.getElementById('emptyEditBomRowTemplate');
    if (container && template) {
        const clone = template.content.cloneNode(true);
        const uniqueId = 'cb_edit_bom_rm_' + Date.now() + '_' + Math.floor(Math.random() * 10000);
        const wrapper = clone.querySelector('.combobox-wrapper');
        if (wrapper) {
            wrapper.id = uniqueId + '_wrapper';
            wrapper.setAttribute('data-combobox-id', uniqueId);
            const hidden = wrapper.querySelector('.combobox-hidden-input');
            const search = wrapper.querySelector('.combobox-search-input');
            const clearBtn = wrapper.querySelector('.combobox-clear-btn');
            const dropdown = wrapper.querySelector('.combobox-dropdown');
            if (hidden) { hidden.id = uniqueId + '_hidden'; hidden.value = ''; }
            if (search) { search.id = uniqueId + '_search'; search.value = ''; }
            if (clearBtn) { clearBtn.id = uniqueId + '_clear'; clearBtn.classList.add('hidden'); }
            if (dropdown) { dropdown.id = uniqueId + '_dropdown'; }
        }
        container.appendChild(clone);
    }
}

function addBomRow() {
    const container = document.getElementById('bomRowsContainer');
    const template = document.getElementById('emptyBomRowTemplate');
    if (container && template) {
        const clone = template.content.cloneNode(true);
        const uniqueId = 'cb_bom_rm_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
        const wrapper = clone.querySelector('.combobox-wrapper');
        if (wrapper) {
            wrapper.id = uniqueId + '_wrapper';
            wrapper.setAttribute('data-combobox-id', uniqueId);
            const hidden = wrapper.querySelector('.combobox-hidden-input');
            const search = wrapper.querySelector('.combobox-search-input');
            const clearBtn = wrapper.querySelector('.combobox-clear-btn');
            const dropdown = wrapper.querySelector('.combobox-dropdown');
            if (hidden) { hidden.id = uniqueId + '_hidden'; hidden.value = ''; }
            if (search) { search.id = uniqueId + '_search'; search.value = ''; }
            if (clearBtn) { clearBtn.id = uniqueId + '_clear'; clearBtn.classList.add('hidden'); }
            if (dropdown) { dropdown.id = uniqueId + '_dropdown'; }
        }
        container.appendChild(clone);
    }
}

function openEditBomModal(id, productName, materialName, reqQty, waste) {
    const card = document.getElementById('editBomFormCard');
    const form = document.getElementById('editBomForm');
    const prodText = document.getElementById('edit_bom_prod_name');
    const matText = document.getElementById('edit_bom_mat_name');
    const inputQty = document.getElementById('edit_required_quantity');
    const inputWaste = document.getElementById('edit_waste_percentage');

    if (card && form) {
        form.action = `/bom/${id}`;
        if (prodText) prodText.innerText = productName;
        if (matText) matText.innerText = materialName;
        if (inputQty) inputQty.value = parseFloat(reqQty) || reqQty;
        if (inputWaste) inputWaste.value = parseFloat(waste) || waste;

        card.classList.remove('hidden');
    }
}

function closeEditBomModal() {
    const card = document.getElementById('editBomFormCard');
    if (card) card.classList.add('hidden');
}

// Event Listeners for Live Cost Calculation
document.addEventListener('input', function(e) {
    if (e.target.matches('.bom-rate-input, .bom-qty-input, .bom-waste-input, .combobox-hidden-input')) {
        const row = e.target.closest('.bom-row');
        updateBomRowCost(row);
    }
});

document.addEventListener('click', function(e) {
    // 1. Remove Row
    const btn = e.target.closest('.remove-bom-row-btn');
    if (btn) {
        const row = btn.closest('.bom-row');
        const container = row ? row.parentElement : null;
        if (container && container.querySelectorAll('.bom-row').length > 1) {
            row.remove();
        } else {
            if (window.showToast) {
                window.showToast('error', 'At least one raw material component row is required.');
            } else {
                alert('At least one raw material component row is required.');
            }
        }
        return;
    }

    // 2. Combobox option selected inside BOM row
    const opt = e.target.closest('.combobox-option');
    if (opt) {
        const row = opt.closest('.bom-row');
        if (row) {
            setTimeout(() => {
                updateBomRowCost(row);
            }, 50);
        }
    }
});

// Initialize live costs for initial row
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.bom-row').forEach(row => updateBomRowCost(row));
});

function deleteBomComponent(id, name) {
    window.confirmDelete(
        'Remove BOM Component?',
        `Are you sure you want to remove '${name}' from this product BOM?`,
        function() {
            $.ajax({
                url: `/bom/${id}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        $(`#row-bom-${id}`).fadeOut(300, function() { $(this).remove(); });
                        if (typeof window.showToast === 'function') {
                            window.showToast('success', response.message);
                        } else {
                            alert(response.message);
                        }
                    }
                },
                error: function(xhr) {
                    alert('Error removing BOM component.');
                }
            });
        }
    );
}
</script>
@endsection
