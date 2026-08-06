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

    <!-- Right Side: Today's Date & User Profile Dropdown Icon -->
    <div class="flex items-center space-x-4">
        <!-- Today's Date Badge -->
        <div class="hidden sm:flex items-center space-x-2 bg-slate-100/80 border border-slate-200/80 px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-600">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span>{{ \Carbon\Carbon::now()->format('l, d F Y') }}</span>
        </div>

        <!-- Theme Mode Switcher Button -->
        <button type="button" onclick="window.toggleTheme()" class="p-2 rounded-xl bg-slate-100/80 hover:bg-slate-200/80 text-slate-600 transition cursor-pointer border border-slate-200/80" title="Toggle Light / Dark Theme">
            <svg class="w-4 h-4 text-amber-500 block dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 100 2h1z" clip-rule="evenodd" />
            </svg>
            <svg class="w-4 h-4 text-indigo-400 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
            </svg>
        </button>

        <!-- User Profile Icon with Hover / Click Popover Menu -->
        <div class="relative inline-block text-left group" id="userProfileDropdownWrapper">
            <!-- Icon Button Only -->
            <button type="button" 
                    id="userProfileIconBtn"
                    onclick="toggleHeaderProfileCard(event)"
                    class="w-9 h-9 rounded-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm flex items-center justify-center shadow-xs border-2 border-white ring-2 ring-blue-100 transition duration-150 cursor-pointer focus:outline-none"
                    title="User Account & Settings">
                {{ strtoupper(substr(Auth::user()->name ?? 'P', 0, 1)) }}
            </button>

            <!-- Hover / Click Dropdown Card (Positioned directly below the avatar button) -->
            <div id="headerUserProfileCard" 
                 class="absolute right-0 top-full mt-2 w-64 bg-white rounded-2xl shadow-2xl border border-slate-200/90 p-4 space-y-3 hidden group-hover:block transition-all duration-200 z-50">
                
                <!-- User Info Header -->
                <div class="flex items-center space-x-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 font-black text-base flex items-center justify-center shrink-0 uppercase border border-blue-200">
                        {{ strtoupper(substr(Auth::user()->name ?? 'P', 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-grow">
                        <h4 class="text-sm font-bold text-slate-800 truncate leading-tight">{{ Auth::user()->name ?? 'User' }}</h4>
                        <p class="text-[11px] text-slate-500 font-mono truncate">{{ Auth::user()->email ?? '' }}</p>
                        <span class="inline-block mt-1 px-2 py-0.5 bg-blue-50 text-blue-700 text-[10px] font-bold rounded-md border border-blue-100 uppercase tracking-wider">
                            {{ ucfirst(str_replace('_', ' ', Auth::user()->role ?? 'Staff')) }}
                        </span>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="space-y-1">
                    <a href="{{ route('profile') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>My Profile & Account</span>
                    </a>

                    @if(in_array(Auth::user()->role ?? '', ['super_admin', 'admin']))
                        <a href="{{ route('settings.index') }}" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>System Settings</span>
                        </a>
                    @endif
                </div>

                <!-- Logout Action -->
                <div class="pt-2 border-t border-slate-100">
                    <form action="{{ route('logout') }}" method="POST" class="w-full">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center space-x-2 px-3 py-2 rounded-xl text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100/80 transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span>Logout Account</span>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</header>

<script>
function toggleHeaderProfileCard(e) {
    if (e) e.stopPropagation();
    const card = document.getElementById('headerUserProfileCard');
    if (card) {
        card.classList.toggle('hidden');
    }
}

document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('userProfileDropdownWrapper');
    const card = document.getElementById('headerUserProfileCard');
    if (wrapper && card && !wrapper.contains(e.target)) {
        if (!wrapper.matches(':hover')) {
            card.classList.add('hidden');
        }
    }
});
</script>
