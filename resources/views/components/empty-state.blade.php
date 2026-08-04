@props([
    'title' => 'No Records Found',
    'subtitle' => 'There are no items recorded yet.',
    'colspan' => 10
])

<tr class="empty-state-row">
    <td colspan="{{ $colspan }}" class="px-6 py-12 text-center bg-slate-50/50">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mb-3 border border-slate-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
        </div>
        <h4 class="text-sm font-bold text-slate-700">{{ $title }}</h4>
        <p class="text-xs text-slate-400 font-medium max-w-sm mx-auto mt-1">{{ $subtitle }}</p>
    </td>
</tr>
