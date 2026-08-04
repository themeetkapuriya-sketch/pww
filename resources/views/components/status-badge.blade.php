@props([
    'status' => 'pending',
    'label' => null,
    'size' => 'normal'
])

@php
    $normalized = strtolower(trim($status));
    
    $config = match($normalized) {
        'pending' => [
            'text' => 'Pending',
            'bg' => 'bg-amber-50 text-amber-700 border-amber-200',
            'dot' => 'bg-amber-500'
        ],
        'in_production', 'production' => [
            'text' => 'In Production',
            'bg' => 'bg-blue-50 text-blue-700 border-blue-200',
            'dot' => 'bg-blue-500'
        ],
        'ready_for_dispatch', 'ready' => [
            'text' => 'Ready for Dispatch',
            'bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'dot' => 'bg-indigo-500'
        ],
        'dispatched' => [
            'text' => 'Dispatched',
            'bg' => 'bg-teal-50 text-teal-700 border-teal-200',
            'dot' => 'bg-teal-500'
        ],
        'completed', 'paid' => [
            'text' => $normalized === 'paid' ? 'Paid' : 'Completed',
            'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'dot' => 'bg-emerald-500'
        ],
        'unpaid', 'overdue' => [
            'text' => $normalized === 'overdue' ? 'Overdue' : 'Unpaid',
            'bg' => 'bg-rose-50 text-rose-700 border-rose-200',
            'dot' => 'bg-rose-500'
        ],
        'cancelled' => [
            'text' => 'Cancelled',
            'bg' => 'bg-slate-100 text-slate-600 border-slate-200',
            'dot' => 'bg-slate-400'
        ],
        default => [
            'text' => ucfirst(str_replace('_', ' ', $normalized)),
            'bg' => 'bg-slate-50 text-slate-700 border-slate-200',
            'dot' => 'bg-slate-500'
        ]
    };

    $displayText = $label ?? $config['text'];
    $sizeClasses = $size === 'small' ? 'px-2 py-0.5 text-[10px]' : 'px-2.5 py-1 text-xs';
@endphp

<span class="inline-flex items-center space-x-1.5 font-bold border rounded-full shrink-0 {{ $config['bg'] }} {{ $sizeClasses }}">
    <span class="w-1.5 h-1.5 rounded-full {{ $config['dot'] }}"></span>
    <span>{{ $displayText }}</span>
</span>
