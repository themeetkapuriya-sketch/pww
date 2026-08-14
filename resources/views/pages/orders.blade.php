@extends('layouts.app')

@section('title', 'Sales Orders')

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
                        'client-id' => $client->id
                    ]
                ];
            }
        }
    }

    $trackStockEnabled = (\App\Models\Setting::get('track_stock', 'true') === 'true');
    $productOptions = [];
    foreach ($finishedGoods as $g) {
        $kgPrice = $g->price_per_kg ?? (($g->unit_weight_kg ?? 0) > 0 ? round($g->selling_price / $g->unit_weight_kg, 2) : 0);
        $prodLabel = $g->product_name . (($g->unit_weight_kg ?? 0) > 0 ? ' (' . number_format($g->unit_weight_kg, 3) . ' Kg)' : '') . ($trackStockEnabled ? ' (Stock: ' . number_format($g->current_stock) . ')' : '');
        $productOptions[] = [
            'value' => $g->id,
            'label' => $prodLabel,
            'search' => strtolower($g->product_name . ' ' . ($g->item_code ?? '')),
            'data' => [
                'price' => $g->selling_price,
                'price-pcs' => $g->selling_price,
                'price-kg' => $kgPrice,
                'weight' => $g->unit_weight_kg ?? 0.000,
                'uom' => $g->uom ?? 'piece'
            ]
        ];
    }

    $orderComboboxHtml = View::make('components.combobox', [
        'name' => 'product_ids[]',
        'placeholder' => 'Select product...',
        'options' => $productOptions,
        'required' => true,
    ])->render();

    $salesOrdersJsonMap = [];
    if (isset($orders) && $orders->isNotEmpty()) {
        foreach ($orders as $ordItem) {
            $salesOrdersJsonMap[$ordItem->id] = new \App\Http\Resources\SalesOrderResource($ordItem);
        }
    }
