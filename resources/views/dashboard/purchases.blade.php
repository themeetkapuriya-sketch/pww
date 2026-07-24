@extends('layouts.app')

@section('title', 'Purchase Ledger')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Purchase Ledger</h1>
            <p class="text-sm text-slate-500">Record all factory purchases including raw materials, machinery, tools, and vendor bills.</p>
        </div>
        <button type="button" 
                onclick="toggleInlineForm('purchaseFormContainer', this)" 
                class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-md transition duration-150 flex items-center space-x-2">
            <svg class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Record New Purchase</span>
        </button>
    </div>

    <!-- 1. Log Purchase Bill Form (Expandable) -->
    <div id="purchaseFormContainer" class="hidden transition-all duration-300 ease-in-out">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Record Purchase Invoice / Bill
        </h3>
        <form action="{{ route('purchases.store') }}" method="POST" class="ajax-form space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Purchase Category</label>
                    <select name="purchase_type" id="purchaseTypeSelect" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-bold">
                        <option value="raw_material" selected>Raw Material Purchase (Auto-Restocks Inventory)</option>
                        <option value="machinery">Machinery & Capital Equipment</option>
                        <option value="supplies">Factory Consumables & Tools</option>
                        <option value="others">Other Purchases / Miscellaneous</option>
                    </select>
                </div>
                <div id="rawMaterialSelectContainer">
                    <label class="block text-xs font-bold text-blue-600 uppercase mb-1">Raw Material Sub-Category (Select to Restock)</label>
                    <select name="raw_material_id" id="rawMaterialSelect"
                            class="w-full bg-blue-50/50 border border-blue-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-bold">
                        <option value="">Select Existing Raw Material...</option>
                        @foreach ($rawMaterials as $mat)
                            <option value="{{ $mat->id }}" data-name="{{ $mat->material_name }}" data-unit="{{ $mat->unit }}">{{ $mat->material_name }} (Stock: {{ number_format($mat->current_stock, 1) }} {{ $mat->unit }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Vendor / Supplier Name</label>
                    <input type="text" name="vendor_name" placeholder="e.g. TATA Steel Ltd" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Bill / Invoice No. (Optional)</label>
                    <input type="text" name="bill_number" placeholder="e.g. INV-2026-9041"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-mono">
                </div>

                <div id="itemNameInputContainer" class="hidden">
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Item Description / Name</label>
                    <input type="text" name="item_name" id="itemNameInput" placeholder="e.g. Spot Welding Machine 25kVA"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>

                <div id="qtyUnitContainer" class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Qty</label>
                        <input type="number" name="quantity" step="0.0001" min="0.0001" placeholder="e.g. 5000"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Unit</label>
                        <input type="text" name="unit" id="unitInput" placeholder="e.g. kg" value="kg"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Total Bill Amount (₹)</label>
                    <input type="number" name="total_amount" step="0.01" min="0" placeholder="e.g. 425000.00" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-lg">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">GST Rate Slab (%)</label>
                    <select name="gst_rate" id="gstRateSelect" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-bold">
                        <option value="0">0% (GST Exempt / Nil)</option>
                        <option value="5">5% GST</option>
                        <option value="12">12% GST</option>
                        <option value="18" selected>18% GST (Standard)</option>
                        <option value="28">28% GST</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Purchase Date</label>
                    <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>
            </div>

            <button type="submit" class="btn-primary py-2.5 px-6 text-sm font-bold">
                Log Purchase Entry
        </form>
    </div>
</div>

    <!-- 2. Purchase Bills Ledger Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Purchase History & Bills Ledger
        </h3>
        
        <div class="overflow-x-auto">
            <table class="erp-datatable min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-[#4371D7] text-white divide-x divide-white/25">
                    <tr>
                        <th class="px-4 py-3.5 text-center text-xs font-bold uppercase w-12">#</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Date</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Vendor / Supplier</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Category</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Item / Machinery Name</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Quantity</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">GST Slab</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Total Bill (₹)</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">Payment Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach ($purchases as $pur)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-4 text-center font-bold text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 text-slate-600 font-medium text-xs">{{ $pur->purchase_date->format('d M Y') }}</td>
                            <td class="px-6 py-4 font-bold text-slate-800">
                                {{ $pur->vendor_name }}
                                @if($pur->bill_number)
                                    <div class="text-[10px] text-slate-400 font-mono">Bill #: {{ $pur->bill_number }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($pur->purchase_type === 'raw_material')
                                    <span class="px-2.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 text-[10px] rounded font-bold uppercase">Raw Material</span>
                                @elseif($pur->purchase_type === 'machinery')
                                    <span class="px-2.5 py-0.5 bg-purple-50 text-purple-700 border border-purple-200 text-[10px] rounded font-bold uppercase">Machinery / Capital</span>
                                @elseif($pur->purchase_type === 'supplies')
                                    <span class="px-2.5 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 text-[10px] rounded font-bold uppercase">Supplies & Tools</span>
                                @else
                                    <span class="px-2.5 py-0.5 bg-slate-100 text-slate-700 border border-slate-200 text-[10px] rounded font-bold uppercase">Other Purchases</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $pur->item_name }}</td>
                            <td class="px-6 py-4 text-right font-medium text-slate-700">{{ number_format($pur->quantity, 2) }} {{ $pur->unit }}</td>
                            <td class="px-6 py-4 text-right text-slate-700 font-medium">
                                <span class="font-bold text-blue-600">{{ number_format($pur->gst_rate, 0) }}%</span>
                                <div class="text-[10px] text-slate-400">₹{{ number_format($pur->gst_amount, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-slate-900">₹{{ number_format($pur->total_amount, 2) }}</td>
                            <td class="px-6 py-4 text-center">
                                @if(($pur->payment_status ?? 'paid') === 'paid')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs">
                                        PAID
                                    </span>
                                @elseif(($pur->payment_status ?? 'paid') === 'partially_paid')
                                    <button type="button" 
                                            onclick="openVendorPaymentModal({{ $pur->id }}, '{{ addslashes($pur->vendor_name) }}', {{ $pur->remaining_balance }})"
                                            title="Click to record vendor payment"
                                            class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-300 hover:bg-amber-200 transition cursor-pointer shadow-2xs">
                                        PARTIAL (₹{{ number_format($pur->remaining_balance, 0) }} DUE)
                                    </button>
                                @else
                                    <button type="button" 
                                            onclick="openVendorPaymentModal({{ $pur->id }}, '{{ addslashes($pur->vendor_name) }}', {{ $pur->remaining_balance }})"
                                            title="Click to record vendor payment"
                                            class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-300 hover:bg-rose-200 transition cursor-pointer shadow-2xs">
                                        UNPAID
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $purchases->links() }}
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    function toggleFields() {
        const val = $('#purchaseTypeSelect').val();
        if (val === 'raw_material') {
            $('#rawMaterialSelectContainer').removeClass('hidden');
            $('#qtyUnitContainer').removeClass('hidden');
            $('#itemNameInputContainer').addClass('hidden');
        } else {
            $('#rawMaterialSelectContainer').addClass('hidden');
            $('#rawMaterialSelect').val('');
            $('#qtyUnitContainer').addClass('hidden');
            $('#itemNameInputContainer').removeClass('hidden');
        }
    }

    toggleFields();
    $('#purchaseTypeSelect').on('change', toggleFields);

    $('#rawMaterialSelect').on('change', function() {
        const opt = $(this).find('option:selected');
        if (opt.val()) {
            $('#itemNameInput').val(opt.data('name'));
            $('#unitInput').val(opt.data('unit'));
        }
    });
});

