<header class="bg-white border-b border-slate-200 sticky top-0 z-20 px-4 sm:px-6 py-2.5 sm:py-3 flex items-center justify-between shadow-2xs gap-2 sm:gap-4">
    <!-- Left: Current Page Name & Mobile Toggle -->
    <div class="flex items-center space-x-2 sm:space-x-3 shrink-0">
        <!-- Mobile Sidebar Toggle -->
        <button id="sidebarHeaderToggle" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition md:hidden">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>

        <h1 class="text-sm sm:text-base font-extrabold text-slate-800 tracking-tight hidden lg:block" id="headerPageTitle">
            {!! strip_tags(View::yieldContent('title', 'Dashboard')) !!}
        </h1>
    </div>

    <!-- Center: Global Quick Command Search Bar (Ctrl + K) -->
    @if(\App\Models\Setting::get('module_global_search', 'true') === 'true')
        @include('layouts.header.global_search')
    @endif

    <!-- Right Side: Active Orders Widget, Today's Date, Theme Toggle, Stock Alerts & User Profile Dropdown -->
    <div class="flex items-center space-x-2 sm:space-x-3 shrink-0">
        <!-- 1. Active Sales Orders Pipeline Widget -->
        @if(\App\Models\Setting::get('simplified_billing_mode', 'false') !== 'true')
            @include('layouts.header.active_orders')
        @endif

        <!-- 2. Today's Date Badge -->
        <div class="hidden sm:flex items-center space-x-2 bg-slate-100/80 border border-slate-200/80 px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-600">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span>{{ \Carbon\Carbon::now()->format('l, d F Y') }}</span>
        </div>

        <!-- 3. Theme Mode Switcher Button -->
        <button type="button" onclick="window.toggleTheme()" class="p-2 rounded-xl bg-slate-100/80 hover:bg-slate-200/80 text-slate-600 transition cursor-pointer border border-slate-200/80" title="Toggle Light / Dark Theme">
            <svg class="w-4 h-4 text-amber-500 block dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 100 2h1z" clip-rule="evenodd" />
            </svg>
            <svg class="w-4 h-4 text-indigo-400 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
            </svg>
        </button>

        <!-- 4. Low Stock Alert Notification Widget -->
        @if(\App\Models\Setting::isStockEnabled() && \App\Models\Setting::get('simplified_billing_mode', 'false') !== 'true')
            @include('layouts.header.stock_alerts')
        @endif

        <!-- 5. User Profile Icon with Hover / Click Popover Menu -->
        @include('layouts.header.profile_dropdown')
    </div>
</header>

<script>
function closeActiveOrdersDropdown() {
    const card = document.getElementById('headerActiveOrdersCard');
    if (card) card.classList.add('hidden');
}

function toggleActiveOrdersDropdown(e) {
    if (e) e.stopPropagation();
    const card = document.getElementById('headerActiveOrdersCard');
    const stockCard = document.getElementById('headerLowStockCard');
    const profileCard = document.getElementById('headerUserProfileCard');
    if (stockCard) stockCard.classList.add('hidden');
    if (profileCard) profileCard.classList.add('hidden');
    if (card) {
        card.classList.toggle('hidden');
    }
}

function closeLowStockDropdown() {
    const card = document.getElementById('headerLowStockCard');
    if (card) card.classList.add('hidden');
}

function toggleLowStockDropdown(e) {
    if (e) e.stopPropagation();
    const card = document.getElementById('headerLowStockCard');
    const activeOrdersCard = document.getElementById('headerActiveOrdersCard');
    const profileCard = document.getElementById('headerUserProfileCard');
    if (activeOrdersCard) activeOrdersCard.classList.add('hidden');
    if (profileCard) profileCard.classList.add('hidden');
    if (card) {
        card.classList.toggle('hidden');
    }
}

function toggleHeaderProfileCard(e) {
    if (e) e.stopPropagation();
    const card = document.getElementById('headerUserProfileCard');
    const stockCard = document.getElementById('headerLowStockCard');
    const activeOrdersCard = document.getElementById('headerActiveOrdersCard');
    if (stockCard) stockCard.classList.add('hidden');
    if (activeOrdersCard) activeOrdersCard.classList.add('hidden');
    if (card) {
        card.classList.toggle('hidden');
    }
}

document.addEventListener('click', function(e) {
    // If click was on a link or button inside the active orders dropdown, close it immediately
    const activeOrdersCard = document.getElementById('headerActiveOrdersCard');
    if (activeOrdersCard && activeOrdersCard.contains(e.target) && (e.target.closest('a') || e.target.closest('button'))) {
        closeActiveOrdersDropdown();
    }

    // If click was on a link or button inside the low stock dropdown, close it immediately
    const stockCard = document.getElementById('headerLowStockCard');
    if (stockCard && stockCard.contains(e.target) && (e.target.closest('a') || e.target.closest('button'))) {
        closeLowStockDropdown();
        return;
    }

    const profileWrapper = document.getElementById('userProfileDropdownWrapper');
    const profileCard = document.getElementById('headerUserProfileCard');
    if (profileWrapper && profileCard && !profileWrapper.contains(e.target)) {
        if (!profileWrapper.matches(':hover')) {
            profileCard.classList.add('hidden');
        }
    }

    const stockWrapper = document.getElementById('lowStockDropdownWrapper');
    if (stockWrapper && stockCard && !stockWrapper.contains(e.target)) {
        stockCard.classList.add('hidden');
    }

    const activeOrdersWrapper = document.getElementById('activeOrdersDropdownWrapper');
    if (activeOrdersWrapper && activeOrdersCard && !activeOrdersWrapper.contains(e.target)) {
        activeOrdersCard.classList.add('hidden');
    }
});

// Close dropdown on browser back/forward or SPA page changes
window.addEventListener('popstate', function() {
    closeLowStockDropdown();
    closeActiveOrdersDropdown();
});
</script>
