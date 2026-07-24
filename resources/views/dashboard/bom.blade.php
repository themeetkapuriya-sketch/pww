@extends('layouts.app')

@section('title', 'Bill of Materials (BOM)')

@section('content')
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
            <h3 class="text-base font-bold text-slate-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Assign Raw Material Components to Product
            </h3>
            <button type="button" onclick="toggleAddBomForm()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times; Close</button>
        </div>

        <form action="{{ route('bom.store') }}" method="POST" class="ajax-form space-y-5">
            @csrf
            
            <div class="max-w-md">
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Select Target Product</label>
                <select name="product_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-semibold" required>
                    <option value="">Select Product...</option>
                    @foreach ($finishedGoods as $good)
                        <option value="{{ $good->id }}">{{ $good->product_name }} (SKU: {{ $good->sku }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Multi-Row Raw Materials Itemizer -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">Raw Material Components & Waste Allowance</label>
                    <button type="button" onclick="addBomRow()" class="text-xs text-blue-600 hover:text-blue-800 font-bold flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>+ Add Raw Material Row</span>
                    </button>
                </div>

                <div id="bomRowsContainer" class="space-y-3">
                    <div class="bom-row flex flex-col md:flex-row items-stretch md:items-center gap-3 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                        <div class="flex-grow">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Raw Material</label>
                            <select name="raw_material_ids[]" class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-medium" required>
                                <option value="">Select Raw Material...</option>
                                @foreach ($rawMaterials as $mat)
                                    <option value="{{ $mat->id }}">{{ $mat->material_name }} ({{ $mat->unit }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-36">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Required Qty</label>
                            <input type="number" name="required_quantities[]" step="0.0001" min="0.0001" placeholder="e.g. 4.5" required
                                   class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                        </div>
                        <div class="w-full md:w-32">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Waste Factor (%)</label>
                            <input type="number" name="waste_percentages[]" step="0.01" min="0" value="0" placeholder="e.g. 5%" required
                                   class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                        </div>
                        <div class="flex items-end pb-0.5">
                            <button type="button" class="remove-bom-row-btn w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-sm transition">
                                &times;
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="toggleAddBomForm()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800">Cancel</button>
                <button type="submit" class="btn-primary py-2.5 px-6 text-xs font-bold">Assign Components to Product</button>
            </div>
        </form>
    </div>
    
    <!-- 2. BOM List (Products Formula Ledgers) -->
    <div class="space-y-6">
        @foreach ($finishedGoods as $good)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">{{ $good->product_name }}</h3>
                        <span class="text-xs text-slate-500 font-mono">SKU: {{ $good->sku }} | List Price: ₹{{ number_format($good->selling_price, 2) }}</span>
                    </div>
                    <span class="px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-lg border border-blue-100">
                        {{ $good->billOfMaterials->count() }} ingredients
                    </span>
                </div>

                @if ($good->billOfMaterials->isEmpty())
                    <p class="text-xs text-slate-400 py-4 border border-dashed rounded-xl border-slate-200 text-center font-medium">No BOM components assigned yet. Click "Add BOM Formula" above to assign raw materials.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="erp-datatable min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-[#4371D7] text-white divide-x divide-white/25">
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-xs font-bold uppercase">Raw Material</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-bold uppercase">Qty Required</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-bold uppercase">Waste Allowance</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-bold uppercase">Net Consumption</th>
                                    <th class="px-4 py-2.5 text-center text-xs font-bold uppercase w-16">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach ($good->billOfMaterials as $bom)
                                    @php
                                        $wasteMultiplier = 1 + ($bom->waste_percentage / 100);
                                        $netConsumption = $bom->required_quantity * $wasteMultiplier;
                                    @endphp
                                    <tr class="hover:bg-slate-50 transition" id="row-bom-{{ $bom->id }}">
                                        <td class="px-4 py-3 font-semibold text-slate-800">{{ $bom->rawMaterial->material_name }}</td>
                                        <td class="px-4 py-3 text-right text-slate-700 font-medium">{{ number_format($bom->required_quantity, 4) }} {{ $bom->rawMaterial->unit }}</td>
                                        <td class="px-4 py-3 text-right text-rose-600 font-semibold">+{{ number_format($bom->waste_percentage, 1) }}%</td>
                                        <td class="px-4 py-3 text-right font-bold text-slate-800">{{ number_format($netConsumption, 4) }} {{ $bom->rawMaterial->unit }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center space-x-1.5">
                                                <button type="button" 
                                                        title="Edit Component Quantity & Waste"
                                                        onclick="openEditBomModal({{ $bom->id }}, '{{ addslashes($good->product_name) }}', '{{ addslashes($bom->rawMaterial->material_name) }}', '{{ $bom->required_quantity }}', '{{ $bom->waste_percentage }}')"
                                                        class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-xs transition duration-150 transform hover:scale-105">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                </button>
                                                <button type="button" 
                                                        title="Remove Component"
                                                        onclick="deleteBomComponent({{ $bom->id }}, '{{ addslashes($bom->rawMaterial->material_name) }}')"
                                                        class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-rose-500 hover:bg-rose-600 text-white shadow-xs transition duration-150 transform hover:scale-105">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

<!-- 3. Edit BOM Component Form Card -->
<div id="editBomFormCard" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl border-2 border-amber-500/40 p-6 max-w-lg w-full transition-all duration-300">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
            <h3 class="text-base font-bold text-slate-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                <span>Edit Component:</span>
                <span id="edit_bom_title_text" class="ml-1.5 text-blue-600 font-extrabold"></span>
            </h3>
            <button type="button" onclick="closeEditBomModal()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
        </div>

        <form id="editBomForm" action="" method="POST" class="ajax-form space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Required Quantity</label>
                    <input type="number" id="edit_required_quantity" name="required_quantity" step="0.0001" min="0.0001" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-800 font-bold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Waste Factor (%)</label>
                    <input type="number" id="edit_waste_percentage" name="waste_percentage" step="0.01" min="0" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-800 font-bold">
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeEditBomModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800">Cancel</button>
                <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-2.5 px-6 rounded-xl text-xs shadow-xs transition duration-150">Update Component</button>
            </div>
        </form>
    </div>
</div>

<!-- Template for Dynamic Raw Material Row -->
<template id="emptyBomRowTemplate">
    <div class="bom-row flex flex-col md:flex-row items-stretch md:items-center gap-3 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
        <div class="flex-grow">
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Raw Material</label>
            <select name="raw_material_ids[]" class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-medium" required>
                <option value="">Select Raw Material...</option>
                @foreach ($rawMaterials as $mat)
                    <option value="{{ $mat->id }}">{{ $mat->material_name }} ({{ $mat->unit }})</option>
                @endforeach
            </select>
        </div>
        <div class="w-full md:w-36">
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Required Qty</label>
            <input type="number" name="required_quantities[]" step="0.0001" min="0.0001" placeholder="e.g. 4.5" required
                   class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
        </div>
        <div class="w-full md:w-32">
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Waste Factor (%)</label>
            <input type="number" name="waste_percentages[]" step="0.01" min="0" value="0" placeholder="e.g. 5%" required
                   class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
        </div>
        <div class="flex items-end pb-0.5">
            <button type="button" class="remove-bom-row-btn w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-sm transition">
                &times;
            </button>
        </div>
    </div>
</template>

<script>
function toggleAddBomForm() {
    const card = document.getElementById('addBomFormCard');
    if (card) card.classList.toggle('hidden');
}

function addBomRow() {
    const container = document.getElementById('bomRowsContainer');
    const template = document.getElementById('emptyBomRowTemplate');
    if (container && template) {
        const clone = template.content.cloneNode(true);
        container.appendChild(clone);
    }
}

function openEditBomModal(id, productName, materialName, reqQty, waste) {
    const card = document.getElementById('editBomFormCard');
    const form = document.getElementById('editBomForm');
    const titleText = document.getElementById('edit_bom_title_text');
    const inputQty = document.getElementById('edit_required_quantity');
    const inputWaste = document.getElementById('edit_waste_percentage');

    if (card && form) {
        form.action = `/bom/${id}`;
        if (titleText) titleText.innerText = `${productName} → ${materialName}`;
        if (inputQty) inputQty.value = reqQty;
        if (inputWaste) inputWaste.value = waste;

        card.classList.remove('hidden');
    }
}

function closeEditBomModal() {
    const card = document.getElementById('editBomFormCard');
    if (card) card.classList.add('hidden');
}

document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('remove-bom-row-btn')) {
        const container = document.getElementById('bomRowsContainer');
        if (container && container.querySelectorAll('.bom-row').length > 1) {
            e.target.closest('.bom-row').remove();
        } else {
            alert('At least one raw material component row is required.');
        }
    }
});

function deleteBomComponent(id, name) {
    if (confirm(`Are you sure you want to remove '${name}' from this product BOM?`)) {
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
                        window.showToast(response.message, 'success');
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
}
</script>
@endsection
