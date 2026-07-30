@extends('layouts.app')

@section('title', 'Production Logs')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Production Logs</h1>
            <p class="text-sm text-slate-500">Record rack manufacturing batches and monitor stock inventory.</p>
        </div>
        <div>
            <button type="button" onclick="toggleProductionForm()" class="btn-primary py-2.5 px-5 text-xs font-bold shadow-xs flex items-center">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span id="btnProductionToggleText">Log Production Run</span>
            </button>
        </div>
    </div>

    <!-- 1. COLLAPSIBLE PRODUCTION LOG FORM -->
    <div id="productionFormCard" class="hidden bg-white rounded-2xl shadow-sm border border-slate-200 p-6 transition-all duration-300">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100/60">
            <h3 id="productionFormTitle" class="text-base font-bold text-slate-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-[#4371D7]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Log Finished Rack Output
            </h3>
            <button type="button" id="productionCloseBtn" onclick="toggleProductionForm(false)" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition cursor-pointer">&times; Close</button>
        </div>

        <form id="productionForm" action="{{ route('production.store') }}" method="POST" class="ajax-form space-y-4">
            @csrf
            <input type="hidden" name="_method" id="production_form_method" value="POST">

            <!-- Shift Information Header -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pb-3 border-b border-slate-100">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Production Date</label>
                    <input type="date" id="prod_date" name="production_date" value="{{ date('Y-m-d') }}" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                </div>
                <div id="recorded_by_wrapper">
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Recorded By</label>
                    <select id="prod_recorded_by" name="recorded_by" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium" required>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" {{ $u->id == auth()->id() ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Single Edit Mode Hidden Inputs -->
            <div id="single_edit_inputs" class="hidden">
                <input type="hidden" id="single_product_id" name="product_id" disabled>
                <input type="hidden" id="single_qty_mfg" name="quantity_manufactured" disabled>
                <input type="hidden" id="single_qty_rej" name="quantity_rejected" disabled>
            </div>

            <!-- Multi-Product Output Items Container -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase">Manufactured Product Items</label>
                    <button type="button" id="btnAddProductRow" onclick="addProductRow()" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition flex items-center gap-1 cursor-pointer">
                        <span>+ Add Another Product</span>
                    </button>
                </div>

                <div id="productionItemsList" class="space-y-3">
                    <!-- Default Row 0 -->
                    <div class="production-item-row grid grid-cols-1 md:grid-cols-12 gap-3 items-center bg-slate-50/70 p-3 rounded-xl border border-slate-200" data-row-index="0">
                        <div class="md:col-span-6">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 md:hidden">Product</label>
                            <select name="items[0][product_id]" id="prod_product_id_0" class="prod-select-input w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium" required>
                                <option value="">Select Product...</option>
                                @foreach ($finishedGoods as $good)
                                    <option value="{{ $good->id }}">{{ $good->product_name }} (SKU: {{ $good->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 md:hidden">Qty Manufactured</label>
                            <input type="number" name="items[0][quantity_manufactured]" id="prod_qty_mfg_0" min="1" placeholder="Qty Mfg" required
                                   class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 md:hidden">Qty Rejected</label>
                            <input type="number" name="items[0][quantity_rejected]" id="prod_qty_rej_0" min="0" value="0" placeholder="Rejected" required
                                   class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                        </div>
                        <div class="md:col-span-1 text-right flex items-center justify-end">
                            <button type="button" onclick="removeProductRow(this)" class="btn-remove-row p-2 text-rose-500 hover:text-rose-700 transition" title="Remove Product Row">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-2">
                <button type="button" onclick="toggleProductionForm(false)" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold py-2.5 px-5 rounded-xl transition cursor-pointer">Cancel</button>
                <button type="submit" id="productionSubmitBtn" class="btn-primary py-2.5 px-6 text-sm font-bold bg-[#2563EB] hover:bg-blue-700 text-white rounded-xl shadow-xs">
                    Log Production Run
                </button>
            </div>
        </form>
    </div>

    <!-- 2. RECORDS LIST UNDERNEATH -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Manufacturing Logs Ledger
        </h3>
        
        <div class="overflow-x-auto w-full max-w-full">
            <table class="erp-datatable min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-[#EDF4FA] text-black divide-x divide-slate-200">
                    <tr>
                        <th class="px-4 py-3.5 text-center text-xs font-bold uppercase w-12">#</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Production Date</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Product</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Qty Manufactured</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Qty Rejected</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Recorded By</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase w-28">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach ($productionLogs as $log)
                        <tr class="hover:bg-slate-50 transition" id="row-prod-{{ $log->id }}">
                            <td class="px-4 py-4 text-center font-bold text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ $log->production_date ? $log->production_date->format('d M Y') : 'N/A' }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ $log->product->product_name ?? $log->finishedGood->product_name ?? 'N/A' }}
                                <span class="block text-xs font-normal text-slate-400">SKU: {{ $log->product->sku ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-emerald-600 font-mono">+{{ number_format($log->quantity_manufactured) }} {{ $log->product->uom ?? 'pcs' }}</td>
                            <td class="px-6 py-4 text-right font-medium text-rose-500 font-mono">{{ number_format($log->quantity_rejected) }}</td>
                            <td class="px-6 py-4 text-slate-600 font-medium">{{ $log->recordedByUser->name ?? 'System Admin' }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <button type="button" 
                                            onclick="openEditProductionModal({{ $log->id }}, {{ $log->product_id }}, {{ $log->quantity_manufactured }}, {{ $log->quantity_rejected }}, '{{ $log->production_date ? $log->production_date->format('Y-m-d') : '' }}')"
                                            class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-xs transition duration-150 transform hover:scale-105"
                                            title="Edit Production Log">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    <button type="button" 
                                            onclick="deleteProductionLog({{ $log->id }})"
                                            class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-rose-500 hover:bg-rose-600 text-white shadow-xs transition duration-150 transform hover:scale-105"
                                            title="Delete Production Log">
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
</div>

<script>
let productRowCounter = 1;

function getProductsOptionsHtml() {
    return `
        <option value="">Select Product...</option>
        @foreach ($finishedGoods as $good)
            <option value="{{ $good->id }}">{{ addslashes($good->product_name) }} (SKU: {{ addslashes($good->sku) }})</option>
        @endforeach
    `;
}

function addProductRow() {
    const list = document.getElementById('productionItemsList');
    if (!list) return;
    
    const idx = productRowCounter++;
    const rowHtml = `
        <div class="production-item-row grid grid-cols-1 md:grid-cols-12 gap-3 items-center bg-slate-50/70 p-3 rounded-xl border border-slate-200" data-row-index="${idx}">
            <div class="md:col-span-6">
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 md:hidden">Product</label>
                <select name="items[${idx}][product_id]" id="prod_product_id_${idx}" class="prod-select-input w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium" required>
                    ${getProductsOptionsHtml()}
                </select>
            </div>
            <div class="md:col-span-3">
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 md:hidden">Qty Manufactured</label>
                <input type="number" name="items[${idx}][quantity_manufactured]" id="prod_qty_mfg_${idx}" min="1" placeholder="Qty Mfg" required
                       class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
            </div>
            <div class="md:col-span-2">
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 md:hidden">Qty Rejected</label>
                <input type="number" name="items[${idx}][quantity_rejected]" id="prod_qty_rej_${idx}" min="0" value="0" placeholder="Rejected" required
                       class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
            </div>
            <div class="md:col-span-1 text-right flex items-center justify-end">
                <button type="button" onclick="removeProductRow(this)" class="btn-remove-row p-2 text-rose-500 hover:text-rose-700 transition" title="Remove Product Row">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </div>
        </div>
    `;
    list.insertAdjacentHTML('beforeend', rowHtml);
}

function removeProductRow(btn) {
    const rows = document.querySelectorAll('.production-item-row');
    if (rows.length <= 1) {
        if (window.showToast) window.showToast('info', 'At least one product row is required.');
        return;
    }
    const row = btn.closest('.production-item-row');
    if (row) row.remove();
}

function resetProductionForm() {
    const form = document.getElementById('productionForm');
    if (!form) return;
    form.action = "{{ route('production.store') }}";
    document.getElementById('production_form_method').value = "POST";
    
    const card = document.getElementById('productionFormCard');
    if (card) card.className = 'bg-white rounded-2xl shadow-sm border border-slate-200 p-6 transition-all duration-300';

    document.getElementById('productionFormTitle').className = 'text-base font-bold text-slate-800 flex items-center';
    document.getElementById('productionFormTitle').innerHTML = `
        <svg class="w-5 h-5 mr-2 text-[#4371D7]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        Log Finished Rack Output
    `;
    document.getElementById('productionCloseBtn').className = 'text-xs font-bold text-slate-400 hover:text-slate-600';

    document.getElementById('btnAddProductRow').style.display = 'flex';
    document.getElementById('single_edit_inputs').style.display = 'none';

    // Disable single edit inputs
    document.getElementById('single_product_id').disabled = true;
    document.getElementById('single_qty_mfg').disabled = true;
    document.getElementById('single_qty_rej').disabled = true;

    // Reset items list to 1 row
    const list = document.getElementById('productionItemsList');
    list.innerHTML = `
        <div class="production-item-row grid grid-cols-1 md:grid-cols-12 gap-3 items-center bg-slate-50/70 p-3 rounded-xl border border-slate-200" data-row-index="0">
            <div class="md:col-span-6">
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 md:hidden">Product</label>
                <select name="items[0][product_id]" id="prod_product_id_0" class="prod-select-input w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium" required>
                    ${getProductsOptionsHtml()}
                </select>
            </div>
            <div class="md:col-span-3">
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 md:hidden">Qty Manufactured</label>
                <input type="number" name="items[0][quantity_manufactured]" id="prod_qty_mfg_0" min="1" placeholder="Qty Mfg" required
                       class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
            </div>
            <div class="md:col-span-2">
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 md:hidden">Qty Rejected</label>
                <input type="number" name="items[0][quantity_rejected]" id="prod_qty_rej_0" min="0" value="0" placeholder="Rejected" required
                       class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
            </div>
            <div class="md:col-span-1 text-right flex items-center justify-end">
                <button type="button" onclick="removeProductRow(this)" class="btn-remove-row p-2 text-rose-500 hover:text-rose-700 transition" title="Remove Product Row">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </div>
        </div>
    `;

    document.getElementById('prod_date').value = "{{ date('Y-m-d') }}";
    if (document.getElementById('recorded_by_wrapper')) document.getElementById('recorded_by_wrapper').style.display = 'block';
    
    const btn = document.getElementById('productionSubmitBtn');
    btn.innerText = 'Log Production Run';
    btn.className = 'btn-primary py-2.5 px-6 text-sm font-bold bg-[#2563EB] hover:bg-blue-700 text-white rounded-xl shadow-xs';
}

function toggleProductionForm(showExplicit = null) {
    const card = document.getElementById('productionFormCard');
    if (!card) return;
    const isHidden = card.classList.contains('hidden');
    const shouldShow = showExplicit !== null ? showExplicit : isHidden;
    
    if (shouldShow) {
        if (isHidden) resetProductionForm();
        card.classList.remove('hidden');
        card.scrollIntoView({ behavior: 'smooth' });
    } else {
        card.classList.add('hidden');
    }
}

function openEditProductionModal(id, productId, manufactured, rejected, date) {
    const card = document.getElementById('productionFormCard');
    if (!card) return;

    document.getElementById('productionForm').action = `/production/${id}`;
    document.getElementById('production_form_method').value = "PUT";
    
    card.className = 'bg-[#FFFDF5] rounded-2xl shadow-sm border-2 border-amber-300 p-6 transition-all duration-300';
    document.getElementById('productionFormTitle').className = 'text-base font-bold text-amber-900 flex items-center';
    document.getElementById('productionFormTitle').innerHTML = `Edit Production Batch #${id}`;
    document.getElementById('productionCloseBtn').className = 'text-xs font-bold text-amber-700 hover:text-amber-900';

    document.getElementById('btnAddProductRow').style.display = 'none';

    // Enable single edit inputs for PUT request
    document.getElementById('single_edit_inputs').style.display = 'block';
    document.getElementById('single_product_id').disabled = false;
    document.getElementById('single_product_id').value = productId;

    document.getElementById('single_qty_mfg').disabled = false;
    document.getElementById('single_qty_mfg').value = manufactured;

    document.getElementById('single_qty_rej').disabled = false;
    document.getElementById('single_qty_rej').value = rejected;

    // Single item edit row view
    const list = document.getElementById('productionItemsList');
    list.innerHTML = `
        <div class="production-item-row grid grid-cols-1 md:grid-cols-12 gap-3 items-center bg-white p-3 rounded-xl border border-amber-200" data-row-index="0">
            <div class="md:col-span-6">
                <label class="block text-[10px] font-bold text-amber-800 uppercase mb-1 md:hidden">Product</label>
                <select onchange="document.getElementById('single_product_id').value = this.value" class="w-full bg-white border border-amber-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700 font-medium" required>
                    ${getProductsOptionsHtml()}
                </select>
            </div>
            <div class="md:col-span-3">
                <label class="block text-[10px] font-bold text-amber-800 uppercase mb-1 md:hidden">Qty Manufactured</label>
                <input type="number" oninput="document.getElementById('single_qty_mfg').value = this.value" value="${manufactured}" min="1" placeholder="Qty Mfg" required
                       class="w-full bg-white border border-amber-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700 font-medium">
            </div>
            <div class="md:col-span-3">
                <label class="block text-[10px] font-bold text-amber-800 uppercase mb-1 md:hidden">Qty Rejected</label>
                <input type="number" oninput="document.getElementById('single_qty_rej').value = this.value" value="${rejected}" min="0" placeholder="Rejected" required
                       class="w-full bg-white border border-amber-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700 font-medium">
            </div>
        </div>
    `;

    const selectEl = list.querySelector('select');
    if (selectEl) selectEl.value = productId;

    document.getElementById('prod_date').value = date;
    if (document.getElementById('recorded_by_wrapper')) document.getElementById('recorded_by_wrapper').style.display = 'none';
    
    const btn = document.getElementById('productionSubmitBtn');
    btn.innerText = 'Update Production Log';
    btn.className = 'btn-primary py-2.5 px-6 text-sm font-bold bg-[#2563EB] hover:bg-blue-700 text-white rounded-xl shadow-xs';

    card.classList.remove('hidden');
    card.scrollIntoView({ behavior: 'smooth' });
}

function deleteProductionLog(id) {
    window.confirmDelete(
        'Delete Production Batch?',
        `Are you sure you want to delete Production Batch #${id}? This action cannot be undone.`,
        function() {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            $.ajax({
                url: `/production/${id}`,
                method: 'DELETE',
                data: { _token: token },
                success: function(res) {
                    if (window.showToast) window.showToast('success', res.message);
                    $(`#row-prod-${id}`).fadeOut(300, function() { $(this).remove(); });
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.message || 'Failed to delete production log.');
                }
            });
        }
    );
}
</script>
@endsection
