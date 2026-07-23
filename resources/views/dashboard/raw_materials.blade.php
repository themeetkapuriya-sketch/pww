@extends('layouts.app')

@section('title', 'Raw Materials Inventory Audit')

@section('content')
<div class="space-y-6">
    <!-- Page Header with Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight flex items-center">
                <svg class="w-6 h-6 mr-2.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                Raw Materials Catalog
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-1">Manage, edit, and audit factory raw material stock definitions.</p>
        </div>
        <div>
            <button type="button" onclick="toggleCreateMaterialForm()" 
                    class="btn-primary py-2.5 px-5 text-xs font-bold flex items-center shadow-xs">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                + Create Raw Material
            </button>
        </div>
    </div>

    <!-- INLINE EXPANDABLE FORM 1: Create Raw Material -->
    <div id="createMaterialFormCard" class="hidden bg-white rounded-2xl shadow-md border-2 border-blue-500/30 p-6 transition-all duration-300">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
            <h3 class="text-base font-bold text-slate-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Create New Raw Material
            </h3>
            <button type="button" onclick="toggleCreateMaterialForm()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times; Close</button>
        </div>
        <form action="{{ route('inventory.materials.store') }}" method="POST" class="ajax-form space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Material Name</label>
                <input type="text" name="material_name" placeholder="e.g. Iron Wire Coils (5mm)" required
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Measurement Unit</label>
                    <select name="unit" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                        <option value="kg" selected>kg (Kilograms)</option>
                        <option value="liter">liter (Liters)</option>
                        <option value="meter">meter (Meters)</option>
                        <option value="packet">packet (Packets)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Safety Limit</label>
                    <input type="number" name="safety_threshold" step="0.0001" min="0" placeholder="e.g. 2000" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Price (₹)</label>
                    <input type="number" name="average_purchase_price" step="0.01" min="0" placeholder="e.g. 85.00" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-2">
                <button type="button" onclick="toggleCreateMaterialForm()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800">Cancel</button>
                <button type="submit" class="btn-primary py-2 px-6 text-xs font-bold">Save Material</button>
            </div>
        </form>
    </div>

    <!-- INLINE EXPANDABLE FORM 2: Edit Raw Material -->
    <div id="editMaterialFormCard" class="hidden bg-amber-50/70 rounded-2xl shadow-md border-2 border-amber-400 p-6 transition-all duration-300">
        <div class="flex items-center justify-between border-b border-amber-200/80 pb-4 mb-4">
            <h3 class="text-base font-bold text-amber-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Edit Raw Material Details
            </h3>
            <button type="button" onclick="closeEditMaterialForm()" class="text-amber-700 hover:text-amber-900 text-lg font-bold">&times; Close</button>
        </div>
        <form id="editMaterialForm" method="POST" class="ajax-form space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Material Name</label>
                <input type="text" name="material_name" id="edit_material_name" required
                       class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Measurement Unit</label>
                    <select name="unit" id="edit_unit" required
                            class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700 font-medium">
                        <option value="kg">kg</option>
                        <option value="liter">liter</option>
                        <option value="meter">meter</option>
                        <option value="packet">packet</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Safety Limit</label>
                    <input type="number" name="safety_threshold" id="edit_safety_threshold" step="0.0001" min="0" required
                           class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Price (₹)</label>
                    <input type="number" name="average_purchase_price" id="edit_price" step="0.01" min="0" required
                           class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700">
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-2">
                <button type="button" onclick="closeEditMaterialForm()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800">Cancel</button>
                <button type="submit" class="btn-primary py-2 px-6 text-xs font-bold">Update Material</button>
            </div>
        </form>
    </div>

    <!-- Raw Materials Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-slate-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Raw Materials Ledger
            </h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="erp-datatable min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-[#4371D7] text-white divide-x divide-white/25">
                    <tr>
                        <th class="px-4 py-3.5 text-center text-xs font-bold uppercase w-12">#</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Material Name</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Current Stock</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Safety Threshold Limit</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Purchase Price</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">Status</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">Action</th>
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
                                <div class="flex items-center justify-center space-x-2">
                                    <button type="button" 
                                            title="Edit Raw Material"
                                            onclick="openEditMaterialForm({{ $mat->id }}, '{{ addslashes($mat->material_name) }}', '{{ $mat->unit }}', '{{ $mat->safety_threshold }}', '{{ $mat->average_purchase_price }}')"
                                            class="w-8 h-8 p-1.5 inline-flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-xs transition duration-150 transform hover:scale-105">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button type="button" 
                                            title="Delete Raw Material"
                                            onclick="deleteMaterial({{ $mat->id }}, '{{ addslashes($mat->material_name) }}')"
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
            {{ $rawMaterials->links() }}
        </div>
    </div>
</div>

<script>
function toggleCreateMaterialForm() {
    var editCard = document.getElementById('editMaterialFormCard');
    if (editCard) editCard.classList.add('hidden');
    var card = document.getElementById('createMaterialFormCard');
    if (card) {
        if (card.classList.contains('hidden')) {
            if (window.resetFormAndErrors) window.resetFormAndErrors('#createMaterialFormCard form');
            card.classList.remove('hidden');
            card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            if (window.resetFormAndErrors) window.resetFormAndErrors('#createMaterialFormCard form');
            card.classList.add('hidden');
        }
    }
}

function openEditMaterialForm(id, name, unit, threshold, price) {
    var createCard = document.getElementById('createMaterialFormCard');
    if (createCard) createCard.classList.add('hidden');

    var form = document.getElementById('editMaterialForm');
    if (form) form.action = "{{ url('/inventory/materials') }}/" + id;
    if (window.resetFormAndErrors) window.resetFormAndErrors('#editMaterialFormCard form');
    
    var nameEl = document.getElementById('edit_material_name');
    if (nameEl) nameEl.value = name;
    var unitEl = document.getElementById('edit_unit');
    if (unitEl) unitEl.value = unit;
    var threshEl = document.getElementById('edit_safety_threshold');
    if (threshEl) threshEl.value = threshold;
    var priceEl = document.getElementById('edit_price');
    if (priceEl) priceEl.value = price;

    var editCard = document.getElementById('editMaterialFormCard');
    if (editCard) {
        editCard.classList.remove('hidden');
        editCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function closeEditMaterialForm() {
    if (window.resetFormAndErrors) window.resetFormAndErrors('#editMaterialFormCard form');
    var editCard = document.getElementById('editMaterialFormCard');
    if (editCard) editCard.classList.add('hidden');
}

function deleteMaterial(id, name) {
    window.confirmDelete(
        "Delete Raw Material?",
        "Are you sure you want to delete material '" + name + "'?",
        function() {
            $.ajax({
                url: "{{ url('/inventory/materials') }}/" + id,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
                },
                success: function(res) {
                    if (res.success) {
                        $('#row-mat-' + id).fadeOut(300, function() { $(this).remove(); });
                        if (window.showToast) window.showToast('success', res.message);
                    }
                },
                error: function(err) {
                    if (window.showToast) window.showToast('error', 'Failed to delete raw material.');
                }
            });
        }
    );
}
</script>
@endsection
