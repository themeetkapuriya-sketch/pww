@extends('layouts.app')

@section('title', 'Invoice Builder')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            @if ($tab === 'challan-converter')
                <h1 class="text-2xl font-bold text-slate-800">Convert Dispatched Challans</h1>
                <p class="text-sm text-slate-500">Select and merge pending delivery challans into a final invoice.</p>
            @elseif ($tab === 'manual-builder')
                <h1 class="text-2xl font-bold text-slate-800">Invoice Ledger</h1>
                <p class="text-sm text-slate-500">Review generated invoices or log new custom tax invoices.</p>
            @endif
        </div>
    </div>

    <!-- Section 2: Convert Challans (Bulk mapping) -->
    <div id="section-challan-converter" class="{{ $tab === 'challan-converter' ? 'space-y-6' : 'hidden' }}">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-base font-bold text-slate-800 mb-2">Convert Dispatched Challans</h3>
            <p class="text-xs text-slate-500 mb-4">Select one or multiple delivery challans dispatched to Balaji Wafers plants to generate a combined tax compliance invoice.</p>
            
            @if ($pendingChallans->isEmpty())
                <div class="p-8 text-center text-slate-400 border border-dashed rounded-lg border-slate-200">
                    No pending delivery challans available for invoicing.
                </div>
            @else
                <form action="{{ route('invoice.create') }}" method="POST" class="ajax-form space-y-4">
                    @csrf
                    <div class="border border-slate-200 rounded-lg divide-y divide-slate-100 max-h-[300px] overflow-y-auto">
                        @foreach ($pendingChallans as $dc)
                            @php
                                $challanVal = 0;
                                foreach ($dc->items as $item) {
                                    $challanVal += $item->quantity * $item->unit_price;
                                }
                            @endphp
                            <div class="p-3.5 bg-slate-50/50 hover:bg-slate-50 flex items-center justify-between text-sm transition">
                                <div class="flex items-center space-x-3">
                                    <input type="checkbox" name="challan_ids[]" value="{{ $dc->id }}" class="rounded text-blue-600 focus:ring-blue-500">
                                    <div>
                                        <span class="font-bold text-slate-800">{{ $dc->challan_number }}</span>
                                        <span class="text-xs text-slate-500 ml-1">({{ $dc->plant->plant_name }})</span>
                                        <div class="text-[10px] text-slate-400">Dispatched: {{ $dc->dispatch_date->format('d M Y') }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="font-bold text-slate-700">₹{{ number_format($challanVal, 2) }}</span>
                                    <div class="text-[10px] text-slate-500">Taxable Value</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="max-w-xs">
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Invoice Payment Due Date</label>
                        <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+30 days')) }}"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>

                    <button type="submit" class="btn-primary py-2.5 px-6 text-sm font-bold">
                        Generate Tax Invoice
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Section 3: Manual Custom Invoice Builder (Direct details input) -->
    <div id="section-manual-builder" class="{{ $tab === 'manual-builder' ? 'space-y-6' : 'hidden' }}">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Form items editor -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col">
                <h3 class="text-base font-bold text-slate-800 mb-4">Direct Invoice Itemizer</h3>
                <form id="customInvoiceForm" action="{{ route('invoice.generate') }}" method="POST" class="ajax-form space-y-4 flex-grow">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Invoice Number</label>
                            <input type="text" name="invoice_number" value="{{ \App\Models\Invoice::generateNextInvoiceNumber() }}" required
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Destination Plant</label>
                            <select name="plant_id" id="manualPlantSelect" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Choose plant destination...</option>
                                @foreach ($clients as $client)
                                    <optgroup label="{{ $client->company_name }}">
                                        @foreach ($client->plants as $p)
                                            <option value="{{ $p->id }}" data-state="{{ $p->state }}">{{ $p->plant_name }} ({{ $p->state }})</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Payment Due Date</label>
                            <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                        </div>
                    </div>

                    <!-- Items rows container -->
                    <div class="border-t border-slate-200 pt-4">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase">Billing Line Items</label>
                            <button type="button" id="addBillingRowBtn" class="text-blue-600 hover:text-blue-700 text-xs font-bold flex items-center">
                                + Add Row
                            </button>
                        </div>

                        <div id="billingRowsContainer" class="space-y-2 max-h-[220px] overflow-y-auto pr-1">
                            <!-- Custom Row template -->
                            <div class="billing-row flex items-center space-x-3 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                                <select name="finished_good_ids[]" class="flex-grow bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" required>
                                    <option value="">Select product...</option>
                                    @foreach ($finishedGoods as $g)
                                        <option value="{{ $g->id }}" data-price="{{ $g->selling_price }}">{{ $g->product_name }}</option>
                                    @endforeach
                                </select>
                                <input type="number" name="quantities[]" min="1" placeholder="Qty" class="w-24 bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" required>
                                <input type="number" name="unit_prices[]" step="0.01" min="0" placeholder="Price" class="w-32 bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" required>
                                <button type="button" class="remove-billing-row-btn text-rose-500 hover:text-rose-600 font-bold px-2 text-sm">✕</button>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3 pt-2">
                        <button type="submit" class="btn-primary flex-1 py-2.5 px-4 text-sm font-bold">
                            Generate & Save Invoice
                        </button>
                        <button type="button" id="quitEditBtn" onclick="quitEditMode()" class="hidden py-2.5 px-5 rounded-xl text-sm font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition border border-slate-300 shadow-xs">
                            Quit Edit
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Col: Real-time Tax Audit summary -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 h-fit text-sm">
                <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Real-time Tax compliance audit
                </h3>
                
                <div class="space-y-3 bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Taxable Value</span>
                        <span class="font-bold text-slate-700" id="live-taxable">₹0.00</span>
                    </div>
                    <div class="flex justify-between border-t border-slate-200 pt-2">
                        <span class="text-slate-500">CGST (9%)</span>
                        <span class="font-medium text-slate-700" id="live-cgst">₹0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">SGST (9%)</span>
                        <span class="font-medium text-slate-700" id="live-sgst">₹0.00</span>
                    </div>
                    <div class="flex justify-between border-t border-slate-200 pt-2">
                        <span class="text-slate-500">IGST (18%)</span>
                        <span class="font-medium text-slate-700" id="live-igst">₹0.00</span>
                    </div>
                    <div class="flex justify-between border-t border-slate-300 pt-2 text-base font-black">
                        <span class="text-slate-800">Invoice Total</span>
                        <span class="text-blue-700" id="live-total">₹0.00</span>
                    </div>
                </div>
                
                <div class="mt-4 text-xs text-slate-400 text-center leading-relaxed">
                    Note: regional tax calculations auto-resolve based on whether the destination is in Gujarat (CGST+SGST) or outside (IGST).
                </div>
            </div>
        </div>

        <!-- Add Invoice Record Ledger under the custom builder -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mt-6">
            <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                Corporate Invoice Ledger
            </h3>
            
            <div class="overflow-x-auto">
                <table class="erp-datatable min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-[#4371D7] text-white divide-x divide-white/25">
                        <tr>
                            <th class="px-3 py-3 text-center text-xs font-bold uppercase w-12">#</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase">Invoice No</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase">Destination</th>
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase">Taxable Value</th>
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase">CGST+SGST</th>
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase">IGST</th>
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase">Total Amount</th>
                            <th class="px-4 py-3 text-center text-xs font-bold uppercase">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-bold uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($invoices as $inv)
                            @php
                                $pName = 'HQ / Custom';
                                if ($inv->deliveryChallan && $inv->deliveryChallan->plant) {
                                    $pName = $inv->deliveryChallan->plant->plant_name;
                                } elseif ($inv->deliveryChallans->isNotEmpty()) {
                                    $pName = $inv->deliveryChallans->first()->plant->plant_name;
                                }
                            @endphp
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-3 py-3 text-center font-bold text-slate-500">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-800">
                                    <a href="{{ route('invoice.preview', $inv->id) }}" class="text-blue-600 hover:text-blue-800 font-bold hover:underline">
                                        {{ $inv->invoice_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $pName }}</td>
                                <td class="px-4 py-3 text-right text-slate-700">₹{{ number_format($inv->total_taxable_value, 2) }}</td>
                                <td class="px-4 py-3 text-right text-slate-600">
                                    @if ($inv->cgst > 0)
                                        ₹{{ number_format($inv->cgst + $inv->sgst, 2) }}
                                        <span class="text-[9px] block text-slate-400">(9% + 9%)</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-slate-600">
                                    @if ($inv->igst > 0)
                                        ₹{{ number_format($inv->igst, 2) }}
                                        <span class="text-[9px] block text-slate-400">(18%)</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-slate-800">₹{{ number_format($inv->total_amount, 2) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                        {{ $inv->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 
                                           ($inv->payment_status === 'partially_paid' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200') }}">
                                        {{ $inv->payment_status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center space-x-1.5 whitespace-nowrap">
                                    <!-- Preview Button (Green Boxy Curved) -->
                                    <a href="{{ route('invoice.preview', $inv->id) }}" 
                                       title="Preview Invoice"
                                       class="w-8.5 h-8.5 p-2 inline-flex items-center justify-center rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white shadow-xs transition duration-150 transform hover:scale-105">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>

                                    <!-- Print Button (Sky Blue Boxy Curved) -->
                                    <a href="{{ route('invoice.print', $inv->id) }}" 
                                       target="_blank"
                                       title="Print Invoice"
                                       class="w-8.5 h-8.5 p-2 inline-flex items-center justify-center rounded-lg bg-sky-500 hover:bg-sky-600 text-white shadow-xs transition duration-150 transform hover:scale-105">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    </a>

                                    @php
                                        $itemsArray = [];
                                        if ($inv->deliveryChallan && $inv->deliveryChallan->items) {
                                            foreach ($inv->deliveryChallan->items as $it) {
                                                $itemsArray[] = [
                                                    'finished_good_id' => $it->finished_good_id,
                                                    'quantity' => $it->quantity,
                                                    'unit_price' => $it->unit_price
                                                ];
                                            }
                                        } elseif ($inv->deliveryChallans->isNotEmpty()) {
                                            foreach ($inv->deliveryChallans as $dcItem) {
                                                foreach ($dcItem->items as $it) {
                                                    $itemsArray[] = [
                                                        'finished_good_id' => $it->finished_good_id,
                                                        'quantity' => $it->quantity,
                                                        'unit_price' => $it->unit_price
                                                    ];
                                                }
                                            }
                                        }
                                        $invPlantId = '';
                                        if ($inv->deliveryChallan) {
                                            $invPlantId = $inv->deliveryChallan->plant_id;
                                        } elseif ($inv->deliveryChallans->isNotEmpty()) {
                                            $invPlantId = $inv->deliveryChallans->first()->plant_id;
                                        }
                                        $invDataAttr = json_encode([
                                            'id' => $inv->id,
                                            'invoice_number' => $inv->invoice_number,
                                            'plant_id' => $invPlantId,
                                            'due_date' => $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('Y-m-d') : date('Y-m-d'),
                                            'items' => $itemsArray
                                        ]);
                                    @endphp
                                    <!-- Edit Button (Amber Boxy Curved) -->
                                    <button type="button" 
                                            title="Edit Invoice Details"
                                            onclick="window.editInvoiceRecord({{ $inv->id }})"
                                            class="w-8.5 h-8.5 p-2 inline-flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-xs transition duration-150 transform hover:scale-105">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
    // Dynamic builder rows & Live Tax Calculations
    const billingRowsContainer = document.getElementById('billingRowsContainer');
    const addBillingRowBtn = document.getElementById('addBillingRowBtn');
    const manualPlantSelect = document.getElementById('manualPlantSelect');
    
    // Add Row
    addBillingRowBtn.addEventListener('click', function() {
        const originalRow = document.querySelector('.billing-row');
        if (!originalRow) return;
        
        const clone = originalRow.cloneNode(true);
        // Reset values
        clone.querySelector('select').value = '';
        clone.querySelector('input[name="quantities[]"]').value = '';
        clone.querySelector('input[name="unit_prices[]"]').value = '';
        
        billingRowsContainer.appendChild(clone);
        recalculateCustomInvoice();
    });

    // Event delegation on billingRowsContainer
    billingRowsContainer.addEventListener('change', function(e) {
        if (e.target.name === 'finished_good_ids[]') {
            const select = e.target;
            const opt = select.options[select.selectedIndex];
            if (opt) {
                const price = opt.getAttribute('data-price');
                const row = select.closest('.billing-row');
                if (row) {
                    const priceInput = row.querySelector('input[name="unit_prices[]"]');
                    if (priceInput) {
                        priceInput.value = price || '';
                        priceInput.dispatchEvent(new Event('input', { bubbles: true }));
                        priceInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            }
            recalculateCustomInvoice();
        }
    });

    billingRowsContainer.addEventListener('input', function(e) {
        if (e.target.name === 'quantities[]' || e.target.name === 'unit_prices[]') {
            recalculateCustomInvoice();
        }
    });

    billingRowsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-billing-row-btn')) {
            if (billingRowsContainer.querySelectorAll('.billing-row').length > 1) {
                e.target.closest('.billing-row').remove();
                recalculateCustomInvoice();
            }
        }
    });

    // Recalculate tax summary when plant destination changes
    manualPlantSelect.addEventListener('change', recalculateCustomInvoice);

    // Dynamic tax calculation engine
    function recalculateCustomInvoice() {
        const rows = billingRowsContainer.querySelectorAll('.billing-row');
        let totalTaxable = 0.00;
        
        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('input[name="quantities[]"]').value) || 0;
            const price = parseFloat(row.querySelector('input[name="unit_prices[]"]').value) || 0;
            totalTaxable += qty * price;
        });
        
        // Check destination state
        const selectedPlantOpt = manualPlantSelect.options[manualPlantSelect.selectedIndex];
        const state = selectedPlantOpt && selectedPlantOpt.getAttribute('data-state') ? selectedPlantOpt.getAttribute('data-state') : '';
        const isGujarat = state && typeof state === 'string' ? state.toLowerCase().trim() === 'gujarat' : false;
        
        let cgst = 0.00;
        let sgst = 0.00;
        let igst = 0.00;
        
        if (totalTaxable > 0 && state !== '') {
            if (isGujarat) {
                cgst = Math.round(totalTaxable * 0.09 * 100) / 100;
                sgst = Math.round(totalTaxable * 0.09 * 100) / 100;
            } else {
                igst = Math.round(totalTaxable * 0.18 * 100) / 100;
            }
        }
        
        const total = totalTaxable + cgst + sgst + igst;
        
        // Update DOM
        document.getElementById('live-taxable').innerText = '₹' + totalTaxable.toFixed(2);
        document.getElementById('live-cgst').innerText = '₹' + cgst.toFixed(2);
        document.getElementById('live-sgst').innerText = '₹' + sgst.toFixed(2);
        document.getElementById('live-igst').innerText = '₹' + igst.toFixed(2);
        document.getElementById('live-total').innerText = '₹' + total.toFixed(2);
    }

    // Initialize calculation on page load
    recalculateCustomInvoice();
