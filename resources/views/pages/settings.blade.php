@extends('layouts.app')

@section('title', 'System Settings & User Access')

@section('content')
@php
    $reqTab = request('tab', 'profile');
    $reqSub = request('sub');
    $subTabKeys = ['serials', 'financial', 'email', 'categories', 'units', 'security'];
    if (in_array($reqTab, $subTabKeys)) {
        $activeMainTab = 'other';
        $activeSubTab = $reqTab;
    } elseif ($reqTab === 'other') {
        $activeMainTab = 'other';
        $activeSubTab = in_array($reqSub, $subTabKeys) ? $reqSub : 'serials';
    } else {
        $activeMainTab = in_array($reqTab, ['profile', 'bank', 'users', 'modules']) ? $reqTab : 'profile';
        $activeSubTab = null;
    }
@endphp
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                System Settings & User Access Hub
            </h1>
            <p class="text-slate-500 text-xs font-medium mt-1">Configure active ERP modules, manage team login accounts, set role permissions, and customize business branding</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="bg-white rounded-2xl p-2 border border-slate-200/80 shadow-sm flex flex-wrap gap-2">
        <button onclick="switchSettingsTab('profile')" id="tabBtn-profile" class="tab-btn {{ $activeMainTab === 'profile' ? 'active-tab-btn' : 'text-slate-600 hover:bg-slate-50' }} flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <span>Business Profile</span>
        </button>

        <button onclick="switchSettingsTab('bank')" id="tabBtn-bank" class="tab-btn {{ $activeMainTab === 'bank' ? 'active-tab-btn' : 'text-slate-600 hover:bg-slate-50' }} flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            <span>Bank & Billing</span>
        </button>

        <button onclick="switchSettingsTab('users')" id="tabBtn-users" class="tab-btn {{ $activeMainTab === 'users' ? 'active-tab-btn' : 'text-slate-600 hover:bg-slate-50' }} flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span>User Roles</span>
        </button>

        <button onclick="switchSettingsTab('modules')" id="tabBtn-modules" class="tab-btn {{ $activeMainTab === 'modules' ? 'active-tab-btn' : 'text-slate-600 hover:bg-slate-50' }} flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
            </svg>
            <span>Active Modules</span>
        </button>

        <!-- TAB 5: Other Settings Dropdown Menu Button -->
        <div class="relative inline-block text-left" id="otherSettingsDropdownWrapper">
            <button onclick="toggleOtherSettingsDropdown(event)" id="tabBtn-other" class="tab-btn {{ $activeMainTab === 'other' ? 'active-tab-btn' : 'text-slate-600 hover:bg-slate-50' }} flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition cursor-pointer">
                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                </svg>
                @php
                    $subLabelsMap = [
                        'serials' => 'Auto Serial & Prefixes',
                        'financial' => 'Tax & Financial',
                        'email' => 'Email (SMTP)',
                        'categories' => 'Purchase & Expense Categories',
                        'units' => 'Measurement Units (UOM)',
                        'security' => 'Security & Backups'
                    ];
                    $currentOtherLabel = $activeMainTab === 'other' && isset($subLabelsMap[$activeSubTab]) ? $subLabelsMap[$activeSubTab] : 'Other Settings';
                @endphp
                <span id="otherSettingsTabLabel">{{ $currentOtherLabel }}</span>
                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Dropdown Menu Popup -->
            <div id="otherSettingsDropdownMenu" class="hidden absolute right-0 mt-2 w-72 rounded-2xl bg-white dark:bg-slate-800 shadow-xl border border-slate-200/90 dark:border-slate-700 p-1.5 z-50">
                <button type="button" onclick="selectOtherSettingsSub('serials')" id="otherOpt-serials" class="other-opt-btn {{ $activeSubTab === 'serials' ? 'active-sub-tab-btn bg-blue-50 text-blue-700' : '' }} w-full flex items-center gap-2.5 px-3 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-slate-700/80 hover:text-blue-700 dark:hover:text-blue-400 transition rounded-xl cursor-pointer">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                    </svg>
                    <span class="whitespace-nowrap">Auto Serial & Prefixes</span>
                </button>

                <button type="button" onclick="selectOtherSettingsSub('financial')" id="otherOpt-financial" class="other-opt-btn {{ $activeSubTab === 'financial' ? 'active-sub-tab-btn bg-blue-50 text-blue-700' : '' }} w-full flex items-center gap-2.5 px-3 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-slate-700/80 hover:text-blue-700 dark:hover:text-blue-400 transition rounded-xl cursor-pointer">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="whitespace-nowrap">Tax & Financial</span>
                </button>

                <button type="button" onclick="selectOtherSettingsSub('email')" id="otherOpt-email" class="other-opt-btn {{ $activeSubTab === 'email' ? 'active-sub-tab-btn bg-blue-50 text-blue-700' : '' }} w-full flex items-center gap-2.5 px-3 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-slate-700/80 hover:text-blue-700 dark:hover:text-blue-400 transition rounded-xl cursor-pointer">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span class="whitespace-nowrap">Email (SMTP)</span>
                </button>

                <button type="button" onclick="selectOtherSettingsSub('categories')" id="otherOpt-categories" class="other-opt-btn {{ $activeSubTab === 'categories' ? 'active-sub-tab-btn bg-blue-50 text-blue-700' : '' }} w-full flex items-center gap-2.5 px-3 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-slate-700/80 hover:text-blue-700 dark:hover:text-blue-400 transition rounded-xl cursor-pointer">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span class="whitespace-nowrap">Purchase & Expense Categories</span>
                </button>

                <button type="button" onclick="selectOtherSettingsSub('units')" id="otherOpt-units" class="other-opt-btn {{ $activeSubTab === 'units' ? 'active-sub-tab-btn bg-blue-50 text-blue-700' : '' }} w-full flex items-center gap-2.5 px-3 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-slate-700/80 hover:text-blue-700 dark:hover:text-blue-400 transition rounded-xl cursor-pointer">
                    <svg class="w-4 h-4 text-teal-600 dark:text-teal-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span class="whitespace-nowrap">Measurement Units (UOM)</span>
                </button>

                <button type="button" onclick="selectOtherSettingsSub('security')" id="otherOpt-security" class="other-opt-btn {{ $activeSubTab === 'security' ? 'active-sub-tab-btn bg-blue-50 text-blue-700' : '' }} w-full flex items-center gap-2.5 px-3 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-slate-700/80 hover:text-blue-700 dark:hover:text-blue-400 transition rounded-xl cursor-pointer">
                    <svg class="w-4 h-4 text-purple-600 dark:text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span class="whitespace-nowrap">Security & Backups</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modular Tab Partials -->
    @include('pages.settings.partials.profile')
    @include('pages.settings.partials.bank')
    @include('pages.settings.partials.users')
    @include('pages.settings.partials.modules')

    <!-- TAB 5: Other System Settings Partial Container -->
    <div id="settingsTab-other" class="tab-content {{ $activeMainTab === 'other' ? '' : 'hidden' }} space-y-6">
        @include('pages.settings.partials.serials')
        @include('pages.settings.partials.financial')
        @include('pages.settings.partials.email')
        @include('pages.settings.partials.categories')
        @include('pages.settings.partials.units')
        @include('pages.settings.partials.security')
    </div>
