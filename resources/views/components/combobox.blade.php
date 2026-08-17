@props([
    'name',
    'id' => null,
    'label' => null,
    'placeholder' => 'Search options...',
    'options' => [],
    'value' => '',
    'required' => false,
    'allowCustom' => false,
    'containerClass' => 'relative',
    'inputClass' => 'w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-800 font-bold transition shadow-xs placeholder:font-normal placeholder:text-slate-400',
    'dropdownClass' => 'hidden absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl z-50 max-h-60 overflow-y-auto divide-y divide-slate-100 text-xs transition-all'
])

@php
    $elementId = $id ?? 'cb_' . str_replace(['[', ']', '.'], '_', $name) . '_' . uniqid();
    $selectedValue = old($name, $value);
    $selectedLabel = '';
    if ($selectedValue !== null && $selectedValue !== '') {
        foreach ($options as $opt) {
            $optValue = is_array($opt) ? ($opt['value'] ?? '') : $opt;
            $optLabel = is_array($opt) ? ($opt['label'] ?? '') : $opt;
            if ((string)$optValue === (string)$selectedValue) {
                $selectedLabel = $optLabel;
                break;
            }
        }
        if (!$selectedLabel && $allowCustom) {
            $selectedLabel = $selectedValue;
        }
    }
@endphp

<div id="{{ $elementId }}_wrapper" class="combobox-wrapper {{ $containerClass }}" data-combobox-id="{{ $elementId }}" data-allow-custom="{{ $allowCustom ? 'true' : 'false' }}" data-required="{{ $required ? 'true' : 'false' }}">
    @if($label)
        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">
            {!! $label !!}
            @if($required && !str_contains($label, '*'))
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <!-- Hidden Input for Form Submit -->
    <input type="hidden" name="{{ $name }}" id="{{ $elementId }}_hidden" value="{{ $selectedValue }}" class="combobox-hidden-input" {{ $required ? 'required' : '' }}>

    <!-- Text Search Input Box -->
    <div class="relative">
        <input type="text" 
               id="{{ $elementId }}_search"
               autocomplete="off"
               placeholder="{{ $placeholder }}"
               value="{{ $selectedLabel }}"
               class="combobox-search-input {{ $inputClass }}"
               {{ $required ? 'data-required=true' : '' }}>
        
        <button type="button" 
                id="{{ $elementId }}_clear" 
                class="combobox-clear-btn {{ $selectedLabel ? '' : 'hidden' }} absolute right-2.5 top-2 text-slate-400 hover:text-red-500 transition text-xs font-bold p-0.5 rounded" 
                title="Clear selection">
            ✕
        </button>
    </div>

    <!-- Dropdown Menu -->
    <div id="{{ $elementId }}_dropdown" class="combobox-dropdown {{ $dropdownClass }}">
        @foreach($options as $opt)
            @php
                $optValue = is_array($opt) ? ($opt['value'] ?? '') : $opt;
                $optLabel = is_array($opt) ? ($opt['label'] ?? '') : $opt;
                $optSearch = is_array($opt) ? ($opt['search'] ?? strtolower($optLabel)) : strtolower($optLabel);
                $optBadge = is_array($opt) ? ($opt['badge'] ?? null) : null;
                $dataAttrs = '';
                if (is_array($opt) && isset($opt['data']) && is_array($opt['data'])) {
                    foreach ($opt['data'] as $dk => $dv) {
                        $dataAttrs .= ' data-' . $dk . '="' . e($dv) . '"';
                    }
                }
            @endphp
            <div class="combobox-option px-3 py-2.5 cursor-pointer hover:bg-blue-50 transition flex items-center justify-between text-slate-700"
                 data-value="{{ $optValue }}"
                 data-label="{{ $optLabel }}"
                 data-search="{{ strtolower($optSearch) }}"
                 {!! $dataAttrs !!}>
                <div class="truncate">
                    <span class="font-bold text-slate-800">{{ $optLabel }}</span>
                </div>
                @if($optBadge)
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 uppercase shrink-0 ml-2">{{ $optBadge }}</span>
                @endif
            </div>
        @endforeach
        <div class="combobox-no-match hidden px-3 py-3 text-center text-slate-400 font-medium italic">
            No matching options
        </div>
    </div>
</div>
