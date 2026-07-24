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
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
            <h3 class="text-base font-bold text-slate-800 mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                Sales Order Booking Form
            </h3>

            <form action="{{ route('orders.store') }}" method="POST" class="ajax-form space-y-4">
                @csrf
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
                            <input type="number" name="quantities[]" min="1" placeholder="Qty" required class="w-28 bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                            <input type="number" name="unit_prices[]" step="0.01" min="0" placeholder="Price (₹)" required class="w-36 bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
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

        <div class="overflow-x-auto">
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
                            <td class="px-4 py-3 text-center">
                                <select onchange="updateOrderStatus({{ $ord->id }}, this.value)" 
                                        class="text-[10px] font-bold uppercase rounded-full px-2.5 py-1 focus:outline-none border border-slate-200 shadow-2xs 
                                        {{ $ord->status === 'pending' ? 'bg-amber-100 text-amber-800 border-amber-300' : '' }}
                                        {{ $ord->status === 'in_production' ? 'bg-blue-100 text-blue-800 border-blue-300' : '' }}
                                        {{ $ord->status === 'ready_for_dispatch' ? 'bg-indigo-100 text-indigo-800 border-indigo-300' : '' }}
                                        {{ $ord->status === 'dispatched' || $ord->status === 'completed' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : '' }}
                                        {{ $ord->status === 'cancelled' ? 'bg-rose-100 text-rose-800 border-rose-300' : '' }}">
                                    <option value="pending" {{ $ord->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="in_production" {{ $ord->status === 'in_production' ? 'selected' : '' }}>In Production</option>
                                    <option value="ready_for_dispatch" {{ $ord->status === 'ready_for_dispatch' ? 'selected' : '' }}>Ready For Dispatch</option>
                                    <option value="dispatched" {{ $ord->status === 'dispatched' || $ord->status === 'completed' ? 'selected' : '' }}>Dispatched</option>
                                    <option value="cancelled" {{ $ord->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    @if($ord->status !== 'dispatched' && $ord->status !== 'completed' && $ord->status !== 'cancelled')
                                        <a href="{{ route('invoices', ['order_id' => $ord->id]) }}"
                                           title="Generate Tax Invoice"
                                           class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-[11px] font-bold shadow-xs transition flex items-center space-x-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            <span>Gen Invoice</span>
                                        </a>
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

    function removeOrderRow(btn) {
        const container = document.getElementById('orderRowsContainer');
        if (container.querySelectorAll('.order-row').length > 1) {
            btn.closest('.order-row').remove();
        } else {
            alert('At least one product item line is required for an order.');
        }
    }

    function updateOrderStatus(id, status) {
        const token = $('meta[name="csrf-token"]').attr('content') || '';
        $.ajax({
            url: `/orders/${id}/status`,
            method: 'PATCH',
            data: { status: status, _token: token },
            success: function(res) {
                if (window.showToast) window.showToast('success', res.message);
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Failed to update order status.');
            }
        });
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