</div>

<!-- Modal: Add New Role -->
<div id="addRoleModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 border border-slate-200 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-800">Add New Custom Role</h3>
            <button onclick="closeAddRoleModal()" class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer">&times;</button>
        </div>
        <form id="addRoleForm" action="{{ route('settings.roles.store') }}" method="POST" class="space-y-4" onsubmit="return handleAddRoleSubmit(event);">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Role Name <span class="text-rose-500">*</span></label>
                <input type="text" name="role_name" required placeholder="e.g. Production Manager" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
            </div>
            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="closeAddRoleModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer">Cancel</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2 px-5 rounded-xl transition shadow-xs cursor-pointer">Create Role</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add/Edit Category -->
<div id="categoryModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 border border-slate-200 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 id="categoryModalTitle" class="text-base font-bold text-slate-800">Add New Category</h3>
            <button onclick="closeCategoryModal()" class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer">&times;</button>
        </div>
        <form id="categoryForm" action="{{ route('settings.categories.store') }}" method="POST" class="space-y-4" onsubmit="return handleCategoryFormSubmit(event);">
            @csrf
            <input type="hidden" name="type" id="categoryTypeInput">
            <input type="hidden" name="key" id="categoryKeyInput">
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Category Label / Name <span class="text-rose-500">*</span></label>
                <input type="text" name="label" id="categoryLabelInput" required placeholder="e.g. Factory Repairs" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
            </div>
            <div id="categoryIconWrapper" class="hidden space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-600 uppercase">Category Icon / Emoji</label>
                    <span class="text-[10px] text-slate-400 font-medium">Click icon or press <kbd class="px-1 py-0.5 bg-slate-100 border border-slate-300 rounded font-mono text-[9px] font-bold text-slate-600">Win + .</kbd></span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-xl shrink-0" id="categoryIconPreview">📦</div>
                    <input type="text" name="icon" id="categoryIconInput" placeholder="e.g. 🎨 or 🔩 or 📦"
                           oninput="document.getElementById('categoryIconPreview').innerText = this.value || '📦'"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                </div>
                
                <!-- Quick Emoji Chips Grid -->
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Quick Select Popular Material Icons:</span>
                    <div class="grid grid-cols-8 gap-1.5 p-2 bg-slate-50 border border-slate-200/80 rounded-xl">
                        @php
                            $quickEmojis = [
                                ['icon' => '🔩', 'title' => 'Pipes / Metal / Screws'],
                                ['icon' => '🎨', 'title' => 'Powder / Paints'],
                                ['icon' => '📐', 'title' => 'Sheets / Plates'],
                                ['icon' => '⚡', 'title' => 'Welding & Gas'],
                                ['icon' => '🔧', 'title' => 'Hardware & Tools'],
                                ['icon' => '📦', 'title' => 'Packages & Cartons'],
                                ['icon' => '🧪', 'title' => 'Chemicals & Liquids'],
                                ['icon' => '🪵', 'title' => 'Wood & Pallets'],
                                ['icon' => '🔲', 'title' => 'Rubber & Seals'],
                                ['icon' => '⚙️', 'title' => 'Gears & Machinery'],
                                ['icon' => '🪚', 'title' => 'Blades & Cutting'],
                                ['icon' => '🧲', 'title' => 'Magnets & Alloys'],
                                ['icon' => '🛡️', 'title' => 'Safety & Protective'],
                                ['icon' => '🏷️', 'title' => 'Labels & Tags'],
                                ['icon' => '🛢️', 'title' => 'Oil & Drums'],
                                ['icon' => '🧵', 'title' => 'Threads & Ropes'],
                            ];
                        @endphp
                        @foreach($quickEmojis as $qEmoji)
                            <button type="button" 
                                    onclick="selectQuickCategoryEmoji('{{ $qEmoji['icon'] }}')"
                                    title="{{ $qEmoji['title'] }}"
                                    class="w-8 h-8 rounded-lg bg-white border border-slate-200 hover:border-blue-500 hover:bg-blue-50 hover:scale-110 transition flex items-center justify-center text-base cursor-pointer shadow-2xs">
                                {{ $qEmoji['icon'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="closeCategoryModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer">Cancel</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2 px-5 rounded-xl transition shadow-xs cursor-pointer">Save Category</button>
            </div>
        </form>
    </div>
</div>

<script>
// --- Navigation Tab Switching ---
const OTHER_SUB_TABS = ['serials', 'financial', 'email', 'categories', 'security'];

window.switchSettingsTab = function(tabKey) {
    if (OTHER_SUB_TABS.includes(tabKey)) {
        selectOtherSettingsSub(tabKey);
        return;
    }

    document.querySelectorAll('.tab-content').forEach(el => {
        el.classList.add('hidden');
        el.style.opacity = '0';
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active-tab-btn');
        btn.classList.add('text-slate-600');
    });

    const targetTab = document.getElementById(`settingsTab-${tabKey}`);
    const targetBtn = document.getElementById(`tabBtn-${tabKey}`);

    if (targetTab) {
        targetTab.classList.remove('hidden');
        targetTab.style.opacity = '1';
    }

    if (targetBtn) {
        targetBtn.classList.add('active-tab-btn');
        targetBtn.classList.remove('text-slate-600');
    }

    if (tabKey === 'other') {
        const activeSub = document.querySelector('.sub-tab-content:not(.hidden)');
        if (!activeSub) {
            selectOtherSettingsSub('serials');
        }
    }

    try {
        const url = new URL(window.location);
        url.searchParams.set('tab', tabKey);
        if (tabKey !== 'other') {
            url.searchParams.delete('sub');
        }
        window.history.replaceState({}, '', url);
    } catch(e) {}
};

window.toggleOtherSettingsDropdown = function(e) {
    e.stopPropagation();
    const menu = document.getElementById('otherSettingsDropdownMenu');
    if (menu) menu.classList.toggle('hidden');
};

window.selectOtherSettingsSub = function(subKey) {
    // 1. Show container #settingsTab-other and activate #tabBtn-other
    document.querySelectorAll('.tab-content').forEach(el => {
        el.classList.add('hidden');
        el.style.opacity = '0';
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active-tab-btn');
        btn.classList.add('text-slate-600');
    });

    const otherContainer = document.getElementById('settingsTab-other');
    const otherBtn = document.getElementById('tabBtn-other');
    if (otherContainer) {
        otherContainer.classList.remove('hidden');
        otherContainer.style.opacity = '1';
    }
    if (otherBtn) {
        otherBtn.classList.add('active-tab-btn');
        otherBtn.classList.remove('text-slate-600');
    }

    // 2. Hide all subTabs and show the selected subTab
    document.querySelectorAll('.sub-tab-content').forEach(el => {
        el.classList.add('hidden');
        el.style.opacity = '0';
    });

    const targetSub = document.getElementById(`subTab-${subKey}`);
    if (targetSub) {
        targetSub.classList.remove('hidden');
        targetSub.style.opacity = '1';
    }

    const menu = document.getElementById('otherSettingsDropdownMenu');
    if (menu) menu.classList.add('hidden');

    const subLabels = {
        'serials': 'Auto Serial & Prefixes',
        'financial': 'Tax & Financial',
        'email': 'Email (SMTP)',
        'categories': 'Purchase & Expense Categories',
        'security': 'Security & Backups'
    };

    const labelEl = document.getElementById('otherSettingsTabLabel');
    if (labelEl && subLabels[subKey]) labelEl.innerText = subLabels[subKey];

    // Highlight the sub-tab option in the dropdown
    document.querySelectorAll('.other-opt-btn').forEach(btn => {
        btn.classList.remove('active-sub-tab-btn', 'bg-blue-50', 'text-blue-700');
    });
    const targetOptBtn = document.getElementById(`otherOpt-${subKey}`);
    if (targetOptBtn) {
        targetOptBtn.classList.add('active-sub-tab-btn', 'bg-blue-50', 'text-blue-700');
    }

    if (subKey === 'serials') {
        setTimeout(function() {
            if (typeof window.updateSerialPreview === 'function') {
                window.updateSerialPreview();
            }
        }, 50);
    }

    try {
        const url = new URL(window.location);
        url.searchParams.set('tab', subKey);
        window.history.replaceState({}, '', url);
    } catch(e) {}
};


// --- Live Serial Pattern Preview ---
window.updateSerialPreview = function() {
    const invPrefix = $('#invoice_prefix_input').val() ?? '';
    const invSeq = parseInt($('#invoice_seq_input').val()) || 1;
    const ordPrefix = $('#order_prefix_input').val() ?? '';
    const ordSeq = parseInt($('#order_seq_input').val()) || 1;
    const dateFormat = $('#serial_date_format_select').val() || 'none';
    const digits = parseInt($('#serial_number_digits_select').val()) || 1;

    const today = new Date();
    const YYYY = today.getFullYear();
    const MM = String(today.getMonth() + 1).padStart(2, '0');
    const DD = String(today.getDate()).padStart(2, '0');
    const YY = String(YYYY).slice(-2);

    let dateStr = '';
    if (dateFormat === 'Ymd') dateStr = `${YYYY}${MM}${DD}`;
    else if (dateFormat === 'Ym') dateStr = `${YYYY}${MM}`;
    else if (dateFormat === 'ym') dateStr = `${YY}${MM}`;
    else if (dateFormat === 'FY') {
        const month = today.getMonth() + 1;
        const fyStart = month >= 4 ? YY : String(parseInt(YY) - 1).padStart(2, '0');
        const fyEnd = month >= 4 ? String(parseInt(YY) + 1).padStart(2, '0') : YY;
        dateStr = `${fyStart}${fyEnd}`;
    }

    function formatNumber(prefix, seq) {
        const formattedSeq = digits > 1 ? String(seq).padStart(digits, '0') : String(seq);
        if (dateStr) {
            if (!prefix) return `${dateStr}-${formattedSeq}`;
            const sep = (prefix.endsWith('-') || prefix.endsWith('/')) ? '' : '-';
            return `${prefix}${sep}${dateStr}-${formattedSeq}`;
        }
        return `${prefix}${formattedSeq}`;
    }

    $('#invoice_sample_preview').text(formatNumber(invPrefix, invSeq));
    $('#order_sample_preview').text(formatNumber(ordPrefix, ordSeq));
};

// --- Generic Inline Error Helpers ---
function addInlineFieldError($input, message) {
    $input.addClass('border-rose-500 ring-2 ring-rose-200');
    
    let $wrapper = $input.closest('.combobox-wrapper');
    if (!$wrapper.length) $wrapper = $input.closest('div');
    
    $wrapper.find('.field-error-text').remove();
    
    let formattedMessage = message;
    if (typeof formattedMessage === 'string') {
        // Convert any '2048 kilobytes' or similar into '2 MB'
        formattedMessage = formattedMessage.replace(/(\d+)\s*kilobytes/gi, function(match, kb) {
            const mb = parseInt(kb) / 1024;
            return (mb % 1 === 0 ? mb : mb.toFixed(1)) + ' MB';
        });
    }
    
    const errorHtml = `<p class="field-error-text text-xs text-rose-600 font-semibold mt-1 flex items-center gap-1">
        <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>${formattedMessage}</span>
    </p>`;
    $wrapper.append(errorHtml);
}

function clearInlineFieldErrors($form) {
    $form.find('.field-error-text').remove();
    $form.find('input, select, textarea').removeClass('border-rose-500 ring-2 ring-rose-200');
}

// --- Generic AJAX Form Submit Handler with Inline Validation ---
function submitFormWithAjax($form, successMsg, customValidate, onSuccess) {
    if (typeof customValidate === 'function' && !onSuccess && customValidate.length < 1) {
        onSuccess = customValidate;
        customValidate = null;
    }

    clearInlineFieldErrors($form);

    let hasErrors = false;
    let firstErrorField = null;

    // Check required fields
    $form.find('[required]').each(function() {
        const $elem = $(this);
        if ($elem.is(':hidden') && !$elem.hasClass('combobox-hidden-input')) {
            return;
        }

        const val = $.trim($elem.val());
        if (!val) {
            hasErrors = true;
            let fieldLabel = $elem.closest('div').find('label').text().replace('*', '').trim();
            if (!fieldLabel || fieldLabel.length > 50) fieldLabel = $elem.attr('placeholder') || 'This field';
            const msg = `${fieldLabel} is required.`;
            
            let $targetInput = $elem;
            if ($elem.hasClass('combobox-hidden-input')) {
                const visId = $elem.attr('id').replace('_hidden', '_input');
                const $visInput = $('#' + visId);
                if ($visInput.length) $targetInput = $visInput;
            }

            addInlineFieldError($targetInput, msg);
            if (!firstErrorField) firstErrorField = $targetInput;
        }
    });

    if (typeof customValidate === 'function') {
        try {
            const customErrs = customValidate($form);
            if (Array.isArray(customErrs) && customErrs.length) {
                hasErrors = true;
                customErrs.forEach(item => {
                    const $targetInput = item.$elem || $form.find(`[name="${item.name}"]`);
                    addInlineFieldError($targetInput, item.message);
                    if (!firstErrorField) firstErrorField = $targetInput;
                });
            }
        } catch (err) {
            console.error("Custom validation error:", err);
        }
    }

    if (hasErrors) {
        if (firstErrorField && firstErrorField.length && firstErrorField.is(':visible')) {
            firstErrorField.focus();
        }
        if (window.showToast) {
            window.showToast('danger', 'Please correct the highlighted fields.');
        }
        return false;
    }

    const $btn = $form.find('button[type="submit"]');
    if ($btn.data('is-submitting')) {
        return false;
    }

    if (window.setButtonLoading) {
        window.setButtonLoading($btn, true, 'Saving...');
    } else {
        $btn.prop('disabled', true).html('Saving...');
    }
    $btn.data('is-submitting', true);

    const formData = new FormData($form[0]);

    $.ajax({
        url: $form.attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept': 'application/json' },
        success: function(res) {
            $btn.removeData('is-submitting');
            if (window.setButtonLoading) {
                window.setButtonLoading($btn, false);
            } else {
                $btn.prop('disabled', false).html('Save');
            }
            if (res.success) {
                if (window.showToast) {
                    window.showToast('success', res.message || successMsg);
                }
                if (typeof onSuccess === 'function') {
                    onSuccess($form, res);
                }
            } else {
                if (window.showToast) window.showToast('error', res.message || 'Action failed.');
            }
        },
        error: function(xhr) {
            $btn.removeData('is-submitting');
            if (window.setButtonLoading) {
                window.setButtonLoading($btn, false);
            } else {
                $btn.prop('disabled', false).html('Save');
            }
            let globalMsg = 'Please correct the highlighted fields.';
            if (xhr.responseJSON) {
                if (xhr.responseJSON.errors && typeof xhr.responseJSON.errors === 'object') {
                    Object.keys(xhr.responseJSON.errors).forEach(key => {
                        const errMsgs = xhr.responseJSON.errors[key];
                        const msg = Array.isArray(errMsgs) ? errMsgs[0] : errMsgs;
                        const $targetInput = $form.find(`[name="${key}"]`);
                        if ($targetInput.length) {
                            addInlineFieldError($targetInput, msg);
                        }
                    });
                } else if (xhr.responseJSON.message) {
                    globalMsg = xhr.responseJSON.message;
                }
            }
            if (window.showToast) {
                window.showToast('danger', globalMsg);
            }
        }
    });

    return false;
}

// Dedicated Handlers with Inline Validation
window.handleBusinessProfileSubmit = function(e) {
    e.preventDefault();
    const $form = $(e.target).closest('form');
    return submitFormWithAjax($form, 'Business profile updated successfully!', function($f) {
        let errs = [];
        const email = $.trim($f.find('input[name="business_email"]').val());
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errs.push({ name: 'business_email', message: 'Please enter a valid Official Email address.' });
        }
        const gstin = $.trim($f.find('input[name="gstin"]').val());
        if (gstin && gstin.length !== 15) {
            errs.push({ name: 'gstin', message: 'GSTIN Number must be exactly 15 characters.' });
        }
        return errs;
    }, function($f, res) {
        if (res && res.data) {
            if (res.data.logo_url && res.data.has_logo) {
                $('#businessLogoPreviewImg').attr('src', res.data.logo_url).removeClass('hidden');
                $('#businessLogoBlankPlaceholder').addClass('hidden');
                $('#removeLogoWrapper').removeClass('hidden');
                // Sync Sidebar Live
                $('#sidebarLogoImg').attr('src', res.data.logo_url).removeClass('hidden');
                $('#sidebarLogoAvatar').addClass('hidden');
            } else if (!res.data.has_logo) {
                $('#businessLogoPreviewImg').addClass('hidden');
                $('#businessLogoBlankPlaceholder').removeClass('hidden');
                $('#removeLogoWrapper').addClass('hidden');
                // Sync Sidebar Live to Monogram
                $('#sidebarLogoImg').addClass('hidden');
                $('#sidebarLogoAvatar').removeClass('hidden');
            }

            if (res.data.signature_url && res.data.has_signature) {
                $('#businessSigPreviewImg').attr('src', res.data.signature_url).removeClass('hidden');
                $('#businessSigBlankPlaceholder').addClass('hidden');
                $('#removeSigWrapper').removeClass('hidden');
            } else if (!res.data.has_signature) {
                $('#businessSigPreviewImg').addClass('hidden');
                $('#businessSigBlankPlaceholder').removeClass('hidden');
                $('#removeSigWrapper').addClass('hidden');
            }
        }
        // Reset file inputs cleanly so chosen file text is cleared
        $f.find('input[type="file"]').val('');
        $('#removeLogoCheckbox').prop('checked', false);
        $('#removeSignatureCheckbox').prop('checked', false);
    });
};

window.handleBankBillingSubmit = function(e) {
    e.preventDefault();
    const $form = $(e.target).closest('form');
    return submitFormWithAjax($form, 'Bank details & billing defaults saved successfully!', function($f) {
        let errs = [];
        const ifsc = $.trim($f.find('input[name="bank_ifsc"]').val());
        if (ifsc && ifsc.length !== 11) {
            errs.push({ name: 'bank_ifsc', message: 'IFSC Code must be exactly 11 characters (e.g. IBKL0JIVAN3).' });
        }
        return errs;
    });
};

window.handleSerialsSubmit = function(e) {
    e.preventDefault();
    const $form = $(e.target).closest('form');
    return submitFormWithAjax($form, 'Serial and prefix settings saved successfully!');
};

window.handleFinancialSubmit = function(e) {
    e.preventDefault();
    const $form = $(e.target).closest('form');
    return submitFormWithAjax($form, 'Financial and tax settings saved successfully!');
};

window.handleEmailSettingsSubmit = function(e) {
    e.preventDefault();
    const $form = $(e.target).closest('form');
    return submitFormWithAjax($form, 'Email SMTP settings saved successfully!');
};

window.handleTestEmailSubmit = function(e) {
    e.preventDefault();
    const $form = $(e.target).closest('form');
    return submitFormWithAjax($form, 'Test email sent successfully!');
};

window.handleSecuritySettingsSubmit = function(e) {
    e.preventDefault();
    const $form = $(e.target).closest('form');
    return submitFormWithAjax($form, 'Security preferences saved successfully!');
};

window.handleRoleFormSubmit = function(e) {
    e.preventDefault();
    const $form = $(e.target).closest('form');
    return submitFormWithAjax($form, 'New role created successfully!', async function() {
        closeAddRoleModal();
        if (window.clearPageCache) window.clearPageCache();
        const targetUrl = "{{ route('settings.index') }}?tab=roles";
        if (window.loadPage) await window.loadPage(targetUrl, true, true);
        else window.location.href = targetUrl;
    });
};

window.handleCategoryFormSubmit = function(e) {
    if (e) e.preventDefault();
    const form = document.getElementById('categoryForm');
    if (!form) return false;

    const $form = $(form);
    const type = document.getElementById('categoryTypeInput') ? document.getElementById('categoryTypeInput').value : 'purchase';
    const key = document.getElementById('categoryKeyInput') ? document.getElementById('categoryKeyInput').value : '';
    const label = document.getElementById('categoryLabelInput') ? document.getElementById('categoryLabelInput').value : '';
    const icon = document.getElementById('categoryIconInput') ? document.getElementById('categoryIconInput').value : '';

    return submitFormWithAjax($form, 'Category saved successfully!', function($f, res) {
        closeCategoryModal();
        if (window.clearPageCache) window.clearPageCache();

        const catData = (res && res.category) ? res.category : {
            type: type,
            key: key || label.toLowerCase().replace(/[^a-z0-9]+/g, '_'),
            label: label,
            icon: icon || '📦'
        };

        const itemSelector = `#cat-item-${catData.type}-${catData.key}`;
        const $existingItem = $(itemSelector);

        if ($existingItem.length) {
            // In-place Update existing category item
            $existingItem.find('.cat-label').text(catData.label);
            if (catData.type === 'material' && catData.icon) {
                $existingItem.find('.cat-icon').text(catData.icon);
            }
            $existingItem.find('.cat-edit-btn').attr('data-label', catData.label);
            if (catData.icon) {
                $existingItem.find('.cat-edit-btn').attr('data-icon', catData.icon);
            }
            $existingItem.find('.cat-del-btn').attr('data-label', catData.label);

            // Subtle feedback highlight
            $existingItem.addClass('ring-2 ring-blue-400');
            setTimeout(() => $existingItem.removeClass('ring-2 ring-blue-400'), 1000);
        } else {
            // In-place Append new category item strictly into its specific list container
            const listContainerId = `#${catData.type}-categories-list`;
            let $container = $(listContainerId);

            let newHtml = '';
            const safeLabel = $('<div>').text(catData.label).html();
            const safeKey = $('<div>').text(catData.key).html();
            const safeIcon = $('<div>').text(catData.icon || '📦').html();

            if (catData.type === 'material') {
                newHtml = `
                <div id="cat-item-material-${safeKey}" class="flex items-center justify-between p-3 bg-slate-50 border border-slate-200/70 rounded-xl text-xs hover:border-slate-300 transition">
                    <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                        <span class="text-base cat-icon">${safeIcon}</span>
                        <span class="font-bold text-slate-800 text-sm cat-label">${safeLabel}</span>
                        <span class="text-[10px] font-mono bg-slate-200/80 text-slate-700 px-2 py-0.5 rounded-md font-semibold">key: ${safeKey}</span>
                    </div>
                    <div class="flex items-center space-x-2 shrink-0">
                        <button type="button" 
                                onclick="openEditCategoryModal('material', '${safeKey}', this.getAttribute('data-label'), this.getAttribute('data-icon'))" 
                                data-label="${safeLabel}"
                                data-icon="${safeIcon}"
                                class="cat-edit-btn w-7 h-7 inline-flex items-center justify-center bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white rounded-lg border border-blue-200/80 transition duration-150 transform hover:scale-105 cursor-pointer shadow-2xs" 
                                title="Edit Category Label & Icon">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>
                        <button type="button" 
                                onclick="deleteCategorySetting('material', '${safeKey}', this.getAttribute('data-label'))" 
                                data-label="${safeLabel}"
                                class="cat-del-btn w-7 h-7 inline-flex items-center justify-center bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white rounded-lg border border-rose-200/80 transition duration-150 transform hover:scale-105 cursor-pointer shadow-2xs" 
                                title="Delete Category">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>`;
            } else {
                newHtml = `
                <div id="cat-item-${catData.type}-${safeKey}" class="flex items-center justify-between p-3 bg-slate-50 border border-slate-200/70 rounded-xl text-xs hover:border-slate-300 transition">
                    <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                        <span class="font-bold text-slate-800 text-sm cat-label">${safeLabel}</span>
                        <span class="text-[10px] font-mono bg-slate-200/80 text-slate-700 px-2 py-0.5 rounded-md font-semibold">key: ${safeKey}</span>
                    </div>
                    <div class="flex items-center space-x-2 shrink-0">
                        <button type="button" 
                                onclick="openEditCategoryModal('${catData.type}', '${safeKey}', this.getAttribute('data-label'))" 
                                data-label="${safeLabel}"
                                class="cat-edit-btn w-7 h-7 inline-flex items-center justify-center bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white rounded-lg border border-blue-200/80 transition duration-150 transform hover:scale-105 cursor-pointer shadow-2xs" 
                                title="Edit Category Label">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>
                        <button type="button" 
                                onclick="deleteCategorySetting('${catData.type}', '${safeKey}', this.getAttribute('data-label'))" 
                                data-label="${safeLabel}"
                                class="cat-del-btn w-7 h-7 inline-flex items-center justify-center bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white rounded-lg border border-rose-200/80 transition duration-150 transform hover:scale-105 cursor-pointer shadow-2xs" 
                                title="Delete Category">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>`;
            }

            const $newEl = $(newHtml).hide();
            $container.append($newEl);
            $newEl.fadeIn(300);
            $newEl.addClass('ring-2 ring-emerald-400');
            setTimeout(() => $newEl.removeClass('ring-2 ring-emerald-400'), 1000);
        }
    });
};

window.handleUserFormSubmit = function(e) {
    e.preventDefault();
    const $form = $(e.target).closest('form');
    return submitFormWithAjax($form, 'User account saved successfully!', async function() {
        closeAddUserModal();
        closeEditUserModal();
        if (window.clearPageCache) window.clearPageCache();
        const targetUrl = "{{ route('settings.index') }}?tab=roles";
        if (window.loadPage) await window.loadPage(targetUrl, true, true);
        else window.location.href = targetUrl;
    });
};

// --- User & Role Actions ---
window.toggleCreateUserForm = function() {
    const card = document.getElementById('createUserFormCard');
    if (card) {
        if (card.classList.contains('hidden')) {
            card.classList.remove('hidden');
            card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            card.classList.add('hidden');
        }
    }
};

window.openEditUserModal = function(btn) {
    const user = JSON.parse(btn.getAttribute('data-user'));
    const card = document.getElementById('createUserFormCard');
    const title = document.getElementById('userFormCardTitle');
    const form = document.getElementById('userCardForm');
    const methodInput = document.getElementById('userFormMethodInput');
    const nameInput = document.getElementById('cardUserNameInput');
    const emailInput = document.getElementById('cardUserEmailInput');
    const passInput = document.getElementById('cardUserPasswordInput');
    const passHint = document.getElementById('passwordRequiredHint');
    const roleSelect = document.getElementById('cardUserRoleSelect');
    const submitBtn = document.getElementById('userFormSubmitBtn');

    if (!card || !form) return;

    form.action = `/settings/users/${user.id}`;
    if (methodInput) methodInput.value = 'PUT';
    if (title) title.innerText = `Edit User Details: ${user.name}`;
    if (nameInput) nameInput.value = user.name || '';
    if (emailInput) emailInput.value = user.email || '';
    if (passInput) {
        passInput.value = '';
        passInput.removeAttribute('required');
        passInput.placeholder = 'Leave blank to keep current password';
    }
    if (passHint) passHint.classList.add('hidden');
    if (roleSelect) roleSelect.value = user.role || 'staff';
    if (submitBtn) submitBtn.innerText = 'Update User Details';

    card.classList.remove('hidden');
    card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
};

window.toggleUserStatusAjax = function(id, name, btn) {
    try {
        const $btn = $(btn).closest('button');
        const isActive = $btn.attr('data-active') === '1';
        const actionText = isActive ? 'deactivate' : 'activate';
        const title = `${isActive ? 'Deactivate' : 'Activate'} User Account?`;
        const text = `Are you sure you want to ${actionText} user account '${name}'?`;

        const performToggle = function() {
            $.ajax({
                url: `/settings/users/${id}/toggle-status`,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    if (res.success) {
                        const newActiveState = typeof res.is_active !== 'undefined' ? res.is_active : !isActive;
                        if (window.showToast) {
                            window.showToast(newActiveState ? 'success' : 'danger', res.message);
                        }
                        
                        $btn.attr('data-active', newActiveState ? '1' : '0');
                        
                        if (newActiveState) {
                            $btn.removeClass('bg-rose-500 hover:bg-rose-600')
                                .addClass('bg-emerald-500 hover:bg-emerald-600')
                                .attr('title', 'Deactivate User Account');
                            $btn.html(`
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                </svg>
                            `);
                        } else {
                            $btn.removeClass('bg-emerald-500 hover:bg-emerald-600')
                                .addClass('bg-rose-500 hover:bg-rose-600')
                                .attr('title', 'Activate User Account');
                            $btn.html(`
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 1.002 0 1.97-.146 2.883-.404z" />
                                </svg>
                            `);
                        }
                        
                        const $statusCell = $btn.closest('tr').find('.user-status-cell');
                        if ($statusCell.length) {
                            if (newActiveState) {
                                $statusCell.html(`
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Active
                                    </span>
                                `);
                            } else {
                                $statusCell.html(`
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-bold uppercase bg-rose-50 text-rose-700 border border-rose-200">
                                        Inactive
                                    </span>
                                `);
                            }
                        }
                    }
                },
                error: function(err) {
                    if (window.showToast) window.showToast('danger', 'Failed to toggle user status.');
                }
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: title,
                text: text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: isActive ? '#ef4444' : '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: isActive ? 'Yes, deactivate' : 'Yes, activate',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    performToggle();
                }
            });
        } else {
            if (confirm(`${title}\n\n${text}`)) {
                performToggle();
            }
        }
    } catch (e) {
        console.error("Error in toggleUserStatusAjax:", e);
    }
};

