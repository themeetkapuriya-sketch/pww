<!-- Sub Content 3.5: Purchase, Expense & Raw Material Categories Partial -->
@php
    $purchaseCategoriesList = \App\Services\CategoryService::getPurchaseCategories();
    $expenseCategoriesList = \App\Services\CategoryService::getExpenseCategories();
    $materialCategoriesList = \App\Services\CategoryService::getMaterialCategories();
@endphp
<div id="subTab-categories" class="sub-tab-content hidden space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 1. Purchase Categories Manager -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        Purchase Categories
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Manage categories available in the Purchase Ledger.</p>
                </div>
                <button type="button" onclick="openAddCategoryModal('purchase')" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2 px-3 rounded-xl transition flex items-center gap-1 shadow-xs cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Add</span>
                </button>
            </div>

            <div class="space-y-2">
                @foreach ($purchaseCategoriesList as $pCat)
                    @php
                        $isProtected = ($pCat['protected'] ?? false) || ($pCat['is_system'] ?? false) || in_array($pCat['key'], ['raw_material']);
                    @endphp
                    <div class="flex items-center justify-between p-3 bg-slate-50 border border-slate-200/70 rounded-xl text-xs hover:border-slate-300 transition">
                        <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                            <span class="font-bold text-slate-800 text-sm">{{ $pCat['label'] }}</span>
                            <span class="text-[10px] font-mono bg-slate-200/80 text-slate-700 px-2 py-0.5 rounded-md font-semibold">key: {{ $pCat['key'] }}</span>
                            @if($isProtected)
                                <span class="text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200/60 px-2 py-0.5 rounded-md inline-flex items-center gap-1">
                                    <svg class="w-3 h-3 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    System Core
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center space-x-2 shrink-0">
                            <!-- Edit Button (Blue) -->
                            <button type="button" 
                                    onclick="openEditCategoryModal('purchase', '{{ $pCat['key'] }}', '{{ addslashes($pCat['label']) }}')" 
                                    class="w-7 h-7 inline-flex items-center justify-center bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white rounded-lg border border-blue-200/80 transition duration-150 transform hover:scale-105 cursor-pointer shadow-2xs" 
                                    title="Edit Category Label">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>

                            @if(!$isProtected)
                                <!-- Delete Button (Red) -->
                                <button type="button" 
                                        onclick="deleteCategorySetting('purchase', '{{ $pCat['key'] }}', '{{ addslashes($pCat['label']) }}')" 
                                        class="w-7 h-7 inline-flex items-center justify-center bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white rounded-lg border border-rose-200/80 transition duration-150 transform hover:scale-105 cursor-pointer shadow-2xs" 
                                        title="Delete Category">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            @else
                                <!-- Lock Icon for Protected System Categories -->
                                <span class="w-7 h-7 inline-flex items-center justify-center bg-amber-50 text-amber-600 rounded-lg border border-amber-200/80 shadow-2xs" title="Protected System Category (Cannot be deleted)">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 2. Expense Categories Manager -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Expense Categories
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Manage categories available in Expense Voucher entries.</p>
                </div>
                <button type="button" onclick="openAddCategoryModal('expense')" class="bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold py-2 px-3 rounded-xl transition flex items-center gap-1 shadow-xs cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Add</span>
                </button>
            </div>

            <div class="space-y-2">
                @foreach ($expenseCategoriesList as $eCat)
                    @php
                        $isProtected = ($eCat['protected'] ?? false) || ($eCat['is_system'] ?? false) || in_array($eCat['key'], ['salary', 'gst_payment']);
                    @endphp
                    <div class="flex items-center justify-between p-3 bg-slate-50 border border-slate-200/70 rounded-xl text-xs hover:border-slate-300 transition">
                        <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                            <span class="font-bold text-slate-800 text-sm">{{ $eCat['label'] }}</span>
                            <span class="text-[10px] font-mono bg-slate-200/80 text-slate-700 px-2 py-0.5 rounded-md font-semibold">key: {{ $eCat['key'] }}</span>
                            @if($isProtected)
                                <span class="text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200/60 px-2 py-0.5 rounded-md inline-flex items-center gap-1">
                                    <svg class="w-3 h-3 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    System Core
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center space-x-2 shrink-0">
                            <!-- Edit Button (Blue) -->
                            <button type="button" 
                                    onclick="openEditCategoryModal('expense', '{{ $eCat['key'] }}', '{{ addslashes($eCat['label']) }}')" 
                                    class="w-7 h-7 inline-flex items-center justify-center bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white rounded-lg border border-blue-200/80 transition duration-150 transform hover:scale-105 cursor-pointer shadow-2xs" 
                                    title="Edit Category Label">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>

                            @if(!$isProtected)
                                <!-- Delete Button (Red) -->
                                <button type="button" 
                                        onclick="deleteCategorySetting('expense', '{{ $eCat['key'] }}', '{{ addslashes($eCat['label']) }}')" 
                                        class="w-7 h-7 inline-flex items-center justify-center bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white rounded-lg border border-rose-200/80 transition duration-150 transform hover:scale-105 cursor-pointer shadow-2xs" 
                                        title="Delete Category">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            @else
                                <!-- Lock Icon for Protected System Categories -->
                                <span class="w-7 h-7 inline-flex items-center justify-center bg-amber-50 text-amber-600 rounded-lg border border-amber-200/80 shadow-2xs" title="Protected System Category (Cannot be deleted)">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 3. Raw Material Categories Manager -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        Material Categories
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Manage classification of factory raw materials.</p>
                </div>
                <button type="button" onclick="openAddCategoryModal('material')" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2 px-3 rounded-xl transition flex items-center gap-1 shadow-xs cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Add</span>
                </button>
            </div>

            <div class="space-y-2">
                @foreach ($materialCategoriesList as $mCat)
                    @php
                        $icon = $mCat['icon'] ?? '📦';
                    @endphp
                    <div class="flex items-center justify-between p-3 bg-slate-50 border border-slate-200/70 rounded-xl text-xs hover:border-slate-300 transition">
                        <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                            <span class="text-base">{{ $icon }}</span>
                            <span class="font-bold text-slate-800 text-sm">{{ $mCat['label'] }}</span>
                            <span class="text-[10px] font-mono bg-slate-200/80 text-slate-700 px-2 py-0.5 rounded-md font-semibold">key: {{ $mCat['key'] }}</span>
                        </div>

                        <div class="flex items-center space-x-2 shrink-0">
                            <!-- Edit Button (Blue) -->
                            <button type="button" 
                                    onclick="openEditCategoryModal('material', '{{ $mCat['key'] }}', '{{ addslashes($mCat['label']) }}', '{{ addslashes($icon) }}')" 
                                    class="w-7 h-7 inline-flex items-center justify-center bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white rounded-lg border border-blue-200/80 transition duration-150 transform hover:scale-105 cursor-pointer shadow-2xs" 
                                    title="Edit Category Label & Icon">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>

                            <!-- Delete Button (Red) -->
                            <button type="button" 
                                    onclick="deleteCategorySetting('material', '{{ $mCat['key'] }}', '{{ addslashes($mCat['label']) }}')" 
                                    class="w-7 h-7 inline-flex items-center justify-center bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white rounded-lg border border-rose-200/80 transition duration-150 transform hover:scale-105 cursor-pointer shadow-2xs" 
                                    title="Delete Category">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
