@extends('layouts.app')

@section('title', 'Purchase Ledger')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Purchase Ledger</h1>
            <p class="text-sm text-slate-500">Record all factory purchases including raw materials, machinery, tools, and vendor bills.</p>
        </div>
    </div>

    <!-- 1. Log Purchase Bill Form -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Record Purchase Invoice / Bill
        </h3>
        <form action="{{ route('purchases.store') }}" method="POST" class="ajax-form space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Purchase Category</label>
                    <select name="purchase_type" id="purchaseTypeSelect" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-bold">
                        <option value="raw_material" selected>Raw Material Purchase (Auto-Restocks Inventory)</option>
                        <option value="machinery">Machinery & Capital Equipment</option>
                        <option value="supplies">Factory Consumables & Tools</option>
                    </select>
                </div>
                <div id="rawMaterialSelectContainer">
                    <label class="block text-xs font-bold text-blue-600 uppercase mb-1">Raw Material Sub-Category (Select to Restock)</label>
                    <select name="raw_material_id" id="rawMaterialSelect"
                            class="w-full bg-blue-50/50 border border-blue-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-bold">
                        <option value="">Select Existing Raw Material...</option>
                        @foreach ($rawMaterials as $mat)
                            <option value="{{ $mat->id }}" data-name="{{ $mat->material_name }}" data-unit="{{ $mat->unit }}">{{ $mat->material_name }} (Stock: {{ number_format($mat->current_stock, 1) }} {{ $mat->unit }})</option>
                        @endforeach
                    </select>
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

                <div id="itemNameInputContainer" class="hidden">
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Item Description / Name</label>
                    <input type="text" name="item_name" id="itemNameInput" placeholder="e.g. Spot Welding Machine 25kVA"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>

                <div id="qtyUnitContainer" class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Qty</label>
                        <input type="number" name="quantity" step="0.0001" min="0.0001" placeholder="e.g. 5000"
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-lg">
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
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Purchase Date</label>
                    <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>
            </div>

            <button type="submit" class="btn-primary py-2.5 px-6 text-sm font-bold">
                Log Purchase Entry
            </button>
        </form>
    </div>

    <!-- 2. Purchase Bills Ledger Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Purchase History & Bills Ledger
        </h3>
        
        <div class="overflow-x-auto">
            <table class="erp-datatable min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-[#4371D7] text-white divide-x divide-white/25">
                    <tr>
                        <th class="px-4 py-3.5 text-center text-xs font-bold uppercase w-12">#</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Date</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Vendor / Supplier</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Category</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Item / Machinery Name</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Quantity</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">GST Slab</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Total Bill (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach ($purchases as $pur)
                        <tr class="hover:bg-slate-50 transition">
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
                                @elseif($pur->purchase_type === 'machinery')
                                    <span class="px-2.5 py-0.5 bg-purple-50 text-purple-700 border border-purple-200 text-[10px] rounded font-bold uppercase">Machinery / Capital</span>
                                @else
                                    <span class="px-2.5 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 text-[10px] rounded font-bold uppercase">Supplies & Tools</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $pur->item_name }}</td>
                            <td class="px-6 py-4 text-right font-medium text-slate-700">{{ number_format($pur->quantity, 2) }} {{ $pur->unit }}</td>
                            <td class="px-6 py-4 text-right text-slate-700 font-medium">
                                <span class="font-bold text-blue-600">{{ number_format($pur->gst_rate, 0) }}%</span>
                                <div class="text-[10px] text-slate-400">₹{{ number_format($pur->gst_amount, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-slate-900">₹{{ number_format($pur->total_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $purchases->links() }}
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    function toggleFields() {
        const val = $('#purchaseTypeSelect').val();
        if (val === 'raw_material') {
            $('#rawMaterialSelectContainer').removeClass('hidden');
            $('#qtyUnitContainer').removeClass('hidden');
            $('#itemNameInputContainer').addClass('hidden');
        } else {
            $('#rawMaterialSelectContainer').addClass('hidden');
            $('#rawMaterialSelect').val('');
            $('#qtyUnitContainer').addClass('hidden');
            $('#itemNameInputContainer').removeClass('hidden');
        }
    }

    toggleFields();
    $('#purchaseTypeSelect').on('change', toggleFields);

    $('#rawMaterialSelect').on('change', function() {
        const opt = $(this).find('option:selected');
        if (opt.val()) {
            $('#itemNameInput').val(opt.data('name'));
            $('#unitInput').val(opt.data('unit'));
        }
    });
});
</script>
@endsection