window.deleteUserAjax = function(id, name, btn) {
    window.confirmDelete(
        'Delete User Account?',
        `Are you sure you want to permanently delete user account '${name}'?`,
        function() {
            $.ajax({
                url: `/settings/users/${id}`,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    if (res.success) {
                        if (window.showToast) window.showToast('success', res.message);
                        if (window.ERPTableHelper) {
                            window.ERPTableHelper.removeRow($(btn).closest('tr'));
                        } else {
                            $(btn).closest('tr').fadeOut(400, function() { $(this).remove(); });
                        }
                    }
                }
            });
        }
    );
};

window.toggleRoleStatusAjax = function(slug, name, btn) {
    try {
        const $btn = $(btn).closest('button');
        const isActive = $btn.attr('data-active') === '1';
        const actionText = isActive ? 'deactivate' : 'activate';
        const title = `${isActive ? 'Deactivate' : 'Activate'} Role?`;
        const text = `Are you sure you want to ${actionText} role '${name}'?`;

        const performToggle = function() {
            $.ajax({
                url: `/settings/roles/${slug}/toggle-status`,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    if (res.success) {
                        const newActiveState = typeof res.is_active !== 'undefined' ? res.is_active : !isActive;
                        if (window.showToast) {
                            window.showToast(newActiveState ? 'success' : 'danger', res.message);
                        }
                        
                        $btn.attr('data-active', newActiveState ? '1' : '0');
                        
                        if (newActiveState) {
                            $btn.removeClass('bg-rose-500 hover:bg-rose-600')
                                .addClass('bg-emerald-500 hover:bg-emerald-600')
                                .attr('title', 'Deactivate Role');
                            $btn.html(`
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                </svg>
                            `);
                        } else {
                            $btn.removeClass('bg-emerald-500 hover:bg-emerald-600')
                                .addClass('bg-rose-500 hover:bg-rose-600')
                                .attr('title', 'Activate Role');
                            $btn.html(`
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 1.002 0 1.97-.146 2.883-.404z" />
                                </svg>
                            `);
                        }
                    }
                },
                error: function(err) {
                    if (window.showToast) window.showToast('danger', 'Failed to toggle role status.');
                }
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: title,
                text: text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: isActive ? '#ef4444' : '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: isActive ? 'Yes, deactivate' : 'Yes, activate',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    performToggle();
                }
            });
        } else {
            if (confirm(`${title}\n\n${text}`)) {
                performToggle();
            }
        }
    } catch (e) {
        console.error("Error in toggleRoleStatusAjax:", e);
    }
};

