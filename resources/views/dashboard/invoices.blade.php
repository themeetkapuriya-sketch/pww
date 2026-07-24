@extends('layouts.app')

@section('title', 'Invoice Builder')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Invoice Ledger</h1>
            <p class="text-sm text-slate-500">Review generated invoices or log new custom tax invoices.</p>
        </div>
        <button type="button" 
                onclick="toggleInlineForm('section-manual-builder', this)" 
                class="{{ !empty($prefillOrder) ? 'bg-slate-700 hover:bg-slate-800' : 'bg-blue-600 hover:bg-blue-700' }} text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-md transition duration-150 flex items-center space-x-2">
            <svg class="w-4 h-4 transition-transform duration-200" style="{{ !empty($prefillOrder) ? 'transform: rotate(45deg);' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Create Custom Invoice</span>
        </button>
    </div>

    <!-- Direct Invoice Builder (Expandable Full Width) -->
    <div id="section-manual-builder" class="{{ !empty($prefillOrder) ? '' : 'hidden' }} transition-all duration-300 ease-in-out space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col">
            <h3 class="text-base font-bold text-slate-800 mb-4">Direct Invoice Itemizer</h3>
            <form id="customInvoiceForm" action="{{ route('invoice.generate') }}" method="POST" class="ajax-form space-y-4 flex-grow">
                @csrf
                <input type="hidden" name="sales_order_id" id="salesOrderIdHidden" value="{{ $prefillOrder->id ?? '' }}">
                
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Invoice Number</label>
                        <input type="text" name="invoice_number" value="{{ \App\Models\Invoice::generateNextInvoiceNumber() }}" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-mono">
                    </div>
                    @php
                        $prefillClient = !empty($prefillOrder) ? $clients->firstWhere('id', $prefillOrder->client_id) : null;
                        $prefillPlants = $prefillClient ? $prefillClient->plants : collect();
                    @endphp
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Select Client</label>
                        <select id="invoiceClientSelect" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium" required onchange="handleInvoiceClientChange()">
                            <option value="">Choose client...</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}"
                                        {{ (!empty($prefillOrder) && $prefillOrder->client_id == $client->id) ? 'selected' : '' }}
                                        data-plants='@json($client->plants->map(fn($p) => ["id" => $p->id, "name" => $p->plant_name, "state" => $p->state]))'>
                                    {{ $client->company_name }} ({{ $client->plants->count() === 1 ? '1 Location' : $client->plants->count() . ' Plants' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Plant Location</label>
                        <select id="manualPlantSelect" name="plant_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium" required onchange="recalculateCustomInvoice()">
                            <option value="">Select plant...</option>
                            @foreach($prefillPlants as $p)
                                <option value="{{ $p->id }}" data-state="{{ $p->state }}" {{ (!empty($prefillOrder) && $prefillOrder->plant_id == $p->id) ? 'selected' : '' }}>
                                    {{ $p->plant_name }} ({{ $p->state }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Invoice Date</label>
                        <input type="date" name="invoice_date" value="{{ date('Y-m-d') }}" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Delivery Vehicle No.</label>
                        <input type="text" name="vehicle_number" placeholder="e.g. GJ-03-BW-1234"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-mono uppercase">
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
                        @if(!empty($prefillOrder) && $prefillOrder->items->isNotEmpty())
                            @foreach($prefillOrder->items as $it)
                                <div class="billing-row flex items-center space-x-3 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                                    <select name="finished_good_ids[]" class="flex-grow bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" required>
                                        <option value="">Select product...</option>
                                        @foreach ($finishedGoods as $g)
                                            <option value="{{ $g->id }}" data-price="{{ $g->selling_price }}" {{ $g->id == $it->finished_good_id ? 'selected' : '' }}>
                                                {{ $g->product_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="number" name="quantities[]" min="1" value="{{ (int)$it->quantity }}" placeholder="Qty" class="w-24 bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" required>
                                    <input type="number" name="unit_prices[]" step="0.01" min="0" value="{{ $it->unit_price }}" placeholder="Price" class="w-32 bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" required>
                                    <button type="button" class="remove-billing-row-btn text-rose-500 hover:text-rose-600 font-bold px-2 text-sm">✕</button>
                                </div>
                            @endforeach
                        @else
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
                        @endif
                    </div>

                    <!-- Inline Tax & Round Off Summary Bar under Billing Line Items -->
                    <div class="mt-4 p-4 bg-slate-50 border border-slate-200 rounded-xl">
                        <div class="flex flex-wrap items-center justify-between gap-4 text-xs font-semibold text-slate-600">
                            <div>
                                <span>Taxable Subtotal: </span>
                                <span class="font-bold text-slate-800 text-sm ml-1" id="live-taxable">₹0.00</span>
                            </div>
                            <div id="cgst-sgst-box" class="flex items-center space-x-3">
                                <span>CGST (9%): <strong class="text-slate-800 text-sm ml-1" id="live-cgst">₹0.00</strong></span>
                                <span>SGST (9%): <strong class="text-slate-800 text-sm ml-1" id="live-sgst">₹0.00</strong></span>
                            </div>
                            <div id="igst-box" class="hidden">
                                <span>IGST (18%): <strong class="text-slate-800 text-sm ml-1" id="live-igst">₹0.00</strong></span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <label class="inline-flex items-center cursor-pointer select-none text-slate-700 font-bold">
                                    <input type="checkbox" id="roundOffCheckbox" checked onchange="recalculateCustomInvoice()" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 mr-1.5">
                                    <span>Round Off</span>
                                </label>
                                <span class="text-xs text-slate-500 italic" id="live-roundoff">(+₹0.00)</span>
                            </div>
                            <div class="bg-blue-600 text-white px-4 py-2 rounded-xl flex items-center space-x-2 shadow-xs ml-auto">
                                <span class="text-xs uppercase font-bold tracking-wider">Grand Total (Inc. GST):</span>
                                <span class="text-base font-black font-mono" id="live-total">₹0.00</span>
                            </div>
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
    </div>

    <!-- Add Invoice Record Ledger under the custom builder -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                Invoice Ledger
            </h3>
            
            <div class="overflow-x-auto">
                <table class="erp-datatable min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-[#4371D7] text-white divide-x divide-white/25">
                        <tr>
                            <th class="px-3 py-3 text-center text-xs font-bold uppercase w-12">#</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase">Invoice No</th>
                            <th class="px-4 py-3 text-center text-xs font-bold uppercase">Vehicle No</th>
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
                                <td class="px-4 py-3 text-center">
                                    @if(!empty($inv->vehicle_number))
                                        <span class="font-mono font-bold text-slate-800 bg-slate-100 px-2 py-1 rounded-lg border border-slate-200 text-xs uppercase">{{ $inv->vehicle_number }}</span>
                                    @else
                                        <span class="text-slate-400 font-medium text-xs">-</span>
                                    @endif
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
                                    @if(($inv->payment_status ?? 'unpaid') === 'paid')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs">
                                            PAID
                                        </span>
                                    @elseif(($inv->payment_status ?? 'unpaid') === 'partially_paid')
                                        <button type="button" 
                                                onclick="openInvoicePaymentModal({{ $inv->id }}, '{{ $inv->invoice_number }}', {{ $inv->remaining_balance }})"
                                                title="Click to record next payment for this invoice"
                                                class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-300 hover:bg-amber-200 transition cursor-pointer shadow-2xs">
                                            PARTIAL (₹{{ number_format($inv->remaining_balance, 0) }} DUE)
                                        </button>
                                    @else
                                        <button type="button" 
                                                onclick="openInvoicePaymentModal({{ $inv->id }}, '{{ $inv->invoice_number }}', {{ $inv->remaining_balance }})"
                                                title="Click to record payment for this invoice"
                                                class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-300 hover:bg-rose-200 transition cursor-pointer shadow-2xs">
                                            UNPAID
                                        </button>
                                    @endif
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
                                    <!-- Delete Button (Rose Boxy Curved) -->
                                    <button type="button" 
                                            title="Delete Invoice"
                                            onclick="window.deleteInvoiceRecord({{ $inv->id }}, '{{ addslashes($inv->invoice_number) }}')"
                                            class="w-8.5 h-8.5 p-2 inline-flex items-center justify-center rounded-lg bg-rose-500 hover:bg-rose-600 text-white shadow-xs transition duration-150 transform hover:scale-105">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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

    billingRowsContainer.addEventListener('input', recalculateCustomInvoice);
    billingRowsContainer.addEventListener('change', recalculateCustomInvoice);

    // Handle Client selection -> populate plant dropdown
    window.handleInvoiceClientChange = function() {
        const clientSelect = document.getElementById('invoiceClientSelect');
        const plantSelect = document.getElementById('manualPlantSelect');
        
        if (!clientSelect || !clientSelect.value) {
            if (plantSelect) {
                plantSelect.innerHTML = '<option value="">Select plant...</option>';
            }
            recalculateCustomInvoice();
            return;
        }

        const selectedOption = clientSelect.options[clientSelect.selectedIndex];
        let plantsData = [];
        try {
            plantsData = JSON.parse(selectedOption.getAttribute('data-plants') || '[]');
        } catch(e) {
            console.error('Error parsing client plants data:', e);
        }

        if (plantSelect) {
            if (plantsData.length === 0) {
                plantSelect.innerHTML = '<option value="">No plants registered</option>';
            } else if (plantsData.length === 1) {
                plantSelect.innerHTML = `<option value="${plantsData[0].id}" data-state="${plantsData[0].state || 'Gujarat'}" selected>${plantsData[0].name} (${plantsData[0].state || 'Gujarat'})</option>`;
            } else {
                let html = '<option value="">Choose plant location...</option>';
                plantsData.forEach(p => {
                    html += `<option value="${p.id}" data-state="${p.state}">${p.name} (${p.state})</option>`;
                });
                plantSelect.innerHTML = html;
            }
        }
        recalculateCustomInvoice();
    };

    // Dynamic tax calculation engine
    window.recalculateCustomInvoice = function() {
        const container = document.getElementById('billingRowsContainer');
        if (!container) return;
        const rows = container.querySelectorAll('.billing-row');
        let totalTaxable = 0.00;
        
        rows.forEach(row => {
            const qtyInput = row.querySelector('input[name="quantities[]"]');
            const priceInput = row.querySelector('input[name="unit_prices[]"]');
            const qty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;
            const price = parseFloat(priceInput ? priceInput.value : 0) || 0;
            totalTaxable += qty * price;
        });
        
        // Resolve destination state cleanly from manual plant select
        let state = '';
        const manualPlantSelect = document.getElementById('manualPlantSelect');

        if (manualPlantSelect && manualPlantSelect.value && manualPlantSelect.selectedIndex >= 0) {
            const selectedPlantOpt = manualPlantSelect.options[manualPlantSelect.selectedIndex];
            state = selectedPlantOpt && selectedPlantOpt.getAttribute('data-state') ? selectedPlantOpt.getAttribute('data-state') : 'Gujarat';
        }

        if (!state) {
            state = 'Gujarat';
        }

        const isGujarat = state.toLowerCase().trim() === 'gujarat';
        
        let cgst = 0.00;
        let sgst = 0.00;
        let igst = 0.00;
        
        if (totalTaxable > 0) {
            if (isGujarat) {
                cgst = Math.round(totalTaxable * 0.09 * 100) / 100;
                sgst = Math.round(totalTaxable * 0.09 * 100) / 100;
            } else {
                igst = Math.round(totalTaxable * 0.18 * 100) / 100;
            }
        }
        
        const exactTotal = totalTaxable + cgst + sgst + igst;
        let finalTotal = exactTotal;
        let roundOffDiff = 0.00;

        const roundOffCheckbox = document.getElementById('roundOffCheckbox');
        if (roundOffCheckbox && roundOffCheckbox.checked) {
            finalTotal = Math.round(exactTotal);
            roundOffDiff = finalTotal - exactTotal;
        }

        const cgstSgstBox = document.getElementById('cgst-sgst-box');
        const igstBox = document.getElementById('igst-box');

        if (isGujarat) {
            if (cgstSgstBox) cgstSgstBox.classList.remove('hidden');
            if (igstBox) igstBox.classList.add('hidden');
        } else {
            if (cgstSgstBox) cgstSgstBox.classList.add('hidden');
            if (igstBox) igstBox.classList.remove('hidden');
        }
        
        // Update DOM
        const elTaxable = document.getElementById('live-taxable');
        const elCgst = document.getElementById('live-cgst');
        const elSgst = document.getElementById('live-sgst');
        const elIgst = document.getElementById('live-igst');
        const elRoundOff = document.getElementById('live-roundoff');
        const elTotal = document.getElementById('live-total');

        if (elTaxable) elTaxable.innerText = '₹' + totalTaxable.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (elCgst) elCgst.innerText = '₹' + cgst.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (elSgst) elSgst.innerText = '₹' + sgst.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (elIgst) elIgst.innerText = '₹' + igst.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (elRoundOff) {
            const sign = roundOffDiff >= 0 ? '+' : '';
            elRoundOff.innerText = '(' + sign + '₹' + roundOffDiff.toFixed(2) + ')';
        }
        if (elTotal) elTotal.innerText = '₹' + finalTotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    };

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

    window.toggleInlineForm = function(containerId, btn) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        const isHidden = container.classList.contains('hidden');
        if (isHidden) {
            container.classList.remove('hidden');
            if (btn) {
                btn.classList.replace('bg-blue-600', 'bg-slate-700');
                btn.classList.replace('hover:bg-blue-700', 'hover:bg-slate-800');
                const icon = btn.querySelector('svg');
                if (icon) icon.style.transform = 'rotate(45deg)';
            }
        } else {
            container.classList.add('hidden');
            if (btn) {
                btn.classList.replace('bg-slate-700', 'bg-blue-600');
                btn.classList.replace('hover:bg-slate-800', 'hover:bg-blue-700');
                const icon = btn.querySelector('svg');
                if (icon) icon.style.transform = 'rotate(0deg)';
            }
        }
    };

    // Global edit function
    window.editInvoiceRecord = function(id) {
        toggleInlineForm('section-manual-builder', document.querySelector('button[onclick*="section-manual-builder"]'));
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

    // Global delete invoice handler
    window.deleteInvoiceRecord = function(id, invoiceNumber) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Delete Invoice?',
                text: `Are you sure you want to permanently delete Invoice '${invoiceNumber}'? This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f43f5e',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Delete Invoice',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const token = $('meta[name="csrf-token"]').attr('content') || '';
                    $.ajax({
                        url: `/invoices/${id}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        success: async function(response) {
                            if (window.showToast) {
                                window.showToast('success', response.message || 'Invoice deleted successfully!');
                            }
                            await window.loadPage(window.location.href);
                        },
                        error: function(xhr) {
                            const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to delete invoice.';
                            if (window.showToast) {
                                window.showToast('error', msg);
                            } else {
                                alert(msg);
                            }
                        }
                    });
                }
            });
        } else if (confirm(`Are you sure you want to delete Invoice '${invoiceNumber}'?`)) {
            const token = $('meta[name="csrf-token"]').attr('content') || '';
            $.ajax({
                url: `/invoices/${id}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                success: async function(response) {
                    if (window.showToast) {
                        window.showToast('success', response.message || 'Invoice deleted successfully!');
                    }
                    await window.loadPage(window.location.href);
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to delete invoice.';
                    alert(msg);
                }
            });
        }
    };
</script>

<script>
    (function() {
        if (typeof window.recalculateCustomInvoice === 'function') {
            window.recalculateCustomInvoice();
        }
        setTimeout(function() {
            if (typeof window.recalculateCustomInvoice === 'function') {
                window.recalculateCustomInvoice();
            }
        }, 100);
    })();
</script>
@endsection
