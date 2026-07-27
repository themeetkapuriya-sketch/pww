@extends('layouts.app')

@section('title', 'Sales Orders')

@section('content')
<div class="space-y-6">
    <!-- Header & Action Button -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Sales Orders</h1>
            <p class="text-xs text-slate-500 font-medium">Book customer purchase orders, manage production pipelines, and convert to Delivery Challans.</p>
        </div>

        <button type="button" 
                onclick="toggleInlineForm('orderFormContainer', this)" 
                class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-md transition duration-150 flex items-center space-x-2">
            <svg class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Book New Sales Order</span>
        </button>
    </div>

    <!-- Smooth Expandable Order Booking Form -->
    <div id="orderFormContainer" class="hidden transition-all duration-300 ease-in-out">
        <div id="salesOrderFormCard" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4 transition-all duration-300">
            <div class="flex items-center justify-between pb-3 mb-2 border-b border-slate-100/60">
                <h3 id="salesOrderFormTitle" class="text-base font-bold text-slate-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    Sales Order Booking Form
                </h3>
                <button type="button" id="salesOrderCloseBtn" onclick="toggleInlineForm('orderFormContainer', document.querySelector('button[onclick*=\'orderFormContainer\']'))" class="text-xs font-bold text-slate-400 hover:text-slate-600">&times; Close</button>
            </div>

            <form id="salesOrderForm" action="{{ route('orders.store') }}" method="POST" class="ajax-form space-y-4">
                @csrf
                <input type="hidden" name="_method" id="salesOrderFormMethod" value="POST">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Order Number</label>
                        <input type="text" name="order_number_display" value="{{ \App\Models\SalesOrder::generateNextOrderNumber() }}" disabled
                               class="w-full bg-slate-100 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none text-slate-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Select Client</label>
                        <select id="orderClientSelect" name="client_id" required onchange="handleOrderClientChange()"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                            <option value="">Choose client...</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" data-plants='@json($client->plants->map(fn($p) => ["id" => $p->id, "name" => $p->plant_name]))'>
                                    {{ $client->company_name }} ({{ $client->plants->count() === 1 ? '1 Location' : $client->plants->count() . ' Plants' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="orderPlantWrapper" class="hidden">
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Target Plant Location</label>
                        <select id="orderPlantSelect" name="plant_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                            <option value="">Select plant...</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Order Date</label>
                        <input type="date" name="order_date" value="{{ date('Y-m-d') }}" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Target Delivery Date</label>
                        <input type="date" name="delivery_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                    </div>
                </div>

                <!-- Product Line Items -->
                <div class="border-t border-slate-200 pt-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Ordered Products</label>
                        <button type="button" id="addOrderRowBtn" class="text-blue-600 hover:text-blue-700 text-xs font-bold flex items-center">
                            + Add Item
                        </button>
                    </div>

                    <div id="orderRowsContainer" class="space-y-2">
                        <div class="order-row flex items-center space-x-3 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                            <select name="product_ids[]" required class="flex-grow bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" onchange="updateRowUnitPrice(this)">
                                <option value="">Select product...</option>
                                @foreach ($finishedGoods as $g)
                                    <option value="{{ $g->id }}" data-price="{{ $g->selling_price }}">{{ $g->product_name }} (Stock: {{ number_format($g->current_stock) }})</option>
                                @endforeach
                            </select>
                            <input type="number" name="quantities[]" min="1" placeholder="Qty" required class="w-20 bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                            <input type="number" name="unit_prices[]" step="0.01" min="0" placeholder="Price (₹)" required class="w-28 bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                            <button type="button" onclick="removeOrderRow(this)" class="text-rose-500 hover:text-rose-600 font-bold px-2 text-sm">✕</button>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Order Notes / Delivery Instructions</label>
                    <textarea name="notes" rows="2" placeholder="e.g. Special heavy-duty powder coating requirements..."
                              class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" onclick="toggleInlineForm('orderFormContainer', document.querySelector('button[onclick*=\'orderFormContainer\']'))" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800">Cancel</button>
                    <button type="submit" class="btn-primary py-2.5 px-6 text-sm font-bold shadow-xs">
                        Book Sales Order
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Status Overview Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Booked</span>
            <span class="text-2xl font-black text-slate-800 block mt-1">{{ $stats['total'] }}</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <span class="text-[10px] font-bold text-amber-500 uppercase tracking-wider block">Pending</span>
            <span class="text-2xl font-black text-amber-600 block mt-1">{{ $stats['pending'] }}</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <span class="text-[10px] font-bold text-blue-500 uppercase tracking-wider block">In Production</span>
            <span class="text-2xl font-black text-blue-600 block mt-1">{{ $stats['in_production'] }}</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider block">Ready For Dispatch</span>
            <span class="text-2xl font-black text-indigo-600 block mt-1">{{ $stats['ready'] }}</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider block">Dispatched / Done</span>
            <span class="text-2xl font-black text-emerald-600 block mt-1">{{ $stats['completed'] }}</span>
        </div>
    </div>

    <!-- Filter Capsules Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-black uppercase text-slate-400 tracking-wider flex items-center mr-2">
                <svg class="w-4 h-4 mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                Order Pipeline Status:
            </span>
            <a href="{{ route('orders', ['status' => 'all']) }}" class="px-4 py-1.5 rounded-full text-xs font-bold border transition duration-150 {{ $status === 'all' ? 'bg-blue-600 border-blue-600 text-white shadow-sm' : 'border-blue-600/30 text-blue-700 hover:bg-blue-50 bg-white' }}">
                All Orders
            </a>
            <a href="{{ route('orders', ['status' => 'pending']) }}" class="px-4 py-1.5 rounded-full text-xs font-bold border transition duration-150 {{ $status === 'pending' ? 'bg-blue-600 border-blue-600 text-white shadow-sm' : 'border-blue-600/30 text-blue-700 hover:bg-blue-50 bg-white' }}">
                Pending ({{ $stats['pending'] }})
            </a>
            <a href="{{ route('orders', ['status' => 'in_production']) }}" class="px-4 py-1.5 rounded-full text-xs font-bold border transition duration-150 {{ $status === 'in_production' ? 'bg-blue-600 border-blue-600 text-white shadow-sm' : 'border-blue-600/30 text-blue-700 hover:bg-blue-50 bg-white' }}">
                In Production ({{ $stats['in_production'] }})
            </a>
            <a href="{{ route('orders', ['status' => 'ready_for_dispatch']) }}" class="px-4 py-1.5 rounded-full text-xs font-bold border transition duration-150 {{ $status === 'ready_for_dispatch' ? 'bg-blue-600 border-blue-600 text-white shadow-sm' : 'border-blue-600/30 text-blue-700 hover:bg-blue-50 bg-white' }}">
                Ready For Dispatch ({{ $stats['ready'] }})
            </a>
            <a href="{{ route('orders', ['status' => 'dispatched']) }}" class="px-4 py-1.5 rounded-full text-xs font-bold border transition duration-150 {{ $status === 'dispatched' ? 'bg-blue-600 border-blue-600 text-white shadow-sm' : 'border-blue-600/30 text-blue-700 hover:bg-blue-50 bg-white' }}">
                Dispatched / Completed
            </a>
        </div>
    </div>

    <!-- Orders Data Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            Sales Orders Ledger
        </h3>

        <div class="overflow-x-auto w-full max-w-full">
            <table class="erp-datatable min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-[#4371D7] text-white divide-x divide-white/25">
                    <tr>
                        <th class="px-3 py-3 text-center text-xs font-bold uppercase w-12">#</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase">Order #</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase">Client & Plant</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase">Ordered Items</th>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase">Target Date</th>
                        <th class="px-4 py-3 text-right text-xs font-bold uppercase">Total Value</th>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase">Pipeline Status</th>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white text-xs">
                    @forelse ($orders as $ord)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-3 py-3 text-center font-medium text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 font-mono font-bold text-blue-600">
                                {{ $ord->order_number }}
                                @if($ord->po_number)
                                    <div class="text-[10px] text-slate-400 font-mono">PO: {{ $ord->po_number }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-800">
                                {{ $ord->client->company_name ?? 'N/A' }}
                                @if($ord->plant)
                                    <div class="text-[10px] text-blue-600 font-bold">🏭 {{ $ord->plant->plant_name }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <ul class="space-y-0.5">
                                    @foreach($ord->items as $it)
                                        <li class="text-slate-700 font-medium">
                                            • {{ $it->product->product_name ?? $it->finishedGood->product_name ?? 'Product' }}: <strong class="text-slate-900">{{ number_format($it->quantity) }}</strong> @ ₹{{ number_format($it->unit_price, 2) }}
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-center text-slate-600 whitespace-nowrap">
                                <span class="font-bold block">{{ \Carbon\Carbon::parse($ord->order_date)->format('d/m/Y') }}</span>
                                @if($ord->delivery_date)
                                    <span class="text-[10px] text-slate-400 block">Target: {{ \Carbon\Carbon::parse($ord->delivery_date)->format('d/m/Y') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-extrabold text-slate-900">₹{{ number_format($ord->total_amount, 2) }}</td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @php
                                    $hasStock = $ord->hasSufficientStock();
                                    $deficits = $ord->getStockDeficitDetails();
                                @endphp

                                @if ($ord->status === 'dispatched' || $ord->status === 'completed')
                                    <div class="inline-flex flex-col items-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs">
                                            ✓ DISPATCHED
                                        </span>
                                        <span class="text-[9px] text-emerald-600 font-semibold mt-0.5">Stock Deducted</span>
                                    </div>

                                @elseif ($ord->status === 'cancelled')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-300 shadow-2xs">
                                        ✕ CANCELLED
                                    </span>

                                @elseif ($ord->status === 'pending')
                                    <div class="inline-flex flex-col items-center space-y-1">
                                        <button type="button" 
                                                onclick="updateOrderStatus({{ $ord->id }}, 'in_production')"
                                                title="Click to start manufacturing run for this order"
                                                class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white text-[10px] font-bold rounded-lg shadow-2xs transition flex items-center space-x-1 cursor-pointer">
                                            <span>▶ Start Production</span>
                                        </button>
                                        <span class="text-[9.5px] text-amber-600 font-semibold">Stage 1: Pending</span>
                                    </div>

                                @elseif ($ord->status === 'in_production')
                                    @if ($hasStock)
                                        <div class="inline-flex flex-col items-center space-y-1">
                                            <button type="button" 
                                                    onclick="updateOrderStatus({{ $ord->id }}, 'ready_for_dispatch')"
                                                    title="Sufficient stock available! Click to mark Ready for Dispatch"
                                                    class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-bold rounded-lg shadow-2xs transition flex items-center space-x-1 cursor-pointer animate-pulse">
                                                <span>📦 Mark Ready for Dispatch</span>
                                            </button>
                                            <span class="text-[9.5px] text-blue-600 font-semibold">Stock Ready</span>
                                        </div>
                                    @else
                                        <div class="inline-flex flex-col items-center space-y-1" title="{{ implode('; ', array_map(fn($d) => $d['product_name'] . ': missing ' . $d['missing_quantity'] . ' pcs', $deficits)) }}">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                                ⏳ In Production (Awaiting Stock)
                                            </span>
                                            <a href="{{ route('production') }}" class="text-[9.5px] text-amber-700 hover:underline font-bold">+ Log Batch in Production →</a>
                                        </div>
                                    @endif

                                @elseif ($ord->status === 'ready_for_dispatch')
                                    <div class="inline-flex flex-col items-center space-y-1">
                                        <a href="{{ route('invoices', ['order_id' => $ord->id]) }}" 
                                           title="Stock is ready! Click to generate Tax Invoice & dispatch order"
                                           class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-[10px] font-bold rounded-lg shadow-2xs transition flex items-center space-x-1">
                                            <span>🚀 Gen Invoice & Dispatch</span>
                                        </a>
                                        <span class="text-[9.5px] text-indigo-600 font-semibold">Stage 3: Ready for Dispatch</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    @if($ord->status !== 'dispatched' && $ord->status !== 'completed' && $ord->status !== 'cancelled')
                                        <button type="button" 
                                                onclick='openEditOrderModal(@json($ord))'
                                                title="Edit Sales Order"
                                                class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-2xs transition transform hover:scale-105">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                    @endif
                                    <button type="button" 
                                            onclick="deleteOrder({{ $ord->id }}, '{{ addslashes($ord->order_number) }}')"
                                            title="Delete Sales Order"
                                            class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-rose-500 hover:bg-rose-600 text-white shadow-2xs transition transform hover:scale-105">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400 font-semibold italic">
                                No Records Available
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    window.toggleInlineForm = function(containerId, btn) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        const isHidden = container.classList.contains('hidden');
        if (isHidden) {
            resetSalesOrderForm();
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
        }
    };

    function handleOrderClientChange() {
        const select = document.getElementById('orderClientSelect');
        const opt = select.options[select.selectedIndex];
        const wrapper = document.getElementById('orderPlantWrapper');
        const plantSelect = document.getElementById('orderPlantSelect');

        plantSelect.innerHTML = '<option value="">Select plant...</option>';
        wrapper.classList.add('hidden');

        if (opt && opt.dataset.plants) {
            try {
                const plants = JSON.parse(opt.dataset.plants);
                if (plants.length > 0) {
                    wrapper.classList.remove('hidden');
                    plants.forEach(p => {
                        const newOpt = document.createElement('option');
                        newOpt.value = p.id;
                        newOpt.innerText = p.name;
                        plantSelect.appendChild(newOpt);
                    });
                }
            } catch(e) {}
        }
    }

    function updateRowUnitPrice(selectElem) {
        const row = selectElem.closest('.order-row');
        const opt = selectElem.options[selectElem.selectedIndex];
        const priceInput = row.querySelector('input[name="unit_prices[]"]');
        if (opt && opt.dataset.price && priceInput) {
            priceInput.value = parseFloat(opt.dataset.price).toFixed(2);
        }
    }

    function removeOrderRow(btn) {
        const container = document.getElementById('orderRowsContainer');
        if (!container) return;
        const rows = container.querySelectorAll('.order-row');
        if (rows.length > 1) {
            btn.closest('.order-row').remove();
        } else {
            const row = rows[0];
            const select = row.querySelector('select');
            if (select) select.value = '';
            row.querySelectorAll('input').forEach(inp => inp.value = '');
        }
    }
    window.removeOrderRow = removeOrderRow;

    document.getElementById('addOrderRowBtn')?.addEventListener('click', function() {
        const container = document.getElementById('orderRowsContainer');
        const originalRow = container.querySelector('.order-row');
        if (!originalRow) return;

        const clone = originalRow.cloneNode(true);
        clone.querySelector('select').value = '';
        clone.querySelector('input[name="quantities[]"]').value = '';
        clone.querySelector('input[name="unit_prices[]"]').value = '';
        container.appendChild(clone);
    });

    function resetSalesOrderForm() {
        const form = document.getElementById('salesOrderForm');
        if (!form) return;
        form.action = "{{ route('orders.store') }}";
        document.getElementById('salesOrderFormMethod').value = "POST";

        const card = document.getElementById('salesOrderFormCard');
        if (card) card.className = 'bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4 transition-all duration-300';

        const title = document.getElementById('salesOrderFormTitle');
        if (title) {
            title.className = 'text-base font-bold text-slate-800 flex items-center';
            title.innerHTML = `
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                Sales Order Booking Form
            `;
        }

        const closeBtn = document.getElementById('salesOrderCloseBtn');
        if (closeBtn) closeBtn.className = 'text-xs font-bold text-slate-400 hover:text-slate-600';

        form.querySelectorAll('input:not([type="hidden"]):not([name="quantities[]"]):not([name="unit_prices[]"]), select:not([name="product_ids[]"]), textarea').forEach(el => {
            if (!el.disabled) {
                el.className = 'w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium';
            }
        });

        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerText = 'Book Sales Order';
            submitBtn.className = 'btn-primary py-2.5 px-6 text-sm font-bold shadow-xs';
        }

        form.reset();
        handleOrderClientChange();
    }

    function openEditOrderModal(ord) {
        const container = document.getElementById('orderFormContainer');
        const card = document.getElementById('salesOrderFormCard');
        const form = document.getElementById('salesOrderForm');
        const title = document.getElementById('salesOrderFormTitle');
        const closeBtn = document.getElementById('salesOrderCloseBtn');
        const methodInput = document.getElementById('salesOrderFormMethod');
        const submitBtn = form.querySelector('button[type="submit"]');

        form.action = `/orders/${ord.id}`;
        methodInput.value = 'PUT';

        if (card) card.className = 'bg-[#FFFDF5] rounded-2xl shadow-sm border-2 border-amber-300 p-6 space-y-4 transition-all duration-300';
        if (title) {
            title.className = 'text-base font-bold text-amber-900 flex items-center';
            title.innerHTML = `
                <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                Edit Sales Order #${ord.order_number}
            `;
        }
        if (closeBtn) closeBtn.className = 'text-xs font-bold text-amber-700 hover:text-amber-900';

        form.querySelectorAll('input:not([type="hidden"]):not([name="quantities[]"]):not([name="unit_prices[]"]), select:not([name="product_ids[]"]), textarea').forEach(el => {
            if (!el.disabled) {
                el.className = 'w-full bg-white border border-amber-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700 font-medium';
            }
        });

        if (submitBtn) {
            submitBtn.innerText = 'Update Sales Order';
            submitBtn.className = 'btn-primary py-2.5 px-6 text-sm font-bold bg-[#4371D7] hover:bg-blue-700 text-white rounded-xl shadow-xs';
        }

        const orderNumDisplay = form.querySelector('[name="order_number_display"]');
        if (orderNumDisplay) orderNumDisplay.value = ord.order_number;

        const clientSelect = form.querySelector('[name="client_id"]');
        if (clientSelect) {
            clientSelect.value = ord.client_id;
            handleOrderClientChange();
        }

        if (ord.plant_id) {
            setTimeout(() => {
                const plantSelect = form.querySelector('[name="plant_id"]');
                if (plantSelect) plantSelect.value = ord.plant_id;
            }, 50);
        }

        const orderDateInput = form.querySelector('[name="order_date"]');
        if (orderDateInput && ord.order_date) {
            orderDateInput.value = ord.order_date.split('T')[0];
        }

        const deliveryDateInput = form.querySelector('[name="delivery_date"]');
        if (deliveryDateInput && ord.delivery_date) {
            deliveryDateInput.value = ord.delivery_date.split('T')[0];
        }

        const notesInput = form.querySelector('[name="notes"]');
        if (notesInput) notesInput.value = ord.notes || '';

        // Populate line items
        const rowsContainer = document.getElementById('orderRowsContainer');
        if (rowsContainer && ord.items && ord.items.length) {
            rowsContainer.innerHTML = '';
            const finishedGoods = @json($finishedGoods);

            ord.items.forEach(it => {
                const row = document.createElement('div');
                row.className = 'order-row flex items-center space-x-3 bg-amber-50/50 p-2.5 rounded-xl border border-amber-200';
                
                let options = '<option value="">Select product...</option>';
                finishedGoods.forEach(g => {
                    const sel = g.id == it.product_id ? 'selected' : '';
                    options += `<option value="${g.id}" data-price="${g.selling_price}" ${sel}>${g.product_name} (Stock: ${g.current_stock})</option>`;
                });

                row.innerHTML = `
                    <select name="product_ids[]" required class="flex-grow bg-white border border-amber-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700" onchange="updateRowUnitPrice(this)">
                        ${options}
                    </select>
                    <input type="number" name="quantities[]" value="${it.quantity}" min="1" placeholder="Qty" required class="w-20 bg-white border border-amber-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700">
                    <input type="number" name="unit_prices[]" value="${it.unit_price}" step="0.01" min="0" placeholder="Price (₹)" required class="w-28 bg-white border border-amber-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700">
                    <button type="button" onclick="removeOrderRow(this)" class="text-rose-500 hover:text-rose-600 font-bold px-2 text-sm">✕</button>
                `;
                rowsContainer.appendChild(row);
            });
        }

        container.classList.remove('hidden');
        container.scrollIntoView({ behavior: 'smooth' });
    }

    function updateOrderStatus(id, status) {
        const token = $('meta[name="csrf-token"]').attr('content') || '';
        $.ajax({
            url: `/orders/${id}/status`,
            method: 'PATCH',
            data: { status: status, _token: token },
            success: async function(res) {
                if (window.showToast) window.showToast('success', res.message || 'Status updated!');
                if (window.loadPage) {
                    await window.loadPage(window.location.href);
                } else {
                    window.location.reload();
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to update order status.';
                if (window.showToast) {
                    window.showToast('error', msg);
                } else {
                    alert(msg);
                }
            }
        });
    }

    function applyStatusSelectColor(selectEl) {
        const val = selectEl.value;
        selectEl.classList.remove(
            'text-amber-600', 'border-amber-300',
            'text-blue-600', 'border-blue-300',
            'text-indigo-600', 'border-indigo-300',
            'text-emerald-600', 'border-emerald-300',
            'text-rose-600', 'border-rose-300'
        );
        selectEl.style.backgroundColor = '#ffffff';

        if (val === 'pending') {
            selectEl.classList.add('text-amber-600', 'border-amber-300');
        } else if (val === 'in_production') {
            selectEl.classList.add('text-blue-600', 'border-blue-300');
        } else if (val === 'ready_for_dispatch') {
            selectEl.classList.add('text-indigo-600', 'border-indigo-300');
        } else if (val === 'dispatched' || val === 'completed') {
            selectEl.classList.add('text-emerald-600', 'border-emerald-300');
        } else if (val === 'cancelled') {
            selectEl.classList.add('text-rose-600', 'border-rose-300');
        }
    }

    function convertOrderToChallan(id, orderNumber) {
        if (!confirm(`Generate Delivery Challan from Sales Order '${orderNumber}'?`)) return;
        const token = $('meta[name="csrf-token"]').attr('content') || '';
        $.ajax({
            url: `/orders/${id}/convert-to-challan`,
            method: 'POST',
            data: { _token: token },
            success: async function(res) {
                if (window.showToast) window.showToast('success', res.message);
                await window.loadPage(window.location.href);
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Failed to convert order to Delivery Challan.');
            }
        });
    }

    function deleteOrder(id, orderNumber) {
        if (!confirm(`Are you sure you want to delete Sales Order '${orderNumber}'?`)) return;
        const token = $('meta[name="csrf-token"]').attr('content') || '';
        $.ajax({
            url: `/orders/${id}`,
            method: 'DELETE',
            data: { _token: token },
            success: async function(res) {
                if (window.showToast) window.showToast('success', res.message);
                await window.loadPage(window.location.href);
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Failed to delete order.');
            }
        });
    }
</script>
@endsection
