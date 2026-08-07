@if(\App\Models\Setting::get('module_purchases', 'true') === 'true')
<div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-200 space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
        <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
            <span>🛒</span> Recent 5 Purchase Bills
        </h2>
        <a href="{{ route('purchases') }}" class="text-xs font-bold text-purple-600 hover:underline">View Purchases →</a>
    </div>
    <div class="space-y-2">
        @forelse($latestPurchases as $pur)
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between hover:bg-white transition">
                <div>
                    <div class="text-xs font-black text-slate-800">
                        {{ $pur->rawMaterial->material_name ?? ($pur->item_name ?? ucfirst(str_replace('_', ' ', $pur->purchase_type))) }}
                    </div>
                    <div class="text-[11px] font-medium text-slate-500">
                        {{ $pur->purchase_date ? $pur->purchase_date->format('d M Y') : 'N/A' }}
                        @if($pur->vendor_name)
                            • <span class="font-semibold text-slate-700">{{ $pur->vendor_name }}</span>
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-xs font-black text-purple-700">₹{{ format_indian($pur->total_amount, 2) }}</div>
                    @if($pur->quantity > 0)
                        <div class="text-[10px] font-bold text-slate-400">{{ format_indian($pur->quantity, 2) }} {{ $pur->unit ?? 'Units' }}</div>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-xs text-slate-400 text-center py-4">No recent purchase records logged.</p>
        @endforelse
    </div>
</div>
@endif
