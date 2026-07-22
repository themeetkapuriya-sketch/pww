<header class="bg-white/90 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-20 px-6 py-2 flex items-center justify-between shadow-2xs">
    <!-- Left: Breadcrumb / Section -->
    <div class="flex items-center space-x-3">
        <!-- Mobile Sidebar Toggle -->
        <button id="sidebarHeaderToggle" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition md:hidden">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
        
        <div class="flex items-center space-x-2 text-sm">
            <span class="text-xs font-extrabold text-blue-600 uppercase tracking-wider">ERP</span>
            <span class="text-slate-300">/</span>
            <h1 class="text-sm font-extrabold text-slate-800 tracking-tight" id="headerPageTitle">
                {!! strip_tags(View::yieldContent('title', 'Dashboard')) !!}
            </h1>
        </div>
    </div>

    <!-- Right: Today's Date & User Profile Badge -->
    <div class="flex items-center space-x-3">
        <!-- Date Badge -->
        <div class="hidden sm:flex items-center space-x-2 bg-slate-100/80 border border-slate-200/60 px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-600">
            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span>{{ \Carbon\Carbon::now()->format('l, d F Y') }}</span>
        </div>

        <!-- User Profile Dropdown / Badge -->
        <a href="{{ route('profile') }}" class="flex items-center space-x-2 p-1.5 pl-2 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200/80 transition group shadow-2xs">
            <div class="w-7 h-7 rounded-lg bg-blue-600 text-white font-bold flex items-center justify-center text-xs shadow-xs">
                {{ strtoupper(substr(Auth::user()->name ?? 'P', 0, 1)) }}
            </div>
            <div class="text-left hidden sm:block pr-1">
                <div class="text-xs font-bold text-slate-800 group-hover:text-blue-600 transition leading-tight">
                    {{ Auth::user()->name ?? 'Admin' }}
                </div>
            </div>
        </a>
    </div>
</header>
