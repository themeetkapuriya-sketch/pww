<!-- Sub Content 2: Tax & Financial Settings Partial -->
<div id="subTab-financial" class="sub-tab-content hidden space-y-6">
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-6">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Tax & Financial Settings</h2>
            <p class="text-slate-500 text-xs mt-1">Configure default GST slabs, Financial Year cycle, and currency numbering display standards.</p>
        </div>

        <form id="financialForm" action="{{ route('settings.financial') }}" method="POST" class="space-y-6" novalidate onsubmit="return handleFinancialSubmit(event);">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="fin_home_state" class="block text-xs font-bold text-slate-600 uppercase mb-1">Business Home State (Tax Base)</label>
                    @php
                        $currHomeState = \App\Models\Setting::get('home_state', 'Gujarat');
                        $indianStates = [
                            'Gujarat' => '24 - Gujarat',
                            'Maharashtra' => '27 - Maharashtra',
                            'Rajasthan' => '08 - Rajasthan',
                            'Madhya Pradesh' => '23 - Madhya Pradesh',
                            'Delhi' => '07 - Delhi',
                            'Uttar Pradesh' => '09 - Uttar Pradesh',
                            'Haryana' => '06 - Haryana',
                            'Punjab' => '03 - Punjab',
                            'Karnataka' => '29 - Karnataka',
                            'Tamil Nadu' => '33 - Tamil Nadu',
                            'Telangana' => '36 - Telangana',
                            'Andhra Pradesh' => '37 - Andhra Pradesh',
                            'West Bengal' => '19 - West Bengal',
                            'Bihar' => '10 - Bihar',
                            'Odisha' => '21 - Odisha',
                            'Jharkhand' => '20 - Jharkhand',
                            'Chhattisgarh' => '22 - Chhattisgarh',
                            'Goa' => '30 - Goa',
                            'Kerala' => '32 - Kerala',
                            'Assam' => '18 - Assam',
                            'Jammu & Kashmir' => '01 - Jammu & Kashmir',
                            'Himachal Pradesh' => '02 - Himachal Pradesh',
                            'Uttarakhand' => '05 - Uttarakhand',
                            'Chandigarh' => '04 - Chandigarh',
                            'Daman & Diu' => '25 - Daman & Diu and Dadra & Nagar Haveli',
                            'Sikkim' => '11 - Sikkim',
                            'Arunachal Pradesh' => '12 - Arunachal Pradesh',
                            'Nagaland' => '13 - Nagaland',
                            'Manipur' => '14 - Manipur',
                            'Mizoram' => '15 - Mizoram',
                            'Tripura' => '16 - Tripura',
                            'Meghalaya' => '17 - Meghalaya',
                            'Puducherry' => '34 - Puducherry',
                            'Ladakh' => '38 - Ladakh'
                        ];
                    @endphp
                    <select id="fin_home_state" name="home_state" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 transition">
                        @foreach($indianStates as $stName => $stLabel)
                            <option value="{{ $stName }}" {{ strcasecmp($currHomeState, $stName) === 0 ? 'selected' : '' }}>{{ $stLabel }}</option>
                        @endforeach
                    </select>
                    <span class="text-[11px] text-slate-400">Sales inside this state calculate CGST+SGST; outside calculate IGST.</span>
                </div>

                <div>
                    <label for="fin_default_gst_rate" class="block text-xs font-bold text-slate-600 uppercase mb-1">Default GST Rate Slab (%)</label>
                    <select id="fin_default_gst_rate" name="default_gst_rate" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 transition">
                        <option value="28" {{ \App\Models\Setting::get('default_gst_rate') == '28' ? 'selected' : '' }}>28% GST (Heavy Machinery & Special Equipment)</option>
                        <option value="18" {{ \App\Models\Setting::get('default_gst_rate', '18') == '18' ? 'selected' : '' }}>18% GST (Standard Metal Fabrication & Industrial Goods)</option>
                        <option value="12" {{ \App\Models\Setting::get('default_gst_rate') == '12' ? 'selected' : '' }}>12% GST</option>
                        <option value="5" {{ \App\Models\Setting::get('default_gst_rate') == '5' ? 'selected' : '' }}>5% GST</option>
                        <option value="0" {{ \App\Models\Setting::get('default_gst_rate') == '0' ? 'selected' : '' }}>0% (Exempt / Job Work)</option>
                    </select>
                </div>

                <div>
                    <label for="fin_fy_start" class="block text-xs font-bold text-slate-600 uppercase mb-1">Financial Year Start Month</label>
                    <select id="fin_fy_start" name="financial_year_start_month" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 transition">
                        <option value="4" {{ \App\Models\Setting::get('financial_year_start_month', '4') == '4' ? 'selected' : '' }}>April (Indian Financial Year: Apr 1 - Mar 31)</option>
                        <option value="1" {{ \App\Models\Setting::get('financial_year_start_month') == '1' ? 'selected' : '' }}>January (Calendar Year: Jan 1 - Dec 31)</option>
                    </select>
                </div>

                <div>
                    <label for="fin_number_format" class="block text-xs font-bold text-slate-600 uppercase mb-1">Currency & Number Format Style</label>
                    <select id="fin_number_format" name="number_format_style" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 transition">
                        <option value="indian" {{ \App\Models\Setting::get('number_format_style', 'indian') === 'indian' ? 'selected' : '' }}>Indian Format (₹ 1,00,000.00 - Lakhs / Crores)</option>
                        <option value="international" {{ \App\Models\Setting::get('number_format_style') === 'international' ? 'selected' : '' }}>Standard International (₹ 100,000.00 - Millions)</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl text-sm transition shadow-sm cursor-pointer">
                    Save Financial & Tax Settings
                </button>
            </div>
        </form>
    </div>
</div>