window.deleteRoleAjax = function(id, name, btn) {
    window.confirmDelete(
        'Delete Role?',
        `Are you sure you want to permanently delete role '${name}'? This will also clear all its custom permission assignments.`,
        function() {
            $.ajax({
                url: `/settings/roles/${id}`,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    if (res.success) {
                        if (window.showToast) window.showToast('success', res.message);
                        $(btn).closest('tr').fadeOut(400, function() { $(this).remove(); });
                    }
                },
                error: function(err) {
                    if (window.showToast) window.showToast('danger', 'Failed to delete role.');
                }
            });
        }
    );
};

window.toggleRolePerm = function(roleSlug, permKey, isChecked) {
    $.ajax({
        url: "{{ route('settings.roles.toggle-permission') }}",
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            role_slug: roleSlug,
            permission_key: permKey,
            enabled: isChecked ? 1 : 0,
            is_enabled: isChecked ? 1 : 0
        },
        success: function(res) {
            if (res.success && window.showToast) {
                const toastType = res.enabled ? 'success' : 'danger';
                window.showToast(toastType, res.message || `Permission updated for ${roleSlug}`);
            }
        },
        error: function(err) {
            if (window.showToast) {
                window.showToast('danger', 'Failed to update permission.');
            }
        }
    });
};