</script>

<script>
    // Invoice data registry
    window.erpInvoicesMap = window.erpInvoicesMap || {};
    @foreach ($invoices as $inv)
        @php
            $itemsArray = [];
            if ($inv->deliveryChallan && $inv->deliveryChallan->items) {
                foreach ($inv->deliveryChallan->items as $it) {
                    $itemsArray[] = [
                        'finished_good_id' => $it->finished_good_id,
                        'quantity' => $it->quantity,
                        'unit_price' => $it->unit_price
                    ];
                }
            } elseif ($inv->deliveryChallans->isNotEmpty()) {
                foreach ($inv->deliveryChallans as $dcItem) {
                    foreach ($dcItem->items as $it) {
                        $itemsArray[] = [
                            'finished_good_id' => $it->finished_good_id,
                            'quantity' => $it->quantity,
                            'unit_price' => $it->unit_price
                        ];
                    }
                }
            }
            $invPlantId = '';
            if ($inv->deliveryChallan) {
                $invPlantId = $inv->deliveryChallan->plant_id;
            } elseif ($inv->deliveryChallans->isNotEmpty()) {
                $invPlantId = $inv->deliveryChallans->first()->plant_id;
            }
        @endphp
        window.erpInvoicesMap[{{ $inv->id }}] = {
            id: {{ $inv->id }},
            invoice_number: @json($inv->invoice_number),
            plant_id: @json($invPlantId),
            due_date: @json($inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('Y-m-d') : date('Y-m-d')),
            items: @json($itemsArray)
        };
    @endforeach

    // Global edit function
    window.editInvoiceRecord = function(id) {
        const invoice = window.erpInvoicesMap[id];
        if (!invoice) {
            console.error('Invoice record not found for id:', id);
            return;
        }

        const quitBtn = document.getElementById('quitEditBtn');
        if (quitBtn) quitBtn.classList.remove('hidden');

        const $form = $('#customInvoiceForm');
        if ($form.length) {
            $form.find('input[name="invoice_number"]').val(invoice.invoice_number);
            if (invoice.plant_id) {
                $form.find('select[name="plant_id"]').val(invoice.plant_id).trigger('change');
            }
            if (invoice.due_date) {
                $form.find('input[name="due_date"]').val(invoice.due_date);
            }
        }

        const container = document.getElementById('billingRowsContainer');
        if (container && invoice.items && invoice.items.length > 0) {
            container.innerHTML = '';
            invoice.items.forEach(item => {
                const row = document.createElement('div');
                row.className = 'billing-row flex items-center space-x-3 bg-slate-50 p-2.5 rounded-xl border border-slate-200';
                row.innerHTML = `
                    <select name="finished_good_ids[]" class="flex-grow bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" required>
                        <option value="">Select product...</option>
                        @foreach ($finishedGoods as $g)
                            <option value="{{ $g->id }}" data-price="{{ $g->selling_price }}">{{ $g->product_name }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="quantities[]" value="${item.quantity}" min="1" placeholder="Qty" class="w-24 bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" required>
                    <input type="number" name="unit_prices[]" value="${item.unit_price}" step="0.01" min="0" placeholder="Price" class="w-32 bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" required>
                    <button type="button" class="remove-billing-row-btn text-rose-500 hover:text-rose-600 font-bold px-2 text-sm">✕</button>
                `;
                row.querySelector('select').value = item.finished_good_id;
                container.appendChild(row);
            });
            
            if (typeof recalculateCustomInvoice === 'function') {
                recalculateCustomInvoice();
            }
        }

        const formElem = document.getElementById('customInvoiceForm');
        if (formElem) {
            formElem.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    };

    // Quit Edit mode handler
    window.quitEditMode = function() {
        const quitBtn = document.getElementById('quitEditBtn');
        if (quitBtn) quitBtn.classList.add('hidden');

        const $form = $('#customInvoiceForm');
        if ($form.length) {
            $form[0].reset();
            $form.find('input[name="invoice_number"]').val('{{ \App\Models\Invoice::generateNextInvoiceNumber() }}');
            $form.find('select[name="plant_id"]').val('').trigger('change');
            $form.find('input[name="due_date"]').val('{{ date("Y-m-d", strtotime("+30 days")) }}');
        }

        const container = document.getElementById('billingRowsContainer');
        if (container) {
            container.innerHTML = `
                <div class="billing-row flex items-center space-x-3 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                    <select name="finished_good_ids[]" class="flex-grow bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" required>
                        <option value="">Select product...</option>
                        @foreach ($finishedGoods as $g)
                            <option value="{{ $g->id }}" data-price="{{ $g->selling_price }}">{{ $g->product_name }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="quantities[]" min="1" placeholder="Qty" class="w-24 bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" required>
                    <input type="number" name="unit_prices[]" step="0.01" min="0" placeholder="Price" class="w-32 bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" required>
                    <button type="button" class="remove-billing-row-btn text-rose-500 hover:text-rose-600 font-bold px-2 text-sm">✕</button>
                </div>
            `;
        }

        if (typeof recalculateCustomInvoice === 'function') {
            recalculateCustomInvoice();
        }
    };
</script>
@endsection
