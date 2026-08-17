@if(\App\Models\Setting::get('module_invoices', 'true') === 'true')
<div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-200 space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
        <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
            <span>🧾</span> Recent Invoices
        </h2>
        <a href="{{ route('invoices') }}" class="text-xs font-bold text-blue-600 hover:underline">All Invoices →</a>
    </div>
    <div class="space-y-2">
        @forelse($recentInvoices as $inv)
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between hover:bg-white transition">
                <div>
                    @php
                        $isRm = ($inv->invoice_mode === 'raw_material' || str_starts_with($inv->invoice_number, 'RMS-'));
                        $matNames = $isRm ? $inv->items->map(fn($i) => $i->rawMaterial->material_name ?? ($i->item_name ?: 'Scrap Material'))->filter()->unique()->implode(', ') : null;
                        $clientName = $isRm ? ($inv->custom_client_name ?: ($inv->plant->client->company_name ?? 'Local Buyer')) : ($inv->plant->client->company_name ?? 'Client');
                    @endphp
                    @if($isRm)
                        <div class="text-xs font-black text-slate-800 truncate max-w-[200px]" title="{{ $clientName }}">
                            {{ $clientName }}
                        </div>
                        <div class="text-[11px] font-semibold text-slate-500 truncate max-w-[200px]" title="Material: {{ $matNames }}">
                            {{ $matNames ?: 'Scrap / Raw Material Sale' }}
                        </div>
                    @else
                        <div class="text-xs font-black text-slate-800">
                            {{ $inv->invoice_number }}
                        </div>
                        <div class="text-[11px] font-semibold text-slate-500 truncate max-w-[180px]">
                            {{ $clientName }}
                            @if($inv->plant && $inv->plant->plant_name)
                                <span class="text-[10px] font-bold text-blue-600">({{ $inv->plant->plant_name }})</span>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="text-right">
                    <div class="text-xs font-bold text-slate-900">₹{{ format_indian($inv->total_amount, 2) }}</div>
                    @if(($inv->payment_status ?? 'unpaid') === 'paid')
                        <span class="inline-block text-[10px] font-extrabold px-2 py-0.5 rounded-full mt-0.5 bg-emerald-100 text-emerald-800 border border-emerald-300">
                            RECEIVED
                        </span>
                    @else
                        <button type="button" 
                                id="dash-pay-btn-{{ $inv->id }}"
                                onclick="openDashboardPayModal({{ $inv->id }}, '{{ $inv->invoice_number }}', {{ $inv->remaining_balance }})"
                                class="inline-block text-[10px] font-extrabold px-2 py-0.5 rounded-full mt-0.5 transition cursor-pointer border shadow-2xs 
                                {{ $inv->payment_status === 'partially_paid' ? 'bg-amber-100 text-amber-800 border-amber-300 hover:bg-amber-200' : 'bg-rose-100 text-rose-800 border-rose-300 hover:bg-rose-200' }}"
                                title="Click to record payment directly from Dashboard">
                            {{ $inv->payment_status === 'partially_paid' ? 'PARTIAL (₹' . format_indian($inv->remaining_balance, 0) . ' DUE)' : 'DUE (₹' . format_indian($inv->remaining_balance, 0) . ')' }}
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-xs text-slate-400 text-center py-4">No recent invoices logged.</p>
        @endforelse
    </div>
</div>
@endif