window.openAddRoleModal = function() {
    document.getElementById('addRoleModal').classList.remove('hidden');
};

window.closeAddRoleModal = function() {
    document.getElementById('addRoleModal').classList.add('hidden');
};

window.selectQuickCategoryEmoji = function(emoji) {
    const input = document.getElementById('categoryIconInput');
    const preview = document.getElementById('categoryIconPreview');
    if (input) input.value = emoji;
    if (preview) preview.innerText = emoji;
};

window.openAddCategoryModal = function(type) {
    const modal = document.getElementById('categoryModal');
    const title = document.getElementById('categoryModalTitle');
    const typeInput = document.getElementById('categoryTypeInput');
    const keyInput = document.getElementById('categoryKeyInput');
    const labelInput = document.getElementById('categoryLabelInput');
    const iconInput = document.getElementById('categoryIconInput');
    const iconPreview = document.getElementById('categoryIconPreview');
    const iconWrapper = document.getElementById('categoryIconWrapper');
    const form = document.getElementById('categoryForm');

    if (modal && form) {
        form.action = "{{ route('settings.categories.store') }}";
        if (typeInput) typeInput.value = type;
        if (keyInput) keyInput.value = '';
        if (labelInput) labelInput.value = '';
        if (iconInput) iconInput.value = type === 'material' ? '📦' : '';
        if (iconPreview) iconPreview.innerText = type === 'material' ? '📦' : '📦';
        if (iconWrapper) {
            iconWrapper.style.display = type === 'material' ? 'block' : 'none';
        }
        if (title) {
            if (type === 'purchase') title.innerText = 'Add Purchase Category';
            else if (type === 'expense') title.innerText = 'Add Expense Category';
            else title.innerText = 'Add Raw Material Category';
        }
        modal.classList.remove('hidden');
    }
};

