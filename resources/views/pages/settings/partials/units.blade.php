<!-- Sub Content 3.6: Measurement Units (UOM) Partial -->
@php
    $measurementUnitsList = \App\Services\UnitService::getUnits();
    $unitTypesMap = [
        'count' => ['label' => 'Count / Pieces', 'color' => 'blue', 'icon' => '🔢'],
        'weight' => ['label' => 'Weight', 'color' => 'emerald', 'icon' => '⚖️'],
        'length' => ['label' => 'Length & Dimensions', 'color' => 'indigo', 'icon' => '📏'],
        'volume' => ['label' => 'Volume & Liquids', 'color' => 'sky', 'icon' => '🧪'],
        'packaging' => ['label' => 'Packaging & Bundling', 'color' => 'amber', 'icon' => '📦'],
        'area' => ['label' => 'Area / Surface', 'color' => 'purple', 'icon' => '📐'],
    ];
@endphp
<div id="subTab-units" class="sub-tab-content {{ ($activeSubTab ?? 'serials') === 'units' ? '' : 'hidden' }} space-y-6">
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
            <div>
                <h2 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Units of Measure (UOM) & GST Codes
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manage measurement units used across Raw Materials, Products, BOM, Invoices, and GST E-Way Bills.</p>
            </div>
            <button type="button" onclick="openAddUnitModal()" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2.5 px-4 rounded-xl transition flex items-center gap-1.5 shadow-xs cursor-pointer shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Add Measurement Unit</span>
            </button>
        </div>

        <div id="measurement-units-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach ($measurementUnitsList as $unit)
                @php
                    $isProtected = ($unit['protected'] ?? false) || in_array($unit['key'], ['kg', 'pcs', 'nos']);
                    $typeKey = $unit['type'] ?? 'count';
                    $typeInfo = $unitTypesMap[$typeKey] ?? ['label' => ucfirst($typeKey), 'color' => 'slate', 'icon' => '📦'];
                @endphp
                <div id="unit-item-{{ $unit['key'] }}" class="flex items-center justify-between p-3.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/80 rounded-xl text-xs hover:border-slate-300 dark:hover:border-slate-600 transition shadow-2xs">
                    <div class="space-y-1 min-w-0 pr-2">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="font-extrabold text-slate-800 dark:text-slate-100 text-sm unit-name-text">{{ $unit['name'] }}</span>
                            <span class="text-[10px] font-mono bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200/80 dark:border-blue-800/60 px-2 py-0.5 rounded-md font-bold">
                                {{ $unit['symbol'] }}
                            </span>
                            @if($isProtected)
                                <span class="text-[9.5px] font-extrabold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/60 dark:border-amber-700/50 px-1.5 py-0.2 rounded inline-flex items-center gap-0.5" title="Core System Unit">
                                    🔒 Core
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 text-[10.5px] text-slate-500 dark:text-slate-400 flex-wrap">
                            <span class="inline-flex items-center gap-1">
                                <span>{{ $typeInfo['icon'] }}</span>
                                <span>{{ $typeInfo['label'] }}</span>
                            </span>
                            <span>•</span>
                            <span class="font-mono font-bold text-slate-700 dark:text-slate-300">GST UQC: {{ $unit['uqc'] ?? 'NOS' }}</span>
                            <span>•</span>
                            <span>{{ (int)($unit['precision'] ?? 2) }} Decimals</span>
                        </div>
                    </div>

                    <div class="flex items-center space-x-1.5 shrink-0">
                        <!-- Edit Button (Blue) -->
                        <button type="button" 
                                onclick="openEditUnitModal('{{ $unit['key'] }}', '{{ addslashes($unit['name']) }}', '{{ addslashes($unit['symbol']) }}', '{{ $unit['uqc'] ?? 'NOS' }}', '{{ $unit['type'] ?? 'count' }}', {{ (int)($unit['precision'] ?? 2) }})" 
                                class="w-7 h-7 inline-flex items-center justify-center bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white dark:bg-blue-950/50 dark:border-blue-800/60 dark:text-blue-400 dark:hover:bg-blue-600 dark:hover:text-white rounded-lg border border-blue-200/80 transition duration-150 transform hover:scale-105 cursor-pointer shadow-2xs" 
                                title="Edit Unit">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>

                        @if(!$isProtected)
                            <!-- Delete Button (Red) -->
                            <button type="button" 
                                    onclick="deleteMeasurementUnit('{{ $unit['key'] }}', '{{ addslashes($unit['name']) }}')" 
                                    class="w-7 h-7 inline-flex items-center justify-center bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white dark:bg-rose-950/50 dark:border-rose-800/60 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white rounded-lg border border-rose-200/80 transition duration-150 transform hover:scale-105 cursor-pointer shadow-2xs" 
                                    title="Delete Unit">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        @else
                            <!-- Lock Icon for Protected Core Units -->
                            <span class="w-7 h-7 inline-flex items-center justify-center bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 rounded-lg border border-amber-200/80 dark:border-amber-800/60 shadow-2xs" title="Core System Unit (Protected from Deletion)">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Modal: Add / Edit Measurement Unit -->
