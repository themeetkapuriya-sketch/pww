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
            action-text="Book New Sales Order" action-id="toggleFormBtn"
            action-on-click="toggleInlineForm('orderFormContainer', this)" />

        <!-- Smooth Expandable Order Booking Form -->
        <div id="orderFormContainer" class="hidden transition-all duration-300 ease-in-out">
            <div id="salesOrderFormCard"
                class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4 transition-all duration-300">
                <div class="flex items-center justify-between pb-3 mb-2 border-b border-slate-100/60">
                    <h3 id="salesOrderFormTitle" class="text-base font-bold text-slate-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        Sales Order Booking Form
                    </h3>
                    <button type="button" id="salesOrderCloseBtn"
                        onclick="toggleInlineForm('orderFormContainer', document.getElementById('toggleFormBtn'))"
                        class="text-xs font-bold text-slate-400 hover:text-slate-600 transition cursor-pointer">&times;
                        Close</button>
                </div>

                <form id="salesOrderForm" action="{{ route('orders.store') }}" method="POST" class="ajax-form space-y-4"
                    data-redirect="/orders">
                    @csrf
                    <input type="hidden" name="_method" id="salesOrderFormMethod" value="POST">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Order Number</label>
                            <input type="text" name="order_number_display"
                                value="{{ \App\Models\SalesOrder::generateNextOrderNumber() }}" disabled
                                class="w-full bg-slate-100 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none text-slate-500 font-mono">
                        </div>

                        <div id="directOrderClientContainer" class="md:col-span-5 relative">
                            <input type="hidden" name="client_id" id="orderClientSelect">
                            <x-combobox name="plant_id" id="orderPlantSelect" label="Select Client & Plant"
                                placeholder="Search company, plant, or state..." :options="$clientPlantOptions" required />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Order Date</label>
                            <input type="date" name="order_date" value="{{ date('Y-m-d') }}" required
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Target Delivery
                                Date</label>
                            <input type="date" name="delivery_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                        </div>
                    </div>

                    <!-- Product Line Items -->
                    <div class="border-t border-slate-200 pt-4 space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-slate-700 uppercase">Ordered Products</label>
                            <button type="button" id="addOrderRowBtn"
                                class="text-blue-600 hover:text-blue-700 text-xs font-bold flex items-center">
                                + Add Item
                            </button>
                        </div>

                        <div id="orderRowsContainer" class="space-y-2">
                            <div
                                class="order-row flex flex-wrap sm:flex-nowrap items-center gap-2 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                                <div class="w-full sm:w-auto flex-grow min-w-0 sm:min-w-[200px]">
                                    <x-combobox name="product_ids[]" placeholder="Select product..."
                                        :options="$productOptions" required />
                                </div>
                                <div class="grid grid-cols-12 gap-1.5 w-full sm:flex sm:items-center sm:w-auto shrink-0">
                                    <select name="billing_uoms[]"
                                        class="billing-uom-select col-span-3 sm:w-20 bg-white border border-slate-200 rounded-xl py-2 px-1.5 text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-center"
                                        onchange="updateRowUnitPrice(this)">
                                        <option value="Pcs">Pcs</option>
                                        <option value="Kg">Kg</option>
                                    </select>
                                    <input type="number" name="quantities[]" step="any" min="0.01" placeholder="Qty" required
                                        class="col-span-4 sm:w-20 min-w-0 bg-white border border-slate-200 rounded-xl py-2 px-2 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 font-bold">
                                    <input type="number" name="unit_prices[]" step="0.01" min="0" placeholder="Price (₹)"
                                        required
                                        class="col-span-4 sm:w-28 min-w-0 bg-white border border-slate-200 rounded-xl py-2 px-2 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-900 font-bold">
                                    <button type="button" onclick="removeOrderRow(this)"
                                        class="col-span-1 sm:w-auto text-rose-500 hover:text-rose-600 font-bold p-1 text-sm flex items-center justify-center">✕</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Order Notes / Delivery
                            Instructions</label>
                        <textarea name="notes" rows="2" placeholder="e.g. Special heavy-duty powder coating requirements..."
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700"></textarea>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-2">
                        <button type="button"
                            onclick="toggleInlineForm('orderFormContainer', document.querySelector('button[onclick*=\'orderFormContainer\']'))"
                            class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold py-2.5 px-5 rounded-xl transition cursor-pointer">Cancel</button>
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
                <span id="statOrdersTotal"
                    class="text-2xl font-black text-slate-800 block mt-1">{{ $stats['total'] }}</span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                <span class="text-[10px] font-bold text-amber-500 uppercase tracking-wider block">Pending</span>
                <span id="statOrdersPending"
                    class="text-2xl font-black text-amber-600 block mt-1">{{ $stats['pending'] }}</span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                <span class="text-[10px] font-bold text-blue-500 uppercase tracking-wider block">In Production</span>
                <span id="statOrdersInProduction"
                    class="text-2xl font-black text-blue-600 block mt-1">{{ $stats['in_production'] }}</span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider block">Ready For Dispatch</span>
                <span id="statOrdersReady"
                    class="text-2xl font-black text-indigo-600 block mt-1">{{ $stats['ready'] }}</span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider block">Dispatched / Done</span>
                <span id="statOrdersCompleted"
                    class="text-2xl font-black text-emerald-600 block mt-1">{{ $stats['completed'] }}</span>
            </div>
        </div>

        <!-- Filter Capsules Bar -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-black uppercase text-slate-400 tracking-wider flex items-center mr-2">
                    <svg class="w-4 h-4 mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                        </path>
                    </svg>
                    Order Pipeline Status:
                </span>
                <a href="{{ route('orders', ['status' => 'all']) }}"
                    class="px-4 py-1.5 rounded-full text-xs font-bold border transition duration-150 {{ $status === 'all' ? 'bg-blue-600 border-blue-600 text-white shadow-sm' : 'border-blue-600/30 text-blue-700 hover:bg-blue-50 bg-white' }}">
                    All Orders
                </a>
                <a href="{{ route('orders', ['status' => 'pending']) }}"
                    class="px-4 py-1.5 rounded-full text-xs font-bold border transition duration-150 {{ $status === 'pending' ? 'bg-blue-600 border-blue-600 text-white shadow-sm' : 'border-blue-600/30 text-blue-700 hover:bg-blue-50 bg-white' }}">
                    Pending ({{ $stats['pending'] }})
                </a>
                <a href="{{ route('orders', ['status' => 'in_production']) }}"
                    class="px-4 py-1.5 rounded-full text-xs font-bold border transition duration-150 {{ $status === 'in_production' ? 'bg-blue-600 border-blue-600 text-white shadow-sm' : 'border-blue-600/30 text-blue-700 hover:bg-blue-50 bg-white' }}">
                    In Production ({{ $stats['in_production'] }})
                </a>
                <a href="{{ route('orders', ['status' => 'ready_for_dispatch']) }}"
                    class="px-4 py-1.5 rounded-full text-xs font-bold border transition duration-150 {{ $status === 'ready_for_dispatch' ? 'bg-blue-600 border-blue-600 text-white shadow-sm' : 'border-blue-600/30 text-blue-700 hover:bg-blue-50 bg-white' }}">
                    Ready For Dispatch ({{ $stats['ready'] }})
                </a>
                <a href="{{ route('orders', ['status' => 'dispatched']) }}"
                    class="px-4 py-1.5 rounded-full text-xs font-bold border transition duration-150 {{ $status === 'dispatched' ? 'bg-blue-600 border-blue-600 text-white shadow-sm' : 'border-blue-600/30 text-blue-700 hover:bg-blue-50 bg-white' }}">
                    Dispatched / Completed
                </a>
            </div>
        </div>

        <!-- Orders Data Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
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
                                                • {{ $it->product->product_name ?? $it->finishedGood->product_name ?? 'Product' }}:
                                                <strong class="text-slate-900">{{ number_format($it->quantity) }}
                                                    {{ strtolower($it->billing_uom ?? ($it->product->uom ?? 'piece')) }}</strong> @
                                                ₹{{ number_format($it->unit_price, 2) }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="px-4 py-3 text-center text-slate-600 whitespace-nowrap">
                                    <span
                                        class="font-bold block">{{ \Carbon\Carbon::parse($ord->order_date)->format('d/m/Y') }}</span>
                                    @if($ord->delivery_date)
                                        <span class="text-[10px] text-slate-400 block">Target:
                                            {{ \Carbon\Carbon::parse($ord->delivery_date)->format('d/m/Y') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-mono font-extrabold text-slate-900">
                                    ₹{{ number_format($ord->total_amount, 2) }}</td>
                                <td class="px-4 py-3 text-center whitespace-nowrap order-status-cell">
                                    @php
                                        $hasStock = $ord->hasSufficientStock();
                                        $deficits = $ord->getStockDeficitDetails();
                                        $trackStockOn = (\App\Models\Setting::get('track_stock', 'true') === 'true');
                                    @endphp

                                    @if ($ord->status === 'dispatched' || $ord->status === 'completed')
                                        <div class="inline-flex flex-col items-center">
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs">
                                                ✓ DISPATCHED
                                            </span>
                                            @if($trackStockOn)
                                                <span class="text-[9px] text-emerald-600 font-semibold mt-0.5">Stock Deducted</span>
                                            @endif
                                        </div>

                                    @elseif ($ord->status === 'cancelled')
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-300 shadow-2xs">
                                            ✕ CANCELLED
                                        </span>

                                    @elseif ($ord->status === 'pending')
                                        <div class="inline-flex flex-col items-center space-y-1">
                                            <button type="button" onclick="updateOrderStatus({{ $ord->id }}, 'in_production', this)"
                                                title="Click to start manufacturing run for this order"
                                                class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white text-[10px] font-bold rounded-lg shadow-2xs transition flex items-center space-x-1 cursor-pointer">
                                                <span>▶ Start Production</span>
                                            </button>
                                            <span class="text-[9.5px] text-amber-600 font-semibold">Stage 1: Pending</span>
                                        </div>

                                    @elseif ($ord->status === 'in_production')
                                        @if (!$trackStockOn || $hasStock)
                                            <div class="inline-flex flex-col items-center space-y-1">
                                                <button type="button" onclick="updateOrderStatus({{ $ord->id }}, 'ready_for_dispatch', this)"
                                                    title="{{ $trackStockOn ? 'Sufficient stock available! Click to mark Ready for Dispatch' : 'Click to mark Ready for Dispatch' }}"
                                                    class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-bold rounded-lg shadow-2xs transition flex items-center space-x-1 cursor-pointer {{ $trackStockOn ? 'animate-pulse' : '' }}">
                                                    <span>📦 Mark Ready for Dispatch</span>
                                                </button>
                                                <span
                                                    class="text-[9.5px] text-blue-600 font-semibold">{{ $trackStockOn ? 'Stock Ready' : 'Stage 2: In Production' }}</span>
                                            </div>
                                        @else
                                            <div class="inline-flex flex-col items-center space-y-1"
                                                title="{{ implode('; ', array_map(fn($d) => $d['product_name'] . ': missing ' . $d['missing_quantity'] . ' pcs', $deficits)) }}">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                                    ⏳ In Production (Awaiting Stock)
                                                </span>
                                                <a href="{{ route('production') }}"
                                                    class="text-[9.5px] text-amber-700 hover:underline font-bold">+ Log Batch in
                                                    Production →</a>
                                            </div>
                                        @endif

                                    @elseif ($ord->status === 'ready_for_dispatch')
                                        <div class="inline-flex flex-col items-center space-y-1">
                                            <a href="{{ route('invoices', ['order_id' => $ord->id]) }}"
                                                title="{{ $trackStockOn ? 'Stock is ready! Click to generate Tax Invoice & dispatch order' : 'Click to generate Tax Invoice & dispatch order' }}"
                                                class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-[10px] font-bold rounded-lg shadow-2xs transition flex items-center space-x-1">
                                                <span>🚀 Gen Invoice & Dispatch</span>
                                            </a>
                                            <span class="text-[9.5px] text-indigo-600 font-semibold">Stage 3: Ready for
                                                Dispatch</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center space-x-1.5">
                                        <button type="button" onclick="openOrderInfoModal({{ $ord->id }})"
                                            title="Order 360° Info & MRP Hub"
                                            class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition transform hover:scale-105 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </button>

                                        @if($ord->status !== 'dispatched' && $ord->status !== 'completed' && $ord->status !== 'cancelled')
                                            <button type="button" onclick="openEditOrderModal({{ $ord->id }})"
                                                title="Edit Sales Order"
                                                class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-2xs transition transform hover:scale-105 cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                                    </path>
                                                </svg>
                                            </button>
                                        @endif
                                        <button type="button"
                                            onclick="deleteOrder({{ $ord->id }}, '{{ addslashes($ord->order_number) }}')"
                                            title="Delete Sales Order"
                                            class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-rose-500 hover:bg-rose-600 text-white shadow-2xs transition transform hover:scale-105 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-empty-state title="No Sales Orders Found"
                                subtitle="There are no sales orders matching this status filter." colspan="8" />
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- 360° Order Info & MRP Hub Modal -->
            <div id="orderInfoModal"
                class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/60 backdrop-blur-md flex items-center justify-center p-4">
                <div
                    class="relative w-full max-w-5xl md:max-w-6xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden transform transition-all duration-300">
                    <!-- Modal Header -->
                    <div
                        class="flex items-center justify-between px-6 py-4 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
                        <div class="flex items-center space-x-3.5">
                            <div
                                class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-500/20 border border-blue-100 dark:border-blue-500/30 flex items-center justify-center text-blue-600 dark:text-blue-400 text-lg shadow-2xs">
                                ℹ️
                            </div>
                            <div>
                                <h3 id="modalOrderTitle" class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">Sales
                                    Order 360° Control Hub</h3>
                                <p id="modalOrderSubtitle" class="text-xs text-slate-500 dark:text-slate-400 font-medium">Order Info, Finished
                                    Goods Stock Allocation & MRP Raw Material Matrix</p>
                            </div>
                        </div>
                        <button type="button" onclick="closeOrderInfoModal()" data-modal-close="orderInfoModal"
                            class="modal-close-btn text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl p-2 transition text-xl font-bold cursor-pointer"
                            title="Close modal">&times;</button>
                    </div>

                    <!-- Modal Body Container -->
                    <div id="modalOrderContent"
                        class="p-6 space-y-6 max-h-[78vh] overflow-y-auto styled-scrollbar bg-slate-50/50 dark:bg-slate-900/50">
                        <!-- Loading State -->
                        <div id="modalOrderLoading" class="py-16 text-center text-slate-500 dark:text-slate-400 space-y-3">
                            <svg class="w-9 h-9 mx-auto animate-spin text-blue-600 dark:text-blue-400" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <p class="text-sm font-bold">Loading Order Information & Calculations...</p>
                        </div>

                        <!-- Dynamic Loaded Content Container -->
                        <div id="modalOrderData" class="hidden space-y-5">

                            <!-- Notice when Stock is OFF -->
                            <div id="mStockOffNotice"
                                class="hidden p-3.5 bg-blue-50/80 dark:bg-blue-950/40 rounded-2xl border border-blue-200/80 dark:border-blue-800/60 text-xs text-blue-900 dark:text-blue-200 font-medium flex items-center justify-between shadow-2xs">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-base">📦</span>
                                    <span><strong>Stock Management is OFF</strong> — Operating in commercial order booking &
                                        dispatch mode.</span>
                                </div>
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-700">Commercial
                                    Mode</span>
                            </div>

                            <!-- Order Header Balanced Stats Grid (No Awkward Gaps!) -->
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5">
                                <!-- 1. Order Number & Date Card (4 cols) -->
                                <div
                                    class="md:col-span-4 p-4 bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700/70 shadow-2xs flex flex-col justify-between">
                                    <div>
                                        <span
                                            class="block text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-400 mb-0.5">Order
                                            Number</span>
                                        <span id="mOrderNum"
                                            class="text-base font-black text-blue-600 dark:text-blue-400 font-mono tracking-tight">PWW-ORD-000</span>
                                    </div>
                                    <div
                                        class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between text-xs">
                                        <span class="text-slate-400 text-[10px] font-bold uppercase">Delivery Date</span>
                                        <span id="mDeliveryDate"
                                            class="font-bold text-slate-700 dark:text-slate-300 font-mono">Date</span>
                                    </div>
                                </div>

                                <!-- 2. Client Company & Delivery Plant Card (4 cols) -->
                                <div
                                    class="md:col-span-4 p-4 bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700/70 shadow-2xs flex flex-col justify-between">
                                    <div>
                                        <span
                                            class="block text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-400 mb-0.5">Client
                                            Company & Delivery Plant</span>
                                        <span id="mClientName"
                                            class="text-sm font-black text-slate-800 dark:text-slate-100 leading-snug line-clamp-2">Client
                                            Name</span>
                                    </div>
                                    <div
                                        class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-700/60 text-[10px] text-slate-400 font-medium">
                                        🏢 B2B Commercial Account
                                    </div>
                                </div>

                                <!-- 3. Estimated Production Cost & Margin Card (4 cols) -->
                                <div
                                    class="md:col-span-4 p-4 bg-gradient-to-br from-amber-50/80 to-amber-100/30 dark:from-amber-950/40 dark:to-slate-800/80 rounded-2xl border border-amber-200/80 dark:border-amber-700/50 shadow-2xs flex flex-col justify-between">
                                    <div class="flex items-center justify-between mb-1">
                                        <span
                                            class="text-[10px] font-black uppercase tracking-wider text-amber-800 dark:text-amber-300">Est.
                                            Production Cost</span>
                                        <span id="mEstProfitMargin"
                                            class="text-[9px] font-extrabold text-emerald-800 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-950/80 border border-emerald-300/60 dark:border-emerald-700/60 px-2 py-0.5 rounded-full">Margin:
                                            0%</span>
                                    </div>
                                    <div>
                                        <span id="mEstTotalCost"
                                            class="text-lg font-black text-amber-950 dark:text-amber-200 font-mono">₹0.00</span>
                                    </div>
                                    <div
                                        class="mt-1 pt-1.5 border-t border-amber-200/60 dark:border-amber-800/40 text-[10px] text-amber-700 dark:text-amber-400/90 font-medium">
                                        ⚡ Recipe & Raw Material Cost
                                    </div>
                                </div>
                            </div>

                            <!-- Order Notes / Special Instructions Box (Visible only when notes exist) -->
                            <div id="mOrderNotesWrapper"
                                class="hidden p-3.5 bg-amber-50/70 dark:bg-amber-950/40 rounded-2xl border border-amber-200/80 dark:border-amber-800/60 text-xs shadow-2xs space-y-1">
                                <div class="flex items-center space-x-1.5 text-amber-800 dark:text-amber-300 font-bold text-[11px] uppercase tracking-wider">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    <span>Order Notes / Instructions</span>
                                </div>
                                <p id="mOrderNotes" class="text-xs text-slate-700 dark:text-slate-200 font-medium whitespace-pre-line pl-5"></p>
                            </div>

                            <!-- Ordered Items & Stock Allocation Section -->
                            <div
                                class="space-y-3 bg-white dark:bg-slate-800/70 p-4 rounded-2xl border border-slate-200 dark:border-slate-700/70 shadow-2xs">
                                <div class="flex items-center justify-between">
                                    <h4
                                        class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-100 flex items-center">
                                        <span
                                            class="w-2.5 h-2.5 bg-blue-600 dark:bg-blue-400 rounded-full mr-2 shadow-2xs"></span>
                                        <span id="mItemsSectionTitle">Finished Goods Readiness & Stock Allocation</span>
                                    </h4>
                                    <span id="mFgOverallBadge"
                                        class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-blue-100 dark:bg-blue-950/80 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800 shadow-2xs">Status</span>
                                </div>

                                <div
                                    class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                                    <table class="w-full text-xs text-left">
                                        <thead
                                            class="bg-[#EDF4FA] dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold uppercase text-[11px] border-b border-slate-200 dark:border-slate-700">
                                            <tr id="mItemsTableHeaderRow">
                                                <th class="px-4 py-3">Product Name</th>
                                                <th class="px-4 py-3 text-center">Ordered Qty</th>
                                                <th class="px-4 py-3 text-right font-mono">Est. Cost / Unit</th>
                                                <th class="px-4 py-3 text-right font-mono m-commercial-col hidden">Margin / Unit</th>
                                                <th class="px-4 py-3 text-center m-stock-col">Live Warehouse Stock</th>
                                                <th class="px-4 py-3 text-center m-stock-col">Stock Readiness</th>
                                                <th class="px-4 py-3 text-right m-commercial-col hidden">Unit Price</th>
                                                <th class="px-4 py-3 text-right m-commercial-col hidden">Total Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody id="mFgTableBody"
                                            class="divide-y divide-slate-100 dark:divide-slate-700/60 bg-white dark:bg-slate-900">
                                            <!-- Dynamic JS Injection -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Raw Material MRP Matrix Section -->
                            <div id="mMrpSection"
                                class="space-y-3 bg-white dark:bg-slate-800/70 p-4 rounded-2xl border border-slate-200 dark:border-slate-700/70 shadow-2xs">
                                <div class="flex items-center justify-between">
                                    <h4
                                        class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-100 flex items-center">
                                        <span
                                            class="w-2.5 h-2.5 bg-amber-500 dark:bg-amber-400 rounded-full mr-2 shadow-2xs"></span>
                                        <span id="mMrpSectionTitle">Material Requirement Planning (MRP) Raw Material
                                            Matrix</span>
                                    </h4>
                                    <span class="text-[11px] text-slate-400 dark:text-slate-400 font-medium">* Formula:
                                        (Ordered Qty × BOM Qty) + Waste %</span>
                                </div>

                                <div
                                    class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                                    <table class="w-full text-xs text-left">
                                        <thead
                                            class="bg-[#EDF4FA] dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold uppercase text-[11px] border-b border-slate-200 dark:border-slate-700">
                                            <tr>
                                                <th class="px-4 py-3">Raw Material Name</th>
                                                <th class="px-4 py-3 text-center">Required Material for this Order</th>
                                                <th class="px-4 py-3 text-right font-mono">Est. Material Cost</th>
                                                <th class="px-4 py-3 text-center m-mrp-stock-col">Current Stock</th>
                                                <th class="px-4 py-3 text-center m-mrp-stock-col">Deficit / Readiness</th>
                                                <th class="px-4 py-3 text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="mMrpTableBody"
                                            class="divide-y divide-slate-100 dark:divide-slate-700/60 bg-white dark:bg-slate-900">
                                            <!-- Dynamic JS Injection -->
                                        </tbody>
                                    </table>
                                </div>

                                <div id="mMissingBomWarning"
                                    class="hidden p-3.5 bg-amber-50 dark:bg-amber-950/30 rounded-xl border border-amber-200 dark:border-amber-800/40 text-xs text-amber-800 dark:text-amber-300 font-medium shadow-2xs">
                                    ⚠️ Warning: Some products in this order do not have BOM rules defined. Click <a
                                        href="{{ route('bom') }}"
                                        class="underline font-bold text-amber-900 dark:text-amber-200">BOM Management</a> to
                                    configure formulas.
                                </div>
                            </div>

                            <!-- Full Order Cost & Profitability Matrix -->
                            <div id="mCostMatrixSection"
                                class="p-5 bg-white dark:bg-slate-800/80 rounded-2xl border border-slate-200 dark:border-slate-700/80 shadow-2xs space-y-4">
                                <div class="flex items-center justify-between">
                                    <h4
                                        class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-100 flex items-center">
                                        <span
                                            class="w-2.5 h-2.5 bg-emerald-500 dark:bg-emerald-400 rounded-full mr-2 shadow-2xs"></span>
                                        Order Profitability & Financial Cost Matrix
                                    </h4>
                                    <span class="text-[11px] font-bold text-slate-400 dark:text-slate-400">Commercial vs
                                        Production Economics</span>
                                </div>

                                <!-- 4 Stats Cards Grid with Top Border Colors -->
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5">
                                    <!-- 1. Total Selling Revenue -->
                                    <div
                                        class="p-3.5 bg-slate-50 dark:bg-slate-900/80 rounded-xl border border-slate-200 dark:border-slate-700/80 shadow-2xs border-t-2 border-t-blue-500">
                                        <span
                                            class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-400 block mb-1">Total
                                            Selling Price (Revenue)</span>
                                        <span id="mMatrixRevenue"
                                            class="text-base font-black text-slate-900 dark:text-white font-mono">₹0.00</span>
                                        <span class="text-[10px] text-slate-500 dark:text-slate-400 block mt-0.5">Customer
                                            billing amount</span>
                                    </div>

                                    <!-- 2. Est. Production Cost -->
                                    <div
                                        class="p-3.5 bg-slate-50 dark:bg-slate-900/80 rounded-xl border border-slate-200 dark:border-slate-700/80 shadow-2xs border-t-2 border-t-amber-500">
                                        <span
                                            class="text-[10px] font-black uppercase tracking-wider text-amber-700 dark:text-amber-400 block mb-1">Est.
                                            Manufacturing Cost</span>
                                        <span id="mMatrixCost"
                                            class="text-base font-black text-amber-900 dark:text-amber-200 font-mono">₹0.00</span>
                                        <span class="text-[10px] text-amber-600 dark:text-amber-400/80 block mt-0.5">Raw
                                            materials & BOM recipe</span>
                                    </div>

                                    <!-- 3. Est. Gross Profit -->
                                    <div
                                        class="p-3.5 bg-slate-50 dark:bg-slate-900/80 rounded-xl border border-slate-200 dark:border-slate-700/80 shadow-2xs border-t-2 border-t-emerald-500">
                                        <span
                                            class="text-[10px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400 block mb-1">Estimated
                                            Gross Profit</span>
                                        <span id="mMatrixProfit"
                                            class="text-base font-black text-emerald-700 dark:text-emerald-300 font-mono">₹0.00</span>
                                        <span
                                            class="text-[10px] text-emerald-600 dark:text-emerald-400/80 block mt-0.5">Revenue
                                            minus production cost</span>
                                    </div>

                                    <!-- 4. Profit Margin -->
                                    <div
                                        class="p-3.5 bg-slate-50 dark:bg-slate-900/80 rounded-xl border border-slate-200 dark:border-slate-700/80 shadow-2xs border-t-2 border-t-indigo-500">
                                        <span
                                            class="text-[10px] font-black uppercase tracking-wider text-indigo-700 dark:text-indigo-400 block mb-1">Gross
                                            Profit Margin %</span>
                                        <span id="mMatrixMargin"
                                            class="text-base font-black text-indigo-700 dark:text-indigo-300 font-mono">0.0%</span>
                                        <span
                                            class="text-[10px] text-indigo-600 dark:text-indigo-400/80 block mt-0.5">Profitability
                                            percentage</span>
                                    </div>
                                </div>

                                <!-- Visual Ratio Progress Bar -->
                                <div class="space-y-1.5 pt-1">
                                    <div
                                        class="flex justify-between text-[11px] font-bold text-slate-600 dark:text-slate-300">
                                        <span class="flex items-center gap-1.5"><span
                                                class="w-2 h-2 rounded-full bg-amber-500 dark:bg-amber-400"></span>
                                            Production Cost: <span id="mMatrixCostPercent"
                                                class="font-mono text-amber-800 dark:text-amber-300">0%</span></span>
                                        <span class="flex items-center gap-1.5"><span
                                                class="w-2 h-2 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                                            Gross Margin: <span id="mMatrixProfitPercent"
                                                class="font-mono text-emerald-800 dark:text-emerald-300">0%</span></span>
                                    </div>
                                    <div
                                        class="h-2.5 w-full bg-slate-200 dark:bg-slate-700/70 rounded-full overflow-hidden flex shadow-inner">
                                        <div id="mCostBar"
                                            class="h-full bg-amber-500 dark:bg-amber-400 transition-all duration-500"
                                            style="width: 50%"></div>
                                        <div id="mProfitBar"
                                            class="h-full bg-emerald-500 dark:bg-emerald-400 transition-all duration-500"
                                            style="width: 50%"></div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Modal Footer Action Bar -->
                    <div
                        class="flex flex-wrap items-center justify-end px-6 py-4 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 gap-2.5">
                        <div id="mModalActions" class="flex items-center space-x-2">
                            <!-- Dynamic Buttons -->
                        </div>
                        <div id="mOrderStatusBadge"
                            class="px-3.5 py-2 text-xs font-bold rounded-xl border shadow-2xs transition"></div>
                        <button type="button" onclick="closeOrderInfoModal()" data-modal-close="orderInfoModal"
                            class="modal-close-btn px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl transition cursor-pointer border border-slate-200 dark:border-slate-700">
                            Close
                        </button>
                    </div>

                </div>
            </div>

            <script>
                window.salesOrdersDataMap = @json($salesOrdersJsonMap);

                window.toggleInlineForm = function (containerId, btn) {
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

                document.addEventListener('change', function (e) {
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
                document.getElementById('addOrderRowBtn')?.addEventListener('click', function () {
                    if (_addOrderRowPending) return;
                    _addOrderRowPending = true;
                    setTimeout(function () { _addOrderRowPending = false; }, 300);

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
                        try { ord = JSON.parse(btnOrOrder.dataset.order); } catch (e) { console.error("Invalid order dataset", e); }
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

                function updateOrderStatus(id, status, btnEl) {
                    const $btn = btnEl ? $(btnEl) : null;
                    if ($btn && window.setButtonLoading) window.setButtonLoading($btn, true, 'Updating...');

                    const token = $('meta[name="csrf-token"]').attr('content') || '';
                    $.ajax({
                        url: `/orders/${id}/status`,
                        method: 'PATCH',
                        data: { status: status, _token: token },
                        success: async function (res) {
                            if ($btn && window.setButtonLoading) window.setButtonLoading($btn, false);
                            if (window.showToast) window.showToast('success', res.message || 'Status updated!');

                            const $row = $(`#order-row-${id}`);
                            if ($row.length) {
                                const $statusCell = $row.find('.order-status-cell').length ? $row.find('.order-status-cell') : $row.find('td:nth-child(7)');
                                const actualStatus = res.status || status;
                                const hasStock = res.has_stock !== undefined ? res.has_stock : true;
                                const trackStockOn = res.track_stock !== undefined ? res.track_stock : true;
                                const deficits = res.deficits || [];

                                if (actualStatus === 'in_production') {
                                    if (!trackStockOn || hasStock) {
                                        $statusCell.html(`
                                            <div class="inline-flex flex-col items-center space-y-1">
                                                <button type="button" 
                                                        onclick="updateOrderStatus(${id}, 'ready_for_dispatch', this)"
                                                        title="${trackStockOn ? 'Sufficient stock available! Click to mark Ready for Dispatch' : 'Click to mark Ready for Dispatch'}"
                                                        class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-bold rounded-lg shadow-2xs transition flex items-center space-x-1 cursor-pointer ${trackStockOn ? 'animate-pulse' : ''}">
                                                    <span>📦 Mark Ready for Dispatch</span>
                                                </button>
                                                <span class="text-[9.5px] text-blue-600 font-semibold">${trackStockOn ? 'Stock Ready' : 'Stage 2: In Production'}</span>
                                            </div>
                                        `);
                                    } else {
                                        const deficitTitle = deficits.map(d => `${d.product_name}: missing ${d.missing_quantity} pcs`).join('; ');
                                        $statusCell.html(`
                                            <div class="inline-flex flex-col items-center space-y-1" title="${deficitTitle}">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                                    ⏳ In Production (Awaiting Stock)
                                                </span>
                                                <a href="/production" class="text-[9.5px] text-amber-700 hover:underline font-bold">+ Log Batch in Production →</a>
                                            </div>
                                        `);
                                    }
                                    window.updateStatCounter('#statOrdersPending', -1);
                                    window.updateStatCounter('#statOrdersInProduction', +1);
                                } else if (actualStatus === 'ready_for_dispatch') {
                                    $statusCell.html(`
                                        <div class="inline-flex flex-col items-center space-y-1">
                                            <a href="/invoices?order_id=${id}" 
                                               title="${trackStockOn ? 'Stock is ready! Click to generate Tax Invoice & dispatch order' : 'Click to generate Tax Invoice & dispatch order'}"
                                               class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-[10px] font-bold rounded-lg shadow-2xs transition flex items-center space-x-1">
                                                <span>🚀 Gen Invoice & Dispatch</span>
                                            </a>
                                            <span class="text-[9.5px] text-indigo-600 font-semibold">Stage 3: Ready for Dispatch</span>
                                        </div>
                                    `);
                                    window.updateStatCounter('#statOrdersInProduction', -1);
                                    window.updateStatCounter('#statOrdersReady', +1);
                                } else if (actualStatus === 'dispatched' || actualStatus === 'completed') {
                                    $statusCell.html(`
                                        <div class="inline-flex flex-col items-center">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs">
                                                ✓ DISPATCHED
                                            </span>
                                        </div>
                                    `);
                                    window.updateStatCounter('#statOrdersReady', -1);
                                    window.updateStatCounter('#statOrdersCompleted', +1);
                                } else if (actualStatus === 'cancelled') {
                                    $statusCell.html(`
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-300 shadow-2xs">
                                            ✕ CANCELLED
                                        </span>
                                    `);
                                }
                                if (window.ERPTableHelper) window.ERPTableHelper.highlightRow($row);
                            } else if (window.loadPage) {
                                await window.loadPage(window.location.href);
                            }
                        },
                        error: function (xhr) {
                            if ($btn && window.setButtonLoading) window.setButtonLoading($btn, false);
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

                function deleteOrder(id, orderNumber) {
                    window.confirmDelete(
                        'Delete Sales Order?',
                        `Are you sure you want to delete Sales Order '${orderNumber}'? This action cannot be undone.`,
                        function () {
                            const token = $('meta[name="csrf-token"]').attr('content') || '';
                            $.ajax({
                                url: `/orders/${id}`,
                                method: 'DELETE',
                                data: { _token: token },
                                success: function (res) {
                                    if (window.showToast) window.showToast('success', res.message);
                                    if (window.ERPTableHelper) {
                                        window.ERPTableHelper.removeRow(`#order-row-${id}`, function () {
                                            window.updateStatCounter('#statOrdersTotal', -1);
                                        });
                                    } else {
                                        $(`#order-row-${id}`).fadeOut(300, function () { $(this).remove(); });
                                    }
                                    if (window.clearPageCache) window.clearPageCache();
                                },
                                error: function (xhr) {
                                    const msg = xhr.responseJSON?.message || 'Failed to delete order.';
                                    if (window.showToast) window.showToast('error', msg);
                                    else alert(msg);
                                }
                            });
                        }
                    );
                }

                window.updateRowUnitPrice = updateRowUnitPrice;
                window.openEditOrderModal = openEditOrderModal;
                window.updateOrderStatus = updateOrderStatus;
                window.deleteOrder = deleteOrder;

                window.closeOrderInfoModal = function () {
                    const modal = document.getElementById('orderInfoModal');
                    if (modal) modal.classList.add('hidden');
                };
                function closeOrderInfoModal() {
                    window.closeOrderInfoModal();
                }

                window.orderCache = window.orderCache || new Map();
                window.salesOrders360Map = @json($salesOrders360Map ?? []);

                function openOrderInfoModal(orderId) {
                    const modal = document.getElementById('orderInfoModal');
                    const loading = document.getElementById('modalOrderLoading');
                    const dataBox = document.getElementById('modalOrderData');

                    if (!modal) return;
                    modal.classList.remove('hidden');

                    // Instant 0ms render if data is in memory cache or pre-embedded from page load
                    const preloaded = (window.orderCache && window.orderCache.get(orderId)) || (window.salesOrders360Map && window.salesOrders360Map[orderId]);
                    if (preloaded) {
                        loading.classList.add('hidden');
                        renderOrderInfoModalData(preloaded);

                        // Silent background refresh for 100% real-time stock sync
                        fetch(`/orders/${orderId}/details`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                            .then(r => r.ok ? r.json() : null)
                            .then(res => {
                                if (res) {
                                    const liveData = res.data || res;
                                    if (window.orderCache) window.orderCache.set(orderId, liveData);
                                    renderOrderInfoModalData(liveData);
                                }
                            })
                            .catch(() => { });
                        return;
                    }

                    loading.classList.remove('hidden');
                    dataBox.classList.add('hidden');

                    $.ajax({
                        url: `/orders/${orderId}/details`,
                        method: 'GET',
                        success: function (res) {
                            const data = res.data || res;
                            if (window.orderCache) window.orderCache.set(orderId, data);
                            renderOrderInfoModalData(data);
                        },
                        error: function (xhr) {
                            alert('Failed to load order details.');
                            closeOrderInfoModal();
                        }
                    });
                }

                // Background hover & touch pre-fetch for instant 0ms open
                $(document).on('mouseenter touchstart', 'button[onclick*="openOrderInfoModal"]', function () {
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
                                    .catch(() => { });
                            }
                        }
                    } catch (e) { }
                });

                window.trackStockEnabled = {{ \App\Models\Setting::isStockEnabled() ? 'true' : 'false' }};
                window.moduleProductionEnabled = {{ \App\Models\Setting::get('module_production', 'true') === 'true' ? 'true' : 'false' }};
                window.moduleInvoicesEnabled = {{ \App\Models\Setting::get('module_invoices', 'true') === 'true' ? 'true' : 'false' }};

                function renderOrderInfoModalData(data) {
                    document.getElementById('modalOrderLoading').classList.add('hidden');
                    document.getElementById('modalOrderData').classList.remove('hidden');

                    const isStockOn = typeof data.track_stock_enabled !== 'undefined' ? !!data.track_stock_enabled : !!window.trackStockEnabled;

                    // Header info
                    document.getElementById('mOrderNum').textContent = data.order_number || 'N/A';
                    const poEl = document.getElementById('mPoNum');
                    if (poEl) poEl.textContent = data.po_number || 'N/A';
                    document.getElementById('mClientName').textContent = (data.client_name || 'N/A') + (data.plant_name ? ' (' + data.plant_name + ')' : '');
                    document.getElementById('mDeliveryDate').textContent = data.delivery_date || data.order_date || 'N/A';

                    // Order Notes / Special Instructions
                    const notesWrapper = document.getElementById('mOrderNotesWrapper');
                    const notesEl = document.getElementById('mOrderNotes');
                    if (notesWrapper && notesEl) {
                        if (data.notes && String(data.notes).trim() !== '') {
                            notesEl.textContent = String(data.notes).trim();
                            notesWrapper.classList.remove('hidden');
                        } else {
                            notesEl.textContent = '';
                            notesWrapper.classList.add('hidden');
                        }
                    }

                    // Est Production Cost & Profit Margin
                    const costSummary = data.estimated_cost_summary || {};
                    const totalEstCost = costSummary.total_estimated_cost || 0;
                    const marginPct = costSummary.profit_margin_percentage || 0;
                    const grossProfit = costSummary.estimated_gross_profit || 0;

                    const estCostEl = document.getElementById('mEstTotalCost');
                    if (estCostEl) {
                        estCostEl.textContent = `₹${totalEstCost.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                    }

                    const marginEl = document.getElementById('mEstProfitMargin');
                    if (marginEl) {
                        if (totalEstCost > 0) {
                            marginEl.textContent = `${marginPct}% Margin`;
                            marginEl.title = `Est. Gross Profit: ₹${grossProfit.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                            marginEl.classList.remove('hidden');
                        } else {
                            marginEl.textContent = 'No BOM Set';
                        }
                    }

                    // Subtitle & Stock Notice
                    const subtitleEl = document.getElementById('modalOrderSubtitle');
                    const noticeEl = document.getElementById('mStockOffNotice');
                    const mrpSection = document.getElementById('mMrpSection');
                    const itemsSectionTitle = document.getElementById('mItemsSectionTitle');
                    const mrpSectionTitle = document.getElementById('mMrpSectionTitle');

                    if (isStockOn) {
                        if (subtitleEl) subtitleEl.textContent = 'Order Info, Finished Goods Stock Allocation & MRP Raw Material Matrix';
                        if (noticeEl) noticeEl.classList.add('hidden');
                        if (mrpSection) mrpSection.classList.remove('hidden');
                        if (itemsSectionTitle) itemsSectionTitle.textContent = 'Finished Goods Readiness & Stock Allocation';
                        if (mrpSectionTitle) mrpSectionTitle.textContent = 'Material Requirement Planning (MRP) Raw Material Matrix';
                        $('.m-stock-col').removeClass('hidden');
                        $('.m-commercial-col').addClass('hidden');
                        $('.m-mrp-stock-col').removeClass('hidden');
                    } else {
                        if (subtitleEl) subtitleEl.textContent = 'Commercial Order Breakdown & Estimated Raw Material Requirements';
                        if (noticeEl) noticeEl.classList.remove('hidden');
                        if (mrpSection) mrpSection.classList.remove('hidden');
                        if (itemsSectionTitle) itemsSectionTitle.textContent = 'Ordered Items & Commercial Pricing Breakdown';
                        if (mrpSectionTitle) mrpSectionTitle.textContent = 'Estimated Raw Material Requirements to Fulfill this Order';
                        $('.m-stock-col').addClass('hidden');
                        $('.m-commercial-col').removeClass('hidden');
                        $('.m-mrp-stock-col').addClass('hidden');
                    }

                    // Status Badge
                    const statusBadge = document.getElementById('mOrderStatusBadge');
                    if (statusBadge) {
                        statusBadge.className = 'px-3.5 py-2 text-xs font-bold rounded-xl border shadow-2xs transition';
                        const status = data.status || 'pending';
                        if (status === 'pending') {
                            statusBadge.classList.add('bg-amber-50', 'dark:bg-amber-950/50', 'text-amber-700', 'dark:text-amber-300', 'border-amber-200', 'dark:border-amber-800/60');
                            statusBadge.innerHTML = '⏳ Pending';
                        } else if (status === 'in_production') {
                            statusBadge.classList.add('bg-blue-50', 'dark:bg-blue-950/50', 'text-blue-700', 'dark:text-blue-300', 'border-blue-200', 'dark:border-blue-800/60');
                            statusBadge.innerHTML = '⚙️ In Production';
                        } else if (status === 'ready_for_dispatch') {
                            statusBadge.classList.add('bg-indigo-50', 'dark:bg-indigo-950/50', 'text-indigo-700', 'dark:text-indigo-300', 'border-indigo-200', 'dark:border-indigo-800/60');
                            statusBadge.innerHTML = '📦 Ready for Dispatch';
                        } else if (status === 'dispatched' || status === 'completed') {
                            statusBadge.classList.add('bg-emerald-50', 'dark:bg-emerald-950/50', 'text-emerald-700', 'dark:text-emerald-300', 'border-emerald-200', 'dark:border-emerald-800/60');
                            statusBadge.innerHTML = '✓ Dispatched';
                        } else if (status === 'cancelled') {
                            statusBadge.classList.add('bg-rose-50', 'dark:bg-rose-950/50', 'text-rose-700', 'dark:text-rose-300', 'border-rose-200', 'dark:border-rose-800/60');
                            statusBadge.innerHTML = '✕ Cancelled';
                        } else {
                            statusBadge.classList.add('bg-slate-50', 'dark:bg-slate-800', 'text-slate-700', 'dark:text-slate-300', 'border-slate-200', 'dark:border-slate-700');
                            statusBadge.innerHTML = data.formatted_status || 'N/A';
                        }
                    }

                    // Items Table Body
                    const fgTbody = document.getElementById('mFgTableBody');
                    fgTbody.innerHTML = '';
                    const fgBadge = document.getElementById('mFgOverallBadge');

                    if (isStockOn) {
                        const fg = data.finished_goods_status || {};
                        if (fg.is_fully_stocked) {
                            fgBadge.textContent = '✓ Ready to Dispatch';
                            fgBadge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800';
                        } else {
                            fgBadge.textContent = '⚡ Production Needed';
                            fgBadge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800';
                        }

                        (fg.items || []).forEach(item => {
                            const tr = document.createElement('tr');
                            tr.className = 'hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors';
                            const statusHtml = item.is_sufficient
                                ? `<span class="px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 font-bold text-[10px]">✓ Fully Stocked</span>`
                                : `<span class="px-2 py-0.5 rounded-md bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 font-bold text-[10px]">Short by ${item.missing_quantity} ${item.billing_uom}</span>`;

                            const matchedItem = (data.items || []).find(i => i.product_id === item.product_id || i.product_name === item.product_name) || {};
                            const unitCost = matchedItem.unit_estimated_cost || 0;

                            tr.innerHTML = `
                        <td class="px-3.5 py-3 font-bold text-slate-800 dark:text-slate-100">${item.product_name}</td>
                        <td class="px-3.5 py-3 text-center font-bold text-slate-900 dark:text-slate-100 font-mono">${item.ordered_quantity} ${item.billing_uom}</td>
                        <td class="px-3.5 py-3 text-right font-mono text-amber-800 dark:text-amber-300 font-bold">₹${unitCost.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                        <td class="px-3.5 py-3 text-center text-slate-700 dark:text-slate-300 font-semibold m-stock-col">${item.available_stock}</td>
                        <td class="px-3.5 py-3 text-center m-stock-col">${statusHtml}</td>
                    `;
                            fgTbody.appendChild(tr);
                        });
                    } else {
                        // Stock OFF: Pure Commercial Breakdown
                        const totalAmount = (data.total_amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        fgBadge.textContent = `Total: ₹${totalAmount}`;
                        fgBadge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-blue-100 dark:bg-blue-950/80 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800';

                        (data.items || []).forEach(item => {
                            const tr = document.createElement('tr');
                            tr.className = 'hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors';
                            const unitCost = Number(item.unit_estimated_cost || 0);
                            const unitPrice = Number(item.unit_price || 0);
                            const marginPerUnit = unitPrice - unitCost;
                            const marginSign = marginPerUnit > 0 ? '+' : (marginPerUnit < 0 ? '-' : '');
                            const marginFormatted = marginSign + '₹' + Math.abs(marginPerUnit).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            const marginColor = marginPerUnit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400';

                            const unitCostFormatted = unitCost.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            const unitPriceFormatted = unitPrice.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            const totalPriceFormatted = (item.total_price || (item.quantity * item.unit_price) || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                            tr.innerHTML = `
                        <td class="px-3.5 py-3">
                            <span class="font-bold text-slate-800 dark:text-slate-100 block">${item.product_name}</span>
                            ${item.sku ? `<span class="text-[10px] font-mono text-slate-400 dark:text-slate-500">SKU: ${item.sku}</span>` : ''}
                        </td>
                        <td class="px-3.5 py-3 text-center font-bold text-slate-900 dark:text-slate-100 font-mono">${item.quantity} ${item.billing_uom || 'Pcs'}</td>
                        <td class="px-3.5 py-3 text-right font-mono text-amber-800 dark:text-amber-300 font-bold">₹${unitCostFormatted}</td>
                        <td class="px-3.5 py-3 text-right font-mono font-bold ${marginColor} m-commercial-col">${marginFormatted}</td>
                        <td class="px-3.5 py-3 text-right font-mono text-slate-700 dark:text-slate-300 font-semibold m-commercial-col">₹${unitPriceFormatted}</td>
                        <td class="px-3.5 py-3 text-right font-mono font-black text-blue-600 dark:text-blue-400 m-commercial-col">₹${totalPriceFormatted}</td>
                    `;
                            fgTbody.appendChild(tr);
                        });
                    }

                    // MRP Raw Material Table (Shows requirements & estimated cost in BOTH Stock ON and Stock OFF modes!)
                    const mrp = data.raw_material_mrp || {};
                    const mrpTbody = document.getElementById('mMrpTableBody');
                    if (mrpTbody) {
                        mrpTbody.innerHTML = '';
                        if (!mrp.mrp_list || mrp.mrp_list.length === 0) {
                            const colSpan = isStockOn ? 6 : 4;
                            mrpTbody.innerHTML = `<tr><td colspan="${colSpan}" class="p-5 text-center text-slate-400 dark:text-slate-500 italic">No raw material calculations available. Configure BOM formulas for products to estimate materials.</td></tr>`;
                        } else {
                            mrp.mrp_list.forEach(item => {
                                const tr = document.createElement('tr');
                                tr.className = 'hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors';

                                const estCostFormatted = (item.estimated_cost || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                const unitRateFormatted = (item.unit_rate || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                                if (isStockOn) {
                                    const readinessHtml = item.is_sufficient
                                        ? `<span class="px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 font-bold text-[10px]">✓ Stock Sufficient</span>`
                                        : `<span class="px-2 py-0.5 rounded-md bg-rose-100 dark:bg-rose-950/70 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60 font-bold text-[10px]">Short by ${item.shortage} ${item.unit}</span>`;

                                    const actionHtml = item.is_sufficient
                                        ? `<span class="text-emerald-600 dark:text-emerald-400 font-bold text-[10px]">Ready</span>`
                                        : `<a href="/purchases?raw_material_id=${item.raw_material_id}&quantity=${item.shortage}" class="inline-flex items-center px-2 py-1 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-lg text-[10px] shadow-2xs transition">+ Order Missing Stock</a>`;

                                    tr.innerHTML = `
                                <td class="px-3.5 py-3 font-bold text-slate-900 dark:text-slate-100">${item.material_name}</td>
                                <td class="px-3.5 py-3 text-center font-bold text-blue-600 dark:text-blue-400 font-mono">${item.total_required} ${item.unit}</td>
                                <td class="px-3.5 py-3 text-right font-mono">
                                    <span class="font-bold text-amber-900 dark:text-amber-300">₹${estCostFormatted}</span>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 block font-normal">(@ ₹${unitRateFormatted}/${item.unit})</span>
                                </td>
                                <td class="px-3.5 py-3 text-center text-slate-700 dark:text-slate-300 font-semibold m-mrp-stock-col">${item.current_stock} ${item.unit}</td>
                                <td class="px-3.5 py-3 text-center m-mrp-stock-col">${readinessHtml}</td>
                                <td class="px-3.5 py-3 text-center">${actionHtml}</td>
                            `;
                                } else {
                                    // Stock OFF: Pure Estimation Mode
                                    tr.innerHTML = `
                                <td class="px-3.5 py-3 font-bold text-slate-900 dark:text-slate-100">${item.material_name}</td>
                                <td class="px-3.5 py-3 text-center font-bold text-blue-600 dark:text-blue-400 font-mono text-sm">${item.total_required} ${item.unit}</td>
                                <td class="px-3.5 py-3 text-right font-mono">
                                    <span class="font-bold text-amber-900 dark:text-amber-300">₹${estCostFormatted}</span>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 block font-normal">(@ ₹${unitRateFormatted}/${item.unit})</span>
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <a href="/purchases?raw_material_id=${item.raw_material_id}&quantity=${item.total_required}" class="inline-flex items-center px-2.5 py-1 bg-blue-50 dark:bg-blue-950/60 hover:bg-blue-600 dark:hover:bg-blue-600 text-blue-600 dark:text-blue-300 hover:text-white border border-blue-200 dark:border-blue-800 font-bold rounded-lg text-[10px] shadow-2xs transition">
                                        + Purchase Material
                                    </a>
                                </td>
                            `;
                                }
                                mrpTbody.appendChild(tr);
                            });
                        }
                    }

                    // Full Order Cost & Profitability Matrix rendering
                    const revenue = Number(data.total_amount) || 0;
                    const totalMfgCost = Number(costSummary.total_estimated_cost) || 0;
                    const grossProfitVal = Number(costSummary.estimated_gross_profit) || (revenue - totalMfgCost);
                    const marginPctVal = Number(costSummary.profit_margin_percentage) || (revenue > 0 ? ((grossProfitVal / revenue) * 100) : 0);

                    const elRevenue = document.getElementById('mMatrixRevenue');
                    if (elRevenue) elRevenue.textContent = `₹${revenue.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

                    const elCost = document.getElementById('mMatrixCost');
                    if (elCost) elCost.textContent = `₹${totalMfgCost.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

                    const elProfit = document.getElementById('mMatrixProfit');
                    if (elProfit) {
                        elProfit.textContent = (grossProfitVal >= 0 ? '+₹' : '-₹') + Math.abs(grossProfitVal).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        elProfit.className = grossProfitVal >= 0
                            ? 'text-base font-black text-emerald-700 dark:text-emerald-400 font-mono'
                            : 'text-base font-black text-rose-600 dark:text-rose-400 font-mono';
                    }

                    const elMargin = document.getElementById('mMatrixMargin');
                    if (elMargin) {
                        elMargin.textContent = `${marginPctVal.toFixed(1)}%`;
                        elMargin.className = marginPctVal >= 0
                            ? 'text-base font-black text-blue-700 dark:text-blue-400 font-mono'
                            : 'text-base font-black text-rose-600 dark:text-rose-400 font-mono';
                    }

                    const costPctOfRevenue = revenue > 0 ? Math.min(100, Math.max(0, (totalMfgCost / revenue) * 100)) : (totalMfgCost > 0 ? 100 : 0);
                    const profitPctOfRevenue = revenue > 0 ? Math.min(100, Math.max(0, (grossProfitVal / revenue) * 100)) : 0;

                    const elCostPct = document.getElementById('mMatrixCostPercent');
                    if (elCostPct) elCostPct.textContent = `${costPctOfRevenue.toFixed(1)}%`;

                    const elProfitPct = document.getElementById('mMatrixProfitPercent');
                    if (elProfitPct) elProfitPct.textContent = `${profitPctOfRevenue.toFixed(1)}%`;

                    const costBar = document.getElementById('mCostBar');
                    if (costBar) costBar.style.width = `${costPctOfRevenue}%`;

                    const profitBar = document.getElementById('mProfitBar');
                    if (profitBar) profitBar.style.width = `${profitPctOfRevenue}%`;

                    // Missing BOM Warning
                    const warningBox = document.getElementById('mMissingBomWarning');
                    if (warningBox) {
                        if (mrp.missing_boms && mrp.missing_boms.length > 0) {
                            warningBox.classList.remove('hidden');
                        } else {
                            warningBox.classList.add('hidden');
                        }
                    }

                    // Modal Actions
                    const actionsContainer = document.getElementById('mModalActions');
                    if (actionsContainer) {
                        actionsContainer.innerHTML = '';
                        if (isStockOn && window.moduleProductionEnabled) {
                            actionsContainer.innerHTML += `
                        <a href="/production" class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-2xs transition flex items-center gap-1.5">
                            ⚙️ Log Production
                        </a>
                    `;
                        } else if (!isStockOn && window.moduleInvoicesEnabled && data.status !== 'cancelled') {
                            actionsContainer.innerHTML += `
                        <a href="/invoices?create_from_order=${data.id}&client_id=${data.client_id}" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-2xs transition flex items-center gap-1.5">
                            📄 Invoices Hub
                        </a>
                    `;
                        }
                    }
                }

                window.openOrderInfoModal = openOrderInfoModal;
                window.closeOrderInfoModal = closeOrderInfoModal;
            </script>
@endsection