window.openEditCategoryModal = function(type, key, currentLabel, currentIcon = '') {
    const modal = document.getElementById('categoryModal');
    const title = document.getElementById('categoryModalTitle');
    const typeInput = document.getElementById('categoryTypeInput');
    const keyInput = document.getElementById('categoryKeyInput');
    const labelInput = document.getElementById('categoryLabelInput');
    const iconInput = document.getElementById('categoryIconInput');
    const iconPreview = document.getElementById('categoryIconPreview');
    const iconWrapper = document.getElementById('categoryIconWrapper');
    const form = document.getElementById('categoryForm');

    if (modal && form) {
        form.action = "{{ route('settings.categories.update') }}";
        if (typeInput) typeInput.value = type;
        if (keyInput) keyInput.value = key;
        if (labelInput) labelInput.value = currentLabel;
        const finalIcon = currentIcon || (type === 'material' ? '📦' : '');
        if (iconInput) iconInput.value = finalIcon;
        if (iconPreview) iconPreview.innerText = finalIcon || '📦';
        if (iconWrapper) {
            iconWrapper.style.display = type === 'material' ? 'block' : 'none';
        }
        if (title) {
            if (type === 'purchase') title.innerText = 'Edit Purchase Category';
            else if (type === 'expense') title.innerText = 'Edit Expense Category';
            else title.innerText = 'Edit Raw Material Category';
        }
        modal.classList.remove('hidden');
    }
};

