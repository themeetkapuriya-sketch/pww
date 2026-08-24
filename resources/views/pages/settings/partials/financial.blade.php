<!-- Sub Content 2: Tax & Financial Settings Partial -->
<div id="subTab-financial" class="sub-tab-content {{ ($activeSubTab ?? 'serials') === 'financial' ? '' : 'hidden' }} space-y-6">
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

    <!-- Financial Year Period Lock Card -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Financial Year Period Lock (Audit Protection)
                </h3>
                <p class="text-xs text-slate-500 font-medium mt-0.5">
                    Lock closed financial years after tax filing or CA audit. Locked periods remain 100% viewable & printable, but accidental edits or deletions are blocked.
                </p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg w-fit">
                🔒 Tamper Prevention
            </span>
        </div>

        @php
            $fyList = \App\Services\FinancialYearService::getFinancialYearsList(5);
        @endphp

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border border-slate-200 rounded-xl overflow-hidden">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 uppercase font-bold">
                    <tr>
                        <th class="py-3 px-4">Financial Period</th>
                        <th class="py-3 px-4 text-center">Period Status</th>
                        <th class="py-3 px-4 text-center">Audit Lock State</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($fyList as $fy)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-bold text-slate-800">
                                {{ $fy['label'] }}
                                @if($fy['is_current'])
                                    <span class="ml-1.5 px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-full text-[10px] font-bold">Active Year</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($fy['is_current'])
                                    <span class="text-slate-600 font-medium">Ongoing FY</span>
                                @else
                                    <span class="text-slate-500 font-medium">Closed FY</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($fy['is_locked'])
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-rose-50 border border-rose-200 text-rose-700 rounded-full text-xs font-bold shadow-2xs">
                                        🔒 Locked (Protected)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-full text-xs font-bold shadow-2xs">
                                        🟢 Open for Editing
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <form action="{{ route('settings.financial.toggle_lock') }}" method="POST" class="inline" onsubmit="return handlePeriodLockSubmit(event, '{{ $fy['key'] }}', {{ $fy['is_locked'] ? 'false' : 'true' }})">
                                    @csrf
                                    <input type="hidden" name="year_key" value="{{ $fy['key'] }}">
                                    <input type="hidden" name="lock_action" value="{{ $fy['is_locked'] ? 'unlock' : 'lock' }}">
                                    @if($fy['is_locked'])
                                        <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition border border-slate-300 cursor-pointer">
                                            🔓 Unlock Period
                                        </button>
                                    @else
                                        <button type="submit" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs transition shadow-2xs cursor-pointer">
                                            🔒 Lock Period
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function handlePeriodLockSubmit(e, yearKey, isLocking) {
    e.preventDefault();
    const form = e.target;
    const actionText = isLocking ? 'LOCK' : 'UNLOCK';
    const confirmMsg = isLocking 
        ? `Are you sure you want to LOCK Financial Year ${yearKey}? All invoices, purchases, expenses, and salary records dated in this year will be protected from edits and deletions.`
        : `Are you sure you want to UNLOCK Financial Year ${yearKey}? Editing and modifications will be temporarily re-enabled.`;

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: `${actionText} Financial Year ${yearKey}?`,
            text: confirmMsg,
            icon: isLocking ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: isLocking ? '#D97706' : '#2563EB',
            cancelButtonColor: '#64748B',
            confirmButtonText: `Yes, ${actionText} FY`,
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl font-bold px-6 py-2.5',
                cancelButton: 'rounded-xl font-bold px-6 py-2.5'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    } else {
        if (confirm(confirmMsg)) form.submit();
    }
    return false;
}
</script>