@endphp
<div class="space-y-6">
    <x-page-header title="Sales Orders" 
                   subtitle="Book customer purchase orders, manage production pipelines, and convert to Delivery Challans."
                   action-text="Book New Sales Order" 
                   action-id="toggleFormBtn"
                   action-on-click="toggleInlineForm('orderFormContainer', this)" />

    <!-- Smooth Expandable Order Booking Form -->
    <div id="orderFormContainer" class="hidden transition-all duration-300 ease-in-out">
        <div id="salesOrderFormCard" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4 transition-all duration-300">
            <div class="flex items-center justify-between pb-3 mb-2 border-b border-slate-100/60">
                <h3 id="salesOrderFormTitle" class="text-base font-bold text-slate-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    Sales Order Booking Form
                </h3>
                <button type="button" id="salesOrderCloseBtn" onclick="toggleInlineForm('orderFormContainer', document.getElementById('toggleFormBtn'))" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition cursor-pointer">&times; Close</button>
            </div>

            <form id="salesOrderForm" action="{{ route('orders.store') }}" method="POST" class="ajax-form space-y-4" data-redirect="/orders">
                @csrf
                <input type="hidden" name="_method" id="salesOrderFormMethod" value="POST">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Order Number</label>
                        <input type="text" name="order_number_display" value="{{ \App\Models\SalesOrder::generateNextOrderNumber() }}" disabled
                               class="w-full bg-slate-100 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none text-slate-500 font-mono">
                    </div>

                    <div id="directOrderClientContainer" class="md:col-span-5 relative">
                        <input type="hidden" name="client_id" id="orderClientSelect">
                        <x-combobox name="plant_id"
                                    id="orderPlantSelect"
                                    label="Select Client & Plant"
                                    placeholder="Search company, plant, or state..."
                                    :options="$clientPlantOptions"
                                    required />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Order Date</label>
                        <input type="date" name="order_date" value="{{ date('Y-m-d') }}" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                    </div>

                    <div class="md:col-span-2">
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
                        <div class="order-row flex items-center space-x-2 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                            <div class="flex-grow">
                                <x-combobox name="product_ids[]"
                                            placeholder="Select product..."
                                            :options="$productOptions"
                                            required />
                            </div>
                            <select name="billing_uoms[]" class="billing-uom-select w-20 shrink-0 bg-white border border-slate-200 rounded-xl py-2 px-2 text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="updateRowUnitPrice(this)">
                                <option value="Pcs">Pcs</option>
                                <option value="Kg">Kg</option>
                            </select>
                            <input type="number" name="quantities[]" step="any" min="0.01" placeholder="Qty" required class="w-20 bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 font-bold">
                            <input type="number" name="unit_prices[]" step="0.01" min="0" placeholder="Price (₹)" required class="w-28 bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 font-bold">
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
                    <button type="button" onclick="toggleInlineForm('orderFormContainer', document.querySelector('button[onclick*=\'orderFormContainer\']'))" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold py-2.5 px-5 rounded-xl transition cursor-pointer">Cancel</button>
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
                <thead class="bg-[#EDF4FA] text-black divide-x divide-slate-200">
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
                        <tr class="hover:bg-slate-50/60 transition" id="order-row-{{ $ord->id }}">
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
                                            • {{ $it->product->product_name ?? $it->finishedGood->product_name ?? 'Product' }}: <strong class="text-slate-900">{{ number_format($it->quantity) }} {{ strtolower($it->billing_uom ?? ($it->product->uom ?? 'piece')) }}</strong> @ ₹{{ number_format($it->unit_price, 2) }}
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
                                    $trackStockOn = (\App\Models\Setting::get('track_stock', 'true') === 'true');
                                @endphp

                                @if ($ord->status === 'dispatched' || $ord->status === 'completed')
                                    <div class="inline-flex flex-col items-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs">
                                            ✓ DISPATCHED
                                        </span>
                                        @if($trackStockOn)
                                            <span class="text-[9px] text-emerald-600 font-semibold mt-0.5">Stock Deducted</span>
                                        @endif
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
                                    @if (!$trackStockOn || $hasStock)
                                        <div class="inline-flex flex-col items-center space-y-1">
                                            <button type="button" 
                                                    onclick="updateOrderStatus({{ $ord->id }}, 'ready_for_dispatch')"
                                                    title="{{ $trackStockOn ? 'Sufficient stock available! Click to mark Ready for Dispatch' : 'Click to mark Ready for Dispatch' }}"
                                                    class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-bold rounded-lg shadow-2xs transition flex items-center space-x-1 cursor-pointer {{ $trackStockOn ? 'animate-pulse' : '' }}">
                                                <span>📦 Mark Ready for Dispatch</span>
                                            </button>
                                            <span class="text-[9.5px] text-blue-600 font-semibold">{{ $trackStockOn ? 'Stock Ready' : 'Stage 2: In Production' }}</span>
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
                                           title="{{ $trackStockOn ? 'Stock is ready! Click to generate Tax Invoice & dispatch order' : 'Click to generate Tax Invoice & dispatch order' }}"
                                           class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-[10px] font-bold rounded-lg shadow-2xs transition flex items-center space-x-1">
                                            <span>🚀 Gen Invoice & Dispatch</span>
                                        </a>
                                        <span class="text-[9.5px] text-indigo-600 font-semibold">Stage 3: Ready for Dispatch</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <button type="button" 
                                            onclick="openOrderInfoModal({{ $ord->id }})"
                                            title="Order 360° Info & MRP Hub"
                                            class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition transform hover:scale-105 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </button>

                                    @if($ord->status !== 'dispatched' && $ord->status !== 'completed' && $ord->status !== 'cancelled')
                                        <button type="button" 
                                                onclick="openEditOrderModal({{ $ord->id }})"
                                                title="Edit Sales Order"
                                                class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-2xs transition transform hover:scale-105 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                    @endif
                                    <button type="button" 
                                            onclick="deleteOrder({{ $ord->id }}, '{{ addslashes($ord->order_number) }}')"
                                            title="Delete Sales Order"
                                            class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-rose-500 hover:bg-rose-600 text-white shadow-2xs transition transform hover:scale-105 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-empty-state title="No Sales Orders Found" subtitle="There are no sales orders matching this status filter." colspan="8" />
                    @endforelse
                </tbody>
            </table>
        </div>