window.closeCategoryModal = function() {
    document.querySelectorAll('#categoryModal').forEach(m => m.classList.add('hidden'));
};

window.deleteCategorySetting = function(type, key, label) {
    if (type === 'purchase' && key === 'raw_material') {
        if (window.showToast) window.showToast('danger', "Cannot delete 'Raw Material Purchase' category!");
        return;
    }
    if (type === 'expense' && (key === 'salary' || key === 'gst_payment')) {
        if (window.showToast) window.showToast('danger', "Cannot delete 'Salary' or 'GST Payment' categories!");
        return;
    }

    window.confirmDelete(
        "Delete Category Option?",
        "Are you sure you want to delete '" + label + "'?",
        function() {
            $.ajax({
                url: "{{ route('settings.categories.delete') }}",
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', type: type, key: key },
                headers: {
                    'Accept': 'application/json'
                },
                success: function(res) {
                    if (res.success) {
                        if (window.showToast) window.showToast('success', res.message);
                        const itemEl = document.getElementById(`cat-item-${type}-${key}`);
                        if (itemEl) {
                            $(itemEl).fadeOut(300, function() { $(this).remove(); });
                        }
                        if (window.clearPageCache) window.clearPageCache();
                    } else {
                        if (window.showToast) window.showToast('danger', res.message || 'Failed to delete category');
                    }
                },
                error: function(xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error deleting category';
                    if (window.showToast) window.showToast('danger', msg);
                }
            });
        }
    );
};

