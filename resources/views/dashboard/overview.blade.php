@extends('layouts.app')

@section('title', 'Overview Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Overview Dashboard</h1>
            <p class="text-sm text-slate-500">Welcome to Praful Welding Works ERP Management System.</p>
        </div>
        <div class="text-xs text-slate-400 font-semibold uppercase tracking-wider bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200">
            {{ date('l, d F Y') }}
        </div>
    </div>

    <!-- Empty / Minimalist Welcome Banner -->
    <div class="bg-white rounded-2xl border border-slate-200 p-8 shadow-sm text-center space-y-4">
        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto shadow-xs">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
        </div>
        <div>
            <h2 class="text-lg font-bold text-slate-800">Praful Welding Works ERP</h2>
            <p class="text-sm text-slate-500 max-w-md mx-auto mt-1">Use the navigation menu on the left to access Sales Orders, Direct Invoices, Client Ledgers, Products, Inventory, and Employees.</p>
        </div>
    </div>
</div>
@endsection
