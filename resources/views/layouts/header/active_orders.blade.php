<!-- Active Sales Orders Pipeline Widget -->
@php
    $activeOrdersSummary = $headerActiveOrders ?? ['total_count' => 0, 'in_production_count' => 0, 'ready_count' => 0, 'pending_count' => 0, 'orders' => collect()];
    $activeOrderCount = $activeOrdersSummary['total_count'] ?? 0;
    $activeOrdersList = $activeOrdersSummary['orders'] ?? collect();
    $inProdCount = $activeOrdersSummary['in_production_count'] ?? 0;
    $readyDispatchCount = $activeOrdersSummary['ready_count'] ?? 0;
    $trackStock = \App\Models\Setting::isStockEnabled();
    $isProdModuleActive = \App\Models\Setting::get('module_production', 'true') === 'true';
@endphp
<div class="relative inline-block text-left" id="activeOrdersDropdownWrapper">
    <button type="button" 
            id="activeOrdersAlertBtn"
            onclick="toggleActiveOrdersDropdown(event)"
            class="flex items-center space-x-2 bg-gradient-to-r from-blue-50 to-indigo-50/90 hover:from-blue-100 hover:to-indigo-100/90 dark:from-slate-800 dark:to-slate-800/90 dark:hover:from-slate-700 dark:hover:to-slate-700 border border-blue-200/80 dark:border-slate-700 px-3 py-1.5 rounded-xl text-xs font-bold text-blue-950 dark:text-slate-100 shadow-2xs transition cursor-pointer"
            title="{{ $activeOrderCount }} Active Orders in Pipeline">
        <span class="flex h-2 w-2 relative">
            @if($inProdCount > 0 && $trackStock)
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
            @elseif($activeOrderCount > 0)
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-600"></span>
            @else
                <span class="relative inline-flex rounded-full h-2 w-2 bg-slate-400"></span>
            @endif
        </span>
        <svg class="w-4 h-4 text-blue-700 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
        </svg>
        <span class="hidden sm:inline text-blue-950 dark:text-slate-100">Active Orders</span>
        <span class="px-1.5 py-0.5 bg-blue-600 text-white font-black text-[10px] rounded-full shadow-2xs">
            {{ $activeOrderCount }}
        </span>
        @if($inProdCount > 0 && $trackStock)
            <span class="hidden lg:inline-block px-1.5 py-0.5 bg-amber-100 dark:bg-amber-950/80 border border-amber-300 dark:border-amber-700/80 text-amber-800 dark:text-amber-300 text-[10px] font-bold rounded-md">
                {{ $inProdCount }} in Prod
            </span>
        @endif
    </button>

    <!-- Dropdown Card -->
    <div id="headerActiveOrdersCard" 
         class="fixed inset-x-3 top-16 sm:absolute sm:inset-x-auto sm:right-0 sm:top-full sm:mt-2 w-auto sm:w-[430px] max-w-[calc(100vw-1.5rem)] sm:max-w-none bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200/90 dark:border-slate-800 p-4 space-y-3 hidden transition-all duration-200 z-50 max-h-[85vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <div class="flex items-center space-x-2">
                <span class="p-1.5 rounded-lg bg-blue-50 text-blue-700 border border-blue-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </span>
                <div>
                    <h4 class="text-xs font-bold text-slate-800">Active Orders Pipeline</h4>
                    <p class="text-[10px] text-slate-500 font-medium">
                        {{ $activeOrderCount }} order(s) awaiting dispatch
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeActiveOrdersDropdown()" class="text-slate-400 hover:text-slate-600 font-bold text-xs p-1 cursor-pointer">&times; Close</button>
        </div>

        @if($activeOrderCount > 0)
            <div class="space-y-2.5 max-h-96 overflow-y-auto pr-1">
                @foreach($activeOrdersList as $order)
                    @php
                        $statusBadgeClass = match($order->status) {
                            'in_production' => 'bg-amber-100 text-amber-800 border border-amber-300',
                            'ready_for_dispatch' => 'bg-emerald-100 text-emerald-800 border border-emerald-300',
                            'confirmed' => 'bg-blue-100 text-blue-800 border border-blue-300',
                            default => 'bg-slate-100 text-slate-700 border border-slate-200'
                        };
                        $statusLabel = match($order->status) {
                            'in_production' => '⚙️ In Production',
                            'ready_for_dispatch' => '🚚 Ready to Dispatch',
                            'confirmed' => '✓ Confirmed',
                            'pending' => '⏳ Pending',
                            default => ucfirst($order->status)
                        };
                    @endphp
                    <div class="p-3 bg-slate-50/70 hover:bg-blue-50/40 rounded-xl border border-slate-200 transition space-y-2">
                        <!-- Order Header -->
                        <div class="flex items-center justify-between">
                            <div class="min-w-0 pr-2">
                                <div class="flex items-center space-x-1.5">
                                    <span class="font-extrabold text-xs text-blue-900">{{ $order->order_number }}</span>
                                    @if($order->po_number)
                                        <span class="text-[10px] text-slate-400 font-mono">PO: {{ $order->po_number }}</span>
                                    @endif
                                </div>
                                <p class="text-xs font-semibold text-slate-700 truncate">
                                    {{ $order->client ? $order->client->company_name : 'Unknown Client' }}@if($order->plant && $order->client && $order->client->plants->count() > 1) <span class="text-[10px] font-bold text-blue-600">({{ $order->plant->plant_name }})</span>@endif
                                </p>
                            </div>
                            <span class="shrink-0 px-2 py-0.5 text-[10px] font-black rounded-lg {{ $statusBadgeClass }}">
                                {{ $statusLabel }}
                            </span>
                        </div>

                        <!-- Ordered Items & Stock Produced Progress -->
                        <div class="space-y-1.5 pt-1 border-t border-slate-200/60">
                            @foreach($order->items as $item)
                                @php
                                    $prodStock = (int) ($item->product?->current_stock ?? 0);
                                    $orderQty = (int) $item->quantity;
                                    $isReady = $prodStock >= $orderQty;
                                    $percent = $orderQty > 0 ? max(0, min(100, round(($prodStock / $orderQty) * 100))) : 0;
                                    $missing = $prodStock < 0 ? $orderQty : max(0, $orderQty - $prodStock);
                                @endphp
                                <div class="bg-white p-2.5 rounded-lg border border-slate-200/70 space-y-1 text-xs">
                                    <div class="flex items-center justify-between font-medium">
                                        <span class="text-slate-800 font-bold truncate max-w-[200px]" title="{{ $item->product?->product_name }}">
                                            {{ $item->product?->product_name ?? 'Unknown Item' }}
                                        </span>
                                        <span class="font-mono text-[11px] text-slate-600">
                                            Order: <span class="font-bold text-slate-900">{{ $orderQty }}</span> {{ $item->billing_uom ?? 'pcs' }}
                                        </span>
                                    </div>

                                    @if($trackStock)
                                        <!-- Progress Bar & Status -->
                                        <div class="flex items-center justify-between text-[10px] pt-0.5">
                                            @if($isReady)
                                                <span class="font-semibold text-emerald-700">
                                                    ✅ Available Stock: {{ $prodStock }} {{ $item->billing_uom ?? 'pcs' }}
                                                </span>
                                            @elseif($prodStock < 0)
                                                <span class="font-semibold text-rose-600 font-mono">
                                                    ⚠️ Live Deficit: {{ $prodStock }} (Order Needs: {{ $orderQty }})
                                                </span>
                                            @else
                                                <span class="font-semibold text-amber-700 font-mono">
                                                    ⚠️ Stock: {{ $prodStock }} / {{ $orderQty }} (Short: {{ $missing }})
                                                </span>
                                            @endif

                                            @if(!$isReady && $item->product_id && $isProdModuleActive)
                                                <a href="/production?open=1&product_id={{ $item->product_id }}" 
                                                   class="px-2 py-0.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded shadow-2xs text-[9px] transition shrink-0 ml-1">
                                                    + Produce
                                                </a>
                                            @endif
                                        </div>

                                        <!-- Visual Progress Bar -->
                                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                            <div class="h-1.5 rounded-full transition-all duration-300 {{ $isReady ? 'bg-emerald-500' : ($prodStock > 0 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                                 style="width: {{ $percent }}%"></div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <!-- Order Notes (Visible only if available) -->
                        @if(!empty(trim($order->notes ?? '')))
                            <div class="px-2.5 py-1.5 bg-amber-50/80 dark:bg-amber-950/40 rounded-lg border border-amber-200/70 dark:border-amber-800/50 text-[10.5px] text-amber-900 dark:text-amber-200 flex items-start space-x-1.5 shadow-2xs">
                                <span class="shrink-0 text-xs">📝</span>
                                <span class="italic font-medium line-clamp-2" title="{{ $order->notes }}">{{ $order->notes }}</span>
                            </div>
                        @endif

                        <!-- Delivery Date & Quick Action -->
                        <div class="flex items-center justify-between text-[10px] text-slate-500 pt-1">
                            <span>
                                @if($order->delivery_date)
                                    📅 Due: <strong class="text-slate-700">{{ $order->delivery_date->format('d M Y') }}</strong>
                                @else
                                    📅 Date: <strong class="text-slate-700">{{ $order->order_date ? $order->order_date->format('d M Y') : 'N/A' }}</strong>
                                @endif
                            </span>
                            <a href="/orders?search={{ $order->order_number }}" class="text-blue-600 hover:text-blue-800 font-bold underline">
                                View Order ➜
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-6 text-center text-xs text-slate-500 space-y-1">
                <div class="text-2xl">📦</div>
                <p class="font-bold text-slate-700">No Active Orders Pending</p>
                <p class="text-[11px] text-slate-400">All current client orders have been dispatched or completed.</p>
            </div>
        @endif

        <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-blue-600">
            <a href="/orders" class="hover:text-blue-800 flex items-center gap-1">
                <span>All Orders</span>
                <span>➜</span>
            </a>
            @if($isProdModuleActive)
                <a href="/production" class="hover:text-blue-800 flex items-center gap-1 text-slate-600 hover:text-blue-700">
                    <span>Production Runs</span>
                    <span>➜</span>
                </a>
            @endif
        </div>
    </div>
</div>
