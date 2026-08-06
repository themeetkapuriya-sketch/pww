@if(\App\Models\Setting::get('module_expenses', 'true') === 'true')
<div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-200 space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
        <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
            <span>💸</span> Recent 5 Factory Expenses
        </h2>
        <a href="{{ route('expenses') }}" class="text-xs font-bold text-rose-600 hover:underline">View Expenses →</a>
    </div>
    <div class="space-y-2">
        @forelse($latestExpenses as $exp)
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between hover:bg-white transition">
                <div>
                    <div class="text-xs font-black text-slate-800 capitalize">
                        {{ $exp->expense_category === 'gst_payment' ? 'GST Payment / Tax' : str_replace('_', ' ', $exp->expense_category) }}
                    </div>
                    <div class="text-[11px] font-medium text-slate-500 truncate max-w-[200px]">
                        {{ $exp->expense_date ? $exp->expense_date->format('d M Y') : 'N/A' }}
                        @if($exp->description)
                            • {{ $exp->description }}
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-xs font-black text-rose-600">₹{{ format_indian($exp->amount, 2) }}</div>
                </div>
            </div>
        @empty
            <p class="text-xs text-slate-400 text-center py-4">No recent expense records logged.</p>
        @endforelse
    </div>
</div>
@endif
