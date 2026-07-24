@extends('layouts.app')

@section('title', 'Clients & Plants')

@section('content')
@php
$indianStates = [
    'Gujarat' => '24 - Gujarat (Intrastate CGST+SGST)',
    'Maharashtra' => '27 - Maharashtra (Interstate IGST)',
    'Madhya Pradesh' => '23 - Madhya Pradesh (Interstate IGST)',
    'Rajasthan' => '08 - Rajasthan (Interstate IGST)',
    'Delhi' => '07 - Delhi (Interstate IGST)',
    'Haryana' => '06 - Haryana (Interstate IGST)',
    'Punjab' => '03 - Punjab (Interstate IGST)',
    'Uttar Pradesh' => '09 - Uttar Pradesh (Interstate IGST)',
    'West Bengal' => '19 - West Bengal (Interstate IGST)',
    'Karnataka' => '29 - Karnataka (Interstate IGST)',
    'Telangana' => '36 - Telangana (Interstate IGST)',
    'Tamil Nadu' => '33 - Tamil Nadu (Interstate IGST)',
    'Kerala' => '32 - Kerala (Interstate IGST)',
    'Goa' => '30 - Goa (Interstate IGST)',
    'Andhra Pradesh' => '37 - Andhra Pradesh (Interstate IGST)',
    'Bihar' => '10 - Bihar (Interstate IGST)',
    'Odisha' => '21 - Odisha (Interstate IGST)',
    'Himachal Pradesh' => '02 - Himachal Pradesh (Interstate IGST)',
    'Uttarakhand' => '05 - Uttarakhand (Interstate IGST)',
    'Jammu & Kashmir' => '01 - Jammu & Kashmir (Interstate IGST)',
    'Ladakh' => '38 - Ladakh (Interstate IGST)',
    'Chandigarh' => '04 - Chandigarh (Interstate IGST)',
    'Jharkhand' => '20 - Jharkhand (Interstate IGST)',
    'Chhattisgarh' => '22 - Chhattisgarh (Interstate IGST)',
    'Assam' => '18 - Assam (Interstate IGST)',
    'Sikkim' => '11 - Sikkim (Interstate IGST)',
    'Arunachal Pradesh' => '12 - Arunachal Pradesh (Interstate IGST)',
    'Nagaland' => '13 - Nagaland (Interstate IGST)',
    'Manipur' => '14 - Manipur (Interstate IGST)',
    'Mizoram' => '15 - Mizoram (Interstate IGST)',
    'Tripura' => '16 - Tripura (Interstate IGST)',
    'Meghalaya' => '17 - Meghalaya (Interstate IGST)',
    'Puducherry' => '34 - Puducherry (Interstate IGST)',
    'Daman & Diu' => '25 - Daman & Diu (Interstate IGST)',
    'Dadra & Nagar Haveli' => '26 - Dadra & Nagar Haveli (Interstate IGST)',
    'Andaman & Nicobar Islands' => '35 - Andaman & Nicobar (Interstate IGST)',
];
@endphp
<div class="space-y-6">
    <!-- Header with Top Action Buttons -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight flex items-center">
                <svg class="w-6 h-6 mr-2.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2.5M9 21h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Clients & Delivery Locations
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-1">Manage client profiles, state-specific GSTINs, and delivery plant locations.</p>
        </div>
        <div class="flex items-center space-x-3">
            <button type="button" onclick="toggleRegisterPlantForm()" class="bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 py-2.5 px-4 rounded-xl text-xs font-bold transition duration-150 flex items-center">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Register Plant</span>
            </button>
            <button type="button" onclick="toggleCreateClientForm()" class="btn-primary py-2.5 px-5 text-xs font-bold shadow-xs flex items-center">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Create Client Profile</span>
            </button>
        </div>
    </div>

    <!-- INLINE FORM 1: Create Client Profile (Collapsible Card) -->
    <div id="createClientFormCard" class="hidden bg-white rounded-2xl shadow-md border-2 border-blue-500/30 p-6 transition-all duration-300">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
            <h3 class="text-base font-bold text-slate-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Create Client Profile
            </h3>
            <button type="button" onclick="toggleCreateClientForm()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times; Close</button>
        </div>

        <form action="{{ route('clients.store') }}" method="POST" class="ajax-form space-y-4">
            @csrf
            <input type="hidden" name="create_primary_plant" id="create_primary_plant_flag" value="1">

            <!-- Toggle Switch Bar: Single Office vs Multi-Plant -->
            <div class="flex items-center justify-center p-1 bg-slate-100/90 rounded-xl max-w-md mx-auto border border-slate-200/80 shadow-2xs">
                <button type="button" id="btnSingleLocationMode" onclick="setClientRegistrationMode('single')" 
                        class="flex-1 py-2 px-3 text-xs font-extrabold rounded-lg transition-all duration-200 bg-white text-blue-600 shadow-xs border border-slate-200/60">
                    📍 Single Location Client
                </button>
                <button type="button" id="btnMultiLocationMode" onclick="setClientRegistrationMode('multi')" 
                        class="flex-1 py-2 px-3 text-xs font-bold rounded-lg transition-all duration-200 text-slate-500 hover:text-slate-700">
                    🏢 Multi-Plant Client
                </button>
            </div>

            <!-- Mode Notice Banner -->
            <div id="clientModeNotice" class="text-center text-xs font-medium text-slate-500 bg-blue-50/50 py-1.5 px-3 rounded-lg border border-blue-100/60">
                📍 <strong>Single Location Mode:</strong> Registers client profile and primary delivery location together in 1-Click.
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Company Name</label>
                    <input type="text" name="company_name" placeholder="e.g. Balaji Wafers Pvt. Ltd." required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Email Address</label>
                    <input type="email" name="client_email" placeholder="e.g. billing@balajiwafers.com" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>
                <!-- State Selection (Visible in Single Location Mode) -->
                <div id="singleStateFieldWrapper">
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">State (GST Region)</label>
                    <select name="state" id="create_client_state_select" class="searchable-select w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                        @foreach ($indianStates as $stVal => $stLabel)
                            <option value="{{ $stVal }}" {{ $stVal === 'Gujarat' ? 'selected' : '' }}>{{ $stLabel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">GSTIN (15 Digits)</label>
                    <input type="text" name="gst_number" placeholder="e.g. 24AAAAB1111A1Z1" minlength="15" maxlength="15" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-mono uppercase">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Office & Delivery Address</label>
                    <textarea name="corporate_address" rows="1" placeholder="Full office & factory address..." required
                              class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-2 border-t border-slate-100">
                <button type="button" onclick="toggleCreateClientForm()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800">Cancel</button>
                <button type="submit" class="btn-primary py-2 px-6 text-xs font-bold">Save Client Profile</button>
            </div>
        </form>
    </div>

    <!-- INLINE FORM 2: Register Plant (Collapsible Card) -->
    <div id="createPlantFormCard" class="hidden bg-white rounded-2xl shadow-md border-2 border-slate-300 p-6 transition-all duration-300">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
            <h3 class="text-base font-bold text-slate-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Register B2B Delivery Plant
            </h3>
            <button type="button" onclick="toggleCreatePlantForm()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times; Close</button>
        </div>
        <form action="{{ route('clients.plants.store') }}" method="POST" class="ajax-form space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Client Profile</label>
                    <select name="client_id" id="inline_create_plant_client_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium" required>
                        <option value="">Select client...</option>
                        @foreach ($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->company_name }} ({{ $c->gst_number }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Plant Location Name</label>
                    <input type="text" name="plant_name" placeholder="e.g. Rajkot Factory" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Plant State (GST Region)</label>
                    <select name="state" class="searchable-select w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium" required>
                        @foreach ($indianStates as $stVal => $stLabel)
                            <option value="{{ $stVal }}" {{ $stVal === 'Gujarat' ? 'selected' : '' }}>{{ $stLabel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">State Plant GSTIN (Optional, 15 Digits)</label>
                    <input type="text" name="gst_number" placeholder="e.g. 27AAAAB1111A1Z5 (Leave blank if same as Main GSTIN)" minlength="15" maxlength="15"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-mono uppercase">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Shipping Warehouse Address</label>
                    <input type="text" name="shipping_address" placeholder="Full plant shipping address..." required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-2">
                <button type="button" onclick="toggleCreatePlantForm()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800">Cancel</button>
                <button type="submit" class="btn-primary py-2 px-6 text-xs font-bold">Register Plant Address</button>
            </div>
        </form>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs flex items-center space-x-3">
        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        <input type="text" id="clientSearchInput" onkeyup="filterClients()" placeholder="Filter clients by company name, GSTIN, city or state..."
               class="w-full text-sm text-slate-700 placeholder-slate-400 bg-transparent outline-none">
    </div>

    <!-- Clients Directory Cards -->
    <div id="clientsContainer" class="space-y-6">
        @if ($clients->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-400">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2.5M9 21h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <p class="text-sm font-semibold">No client profiles registered yet.</p>
                <button type="button" onclick="toggleCreateClientForm()" class="btn-primary mt-4 py-2 px-4 text-xs font-bold">
                    Create First Client
                </button>
            </div>
        @else
            @foreach ($clients as $c)
                <div class="client-card bg-white border border-slate-200 rounded-2xl p-6 shadow-xs hover:border-slate-300 transition" id="client-card-{{ $c->id }}" data-search="{{ strtolower($c->company_name . ' ' . $c->gst_number . ' ' . $c->client_email . ' ' . $c->corporate_address . ' ' . $c->plants->pluck('plant_name')->implode(' ') . ' ' . $c->plants->pluck('state')->implode(' ')) }}">
                    <!-- Card Top Header -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-slate-100 pb-4 mb-4 gap-3">
                        <div>
                            <div class="flex items-center space-x-3">
                                <h3 class="text-lg font-bold text-slate-800">{{ $c->company_name }}</h3>
                                @if ($c->plants->count() === 1)
                                    <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-100">
                                        📍 Single Location
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 bg-blue-50 text-blue-700 text-xs font-bold rounded-lg border border-blue-100">
                                        🏢 {{ $c->plants->count() }} {{ Str::plural('plant', $c->plants->count()) }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center gap-x-4 text-xs text-slate-500 font-mono mt-1.5">
                                <span>Main GSTIN: <span class="font-bold text-slate-700">{{ $c->gst_number }}</span></span>
                            <span class="text-slate-300">|</span>
                            <span>Outstanding Dues: <span class="font-bold font-mono {{ $c->outstanding_balance > 0 ? 'text-amber-600' : 'text-emerald-600' }}">₹{{ number_format($c->outstanding_balance, 2) }}</span></span>
                        </div>
                    </div>

                    <!-- Client Universal Action Buttons -->
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('clients.ledger', $c->id) }}" 
                           class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition flex items-center space-x-1 shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span>Account Ledger</span>
                        </a>
                        @if ($c->plants->count() !== 1)
                            <button type="button" 
                                    onclick="openCreatePlantFormForClient({{ $c->id }}, '{{ addslashes($c->company_name) }}')"
                                    class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg text-xs font-bold transition flex items-center space-x-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                <span>Add Plant</span>
                            </button>
                        @endif
                            <button type="button" 
                                    title="Edit Client Profile"
                                    onclick="openEditClientForm({{ $c->id }}, '{{ addslashes($c->company_name) }}', '{{ addslashes($c->client_email) }}', '{{ addslashes($c->gst_number) }}', '{{ addslashes($c->corporate_address) }}')"
                                    class="w-8 h-8 p-1.5 inline-flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-xs transition duration-150 transform hover:scale-105">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            <button type="button" 
                                    title="Delete Client Profile"
                                    onclick="deleteClient({{ $c->id }}, '{{ addslashes($c->company_name) }}')"
                                    class="w-8 h-8 p-1.5 inline-flex items-center justify-center rounded-lg bg-rose-500 hover:bg-rose-600 text-white shadow-xs transition duration-150 transform hover:scale-105">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- INLINE EDIT CLIENT FORM (COLLAPSIBLE PER CLIENT) -->
                    <div id="inlineEditClientForm-{{ $c->id }}" class="hidden mb-6 p-5 bg-amber-50/60 border border-amber-200 rounded-xl">
                        <div class="flex items-center justify-between border-b border-amber-200/60 pb-3 mb-3">
                            <h4 class="text-sm font-bold text-amber-900">Edit Client Profile</h4>
                            <button type="button" onclick="closeEditClientForm({{ $c->id }})" class="text-amber-700 hover:text-amber-900 text-xs font-bold">&times; Close</button>
                        </div>
                        <form action="{{ route('clients.update', $c->id) }}" method="POST" class="ajax-form space-y-3">
                            @csrf
                            @method('PUT')
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Company Name</label>
                                    <input type="text" name="company_name" id="edit_company_name_{{ $c->id }}" value="{{ $c->company_name }}" required
                                           class="w-full bg-white border border-slate-200 rounded-lg py-1.5 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Email Address</label>
                                    <input type="email" name="client_email" id="edit_client_email_{{ $c->id }}" value="{{ $c->client_email }}" required
                                           class="w-full bg-white border border-slate-200 rounded-lg py-1.5 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Main GSTIN</label>
                                    <input type="text" name="gst_number" id="edit_gst_number_{{ $c->id }}" value="{{ $c->gst_number }}" required
                                           class="w-full bg-white border border-slate-200 rounded-lg py-1.5 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700 font-mono">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Main Billing Address</label>
                                <textarea name="corporate_address" id="edit_corporate_address_{{ $c->id }}" rows="2" required
                                          class="w-full bg-white border border-slate-200 rounded-lg py-1.5 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700">{{ $c->corporate_address }}</textarea>
                            </div>
                            <div class="flex items-center justify-end space-x-2 pt-2">
                                <button type="button" onclick="closeEditClientForm({{ $c->id }})" class="px-3 py-1.5 text-xs font-bold text-slate-600 hover:text-slate-800">Cancel</button>
                                <button type="submit" class="btn-primary py-1.5 px-4 text-xs font-bold">Update Profile</button>
                            </div>
                        </form>
                    </div>

                    <!-- Card Body Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Main Billing Address Section -->
                        <div class="lg:col-span-1 border-b lg:border-b-0 lg:border-r border-slate-100 pb-4 lg:pb-0 lg:pr-6">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Main Billing Address</span>
                            <p class="text-sm text-slate-600 leading-relaxed">{{ $c->corporate_address }}</p>
                        </div>
                        
                        <!-- Delivery Plants Grid -->
                        <div class="lg:col-span-2">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Registered Delivery Plants</span>
                            </div>

                            @if ($c->plants->isEmpty())
                                <div class="p-4 bg-slate-50 border border-dashed border-slate-200 rounded-xl text-center">
                                    <p class="text-xs text-slate-400 italic">No delivery plants attached to this client yet.</p>
                                    <button type="button" onclick="openCreatePlantFormForClient({{ $c->id }}, '{{ addslashes($c->company_name) }}')"
                                            class="mt-2 text-xs font-bold text-blue-600 hover:text-blue-700">
                                        + Register First Delivery Plant
                                    </button>
                                </div>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach ($c->plants as $p)
                                        @php
                                            $gstStateCodesMap = [
                                                'Jammu & Kashmir' => '01', 'Himachal Pradesh' => '02', 'Punjab' => '03', 'Chandigarh' => '04',
                                                'Uttarakhand' => '05', 'Haryana' => '06', 'Delhi' => '07', 'Rajasthan' => '08',
                                                'Uttar Pradesh' => '09', 'Bihar' => '10', 'Sikkim' => '11', 'Arunachal Pradesh' => '12',
                                                'Nagaland' => '13', 'Manipur' => '14', 'Mizoram' => '15', 'Tripura' => '16',
                                                'Meghalaya' => '17', 'Assam' => '18', 'West Bengal' => '19', 'Jharkhand' => '20',
                                                'Odisha' => '21', 'Chhattisgarh' => '22', 'Madhya Pradesh' => '23', 'Gujarat' => '24',
                                                'Daman & Diu' => '25', 'Dadra & Nagar Haveli' => '26', 'Maharashtra' => '27', 'Andhra Pradesh' => '28',
                                                'Karnataka' => '29', 'Goa' => '30', 'Lakshadweep' => '31', 'Kerala' => '32',
                                                'Tamil Nadu' => '33', 'Puducherry' => '34', 'Andaman & Nicobar' => '35', 'Telangana' => '36', 'Ladakh' => '37'
                                            ];
                                            $pStateCode = $gstStateCodesMap[$p->state] ?? '24';
                                            $clientGstStateCode = substr($c->gst_number, 0, 2);
                                            $isDiffState = !empty($c->gst_number) && strlen($c->gst_number) >= 2 && $clientGstStateCode !== $pStateCode;
                                        @endphp
                                        <div class="bg-slate-50/70 border border-slate-200 rounded-xl p-4 flex flex-col justify-between shadow-2xs hover:bg-slate-50 transition" id="plant-card-{{ $p->id }}">
                                            <div>
                                                <div class="flex items-start justify-between gap-2">
                                                    <h4 class="font-bold text-sm text-slate-800">{{ $p->plant_name }}</h4>
                                                    <span class="px-2 py-0.5 bg-blue-100 border border-blue-200 text-blue-800 text-[10px] rounded-md font-bold uppercase tracking-wider shrink-0">
                                                        {{ $p->state }}
                                                    </span>
                                                </div>
                                                <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">{{ $p->shipping_address }}</p>
                                                
                                                <div class="mt-2.5 pt-2 border-t border-slate-200/60 text-[11px] font-mono flex items-center justify-between">
                                                    <div>
                                                        @if ($p->gst_number)
                                                            <span class="text-slate-500">GSTIN: <strong class="text-slate-700">{{ $p->gst_number }}</strong></span>
                                                        @elseif ($isDiffState)
                                                            <span class="inline-flex items-center text-amber-800 font-bold bg-amber-50 px-2 py-0.5 rounded border border-amber-200 text-[10px]">
                                                                ⚠️ Out-of-State: Needs GSTIN (Code: {{ $pStateCode }})
                                                            </span>
                                                        @else
                                                            <span class="text-slate-400 italic">GSTIN: Same as Main</span>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <span class="text-slate-500">Dues: <strong class="{{ $p->outstanding_balance > 0 ? 'text-amber-600' : 'text-emerald-600' }}">₹{{ number_format($p->outstanding_balance, 2) }}</strong></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Plant Action Buttons -->
                                            <div class="flex items-center justify-between pt-3 mt-2 border-t border-slate-100">
                                                <a href="{{ route('clients.ledger', ['id' => $c->id, 'plant_id' => $p->id]) }}" 
                                                   class="px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg text-[11px] font-bold transition flex items-center space-x-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                    <span>Plant Ledger</span>
                                                </a>
                                                <div class="flex items-center space-x-1.5">
                                                    <button type="button" 
                                                            title="Edit Plant Details"
                                                            onclick="openEditPlantForm({{ $p->id }}, '{{ addslashes($p->plant_name) }}', '{{ addslashes($p->state) }}', '{{ addslashes($p->gst_number ?? '') }}', '{{ addslashes($p->shipping_address) }}')"
                                                            class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-2xs transition duration-150 transform hover:scale-105">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                    </button>
                                                    <button type="button" 
                                                            title="Delete Plant"
                                                            onclick="deletePlant({{ $p->id }}, '{{ addslashes($p->plant_name) }}')"
                                                            class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-rose-500 hover:bg-rose-600 text-white shadow-2xs transition duration-150 transform hover:scale-105">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- INLINE EDIT PLANT FORM (PER PLANT) -->
                                            <div id="inlineEditPlantForm-{{ $p->id }}" class="hidden mt-3 p-3 bg-amber-50/70 border border-amber-200 rounded-lg">
                                                <div class="flex items-center justify-between border-b border-amber-200/60 pb-2 mb-2">
                                                    <h5 class="text-xs font-bold text-amber-900">Edit Plant</h5>
                                                    <button type="button" onclick="closeEditPlantForm({{ $p->id }})" class="text-amber-700 hover:text-amber-900 text-xs font-bold">&times;</button>
                                                </div>
                                                <form action="{{ route('clients.plants.update', $p->id) }}" method="POST" class="ajax-form space-y-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <div>
                                                        <label class="block text-[10px] font-bold text-slate-600 uppercase mb-0.5">Plant Name</label>
                                                        <input type="text" name="plant_name" id="edit_plant_name_{{ $p->id }}" value="{{ $p->plant_name }}" required
                                                               class="w-full bg-white border border-slate-200 rounded py-1 px-2 text-xs text-slate-700">
                                                    </div>
                                                    <div>
                                                        <label class="block text-[10px] font-bold text-slate-600 uppercase mb-0.5">State</label>
                                                        <select name="state" id="edit_plant_state_{{ $p->id }}" class="searchable-select w-full bg-white border border-slate-200 rounded py-1 px-2 text-xs text-slate-700" required>
                                                            @foreach ($indianStates as $stVal => $stLabel)
                                                                <option value="{{ $stVal }}" {{ $p->state === $stVal ? 'selected' : '' }}>{{ $stLabel }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-[10px] font-bold text-slate-600 uppercase mb-0.5">
                                                            Plant GSTIN (15 Digits) {{ $isDiffState ? '- REQUIRED (State Code: ' . $pStateCode . ')' : '(Optional)' }}
                                                        </label>
                                                        <input type="text" name="gst_number" id="edit_plant_gst_number_{{ $p->id }}" value="{{ $p->gst_number }}" 
                                                               placeholder="{{ $isDiffState ? 'e.g. ' . $pStateCode . 'AAAAB1111A1Z5 (Required for ' . $p->state . ')' : 'Leave blank if same as Main GSTIN' }}" 
                                                               minlength="15" maxlength="15" {{ $isDiffState && empty($p->gst_number) ? 'required' : '' }}
                                                               class="w-full bg-white border border-slate-200 rounded py-1 px-2 text-xs font-mono text-slate-700 uppercase">
                                                    </div>
                                                    <div>
                                                        <label class="block text-[10px] font-bold text-slate-600 uppercase mb-0.5">Shipping Address</label>
                                                        <textarea name="shipping_address" id="edit_plant_shipping_address_{{ $p->id }}" rows="2" required
                                                                  class="w-full bg-white border border-slate-200 rounded py-1 px-2 text-xs text-slate-700">{{ $p->shipping_address }}</textarea>
                                                    </div>
                                                    <div class="flex items-center justify-end space-x-2 pt-1">
                                                        <button type="button" onclick="closeEditPlantForm({{ $p->id }})" class="px-2 py-1 text-[11px] font-bold text-slate-600">Cancel</button>
                                                        <button type="submit" class="btn-primary py-1 px-3 text-[11px] font-bold">Save</button>
                                                    </div>
                                                </form>
                                            </div>

                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

<script>
function setClientRegistrationMode(mode) {
    var btnSingle = document.getElementById('btnSingleLocationMode');
    var btnMulti = document.getElementById('btnMultiLocationMode');
    var stateWrapper = document.getElementById('singleStateFieldWrapper');
    var flagInput = document.getElementById('create_primary_plant_flag');
    var modeNotice = document.getElementById('clientModeNotice');

    if (mode === 'single') {
        if (flagInput) flagInput.value = "1";
        if (stateWrapper) stateWrapper.classList.remove('hidden');
        if (btnSingle) {
            btnSingle.className = "flex-1 py-2 px-3 text-xs font-extrabold rounded-lg transition-all duration-200 bg-white text-blue-600 shadow-xs border border-slate-200/60";
        }
        if (btnMulti) {
            btnMulti.className = "flex-1 py-2 px-3 text-xs font-bold rounded-lg transition-all duration-200 text-slate-500 hover:text-slate-700";
        }
        if (modeNotice) {
            modeNotice.innerHTML = "📍 <strong>Single Location Mode:</strong> Registers client profile and primary delivery location together in 1-Click.";
        }
    } else {
        if (flagInput) flagInput.value = "0";
        if (stateWrapper) stateWrapper.classList.add('hidden');
        if (btnMulti) {
            btnMulti.className = "flex-1 py-2 px-3 text-xs font-extrabold rounded-lg transition-all duration-200 bg-white text-blue-600 shadow-xs border border-slate-200/60";
        }
        if (btnSingle) {
            btnSingle.className = "flex-1 py-2 px-3 text-xs font-bold rounded-lg transition-all duration-200 text-slate-500 hover:text-slate-700";
        }
        if (modeNotice) {
            modeNotice.innerHTML = "🏢 <strong>Multi-Plant Mode:</strong> Creates client profile only. You can register individual plants for different states afterwards.";
        }
    }
}

function toggleCreateClientForm() {
    var card = document.getElementById('createClientFormCard');
    var plantCard = document.getElementById('createPlantFormCard');
    if (plantCard) plantCard.classList.add('hidden');
    if (card) {
        if (card.classList.contains('hidden')) {
            if (window.resetFormAndErrors) window.resetFormAndErrors('#createClientFormCard form');
            card.classList.remove('hidden');
            card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            if (window.resetFormAndErrors) window.resetFormAndErrors('#createClientFormCard form');
            card.classList.add('hidden');
        }
    }
}

function toggleCreatePlantForm() {
    var card = document.getElementById('createPlantFormCard');
    var clientCard = document.getElementById('createClientFormCard');
    if (clientCard) clientCard.classList.add('hidden');
    if (card) {
        if (card.classList.contains('hidden')) {
            if (window.resetFormAndErrors) window.resetFormAndErrors('#createPlantFormCard form');
            card.classList.remove('hidden');
            card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            if (window.resetFormAndErrors) window.resetFormAndErrors('#createPlantFormCard form');
            card.classList.add('hidden');
        }
    }
}

function openCreatePlantFormForClient(clientId, clientName) {
    if (window.resetFormAndErrors) window.resetFormAndErrors('#createPlantFormCard form');
    var select = document.getElementById('inline_create_plant_client_id');
    if (select) select.value = clientId;
    var card = document.getElementById('createPlantFormCard');
    if (card) {
        card.classList.remove('hidden');
        card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function openEditClientForm(id, name, email, gst, address) {
    var formCard = document.getElementById('inlineEditClientForm-' + id);
    if (formCard) {
        if (formCard.classList.contains('hidden')) {
            if (window.resetFormAndErrors) window.resetFormAndErrors('#inlineEditClientForm-' + id + ' form');
            // re-populate values
            var nameEl = document.getElementById('edit_company_name_' + id);
            if (nameEl) nameEl.value = name;
            var emailEl = document.getElementById('edit_client_email_' + id);
            if (emailEl) emailEl.value = email;
            var gstEl = document.getElementById('edit_gst_number_' + id);
            if (gstEl) gstEl.value = gst;
            var addrEl = document.getElementById('edit_corporate_address_' + id);
            if (addrEl) addrEl.value = address;
            formCard.classList.remove('hidden');
        } else {
            if (window.resetFormAndErrors) window.resetFormAndErrors('#inlineEditClientForm-' + id + ' form');
            formCard.classList.add('hidden');
        }
    }
}
function closeEditClientForm(id) {
    if (window.resetFormAndErrors) window.resetFormAndErrors('#inlineEditClientForm-' + id + ' form');
    var formCard = document.getElementById('inlineEditClientForm-' + id);
    if (formCard) formCard.classList.add('hidden');
}

function openEditPlantForm(id, name, state, gst, address) {
    var formCard = document.getElementById('inlineEditPlantForm-' + id);
    if (formCard) {
        if (formCard.classList.contains('hidden')) {
            if (window.resetFormAndErrors) window.resetFormAndErrors('#inlineEditPlantForm-' + id + ' form');
            // re-populate values
            var nameEl = document.getElementById('edit_plant_name_' + id);
            if (nameEl) nameEl.value = name;
            var stateEl = document.getElementById('edit_plant_state_' + id);
            if (stateEl) stateEl.value = state;
            var gstEl = document.getElementById('edit_plant_gst_number_' + id);
            if (gstEl) gstEl.value = gst;
            var addrEl = document.getElementById('edit_plant_shipping_address_' + id);
            if (addrEl) addrEl.value = address;
            formCard.classList.remove('hidden');
        } else {
            if (window.resetFormAndErrors) window.resetFormAndErrors('#inlineEditPlantForm-' + id + ' form');
            formCard.classList.add('hidden');
        }
    }
}
function closeEditPlantForm(id) {
    if (window.resetFormAndErrors) window.resetFormAndErrors('#inlineEditPlantForm-' + id + ' form');
    var formCard = document.getElementById('inlineEditPlantForm-' + id);
    if (formCard) formCard.classList.add('hidden');
}

window.deleteClient = function(id, name) {
    window.confirmDelete(
        "Delete Corporate Client?",
        "Are you sure you want to delete '" + name + "' and ALL its registered plants?",
        function() {
            $.ajax({
                url: "{{ url('/clients') }}/" + id,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
                },
                success: function(res) {
                    if (res.success) {
                        $('#client-card-' + id).fadeOut(300, function() { $(this).remove(); });
                        if (window.showToast) window.showToast('success', res.message);
                    }
                },
                error: function(err) {
                    if (window.showToast) window.showToast('error', 'Failed to delete client profile.');
                }
            });
        }
    );
};

window.deletePlant = function(id, name) {
    window.confirmDelete(
        "Delete Delivery Plant?",
        "Are you sure you want to delete plant '" + name + "'?",
        function() {
            $.ajax({
                url: "{{ url('/clients/plants') }}/" + id,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
                },
                success: function(res) {
                    if (res.success) {
                        $('#plant-card-' + id).fadeOut(300, function() { $(this).remove(); });
                        if (window.showToast) window.showToast('success', res.message);
                    }
                },
                error: function(err) {
                    if (window.showToast) window.showToast('error', 'Failed to delete plant.');
                }
            });
        }
    );
};

function toggleInlinePrimaryPlantSection() {
    var check = document.getElementById('inline_create_primary_plant_check');
    var fields = document.getElementById('inlinePrimaryPlantFields');
    if (check && fields) {
        fields.style.display = check.checked ? 'block' : 'none';
    }
}

function filterClients() {
    var query = document.getElementById('clientSearchInput').value.toLowerCase().trim();
    var cards = document.getElementsByClassName('client-card');
    for (var i = 0; i < cards.length; i++) {
        var card = cards[i];
        var data = card.getAttribute('data-search') || '';
        if (query === '' || data.indexOf(query) !== -1) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    }
}
</script>
@endsection
