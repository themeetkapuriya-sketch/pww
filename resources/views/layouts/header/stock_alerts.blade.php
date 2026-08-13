<!-- Low Stock Alert Notification Widget -->
@php
    $lowCount = $headerLowStock['total_count'] ?? 0;
    $lowRaw = $headerLowStock['raw_materials'] ?? collect();
    $lowProd = $headerLowStock['products'] ?? collect();
@endphp
<div class="relative inline-block text-left group" id="lowStockDropdownWrapper">
    <button type="button" 
            id="lowStockAlertBtn"
            onclick="toggleLowStockDropdown(event)"
            class="relative p-2 rounded-xl bg-slate-100/80 hover:bg-slate-200/80 text-slate-600 transition cursor-pointer border border-slate-200/80 {{ $lowCount > 0 ? 'text-amber-600 hover:text-amber-700 ring-2 ring-amber-300/60' : '' }}" 
            title="{{ $lowCount > 0 ? $lowCount . ' items are low on stock!' : 'Stock levels optimal' }}">
        <svg class="w-4 h-4 {{ $lowCount > 0 ? 'text-amber-600' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if($lowCount > 0)
            <span class="absolute -top-1.5 -right-1.5 px-1.5 py-0.2 bg-rose-500 text-white font-black text-[10px] rounded-full shadow-xs border border-white animate-pulse">
                {{ $lowCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown Card -->
    <div id="headerLowStockCard" 
         class="absolute right-0 top-full mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-slate-200/90 p-4 space-y-3 hidden transition-all duration-200 z-50">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <div class="flex items-center space-x-2">
                <span class="p-1.5 rounded-lg {{ $lowCount > 0 ? 'bg-amber-50 text-amber-600 border border-amber-200' : 'bg-emerald-50 text-emerald-600 border border-emerald-200' }}">
                    @if($lowCount > 0)
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    @endif
                </span>
                <div>
                    <h4 class="text-xs font-bold text-slate-800">Inventory Stock Alerts</h4>
                    <p class="text-[10px] text-slate-500 font-medium">
                        {{ $lowCount > 0 ? $lowCount . ' item(s) below safe minimum' : 'All warehouse stock is healthy' }}
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeLowStockDropdown()" class="text-slate-400 hover:text-slate-600 font-bold text-xs p-1 cursor-pointer">&times; Close</button>
        </div>

        @if($lowCount > 0)
            <div class="max-h-64 overflow-y-auto space-y-2 pr-1 custom-scrollbar text-xs">
                @if($lowRaw->count() > 0)
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-wider px-1">Raw Materials</div>
                    @foreach($lowRaw as $rm)
                        <div class="p-2.5 bg-amber-50/60 rounded-xl border border-amber-200/80 flex items-center justify-between">
                            <div class="min-w-0 pr-2">
                                <p class="font-bold text-slate-800 truncate">{{ $rm->material_name }}</p>
                                <p class="text-[10px] text-amber-700 font-semibold font-mono">
                                    Live: <span class="font-black text-rose-600">{{ number_format($rm->current_stock, 2) }} {{ $rm->unit ?? 'kg' }}</span>
                                    (Min: {{ number_format($rm->safety_threshold > 0 ? $rm->safety_threshold : 50, 0) }})
                                </p>
                            </div>
                            <a href="/purchases?open=1&material_id={{ $rm->id }}" class="shrink-0 px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white font-bold text-[10px] rounded-lg shadow-2xs transition">
                                + Order
                            </a>
                        </div>
                    @endforeach
                @endif

                @if($lowProd->count() > 0)
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-wider px-1 pt-1">Finished Goods</div>
                    @foreach($lowProd as $pr)
                        <div class="p-2.5 bg-blue-50/60 rounded-xl border border-blue-200/80 flex items-center justify-between">
                            <div class="min-w-0 pr-2">
                                <p class="font-bold text-slate-800 truncate">{{ $pr->product_name }}</p>
                                <p class="text-[10px] text-blue-700 font-semibold font-mono">
                                    Live: <span class="font-black text-rose-600">{{ $pr->current_stock }} {{ $pr->uom ?? 'pcs' }}</span>
                                    (Min: {{ $pr->safety_threshold > 0 ? $pr->safety_threshold : 10 }})
                                </p>
                            </div>
                            <a href="/production?open=1&product_id={{ $pr->id }}" class="shrink-0 px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white font-bold text-[10px] rounded-lg shadow-2xs transition">
                                + Produce
                            </a>
                        </div>
                    @endforeach
                @endif
            </div>
        @else
            <div class="py-6 text-center text-xs text-slate-500 space-y-1">
                <div class="text-2xl">🎉</div>
                <p class="font-bold text-slate-700">No Low Stock Items!</p>
                <p class="text-[11px] text-slate-400">All materials and finished products are well above minimum thresholds.</p>
            </div>
        @endif

        <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] font-bold">
            <a href="/rawmaterial" class="text-blue-600 hover:underline">Raw Materials ➡️</a>
            <a href="/product" class="text-blue-600 hover:underline">Products ➡️</a>
        </div>
    </div>
</div>