<!-- 360° Order Info & MRP Hub Modal -->
<div id="orderInfoModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative w-full max-w-4xl bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all duration-300">
        
        <!-- Modal Header (Light Mode) -->
        <div class="flex items-center justify-between px-6 py-4 bg-white border-b border-slate-200 text-slate-800">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm">
                    ℹ️
                </div>
                <div>
                    <h3 id="modalOrderTitle" class="text-base font-extrabold text-slate-800 tracking-wide">Sales Order 360° Control Hub</h3>
                    <p id="modalOrderSubtitle" class="text-xs text-slate-500 font-medium">Order Info, Finished Goods Stock Allocation & MRP Raw Material Matrix</p>
                </div>
            </div>
            <button type="button" onclick="closeOrderInfoModal()" data-modal-close="orderInfoModal" class="modal-close-btn text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg p-1.5 transition text-xl font-bold cursor-pointer" title="Close modal">&times;</button>
        </div>

        <!-- Modal Body Container -->
        <div id="modalOrderContent" class="p-6 space-y-6 max-h-[75vh] overflow-y-auto">
            <!-- Loading State -->
            <div id="modalOrderLoading" class="py-12 text-center text-slate-500 space-y-3">
                <svg class="w-8 h-8 mx-auto animate-spin text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <p class="text-sm font-bold">Calculating MRP Raw Material Requirements & Stock Allocations...</p>
            </div>

            <!-- Dynamic Loaded Content Container -->
            <div id="modalOrderData" class="hidden space-y-6">
                
                <!-- Order Header Quick Stats Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-200">
                    <div>
                        <span class="block text-[10px] font-black uppercase text-slate-400">Order Number</span>
                        <span id="mOrderNum" class="text-sm font-extrabold text-blue-600 font-mono">PWW-ORD-000</span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-black uppercase text-slate-400">Customer PO #</span>
                        <span id="mPoNum" class="text-sm font-bold text-slate-800 font-mono">N/A</span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-black uppercase text-slate-400">Client Company</span>
                        <span id="mClientName" class="text-sm font-bold text-slate-900 truncate block">Client Name</span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-black uppercase text-slate-400">Target Delivery Date</span>
                        <span id="mDeliveryDate" class="text-sm font-bold text-amber-600 font-mono">Date</span>
                    </div>
                </div>

                <!-- Finished Goods Stock Allocation Section -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-700 flex items-center">
                            <span class="w-2.5 h-2.5 bg-blue-600 rounded-full mr-2"></span>
                            Finished Goods Readiness & Stock Allocation
                        </h4>
                        <span id="mFgOverallBadge" class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-blue-100 text-blue-800 border border-blue-200">Status</span>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-[#EDF4FA] dark:bg-slate-900 text-slate-700 dark:text-slate-200 font-bold uppercase">
                                <tr>
                                    <th class="p-2.5">Product Name</th>
                                    <th class="p-2.5 text-center">Ordered Qty</th>
                                    <th class="p-2.5 text-center">Live Warehouse Stock</th>
                                    <th class="p-2.5 text-center">Stock Readiness</th>
                                </tr>
                            </thead>
                            <tbody id="mFgTableBody" class="divide-y divide-slate-100">
                                <!-- Dynamic JS Injection -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Raw Material MRP Matrix Section -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-700 flex items-center">
                            <span class="w-2.5 h-2.5 bg-amber-500 rounded-full mr-2"></span>
                            Material Requirement Planning (MRP) Raw Material Matrix
                        </h4>
                        <span class="text-[10px] text-slate-400 font-medium">* Formula: (Ordered Qty × BOM Qty) + Waste %</span>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-[#EDF4FA] dark:bg-slate-900 text-slate-700 dark:text-slate-200 font-bold uppercase">
                                <tr>
                                    <th class="p-2.5">Raw Material Name</th>
                                    <th class="p-2.5 text-center">Calculated Requirement</th>
                                    <th class="p-2.5 text-center">Current Stock</th>
                                    <th class="p-2.5 text-center">Deficit / Readiness</th>
                                    <th class="p-2.5 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="mMrpTableBody" class="divide-y divide-slate-100">
                                <!-- Dynamic JS Injection -->
                            </tbody>
                        </table>
                    </div>

                    <div id="mMissingBomWarning" class="hidden p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-800 font-medium">
                        ⚠️ Warning: Some products in this order do not have BOM rules defined. Click <a href="{{ route('bom') }}" class="underline font-bold text-amber-900">BOM Management</a> to configure formulas.
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal Footer Action Bar -->
        <div class="flex flex-wrap items-center justify-end px-6 py-4 bg-slate-50 border-t border-slate-200 gap-2">
            <div class="flex items-center space-x-2">
                <a href="{{ route('production') }}" class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-2xs transition">
                    ⚙️ Log Production
                </a>
                <div id="mOrderStatusBadge" class="px-3.5 py-2 text-xs font-bold rounded-xl border shadow-2xs transition"></div>
                <button type="button" onclick="closeOrderInfoModal()" data-modal-close="orderInfoModal" class="modal-close-btn px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer">
                    Close
                </button>
            </div>
        </div>

    </div>
</div>

<script>
    window.salesOrdersDataMap = @json($salesOrdersJsonMap);

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

    // Direct Type-Ahead Live Search Engine for Sales Orders
    var directOrderClientActiveIndex = -1;

    document.addEventListener('change', function(e) {
        if (e.target && e.target.name === 'plant_id') {
            const wrapper = e.target.closest('.combobox-wrapper');
            if (wrapper) {
                const selectedOpt = wrapper.querySelector(`.combobox-option[data-value="${e.target.value}"]`);
                if (selectedOpt && selectedOpt.dataset.clientId) {
                    const clientInput = document.getElementById('orderClientSelect');
                    if (clientInput) clientInput.value = selectedOpt.dataset.clientId;
                }
            }
        }
        if (e.target && e.target.name === 'product_ids[]') {
            updateRowUnitPrice(e.target);
        }
    });

    function updateRowUnitPrice(elem) {
        const row = elem.closest('.order-row');
        if (!row) return;
        const hiddenProd = row.querySelector('.combobox-hidden-input') || row.querySelector('select[name="product_ids[]"]');
        const uomSelect = row.querySelector('.billing-uom-select');
        const priceInput = row.querySelector('input[name="unit_prices[]"]');

        if (!hiddenProd || !priceInput) return;
        let pricePcs = 0, priceKg = 0;
        if (hiddenProd.tagName === 'SELECT') {
            const opt = hiddenProd.options[hiddenProd.selectedIndex];
            if (opt) {
                pricePcs = parseFloat(opt.dataset.pricePcs || opt.dataset.price || 0);
                priceKg = parseFloat(opt.dataset.priceKg || 0);
            }
        } else {
            const wrapper = hiddenProd.closest('.combobox-wrapper');
            const selectedOpt = wrapper ? wrapper.querySelector(`.combobox-option[data-value="${hiddenProd.value}"]`) : null;
            if (selectedOpt) {
                pricePcs = parseFloat(selectedOpt.dataset.pricePcs || selectedOpt.dataset.price || 0);
                priceKg = parseFloat(selectedOpt.dataset.priceKg || 0);
            }
        }

        const uomVal = uomSelect ? uomSelect.value : 'Pcs';
        if (uomVal === 'Kg' && priceKg > 0) {
            priceInput.value = priceKg.toFixed(2);
        } else if (pricePcs > 0) {
            priceInput.value = pricePcs.toFixed(2);
        } else if (!hiddenProd.value) {
            priceInput.value = '';
        }
    }

    function removeOrderRow(btn) {
        const container = document.getElementById('orderRowsContainer');
        if (!container) return;
        const rows = container.querySelectorAll('.order-row');
        if (rows.length <= 1) {
            if (window.showToast) window.showToast('warning', 'At least one product row is required.');
            return;
        }
        btn.closest('.order-row').remove();
    }
    window.removeOrderRow = removeOrderRow;

    var _addOrderRowPending = false;
    document.getElementById('addOrderRowBtn')?.addEventListener('click', function() {
        if (_addOrderRowPending) return;
        _addOrderRowPending = true;
        setTimeout(function() { _addOrderRowPending = false; }, 300);

        const container = document.getElementById('orderRowsContainer');
        const originalRow = container.querySelector('.order-row');
        if (!originalRow) return;

        const clone = originalRow.cloneNode(true);
        const wrapper = clone.querySelector('.combobox-wrapper');
        if (wrapper) {
            delete wrapper.dataset.comboboxInitialized;
            const hidden = wrapper.querySelector('.combobox-hidden-input');
            const search = wrapper.querySelector('.combobox-search-input');
            const clearBtn = wrapper.querySelector('.combobox-clear-btn');
            if (hidden) hidden.value = '';
            if (search) search.value = '';
            if (clearBtn) clearBtn.classList.add('hidden');
        }
        clone.querySelector('input[name="quantities[]"]').value = '';
        clone.querySelector('input[name="unit_prices[]"]').value = '';
        container.appendChild(clone);

        if (window.ERPComboboxManager) {
            window.ERPComboboxManager.init(clone);
        }
    });

    // Cache default clean row template for resets
    window.rawOrderComboboxTpl = @json($orderComboboxHtml);
    window.defaultOrderRowHtml = document.getElementById('orderRowsContainer')?.innerHTML || '';

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

        form.querySelectorAll('input:not([type="hidden"]):not([name="quantities[]"]):not([name="unit_prices[]"]):not(.combobox-search-input), select:not([name="product_ids[]"]):not([name="billing_uoms[]"]):not(.billing-uom-select):not(#orderClientSelect):not(#orderPlantSelect), textarea').forEach(el => {
            if (!el.disabled) {
                el.className = 'w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium';
            }
        });

        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerText = 'Book Sales Order';
            submitBtn.className = 'btn-primary py-2.5 px-6 text-sm font-bold shadow-xs';
        }

        // Restore clean default single item row
        const rowsContainer = document.getElementById('orderRowsContainer');
        if (rowsContainer && window.defaultOrderRowHtml) {
            rowsContainer.innerHTML = window.defaultOrderRowHtml;
        }

        form.reset();
        const plantInp = document.getElementById('orderPlantSelect_hidden') || document.getElementById('orderPlantSelect');
        if (plantInp) {
            plantInp.value = '';
            const wrapper = plantInp.closest('.combobox-wrapper');
            if (wrapper && window.ERPComboboxManager) {
                window.ERPComboboxManager.syncDisplay(wrapper);
            }
        }
    }

    function openEditOrderModal(btnOrOrder) {
        let ord = null;
        if (typeof btnOrOrder === 'number' || typeof btnOrOrder === 'string') {
            ord = window.salesOrdersDataMap ? window.salesOrdersDataMap[btnOrOrder] : null;
        } else if (btnOrOrder && btnOrOrder.dataset && btnOrOrder.dataset.order) {
            try { ord = JSON.parse(btnOrOrder.dataset.order); } catch(e) { console.error("Invalid order dataset", e); }
        } else {
            ord = btnOrOrder;
        }
        if (!ord) {
            console.error("Order data not found for edit", btnOrOrder);
            return;
        }

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

        form.querySelectorAll('input:not([type="hidden"]):not([name="quantities[]"]):not([name="unit_prices[]"]):not(.combobox-search-input), select:not([name="product_ids[]"]):not([name="billing_uoms[]"]):not(.billing-uom-select):not(#orderClientSelect):not(#orderPlantSelect), textarea').forEach(el => {
            if (!el.disabled) {
                el.className = 'w-full bg-white border border-amber-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700 font-medium';
            }
        });

        if (submitBtn) {
            submitBtn.innerText = 'Update Sales Order';
            submitBtn.className = 'btn-primary py-2.5 px-6 text-sm font-bold bg-[#2563EB] hover:bg-blue-700 text-white rounded-xl shadow-xs';
        }

        const orderNumDisplay = form.querySelector('[name="order_number_display"]');
        if (orderNumDisplay) orderNumDisplay.value = ord.order_number;

        const clientSelect = form.querySelector('[name="client_id"]');
        if (clientSelect) clientSelect.value = ord.client_id;

        const plantInp = document.getElementById('orderPlantSelect_hidden') || document.getElementById('orderPlantSelect');
        if (plantInp) {
            plantInp.value = ord.plant_id || '';
            const wrapper = plantInp.closest('.combobox-wrapper');
            if (wrapper && window.ERPComboboxManager) {
                window.ERPComboboxManager.syncDisplay(wrapper);
            }
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

            ord.items.forEach(it => {
                const row = document.createElement('div');
                row.className = 'order-row flex items-center space-x-2 bg-amber-50/50 p-2.5 rounded-xl border border-amber-200';
                
                row.innerHTML = `
                    <div class="flex-grow">
                        ${window.rawOrderComboboxTpl}
                    </div>
                    <select name="billing_uoms[]" class="billing-uom-select w-20 shrink-0 bg-white border border-amber-200 rounded-xl py-2 px-2 text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-500" onchange="updateRowUnitPrice(this)">
                        <option value="Pcs">Pcs</option>
                        <option value="Kg">Kg</option>
                    </select>
                    <input type="number" name="quantities[]" value="${parseFloat(it.quantity)}" step="any" min="0.01" placeholder="Qty" required class="w-20 bg-white border border-amber-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-900 font-bold">
                    <input type="number" name="unit_prices[]" value="${parseFloat(it.unit_price).toFixed(2)}" step="0.01" min="0" placeholder="Price (₹)" required class="w-28 bg-white border border-amber-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-900 font-bold">
                    <button type="button" onclick="removeOrderRow(this)" class="text-rose-500 hover:text-rose-600 font-bold px-2 text-sm">✕</button>
                `;
                if (row.querySelector('select[name="billing_uoms[]"]')) {
                    row.querySelector('select[name="billing_uoms[]"]').value = it.billing_uom || 'Pcs';
                }

                const prodInp = row.querySelector('.combobox-hidden-input');
                if (prodInp) {
                    prodInp.value = it.product_id;
                }

                rowsContainer.appendChild(row);

                const wrapper = row.querySelector('.combobox-wrapper');
                if (wrapper && window.ERPComboboxManager) {
                    window.ERPComboboxManager.syncDisplay(wrapper);
                }
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
        window.confirmDelete(
            'Delete Sales Order?',
            `Are you sure you want to delete Sales Order '${orderNumber}'? This action cannot be undone.`,
            function() {
                const token = $('meta[name="csrf-token"]').attr('content') || '';
                $.ajax({
                    url: `/orders/${id}`,
                    method: 'DELETE',
                    data: { _token: token },
                    success: function(res) {
                        if (window.showToast) window.showToast('success', res.message);
                        $(`#order-row-${id}`).fadeOut(300, function() { $(this).remove(); });
                        if (window.clearPageCache) window.clearPageCache();
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Failed to delete order.');
                    }
                });
            }
        );
    }

    window.updateRowUnitPrice = updateRowUnitPrice;
    window.openEditOrderModal = openEditOrderModal;
    window.updateOrderStatus = updateOrderStatus;
    window.deleteOrder = deleteOrder;

    window.closeOrderInfoModal = function() {
        const modal = document.getElementById('orderInfoModal');
        if (modal) modal.classList.add('hidden');
    };
    function closeOrderInfoModal() {
        window.closeOrderInfoModal();
    }

    window.orderCache = window.orderCache || new Map();

    function openOrderInfoModal(orderId) {
        const modal = document.getElementById('orderInfoModal');
        const loading = document.getElementById('modalOrderLoading');
        const dataBox = document.getElementById('modalOrderData');

        if (!modal) return;
        modal.classList.remove('hidden');

        // Instant 0ms render from cache if available
        if (window.orderCache.has(orderId)) {
            loading.classList.add('hidden');
            renderOrderInfoModalData(window.orderCache.get(orderId));
            return;
        }

        loading.classList.remove('hidden');
        dataBox.classList.add('hidden');

        $.ajax({
            url: `/orders/${orderId}/details`,
            method: 'GET',
            success: function(res) {
                const data = res.data || res;
                window.orderCache.set(orderId, data);
                renderOrderInfoModalData(data);
            },
            error: function(xhr) {
                alert('Failed to load order details.');
                closeOrderInfoModal();
            }
        });
    }

    // Background hover & touch pre-fetch for instant 0ms open
    $(document).on('mouseenter touchstart', 'button[onclick*="openOrderInfoModal"]', function() {
        try {
            const onclickAttr = $(this).attr('onclick') || '';
            const match = onclickAttr.match(/openOrderInfoModal\((\d+)\)/);
            if (match && match[1]) {
                const id = parseInt(match[1]);
                if (!window.orderCache.has(id)) {
                    fetch(`/orders/${id}/details`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(r => r.ok ? r.json() : null)
                        .then(res => {
                            if (res) {
                                const data = res.data || res;
                                window.orderCache.set(id, data);
                            }
                        })
                        .catch(() => {});
                }
            }
        } catch(e) {}
    });

    function renderOrderInfoModalData(data) {
        document.getElementById('modalOrderLoading').classList.add('hidden');
        document.getElementById('modalOrderData').classList.remove('hidden');

        // Header info
        document.getElementById('mOrderNum').textContent = data.order_number || 'N/A';
        document.getElementById('mPoNum').textContent = data.po_number || 'N/A';
        document.getElementById('mClientName').textContent = (data.client_name || 'N/A') + (data.plant_name ? ' (' + data.plant_name + ')' : '');
        document.getElementById('mDeliveryDate').textContent = data.delivery_date || data.order_date || 'N/A';

        // Status Badge
        const statusBadge = document.getElementById('mOrderStatusBadge');
        if (statusBadge) {
            statusBadge.className = 'px-3.5 py-2 text-xs font-bold rounded-xl border shadow-2xs transition';
            const status = data.status || 'pending';
            if (status === 'pending') {
                statusBadge.classList.add('bg-amber-50', 'text-amber-700', 'border-amber-200');
                statusBadge.innerHTML = '⏳ Pending';
            } else if (status === 'in_production') {
                statusBadge.classList.add('bg-blue-50', 'text-blue-700', 'border-blue-200');
                statusBadge.innerHTML = '⚙️ In Production';
            } else if (status === 'ready_for_dispatch') {
                statusBadge.classList.add('bg-indigo-50', 'text-indigo-700', 'border-indigo-200');
                statusBadge.innerHTML = '📦 Ready for Dispatch';
            } else if (status === 'dispatched' || status === 'completed') {
                statusBadge.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-200');
                statusBadge.innerHTML = '✓ Dispatched';
            } else if (status === 'cancelled') {
                statusBadge.classList.add('bg-rose-50', 'text-rose-700', 'border-rose-200');
                statusBadge.innerHTML = '✕ Cancelled';
            } else {
                statusBadge.classList.add('bg-slate-50', 'text-slate-700', 'border-slate-200');
                statusBadge.innerHTML = data.formatted_status || 'N/A';
            }
        }

        // Finished Goods Table
        const fg = data.finished_goods_status || {};
        const fgBadge = document.getElementById('mFgOverallBadge');
        if (fg.is_fully_stocked) {
            fgBadge.textContent = '✓ Ready to Dispatch';
            fgBadge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800 border border-emerald-300';
        } else {
            fgBadge.textContent = '⚡ Production Needed';
            fgBadge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-amber-100 text-amber-800 border border-amber-300';
        }

        const fgTbody = document.getElementById('mFgTableBody');
        fgTbody.innerHTML = '';
        (fg.items || []).forEach(item => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50';
            const statusHtml = item.is_sufficient 
                ? `<span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">✓ Fully Stocked</span>`
                : `<span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold text-[10px]">Short by ${item.missing_quantity} ${item.billing_uom}</span>`;
            
            tr.innerHTML = `
                <td class="p-2.5 font-bold text-slate-800">${item.product_name}</td>
                <td class="p-2.5 text-center font-bold text-slate-900">${item.ordered_quantity} ${item.billing_uom}</td>
                <td class="p-2.5 text-center text-slate-700 font-semibold">${item.available_stock}</td>
                <td class="p-2.5 text-center">${statusHtml}</td>
            `;
            fgTbody.appendChild(tr);
        });

        // MRP Raw Material Table
        const mrp = data.raw_material_mrp || {};
        const mrpTbody = document.getElementById('mMrpTableBody');
        mrpTbody.innerHTML = '';

        if (!mrp.mrp_list || mrp.mrp_list.length === 0) {
            mrpTbody.innerHTML = `<tr><td colspan="5" class="p-4 text-center text-slate-400 italic">No raw material calculations available.</td></tr>`;
        } else {
            mrp.mrp_list.forEach(item => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50';
                
                const readinessHtml = item.is_sufficient
                    ? `<span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">✓ Stock Sufficient</span>`
                    : `<span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-bold text-[10px]">Short by ${item.shortage} ${item.unit}</span>`;

                const actionHtml = item.is_sufficient
                    ? `<span class="text-emerald-600 font-bold text-[10px]">Ready</span>`
                    : `<a href="/purchases?raw_material_id=${item.raw_material_id}&quantity=${item.shortage}" class="inline-flex items-center px-2 py-1 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded text-[10px] shadow-2xs transition">+ Order Missing Stock</a>`;

                tr.innerHTML = `
                    <td class="p-2.5 font-bold text-slate-900">${item.material_name}</td>
                    <td class="p-2.5 text-center font-bold text-blue-600 font-mono">${item.total_required} ${item.unit}</td>
                    <td class="p-2.5 text-center text-slate-700 font-semibold">${item.current_stock} ${item.unit}</td>
                    <td class="p-2.5 text-center">${readinessHtml}</td>
                    <td class="p-2.5 text-center">${actionHtml}</td>
                `;
                mrpTbody.appendChild(tr);
            });
        }

        // Missing BOM Warning
        const warningBox = document.getElementById('mMissingBomWarning');
        if (mrp.missing_boms && mrp.missing_boms.length > 0) {
            warningBox.classList.remove('hidden');
        } else {
            warningBox.classList.add('hidden');
        }
    }

    window.openOrderInfoModal = openOrderInfoModal;
    window.closeOrderInfoModal = closeOrderInfoModal;
</script>
@endsection