window.openVendorPaymentModal = function(id, vendorName, remainingBalance) {
    document.getElementById('modalPurchaseId').value = id;
    document.getElementById('modalVendorName').innerText = vendorName;
    document.getElementById('modalVendorRemainingText').innerText = parseFloat(remainingBalance).toFixed(2);
    document.getElementById('modalVendorPayAmount').value = parseFloat(remainingBalance).toFixed(2);
    document.getElementById('modalVendorPayAmount').max = parseFloat(remainingBalance);
    document.getElementById('modalVendorPayDate').value = new Date().toISOString().split('T')[0];
    
    document.getElementById('recordVendorPaymentModal').classList.remove('hidden');
};

window.closeVendorPaymentModal = function() {
    document.getElementById('recordVendorPaymentModal').classList.add('hidden');
};
</script>

<!-- Record Vendor Payment Modal -->
<div id="recordVendorPaymentModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" onclick="closeVendorPaymentModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Record Vendor Payment</h3>
                    <p class="text-xs text-slate-500 font-medium">Supplier: <span class="text-purple-600 font-bold" id="modalVendorName"></span> | Dues Remaining: <span class="text-rose-600 font-bold">₹<span id="modalVendorRemainingText">0.00</span></span></p>
                </div>
                <button type="button" onclick="closeVendorPaymentModal()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>

            <form action="" method="POST" onsubmit="submitVendorPayment(event)">
                @csrf
                <input type="hidden" id="modalPurchaseId" name="purchase_id">

                <div class="p-6 space-y-4 text-xs">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-600 uppercase mb-1">Payout Amount (₹)</label>
                            <input type="number" step="0.01" min="0.01" name="amount" id="modalVendorPayAmount" required
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 text-slate-800 font-extrabold">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-600 uppercase mb-1">Payment Date</label>
                            <input type="date" name="payment_date" id="modalVendorPayDate" required
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 text-slate-800 font-medium">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-600 uppercase mb-1">Payment Method</label>
                            <select name="payment_method" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500 text-slate-800 font-bold">
                                <option value="bank_transfer">Bank Transfer (NEFT/RTGS)</option>
                                <option value="cheque">Cheque</option>
                                <option value="upi">UPI / Online</option>
                                <option value="cash">Cash</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-600 uppercase mb-1">Account Source</label>
                            <select name="account_type" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500 text-slate-800 font-bold">
                                <option value="bank">Bank Account</option>
                                <option value="cash">Cash in Hand</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Reference / UTR / Cheque No.</label>
                        <input type="text" name="reference_number" placeholder="e.g. UTR-SUPPLIER-9901"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500 text-slate-800 font-mono">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Internal Notes</label>
                        <textarea name="notes" rows="2" placeholder="Optional notes for vendor ledger..."
                                  class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-purple-500 text-slate-800"></textarea>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-3 border-t border-slate-100 flex justify-end space-x-3">
                    <button type="button" onclick="closeVendorPaymentModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800">Cancel</button>
                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white py-2 px-5 text-xs font-bold rounded-xl shadow-xs transition">
                        Record Vendor Payout
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
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
    window.submitVendorPayment = function(e) {
        e.preventDefault();
        const purId = document.getElementById('modalPurchaseId').value;
        const formData = new FormData(e.target);
        const token = $('meta[name="csrf-token"]').attr('content') || '';

        $.ajax({
            url: `/purchases/${purId}/record-payment`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            success: async function(response) {
                closeVendorPaymentModal();
                if (window.showToast) {
                    window.showToast('success', response.message || 'Vendor payment recorded successfully!');
                }
                await window.loadPage(window.location.href);
            },
            error: function(xhr) {
                const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to record vendor payment.';
                alert(msg);
            }
        });
    };
</script>
@endsection