// --- Module Toggles & User Helpers ---
function updateUIAndSidebarModules(modules) {
    if (!modules) return;

    const isSimplified = !!modules.simplified_billing_mode;
    $('#simplified_billing_active_badge').toggleClass('hidden', !isSimplified);
    $('#simplified_billing_mode_toggle').prop('checked', isSimplified);

    const $stockCard = $('#track_stock_card');
    const $stockInput = $stockCard.find('input[name="track_stock"]');
    const $disabledBadge = $('#track_stock_disabled_badge');

    if (isSimplified) {
        $stockCard.addClass('opacity-60 pointer-events-none');
        $stockInput.prop('checked', false).prop('disabled', true);
        $disabledBadge.removeClass('hidden');
    } else {
        $stockCard.removeClass('opacity-60 pointer-events-none');
        $stockInput.prop('disabled', false);
        $disabledBadge.addClass('hidden');
    }

    for (const [key, enabled] of Object.entries(modules)) {
        const $input = $(`#modulesVisibilityForm input[name="${key}"]`);
        if ($input.length && key !== 'track_stock') {
            $input.prop('checked', !!enabled);
        }
    }

    const mapping = {
        'module_production': '#sidebar-module-production',
        'module_orders': '#sidebar-module-orders',
        'module_invoices': '#sidebar-module-invoices',
        'module_purchases': '#sidebar-module-purchases',
        'module_expenses': '#sidebar-module-expenses',
        'module_inventory': '#sidebar-module-rawmaterial, #sidebar-module-product',
        'module_bom': '#sidebar-module-bom',
        'module_clients': '#sidebar-module-clients',
        'module_payroll': '#sidebar-module-payroll',
        'module_reports': '#sidebar-module-reports',
        'module_backups': '#sidebar-module-backups',
        'module_activity_logs': '#sidebar-module-activity-logs',
    };

    for (const [modKey, selector] of Object.entries(mapping)) {
        if (modules.hasOwnProperty(modKey)) {
            $(selector).toggleClass('hidden', !modules[modKey]);
        }
    }

    const showInvSec = (modules.module_inventory || modules.module_bom);
    $('#sidebar-section-inventory-bom').toggleClass('hidden', !showInvSec);
    if (window.clearPageCache) window.clearPageCache();
}

window.saveModuleToggleAjax = function(elem) {
    const $form = $('#modulesVisibilityForm');
    const formData = new FormData($form[0]);
    const isChecked = elem ? $(elem).is(':checked') : true;

    $form.find('input[type="checkbox"]').each(function() {
        formData.set(this.name, this.checked ? 'true' : 'false');
    });

    let moduleLabel = 'Module';
    if (elem) {
        const $card = $(elem).closest('div.p-4, div.p-5');
        const $titleEl = $card.find('span.font-bold, span.font-extrabold').first().clone();
        $titleEl.find('#track_stock_disabled_badge').remove();
        const cardTitle = $titleEl.text().trim();
        if (cardTitle) {
            moduleLabel = cardTitle.replace(/^⚡\s*/, '').replace(/\s*ACTIVE$/, '').trim();
        }
    }

    $.ajax({
        url: "{{ route('settings.modules') }}",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept': 'application/json' },
        success: function(res) {
            if (res.success) {
                if (res.modules) {
                    updateUIAndSidebarModules(res.modules);
                }
                if (window.showToast) {
                    const toastType = isChecked ? 'success' : 'danger';
                    const actionText = isChecked ? 'enabled' : 'disabled';
                    let customMsg = elem && moduleLabel !== 'Module' 
                        ? `${moduleLabel} ${actionText}!` 
                        : (res.message || 'Module settings updated!');
                    
                    if ($(elem).attr('name') === 'simplified_billing_mode') {
                        customMsg = isChecked 
                            ? 'Simplified Billing Mode enabled!' 
                            : 'Simplified Billing Mode disabled (Full ERP restored)!';
                    }

                    window.showToast(toastType, customMsg);
                }
            }
        },
        error: function(xhr) {
            if (window.showToast) window.showToast('danger', 'Failed to update module toggle.');
        }
    });
};

window.toggleSimplifiedBillingModeAjax = function(elem) {
    const isChecked = $(elem).is(':checked');
    const $stockCard = $('#track_stock_card');
    const $stockInput = $stockCard.find('input[name="track_stock"]');
    const $disabledBadge = $('#track_stock_disabled_badge');
    const $form = $('#modulesVisibilityForm');

    if (isChecked) {
        $stockCard.addClass('opacity-60 pointer-events-none');
        $stockInput.prop('checked', false).prop('disabled', true);
        $disabledBadge.removeClass('hidden');

        $form.find('input[name="module_orders"], input[name="module_production"], input[name="module_bom"], input[name="module_inventory"], input[name="module_payroll"]').prop('checked', false);
    } else {
        $stockCard.removeClass('opacity-60 pointer-events-none');
        $stockInput.prop('disabled', false).prop('checked', true);
        $disabledBadge.addClass('hidden');

        $form.find('input[name="module_orders"], input[name="module_production"], input[name="module_bom"], input[name="module_inventory"], input[name="module_payroll"]').prop('checked', true);
    }

    saveModuleToggleAjax(elem);
};

window.closeAddUserModal = function() {
    const card = document.getElementById('createUserFormCard');
    if (card) card.classList.add('hidden');
};

window.closeEditUserModal = function() {
    const modal = document.getElementById('editUserModal');
    if (modal) modal.classList.add('hidden');
};

window.togglePasswordVisibility = function(inputId, eyeId, eyeOffId) {
    const input = document.getElementById(inputId);
    const eye = document.getElementById(eyeId);
    const eyeOff = document.getElementById(eyeOffId);
    if (!input) return;

    if (input.type === 'password') {
        input.type = 'text';
        if (eye) eye.classList.add('hidden');
        if (eyeOff) eyeOff.classList.remove('hidden');
    } else {
        input.type = 'password';
        if (eye) eye.classList.remove('hidden');
        if (eyeOff) eyeOff.classList.add('hidden');
    }
};

(function initSettingsTabsOnLoad() {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || 'profile';
    const subTab = urlParams.get('sub');
    if (subTab) {
        selectOtherSettingsSub(subTab);
    } else {
        switchSettingsTab(activeTab);
    }

    setTimeout(function() {
        if (typeof window.updateSerialPreview === 'function') {
            window.updateSerialPreview();
        }
    }, 100);

    // Dropdown toggle for mobile/desktop settings nav
    $(document).off('click', '#otherSettingsDropdownBtn').on('click', '#otherSettingsDropdownBtn', function(e) {
        e.stopPropagation();
        $('#otherSettingsDropdownMenu').toggleClass('hidden');
    });
    $(document).off('mouseenter', '#otherSettingsDropdownWrapper').on('mouseenter', '#otherSettingsDropdownWrapper', function() {
        $('#otherSettingsDropdownMenu').removeClass('hidden');
    });
    $(document).off('mouseleave', '#otherSettingsDropdownWrapper').on('mouseleave', '#otherSettingsDropdownWrapper', function() {
        $('#otherSettingsDropdownMenu').addClass('hidden');
    });
    $(document).off('click.otherSettingsDoc').on('click.otherSettingsDoc', function(e) {
        if (!$(e.target).closest('#otherSettingsDropdownWrapper').length) {
            $('#otherSettingsDropdownMenu').addClass('hidden');
        }
    });
})();
</script>

<style>
.active-tab-btn, .active-sub-tab-btn {
    background-color: #eff6ff !important;
    color: #1d4ed8 !important;
    border: 1px solid #bfdbfe !important;
}
</style>
@endsection
