@extends('layouts.app')

@section('title', 'Expenses Ledger')

@section('content')
@php
    $prefillCategory = request('prefill_category');
    $prefillAmount = request('prefill_amount');
    $prefillDesc = request('prefill_desc');
    $shouldShowForm = !empty($prefillCategory) || !empty($prefillAmount) || request()->has('log_gst');

    $expenseOptions = \App\Services\CategoryService::getExpenseComboboxOptions();
@endphp
<div class="space-y-6">
    <x-page-header title="Expenses Ledger" 
                   subtitle="Record factory overheads, transport freight, office administration costs, and machinery depreciation."
                   action-text="Log New Expense" 
                   action-id="toggleFormBtn"
                   action-on-click="toggleInlineForm('expenseFormContainer', this)" />

    <!-- 1. INSERT FORM AT THE TOP (Expandable) -->
    <div id="expenseFormContainer" class="{{ $shouldShowForm ? '' : 'hidden' }} transition-all duration-300 ease-in-out">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Log Factory Overheads
                </h3>
                <button type="button" onclick="toggleInlineForm('expenseFormContainer', document.querySelector('button[onclick*=\'expenseFormContainer\']'))" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition cursor-pointer">&times; Close</button>
            </div>
            <form action="{{ route('expense.store') }}" method="POST" class="ajax-form space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-combobox name="expense_category"
                                id="create_expense_cat"
                                label="Expense Category"
                                placeholder="Search or type expense category..."
                                :options="$expenseOptions"
                                :value="$prefillCategory ?? (request()->has('log_gst') ? 'gst_payment' : '')"
                                :allowCustom="true"
                                required />

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Amount (₹)</label>
                        <input type="number" name="amount" value="{{ $prefillAmount ?? '' }}" step="0.01" min="0.01" placeholder="₹ Value" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Expense Date</label>
                        <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Memo / Detail Description</label>
                    <textarea name="description" rows="2" placeholder="Additional details (e.g. transport allocation)..."
                              class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">{{ $prefillDesc ?? (request()->has('log_gst') ? 'GSTR-3B Tax Paid via Bank Challan' : '') }}</textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" onclick="toggleInlineForm('expenseFormContainer', document.querySelector('button[onclick*=\'expenseFormContainer\']'))" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold py-2.5 px-5 rounded-xl transition cursor-pointer">Cancel</button>
                    <button type="submit" class="btn-primary py-2.5 px-6 text-sm font-bold shadow-xs">
                        Record Expense Ledger
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. EDIT FORM AT THE TOP (Revealed when Edit clicked) -->
    <div id="editExpenseCardContainer" class="hidden transition-all duration-300 ease-in-out">
        <div class="bg-amber-50/50 rounded-2xl shadow-sm border border-amber-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-amber-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Edit Expense Entry Details
                </h3>
                <button type="button" onclick="closeEditExpenseCard()" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition cursor-pointer">&times; Close</button>
            </div>
            <form id="editExpenseForm" method="POST" class="ajax-form space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-combobox name="expense_category"
                                id="edit_expense_cat"
                                label="Expense Category"
                                placeholder="Search or type expense category..."
                                :options="$expenseOptions"
                                :allowCustom="true"
                                inputClass="w-full bg-white border border-slate-200 rounded-xl py-2 pl-10 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-800 font-medium transition shadow-xs placeholder:text-slate-400"
                                required />

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Amount (₹)</label>
                        <input type="number" name="amount" id="edit_amount" step="0.01" min="0.01" placeholder="₹ Value" required
                               class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Expense Date</label>
                        <input type="date" name="expense_date" id="edit_expense_date" required
                               class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Memo / Detail Description</label>
                    <textarea name="description" id="edit_description" rows="2" placeholder="Additional details (e.g. transport allocation)..."
                              class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" onclick="closeEditExpenseCard()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold py-2.5 px-5 rounded-xl transition cursor-pointer">Cancel</button>
                    <button type="submit" class="btn-primary py-2 px-6 text-xs font-bold">Update Expense Entry</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. RECORDS LIST UNDERNEATH -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Logged Operational Expenses
        </h3>
        
        <div class="overflow-x-auto w-full max-w-full">
            <table class="erp-datatable min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-[#EDF4FA] text-black divide-x divide-slate-200">
                    <tr>
                        <th class="px-4 py-3.5 text-center text-xs font-bold uppercase w-12">#</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Expense Date</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Category</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Memo / Description</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Amount (Debit)</th>
                        <th class="px-4 py-3.5 text-center text-xs font-bold uppercase w-24">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($expenses as $exp)
                        <tr id="row-exp-{{ $exp->id }}" class="hover:bg-slate-50 transition">
                            <td class="px-4 py-4 text-center font-bold text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ $exp->expense_date->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-slate-700 font-semibold capitalize">{{ $exp->expense_category === 'gst_payment' ? 'GST Payment' : str_replace('_', ' ', $exp->expense_category) }}</td>
                            <td class="px-6 py-4 text-slate-500 max-w-[300px] truncate">{{ $exp->description ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-right font-bold text-rose-600">₹{{ format_indian($exp->amount, 2) }}</td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-2">
                                    <button type="button" 
                                            title="Edit Expense Entry"
                                            onclick="openEditExpenseForm({{ $exp->id }}, '{{ $exp->expense_category }}', {{ $exp->amount }}, '{{ $exp->expense_date->format('Y-m-d') }}', '{{ addslashes($exp->description ?? '') }}')"
                                            class="w-8 h-8 p-1.5 inline-flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-xs transition duration-150 transform hover:scale-105">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button type="button" 
                                            title="Delete Expense"
                                            onclick="deleteExpenseItem({{ $exp->id }}, '{{ addslashes($exp->description ?? str_replace('_', ' ', $exp->expense_category)) }}')"
                                            class="w-8 h-8 p-1.5 inline-flex items-center justify-center rounded-lg bg-rose-500 hover:bg-rose-600 text-white shadow-xs transition duration-150 transform hover:scale-105">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-empty-state title="No Expenses Found" subtitle="There are no operational expenses recorded yet." colspan="6" />
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    window.clearExpenseForm = function() {
        const container = document.getElementById('expenseFormContainer');
        if (!container) return;
        const form = container.querySelector('form');
        if (!form) return;

        form.querySelectorAll('input, textarea, select').forEach(input => {
            if (input.name === '_token') return;
            if (input.type === 'date') {
                input.value = new Date().toISOString().split('T')[0];
            } else {
                input.value = '';
            }
        });

        container.querySelectorAll('.combobox-wrapper').forEach(w => {
            if (window.ERPComboboxManager) window.ERPComboboxManager.clear(w);
        });
    };

    window.toggleInlineForm = function(containerId, btn) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        // Hide edit card if opening create form
        closeEditExpenseCard();

        const isHidden = container.classList.contains('hidden');
        if (isHidden) {
            const urlParams = new URLSearchParams(window.location.search);
            if (!urlParams.has('prefill_category') && !urlParams.has('prefill_amount')) {
                window.clearExpenseForm();
            }
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
            window.clearExpenseForm();
            if (window.history && window.history.replaceState) {
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        }
    };

    (function() {
        const urlParams = new URLSearchParams(window.location.search);
        const cat = urlParams.get('prefill_category');
        const amt = urlParams.get('prefill_amount');
        const desc = urlParams.get('prefill_desc');
        
        if (cat || amt || urlParams.has('log_gst')) {
            const container = document.getElementById('expenseFormContainer');
            const toggleBtn = document.getElementById('toggleFormBtn');
            if (container) {
                container.classList.remove('hidden');
                if (toggleBtn) {
                    toggleBtn.classList.replace('bg-blue-600', 'bg-slate-700');
                    toggleBtn.classList.replace('hover:bg-blue-700', 'hover:bg-slate-800');
                    const icon = toggleBtn.querySelector('svg');
                    if (icon) icon.style.transform = 'rotate(45deg)';
                }
                if (cat) {
                    const hiddenCat = container.querySelector('.combobox-hidden-input');
                    if (hiddenCat) {
                        hiddenCat.value = cat;
                        const wrapper = hiddenCat.closest('.combobox-wrapper');
                        if (wrapper && window.ERPComboboxManager) window.ERPComboboxManager.syncDisplay(wrapper);
                    }
                }
                if (amt) {
                    const inputAmt = container.querySelector('input[name="amount"]');
                    if (inputAmt) inputAmt.value = amt;
                }
                if (desc) {
                    const inputDesc = container.querySelector('textarea[name="description"]');
                    if (inputDesc) inputDesc.value = desc;
                }
                setTimeout(function() {
                    container.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 150);

                if (window.history && window.history.replaceState) {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            }
        }
    })();

    function openEditExpenseForm(id, category, amount, date, description) {
        // Hide create form if open
        const createForm = document.getElementById('expenseFormContainer');
        if (createForm && !createForm.classList.contains('hidden')) {
            createForm.classList.add('hidden');
            const toggleBtn = document.getElementById('toggleFormBtn');
            if (toggleBtn) {
                toggleBtn.classList.replace('bg-slate-700', 'bg-blue-600');
                toggleBtn.classList.replace('hover:bg-slate-800', 'hover:bg-blue-700');
                const icon = toggleBtn.querySelector('svg');
                if (icon) icon.style.transform = 'rotate(0deg)';
            }
        }

        const editCard = document.getElementById('editExpenseCardContainer');
        const form = document.getElementById('editExpenseForm');

        form.action = "{{ url('/expenses') }}/" + id;
        
        const editCatWrapper = document.getElementById('edit_expense_cat_wrapper');
        if (editCatWrapper) {
            const hidden = editCatWrapper.querySelector('.combobox-hidden-input');
            if (hidden) hidden.value = category;
            if (window.ERPComboboxManager) window.ERPComboboxManager.syncDisplay(editCatWrapper);
        }

        document.getElementById('edit_amount').value = amount;
        document.getElementById('edit_expense_date').value = date;
        document.getElementById('edit_description').value = description;

        editCard.classList.remove('hidden');
        editCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function closeEditExpenseCard() {
        const editCard = document.getElementById('editExpenseCardContainer');
        if (editCard) editCard.classList.add('hidden');
    }

    window.deleteExpenseItem = function(id, name) {
        window.confirmDelete(
            "Delete Expense Record?",
            "Are you sure you want to delete '" + name + "'?",
            function() {
                $.ajax({
                    url: "{{ url('/expenses') }}/" + id,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    success: function(res) {
                        if (res.success) {
                            $('#row-exp-' + id).fadeOut(300, function() { $(this).remove(); });
                            if (window.showToast) window.showToast('success', res.message);
                        }
                    },
                    error: function(err) {
                        const msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Failed to delete expense record.';
                        if (window.showToast) window.showToast('danger', msg);
                    }
                });
            }
        );
    };
</script>
@endsection
