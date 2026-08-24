<!-- TAB 3: User Access & Role Permissions Partial -->
<div id="settingsTab-users" class="tab-content {{ ($activeMainTab ?? 'profile') === 'users' ? '' : 'hidden' }} space-y-6">

    <!-- INLINE FORM: Add / Edit System User (Collapsible Card placed ABOVE Table) -->
    <div id="createUserFormCard" class="hidden bg-white rounded-2xl shadow-md border-2 border-blue-500/30 p-6 transition-all duration-300 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 id="userFormCardTitleHeader" class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg id="userFormHeaderSvg" class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span id="userFormCardTitle">Add New System User Account</span>
            </h3>
            <button type="button" onclick="toggleCreateUserForm()" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition cursor-pointer">&times; Close</button>
        </div>

        <form id="userCardForm" action="{{ route('settings.users.store') }}" method="POST" class="space-y-4" novalidate onsubmit="return handleUserFormSubmit(event);">
            @csrf
            <input type="hidden" name="_method" id="userFormMethodInput" value="POST">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Full Name <span class="text-rose-500">*</span></label>
                    <input type="text" id="cardUserNameInput" name="name" required placeholder="e.g. Ramesh Patel" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Login Email <span class="text-rose-500">*</span></label>
                    <input type="email" id="cardUserEmailInput" name="email" required placeholder="e.g. ramesh@example.com" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">
                        Password <span id="passwordRequiredHint" class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" id="cardUserPasswordInput" name="password" required minlength="6" placeholder="••••••••" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-4 pr-10 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <button type="button" onclick="togglePasswordVisibility('cardUserPasswordInput', 'cardUserPasswordIconEye', 'cardUserPasswordIconEyeOff')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer" title="Toggle password visibility">
                            <svg id="cardUserPasswordIconEye" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="cardUserPasswordIconEyeOff" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Assign System Role</label>
                    <select id="cardUserRoleSelect" name="role" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        @foreach($roles as $key => $r)
                            @if($key !== 'super_admin')
                                <option value="{{ $key }}">{{ $r['name'] }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="toggleCreateUserForm()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition cursor-pointer">Cancel</button>
                <button type="submit" id="userFormSubmitBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded-xl text-xs shadow-sm transition cursor-pointer">Create Account</button>
            </div>
        </form>
    </div>

    <!-- 1. All System User Accounts Table Card -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-800">All System Users & Team Accounts</h3>
            <button onclick="toggleCreateUserForm()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3.5 rounded-xl text-xs transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Add System User</span>
            </button>
        </div>

        <div class="overflow-x-auto w-full">
            <table class="erp-datatable min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50 text-slate-700 font-bold uppercase text-[10px]">
                    <tr>
                        <th class="p-3 text-center w-10">#</th>
                        <th class="p-3 text-left">User</th>
                        <th class="p-3 text-left">Email</th>
                        <th class="p-3 text-left">Role</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($users as $u)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="p-3 text-center font-bold text-slate-500">{{ $loop->iteration }}</td>
                            <td class="p-3 font-bold text-slate-800 flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 font-black flex items-center justify-center text-xs uppercase shrink-0">
                                    {{ substr($u->name, 0, 1) }}
                                </div>
                                <span>{{ $u->name }}</span>
                            </td>
                            <td class="p-3 font-mono text-slate-600">{{ $u->email }}</td>
                            <td class="p-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 capitalize">
                                    {{ str_replace('_', ' ', $u->role) }}
                                </span>
                            </td>
                            <td class="p-3 text-center user-status-cell">
                                @if($u->is_active && ($u->status === 'active' || $u->status === 'approved'))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-bold uppercase bg-rose-50 text-rose-700 border border-rose-200">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="p-3 text-center space-x-1.5 whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if(in_array($u->role, ['super_admin']))
                                        <!-- Protected Lock Badge -->
                                        <span class="w-7 h-7 rounded-lg bg-emerald-500 text-white flex items-center justify-center shadow-2xs inline-flex" title="Protected Super Admin Owner Account">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                        </span>
                                    @else
                                        @php
                                            $userIsActive = ($u->is_active && $u->status === 'active');
                                        @endphp

                                        <!-- 1. Active / Inactive Toggle Eye Button -->
                                        <button type="button" 
                                                data-active="{{ $userIsActive ? '1' : '0' }}" 
                                                onclick="toggleUserStatusAjax('{{ $u->id }}', '{{ addslashes($u->name) }}', this)" 
                                                title="{{ $userIsActive ? 'Deactivate User Account' : 'Activate User Account' }}" 
                                                class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg {{ $userIsActive ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-rose-500 hover:bg-rose-600' }} text-white shadow-2xs transition duration-150 transform hover:scale-105 cursor-pointer">
                                            @if($userIsActive)
                                                <!-- Active Eye Icon (Filled Solid) -->
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                                </svg>
                                            @else
                                                <!-- Inactive Eye-Slash Icon (Filled Solid) -->
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                                                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 1.002 0 1.97-.146 2.883-.404z" />
                                                </svg>
                                            @endif
                                        </button>

                                        <!-- 2. Edit User Details Button -->
                                        <button type="button" 
                                                data-user="{{ json_encode($u) }}"
                                                onclick="openEditUserModal(this)" 
                                                title="Edit User Details" 
                                                class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-2xs transition duration-150 transform hover:scale-105 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <!-- 3. Delete User Button -->
                                        <button type="button" 
                                                onclick="deleteUserAjax('{{ $u->id }}', '{{ addslashes($u->name) }}', this)" 
                                                class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-rose-500 hover:bg-rose-600 text-white shadow-2xs transition duration-150 transform hover:scale-105 cursor-pointer" 
                                                title="Delete User Account">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- 1. Role Actions & Pages Matrix Header & Table Card -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div class="space-y-1">
                <h2 class="text-lg font-black text-slate-800 flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </span>
                    Role Actions & Pages Matrix
                </h2>
                <p class="text-slate-500 text-xs font-medium pl-10">Toggle view page access and action authorization (Insert, Update, Delete) per role in real-time.</p>
            </div>

            <div class="flex items-center gap-2">
                <button onclick="openAddRoleModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3.5 rounded-xl text-xs transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Add Role</span>
                </button>
            </div>
        </div>

        <!-- Role Matrix Table with Inner Horizontal & Vertical Scroll -->
        <div class="overflow-x-auto max-w-full border border-slate-200/80 rounded-2xl relative shadow-2xs custom-horizontal-scrollbar">
            <table class="min-w-[1550px] w-full divide-y divide-slate-200 text-xs text-left whitespace-nowrap">
                <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[9.5px] sticky top-0 z-20 shadow-2xs border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-3 text-left min-w-[160px] sticky left-0 bg-slate-50 z-30 border-r border-slate-200/60 shadow-xs">Role</th>
                        <th class="py-3 px-2 text-center min-w-[95px]">Overview</th>
                        <th class="py-3 px-2 text-center min-w-[95px]">Orders</th>
                        <th class="py-3 px-2 text-center min-w-[95px]">Invoices</th>
                        <th class="py-3 px-2 text-center min-w-[95px]">Purchases</th>
                        <th class="py-3 px-2 text-center min-w-[95px]">Expenses</th>
                        <th class="py-3 px-2 text-center min-w-[100px]">Raw Mat.</th>
                        <th class="py-3 px-2 text-center min-w-[95px]">Products</th>
                        <th class="py-3 px-2 text-center min-w-[85px]">BOM</th>
                        <th class="py-3 px-2 text-center min-w-[105px]">Production</th>
                        <th class="py-3 px-2 text-center min-w-[95px]">Clients</th>
                        <th class="py-3 px-2 text-center min-w-[95px]">Employees</th>
                        <th class="py-3 px-2 text-center min-w-[95px]">Reports</th>
                        <th class="py-3 px-2 text-center min-w-[90px] bg-emerald-50/70 text-emerald-900">Insert</th>
                        <th class="py-3 px-2 text-center min-w-[90px] bg-amber-50/70 text-amber-900">Update</th>
                        <th class="py-3 px-2 text-center min-w-[90px] bg-rose-50/70 text-rose-900">Delete</th>
                        <th class="py-3 px-2 text-center min-w-[85px]">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($roles as $roleKey => $r)
                        @php
                            $rolePerms = \App\Services\RolePermissionService::getDefaultPermissionsForRole($roleKey);
                            $isSuperAdmin = ($roleKey === 'super_admin');
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition group">
                            <td class="p-3 font-bold text-slate-800 sticky left-0 bg-white group-hover:bg-slate-50 transition z-10 border-r border-slate-100 shadow-2xs">
                                <span class="block text-sm font-bold text-slate-900">{{ $r['name'] }}</span>
                            </td>

                            <!-- 1. Overview Dashboard Toggle -->
                            <td class="p-2.5 text-center">
                                <label class="inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" {{ $isSuperAdmin || in_array('page_overview', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'page_overview', this.checked)" class="matrix-toggle-input">
                                    <span class="matrix-toggle-slider"></span>
                                </label>
                            </td>

                            <!-- 2. Sales Orders Toggle -->
                            <td class="p-2.5 text-center">
                                <label class="inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" {{ $isSuperAdmin || in_array('page_orders', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'page_orders', this.checked)" class="matrix-toggle-input">
                                    <span class="matrix-toggle-slider"></span>
                                </label>
                            </td>

                            <!-- 3. Invoices Toggle -->
                            <td class="p-2.5 text-center">
                                <label class="inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" {{ $isSuperAdmin || in_array('page_invoices', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'page_invoices', this.checked)" class="matrix-toggle-input">
                                    <span class="matrix-toggle-slider"></span>
                                </label>
                            </td>

                            <!-- 4. Purchases Toggle -->
                            <td class="p-2.5 text-center">
                                <label class="inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" {{ $isSuperAdmin || in_array('page_purchases', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'page_purchases', this.checked)" class="matrix-toggle-input">
                                    <span class="matrix-toggle-slider"></span>
                                </label>
                            </td>

                            <!-- 5. Expenses Toggle -->
                            <td class="p-2.5 text-center">
                                <label class="inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" {{ $isSuperAdmin || in_array('page_expenses', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'page_expenses', this.checked)" class="matrix-toggle-input">
                                    <span class="matrix-toggle-slider"></span>
                                </label>
                            </td>

                            <!-- 6. Raw Materials Toggle -->
                            <td class="p-2.5 text-center">
                                <label class="inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" {{ $isSuperAdmin || in_array('page_rawmaterial', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'page_rawmaterial', this.checked)" class="matrix-toggle-input">
                                    <span class="matrix-toggle-slider"></span>
                                </label>
                            </td>

                            <!-- 7. Products Toggle -->
                            <td class="p-2.5 text-center">
                                <label class="inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" {{ $isSuperAdmin || in_array('page_product', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'page_product', this.checked)" class="matrix-toggle-input">
                                    <span class="matrix-toggle-slider"></span>
                                </label>
                            </td>

                            <!-- 8. BOM Toggle -->
                            <td class="p-2.5 text-center">
                                <label class="inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" {{ $isSuperAdmin || in_array('page_bom', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'page_bom', this.checked)" class="matrix-toggle-input">
                                    <span class="matrix-toggle-slider"></span>
                                </label>
                            </td>

                            <!-- 9. Production Logs Toggle -->
                            <td class="p-2.5 text-center">
                                <label class="inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" {{ $isSuperAdmin || in_array('page_production', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'page_production', this.checked)" class="matrix-toggle-input">
                                    <span class="matrix-toggle-slider"></span>
                                </label>
                            </td>

                            <!-- 10. Clients & Plants Toggle -->
                            <td class="p-2.5 text-center">
                                <label class="inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" {{ $isSuperAdmin || in_array('page_clients', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'page_clients', this.checked)" class="matrix-toggle-input">
                                    <span class="matrix-toggle-slider"></span>
                                </label>
                            </td>

                            <!-- 11. Employees Toggle -->
                            <td class="p-2.5 text-center">
                                <label class="inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" {{ $isSuperAdmin || in_array('page_employees', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'page_employees', this.checked)" class="matrix-toggle-input">
                                    <span class="matrix-toggle-slider"></span>
                                </label>
                            </td>

                            <!-- 12. Reports Toggle -->
                            <td class="p-2.5 text-center">
                                <label class="inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" {{ $isSuperAdmin || in_array('page_reports', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'page_reports', this.checked)" class="matrix-toggle-input">
                                    <span class="matrix-toggle-slider"></span>
                                </label>
                            </td>

                            <!-- Action Insert Toggle (Green) -->
                            <td class="p-2.5 text-center bg-emerald-50/30">
                                <label class="inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" {{ $isSuperAdmin || in_array('action_insert', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'action_insert', this.checked)" class="matrix-toggle-input matrix-toggle-input-green">
                                    <span class="matrix-toggle-slider"></span>
                                </label>
                            </td>

                            <!-- Action Update Toggle (Yellow) -->
                            <td class="p-2.5 text-center bg-amber-50/30">
                                <label class="inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" {{ $isSuperAdmin || in_array('action_update', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'action_update', this.checked)" class="matrix-toggle-input matrix-toggle-input-amber">
                                    <span class="matrix-toggle-slider"></span>
                                </label>
                            </td>

                            <!-- Action Delete Toggle (Red) -->
                            <td class="p-2.5 text-center bg-rose-50/30">
                                <label class="inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" {{ $isSuperAdmin || in_array('action_delete', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'action_delete', this.checked)" class="matrix-toggle-input matrix-toggle-input-rose">
                                    <span class="matrix-toggle-slider"></span>
                                </label>
                            </td>

                            <!-- Actions Column -->
                            <td class="p-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if($isSuperAdmin)
                                        <span class="w-7 h-7 rounded-lg bg-emerald-500 text-white flex items-center justify-center shadow-xs" title="Protected Owner Role">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                        </span>
                                    @else
                                        @php
                                            $roleRecord = $customRolesList->firstWhere('slug', $roleKey);
                                            $roleIsActive = $roleRecord ? (bool)$roleRecord->is_active : true;
                                        @endphp
                                        <button type="button" 
                                                data-active="{{ $roleIsActive ? '1' : '0' }}" 
                                                onclick="toggleRoleStatusAjax('{{ $roleKey }}', '{{ addslashes($r['name']) }}', this)" 
                                                title="{{ $roleIsActive ? 'Deactivate Role' : 'Activate Role' }}" 
                                                class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg {{ $roleIsActive ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-rose-500 hover:bg-rose-600' }} text-white shadow-2xs transition duration-150 transform hover:scale-105 cursor-pointer">
                                            @if($roleIsActive)
                                                <!-- Active Eye Icon (Filled Solid) -->
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                                </svg>
                                            @else
                                                <!-- Inactive Eye-Slash Icon (Filled Solid) -->
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                                                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 1.002 0 1.97-.146 2.883-.404z" />
                                                </svg>
                                            @endif
                                        </button>

                                        @if($roleKey !== 'super_admin')
                                            <button type="button" 
                                                    onclick="deleteRoleAjax('{{ $roleRecord ? $roleRecord->id : $roleKey }}', '{{ addslashes($r['name']) }}', this)" 
                                                    class="w-7 h-7 rounded-lg bg-rose-500 hover:bg-rose-600 text-white inline-flex items-center justify-center shadow-2xs transition duration-150 transform hover:scale-105 cursor-pointer" 
                                                    title="Delete Role">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
