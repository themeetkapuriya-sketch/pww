<!-- Sub Content 1: Auto-Increment Serial & Prefixes Partial -->
<div id="subTab-serials" class="sub-tab-content space-y-6">
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-6">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Auto Increment Serial Reset & Document Prefixes</h2>
            <p class="text-slate-500 text-xs mt-1">Configure serial number sequences and custom document prefixes for Tax Invoices, Sales Orders, Quotations, and Delivery Challans.</p>
        </div>

        <form id="serialsForm" action="{{ route('settings.serials') }}" method="POST" class="space-y-6" novalidate onsubmit="return handleSerialsSubmit(event);">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Invoice Prefix</label>
                    <input type="text" name="invoice_prefix" id="invoice_prefix_input" oninput="updateSerialPreview()" value="{{ \App\Models\Setting::get('invoice_prefix', 'PWW-') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 font-mono uppercase focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                    <span class="text-[11px] text-slate-400">e.g. PWW- (Leave empty for pure serial number like 0001)</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Next Invoice Serial Number <span class="text-rose-500">*</span></label>
                    <input type="number" name="invoice_next_sequence" id="invoice_seq_input" oninput="updateSerialPreview()" value="{{ \App\Models\Setting::get('invoice_next_sequence', '1') }}" min="1" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 font-mono focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                    <span class="text-[11px] text-slate-400">Current Next Invoice Sequence (Set or Reset)</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Sales Order Prefix</label>
                    <input type="text" name="order_prefix" id="order_prefix_input" oninput="updateSerialPreview()" value="{{ \App\Models\Setting::get('order_prefix', 'PWW-ORD-') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 font-mono uppercase focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                    <span class="text-[11px] text-slate-400">e.g. PWW-ORD- (Leave empty for pure serial number)</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Next Order Serial Number <span class="text-rose-500">*</span></label>
                    <input type="number" name="order_next_sequence" id="order_seq_input" oninput="updateSerialPreview()" value="{{ \App\Models\Setting::get('order_next_sequence', '1') }}" min="1" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 font-mono focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                    <span class="text-[11px] text-slate-400">Current Next Sales Order Sequence</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Middle Date Portion Format</label>
                    <select name="serial_date_format" id="serial_date_format_select" onchange="updateSerialPreview()" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 transition">
                        <option value="Ymd" {{ \App\Models\Setting::get('serial_date_format', 'Ymd') === 'Ymd' ? 'selected' : '' }}>Full Date Format (e.g. {{ date('Ymd') }})</option>
                        <option value="Ym" {{ \App\Models\Setting::get('serial_date_format') === 'Ym' ? 'selected' : '' }}>Year & Month (e.g. {{ date('Ym') }})</option>
                        <option value="ym" {{ \App\Models\Setting::get('serial_date_format') === 'ym' ? 'selected' : '' }}>Short Year & Month (e.g. {{ date('ym') }})</option>
                        <option value="FY" {{ \App\Models\Setting::get('serial_date_format') === 'FY' ? 'selected' : '' }}>Financial Year Format (e.g. 2627)</option>
                        <option value="none" {{ \App\Models\Setting::get('serial_date_format') === 'none' ? 'selected' : '' }}>No Date in Middle (Prefix + Serial Only)</option>
                    </select>
                    <span class="text-[11px] text-slate-400">Controls date pattern in middle of invoice numbers</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Ending Serial Number Digits</label>
                    <select name="serial_number_digits" id="serial_number_digits_select" onchange="updateSerialPreview()" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 font-mono focus:bg-white focus:border-blue-500 transition">
                        <option value="4" {{ \App\Models\Setting::get('serial_number_digits', '4') == '4' ? 'selected' : '' }}>4 Digits (e.g. 0001)</option>
                        <option value="5" {{ \App\Models\Setting::get('serial_number_digits') == '5' ? 'selected' : '' }}>5 Digits (e.g. 00001)</option>
                        <option value="6" {{ \App\Models\Setting::get('serial_number_digits') == '6' ? 'selected' : '' }}>6 Digits (e.g. 000001)</option>
                        <option value="3" {{ \App\Models\Setting::get('serial_number_digits') == '3' ? 'selected' : '' }}>3 Digits (e.g. 001)</option>
                        <option value="1" {{ \App\Models\Setting::get('serial_number_digits') == '1' ? 'selected' : '' }}>No Zero-Padding (e.g. 1)</option>
                    </select>
                    <span class="text-[11px] text-slate-400">Controls total digit length of ending serial sequence</span>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Annual Auto-Reset Frequency</label>
                    <select name="serial_reset_frequency" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 transition">
                        <option value="financial_year" {{ \App\Models\Setting::get('serial_reset_frequency', 'financial_year') === 'financial_year' ? 'selected' : '' }}>Reset serial sequence to 0001 at start of New Financial Year (April 1st)</option>
                        <option value="monthly" {{ \App\Models\Setting::get('serial_reset_frequency') === 'monthly' ? 'selected' : '' }}>Reset serial sequence monthly (1st of every month)</option>
                        <option value="never" {{ \App\Models\Setting::get('serial_reset_frequency') === 'never' ? 'selected' : '' }}>Continuous sequential numbers (Never reset automatically)</option>
                    </select>
                </div>

                <!-- Live Sample Preview Card -->
                @php
                    $initInvPrefix = \App\Models\Setting::get('invoice_prefix', '');
                    $initInvSeq = (int) \App\Models\Setting::get('invoice_next_sequence', 1);
                    $initOrdPrefix = \App\Models\Setting::get('order_prefix', 'PWW-ORD-');
                    $initOrdSeq = (int) \App\Models\Setting::get('order_next_sequence', 1);
                    $initInvPreview = \App\Models\Setting::formatDocumentNumber($initInvPrefix, $initInvSeq);
                    $initOrdPreview = \App\Models\Setting::formatDocumentNumber($initOrdPrefix, $initOrdSeq);
                @endphp
                <div class="md:col-span-2 bg-gradient-to-r from-blue-50 to-indigo-50/50 border border-blue-100 rounded-2xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-2xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 w-full sm:w-auto">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                🧾
                            </div>
                            <div>
                                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wide block">Sample Next Invoice Number:</span>
                                <span id="invoice_sample_preview" class="text-base font-black text-blue-700 font-mono tracking-wide">{{ $initInvPreview }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                📦
                            </div>
                            <div>
                                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wide block">Sample Next Order Number:</span>
                                <span id="order_sample_preview" class="text-base font-black text-indigo-700 font-mono tracking-wide">{{ $initOrdPreview }}</span>
                            </div>
                        </div>
                    </div>
                    <span class="text-[11px] font-semibold text-blue-600 bg-white border border-blue-200 px-3 py-1 rounded-lg shadow-2xs shrink-0">Real-Time Live Preview</span>
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl text-sm transition shadow-sm cursor-pointer">
                    Save Serial & Prefix Settings
                </button>
            </div>
        </form>
    </div>
</div>