<div id="unitModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-800 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <h3 id="unitModalTitle" class="text-base font-bold text-slate-800 dark:text-slate-100">Add Measurement Unit</h3>
            <button onclick="closeUnitModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-bold text-lg cursor-pointer">&times;</button>
        </div>
        <form id="unitForm" action="{{ route('settings.units.store') }}" method="POST" class="space-y-4" onsubmit="return handleUnitFormSubmit(event);">
            @csrf
            <input type="hidden" name="key" id="unitKeyInput">

            <div>
                <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Unit Full Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" id="unitNameInput" required placeholder="e.g. Kilograms, Pieces, Meters" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl py-2 px-3 text-sm font-semibold text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Short Symbol <span class="text-rose-500">*</span></label>
                    <input type="text" name="symbol" id="unitSymbolInput" required placeholder="e.g. kg, pcs, mtr" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl py-2 px-3 text-sm font-mono font-semibold text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition lowercase">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">GST UQC Code <span class="text-rose-500">*</span></label>
                    <input type="text" name="uqc" id="unitUqcInput" required placeholder="e.g. KGS, NOS, MTR" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl py-2 px-3 text-sm font-mono font-semibold text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition uppercase">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Measurement Type</label>
                    <select name="type" id="unitTypeSelect" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl py-2 px-3 text-xs font-bold text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:border-blue-500 transition">
                        <option value="count">Count / Pieces</option>
                        <option value="weight">Weight</option>
                        <option value="length">Length</option>
                        <option value="volume">Volume / Liquid</option>
                        <option value="packaging">Packaging / Box</option>
                        <option value="area">Area / Surface</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase mb-1">Decimal Digits</label>
                    <select name="precision" id="unitPrecisionSelect" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl py-2 px-3 text-xs font-bold text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:border-blue-500 transition">
                        <option value="0">0 (Whole units e.g. 5 pcs)</option>
                        <option value="2">2 (e.g. 12.50 meters)</option>
                        <option value="3">3 (e.g. 1.250 liters)</option>
                        <option value="4">4 (e.g. 0.0050 kg / high-precision)</option>
                    </select>
                </div>
            </div>

            <div class="pt-2 flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeUnitModal()" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer">Cancel</button>
                <button type="submit" id="unitSubmitBtn" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2 px-5 rounded-xl transition shadow-xs cursor-pointer">Save Unit</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddUnitModal() {
    document.getElementById('unitModalTitle').innerText = 'Add Measurement Unit';
    document.getElementById('unitSubmitBtn').innerText = 'Create Unit';
    document.getElementById('unitForm').action = '{{ route("settings.units.store") }}';
    document.getElementById('unitKeyInput').value = '';
    document.getElementById('unitNameInput').value = '';
    document.getElementById('unitSymbolInput').value = '';
    document.getElementById('unitUqcInput').value = 'NOS';
    document.getElementById('unitTypeSelect').value = 'count';
    document.getElementById('unitPrecisionSelect').value = '0';
    document.getElementById('unitModal').classList.remove('hidden');
    document.getElementById('unitNameInput').focus();
}

function openEditUnitModal(key, name, symbol, uqc, type, precision) {
    document.getElementById('unitModalTitle').innerText = 'Edit Measurement Unit';
    document.getElementById('unitSubmitBtn').innerText = 'Update Unit';
    document.getElementById('unitForm').action = '{{ route("settings.units.update") }}';
    document.getElementById('unitKeyInput').value = key;
    document.getElementById('unitNameInput').value = name;
    document.getElementById('unitSymbolInput').value = symbol;
    document.getElementById('unitUqcInput').value = uqc;
    document.getElementById('unitTypeSelect').value = type;
    document.getElementById('unitPrecisionSelect').value = precision;
    document.getElementById('unitModal').classList.remove('hidden');
    document.getElementById('unitNameInput').focus();
}

function closeUnitModal() {
    document.getElementById('unitModal').classList.add('hidden');
}

function handleUnitFormSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('unitSubmitBtn');
    const oldText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Saving...';

    $.ajax({
        url: form.action,
        type: 'POST',
        data: $(form).serialize(),
        success: function(response) {
            btn.disabled = false;
            btn.innerHTML = oldText;
            if (response.success) {
                closeUnitModal();
                if (typeof window.showToast === 'function') {
                    window.showToast('success', response.message);
                }
                setTimeout(() => {
                    window.location.reload();
                }, 400);
            } else {
                if (typeof window.showToast === 'function') {
                    window.showToast('error', response.message || 'Error saving unit.');
                } else {
                    alert(response.message || 'Error saving unit.');
                }
            }
        },
        error: function(xhr) {
            btn.disabled = false;
            btn.innerHTML = oldText;
            const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error processing request.';
            if (typeof window.showToast === 'function') {
                window.showToast('error', msg);
            } else {
                alert(msg);
            }
        }
    });
}

function deleteMeasurementUnit(key, name) {
    window.confirmDelete(
        `Delete Unit '${name}'?`,
        `Are you sure you want to delete the '${name}' measurement unit from the system?`,
        function() {
            $.ajax({
                url: '{{ route("settings.units.delete") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    key: key
                },
                success: function(response) {
                    if (response.success) {
                        $(`#unit-item-${key}`).fadeOut(300, function() { $(this).remove(); });
                        if (typeof window.showToast === 'function') {
                            window.showToast('success', response.message);
                        }
                    } else {
                        if (typeof window.showToast === 'function') {
                            window.showToast('error', response.message);
                        } else {
                            alert(response.message);
                        }
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error deleting unit.';
                    if (typeof window.showToast === 'function') {
                        window.showToast('error', msg);
                    } else {
                        alert(msg);
                    }
                }
            });
        }
    );
}
</script>
