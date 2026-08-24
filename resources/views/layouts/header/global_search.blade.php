<!-- Global Command Search Bar (Ctrl + K) -->
<div class="relative flex-1 max-w-md lg:max-w-lg mx-2 sm:mx-4" id="globalSearchContainer">
    <div class="relative flex items-center">
        <!-- Search Icon -->
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>

        <!-- Search Input -->
        <input 
            type="text" 
            id="globalSearchInput" 
            placeholder="Search invoices, clients, orders, products... (Ctrl + K)" 
            autocomplete="off"
            class="w-full bg-slate-100/90 dark:bg-slate-800/90 hover:bg-slate-100 dark:hover:bg-slate-800 focus:bg-white dark:focus:bg-slate-900 border border-slate-200/80 dark:border-slate-700/80 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900/40 rounded-xl py-2 pl-10 pr-16 text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-100 placeholder-slate-400 transition-all duration-200 shadow-2xs focus:shadow-md"
        >

        <!-- Right Action: Loading Spinner & Ctrl+K Badge / Clear Button -->
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center space-x-1.5">
            <!-- Spinner -->
            <div id="globalSearchSpinner" class="hidden animate-spin text-blue-600">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
            </div>

            <!-- Clear button -->
            <button type="button" id="globalSearchClearBtn" class="hidden text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-0.5 rounded cursor-pointer transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Kbd Shortcut Badge -->
            <kbd id="globalSearchKbdBadge" class="hidden sm:inline-flex items-center px-1.5 py-0.5 text-[10px] font-bold text-slate-400 dark:text-slate-400 bg-white dark:bg-slate-700 rounded-md border border-slate-200 dark:border-slate-600 shadow-2xs pointer-events-none">
                Ctrl K
            </kbd>
        </div>
    </div>

    <!-- Floating Results Dropdown Modal / Card -->
    <div id="globalSearchResultsCard" class="hidden absolute top-full mt-2 left-0 right-0 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200/90 dark:border-slate-700/90 overflow-hidden z-50 max-h-[75vh] flex flex-col backdrop-blur-md">
        <!-- Results Header / Hint Bar -->
        <div class="px-4 py-2 bg-slate-50/80 dark:bg-slate-900/60 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between text-[11px] font-bold text-slate-500 dark:text-slate-400">
            <span id="globalSearchSummaryText">Search Results</span>
            <div class="hidden sm:flex items-center space-x-2 text-[10px] text-slate-400">
                <span>Navigate: <kbd class="px-1 py-0.5 rounded bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">↑</kbd> <kbd class="px-1 py-0.5 rounded bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">↓</kbd></span>
                <span>Select: <kbd class="px-1 py-0.5 rounded bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">↵</kbd></span>
                <span>Close: <kbd class="px-1 py-0.5 rounded bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">ESC</kbd></span>
            </div>
        </div>

        <!-- Results Scrollable List -->
        <div id="globalSearchResultsList" class="overflow-y-auto max-h-[60vh] p-2 space-y-3 divide-y divide-slate-100 dark:divide-slate-700/60">
            <!-- Dynamic Result Groups Injected Here via JavaScript -->
        </div>

        <!-- Results Footer -->
        <div id="globalSearchFooter" class="px-4 py-2 bg-slate-50/80 dark:bg-slate-900/60 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between text-[10px] font-medium text-slate-400">
            <span>Praful Welding Works ERP Spotlight</span>
            <span class="text-blue-600 dark:text-blue-400 font-bold">Fast Quick Search</span>
        </div>
    </div>
</div>
