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
         class="fixed inset-x-3 top-16 sm:absolute sm:inset-x-auto sm:right-0 sm:top-full sm:mt-2 w-auto sm:w-64 max-w-[calc(100vw-1.5rem)] sm:max-w-none bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200/90 dark:border-slate-800 p-4 space-y-3 hidden group-hover:block transition-all duration-200 z-50">
        
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
