@props([
    'title',
    'subtitle' => null,
    'actionText' => null,
    'actionId' => 'toggleFormBtn',
    'actionOnClick' => null,
    'actionUrl' => null,
    'actionIcon' => 'plus'
])

<div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-200 gap-3">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">{{ $subtitle }}</p>
        @endif
    </div>

    @if($actionText || $actionUrl)
        <div class="flex items-center space-x-3">
            @if($actionUrl)
                <a href="{{ $actionUrl }}" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-md transition duration-150 flex items-center space-x-2">
                    @if($actionIcon === 'plus')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    @endif
                    <span>{{ $actionText }}</span>
                </a>
            @else
                <button type="button" 
                        id="{{ $actionId }}"
                        @if($actionOnClick) onclick="{{ $actionOnClick }}" @endif
                        class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-md transition duration-150 flex items-center space-x-2">
                    @if($actionIcon === 'plus')
                        <svg class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    @endif
                    <span id="{{ $actionId }}Text">{{ $actionText }}</span>
                </button>
            @endif
        </div>
    @endif
</div>
