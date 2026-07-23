<header class="bg-white border-b border-slate-200 sticky top-0 z-20 px-6 py-3 flex items-center justify-between shadow-2xs">
    <!-- Left: Current Page Name -->
    <div class="flex items-center space-x-3">
        <!-- Mobile Sidebar Toggle -->
        <button id="sidebarHeaderToggle" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition md:hidden">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>

        <h1 class="text-base font-extrabold text-slate-800 tracking-tight" id="headerPageTitle">
            {!! strip_tags(View::yieldContent('title', 'Dashboard')) !!}
        </h1>
    </div>

    <!-- Right: Today's Date -->
    <div class="flex items-center space-x-2 bg-slate-100/80 border border-slate-200/80 px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-600">
        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <span>{{ \Carbon\Carbon::now()->format('l, d F Y') }}</span>
    </div>
</header>
