<!-- TAB 1: Business Profile & Branding Partial -->
<div id="settingsTab-profile" class="tab-content space-y-6">
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-6">
        <h2 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-4">Business Information & Official Branding</h2>

        @php
        $indianStatesOptions = [
            ['value' => 'Gujarat (24)', 'label' => 'Gujarat (24)'],
            ['value' => 'Maharashtra (27)', 'label' => 'Maharashtra (27)'],
            ['value' => 'Delhi (07)', 'label' => 'Delhi (07)'],
            ['value' => 'Rajasthan (08)', 'label' => 'Rajasthan (08)'],
            ['value' => 'Madhya Pradesh (23)', 'label' => 'Madhya Pradesh (23)'],
            ['value' => 'Uttar Pradesh (09)', 'label' => 'Uttar Pradesh (09)'],
            ['value' => 'Punjab (03)', 'label' => 'Punjab (03)'],
            ['value' => 'Haryana (06)', 'label' => 'Haryana (06)'],
            ['value' => 'West Bengal (19)', 'label' => 'West Bengal (19)'],
            ['value' => 'Tamil Nadu (33)', 'label' => 'Tamil Nadu (33)'],
            ['value' => 'Karnataka (29)', 'label' => 'Karnataka (29)'],
            ['value' => 'Telangana (36)', 'label' => 'Telangana (36)'],
            ['value' => 'Andhra Pradesh (37)', 'label' => 'Andhra Pradesh (37)'],
            ['value' => 'Kerala (32)', 'label' => 'Kerala (32)'],
            ['value' => 'Bihar (10)', 'label' => 'Bihar (10)'],
            ['value' => 'Odisha (21)', 'label' => 'Odisha (21)'],
            ['value' => 'Assam (18)', 'label' => 'Assam (18)'],
            ['value' => 'Chhattisgarh (22)', 'label' => 'Chhattisgarh (22)'],
            ['value' => 'Jharkhand (20)', 'label' => 'Jharkhand (20)'],
            ['value' => 'Uttarakhand (05)', 'label' => 'Uttarakhand (05)'],
            ['value' => 'Himachal Pradesh (02)', 'label' => 'Himachal Pradesh (02)'],
            ['value' => 'Goa (30)', 'label' => 'Goa (30)'],
            ['value' => 'Jammu and Kashmir (01)', 'label' => 'Jammu & Kashmir (01)'],
            ['value' => 'Chandigarh (04)', 'label' => 'Chandigarh (04)'],
            ['value' => 'Puducherry (34)', 'label' => 'Puducherry (34)'],
            ['value' => 'Dadra and Nagar Haveli and Daman and Diu (26)', 'label' => 'Dadra & Nagar Haveli and Daman & Diu (26)'],
            ['value' => 'Tripura (16)', 'label' => 'Tripura (16)'],
            ['value' => 'Manipur (14)', 'label' => 'Manipur (14)'],
            ['value' => 'Meghalaya (17)', 'label' => 'Meghalaya (17)'],
            ['value' => 'Nagaland (13)', 'label' => 'Nagaland (13)'],
            ['value' => 'Arunachal Pradesh (12)', 'label' => 'Arunachal Pradesh (12)'],
            ['value' => 'Mizoram (15)', 'label' => 'Mizoram (15)'],
            ['value' => 'Sikkim (11)', 'label' => 'Sikkim (11)'],
            ['value' => 'Ladakh (38)', 'label' => 'Ladakh (38)'],
        ];
        @endphp

        <form id="businessProfileForm" action="{{ route('settings.business') }}" method="POST" enctype="multipart/form-data" class="space-y-6" novalidate onsubmit="return handleBusinessProfileSubmit(event);">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Company / Business Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="business_name" value="{{ \App\Models\Setting::get('business_name', 'Praful Welding Works') }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Official Email <span class="text-rose-500">*</span></label>
                    <input type="email" name="business_email" value="{{ \App\Models\Setting::get('business_email', 'vekariyah@gmail.com') }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Mobile Contact <span class="text-rose-500">*</span></label>
                    <input type="text" name="business_mobile" value="{{ \App\Models\Setting::get('business_mobile', '9409604420') }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">GSTIN Number <span class="text-rose-500">*</span></label>
                    <input type="text" name="gstin" value="{{ \App\Models\Setting::get('gstin', '24AFHPV5264M1ZU') }}" required maxlength="15" oninput="this.value = this.value.toUpperCase()" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 font-mono focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition uppercase">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">MSME Udhyam Registration <span class="text-rose-500">*</span></label>
                    <input type="text" name="msme_number" value="{{ \App\Models\Setting::get('msme_number', 'UDYAM-GJ-20-0177569') }}" required oninput="this.value = this.value.toUpperCase()" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                </div>
                <div>
                    <x-combobox 
                        name="state" 
                        label="State & State Code" 
                        :options="$indianStatesOptions" 
                        :value="\App\Models\Setting::get('state', 'Gujarat (24)')" 
                        allowCustom="true"
                        required="true"
                        placeholder="Search state name or code (e.g. 24, Gujarat)..." 
                    />
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Factory Registered Address <span class="text-rose-500">*</span></label>
                    <input type="text" name="address_line_1" value="{{ \App\Models\Setting::get('address_line_1', 'VILLAGE : KHORANA TA : RAJKOT DI : RAJKOT - 360 003') }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-slate-100 pt-4">
                <!-- Logo Upload -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Company Logo</label>
                    <div class="flex items-center gap-4">
                        <img src="{{ asset(\App\Models\Setting::get('logo_path', 'logo.jpg')) }}" class="w-14 h-14 object-contain rounded-xl border border-slate-200 bg-slate-50 p-1">
                        <input type="file" name="logo" accept="image/*" class="text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700">
                    </div>
                </div>

                <!-- Signature Upload -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Authorized Stamp / Signature</label>
                    <div class="flex items-center gap-4">
                        <img src="{{ asset(\App\Models\Setting::get('signature_path', 'uploads/signature_1785313553.png')) }}" class="w-14 h-14 object-contain rounded-xl border border-slate-200 bg-slate-50 p-1">
                        <input type="file" name="signature" accept="image/*" class="text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700">
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl text-sm transition shadow-sm cursor-pointer">
                    Save Business Profile
                </button>
            </div>
        </form>
    </div>
</div>
