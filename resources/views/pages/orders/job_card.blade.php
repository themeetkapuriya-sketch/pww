<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factory Job Card - {{ $order->order_number }}</title>
    <!-- Fonts (100% Offline Local Font) -->
    <link rel="stylesheet" href="{{ asset('fonts/outfit/outfit.css') }}">
    <!-- Tailwind CSS (100% Offline Local Vendor Asset) -->
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .font-mono-code {
            font-family: 'JetBrains Mono', monospace;
        }

        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #ffffff !important;
                padding: 0 !important;
            }
            .job-card-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
            }
            .print-border {
                border-color: #000000 !important;
            }
            .print-bg-dark {
                background-color: #0f172a !important;
                color: #ffffff !important;
            }
            .print-bg-slate {
                background-color: #f1f5f9 !important;
            }
        }
    </style>
</head>
<body class="p-4 md:p-8 min-h-screen">

    <!-- Top Action Bar (Hidden in Print) -->
    <div class="no-print max-w-4xl mx-auto mb-6 flex items-center justify-between bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center space-x-3">
            <a href="{{ route('orders') }}" class="inline-flex items-center text-xs font-bold text-slate-600 hover:text-blue-600 bg-slate-100 hover:bg-slate-200 px-3 py-2 rounded-xl transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Orders
            </a>
            <span class="text-xs font-semibold text-slate-400">|</span>
            <span class="text-xs font-bold text-slate-700">Factory Production Work Order / Job Card</span>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="window.print()" class="inline-flex items-center text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-xl shadow-md hover:shadow-lg transition cursor-pointer">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print Job Card (A4)
            </button>
        </div>
    </div>

    <!-- Printable Job Card Container -->
    <div class="job-card-container max-w-4xl mx-auto bg-white border border-slate-300 rounded-2xl shadow-xl p-8 space-y-6">
        
        <!-- Header & Document Metadata -->
        <div class="flex items-start justify-between pb-6 border-b-2 border-slate-900">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 bg-blue-600 rounded-full inline-block"></span>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900 uppercase">Praful Welding Works</h1>
                </div>
                <p class="text-xs text-slate-500 font-medium mt-1">Industrial Fabrication & Welding Engineering Solutions</p>
                <p class="text-xs text-slate-400 mt-0.5">Plot No. 42, GIDC Industrial Estate, Gujarat, India</p>
            </div>
            <div class="text-right">
                <div class="inline-block bg-slate-900 text-white font-black text-xs uppercase px-3 py-1 rounded-md tracking-wider mb-2 print-bg-dark">
                    FACTORY JOB CARD
                </div>
                <div class="text-sm font-mono-code font-bold text-blue-600">JC-{{ $order->order_number }}</div>
                <div class="text-xs text-slate-500 font-medium mt-1">Date: {{ date('d-M-Y') }}</div>
            </div>
        </div>

        <!-- Order & Delivery Context -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-slate-50 rounded-xl border border-slate-200 print-bg-slate">
            <div>
                <span class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Sales Order #</span>
                <span class="text-sm font-bold text-slate-800 font-mono-code">{{ $order->order_number }}</span>
            </div>
            <div>
                <span class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Customer PO #</span>
                <span class="text-sm font-bold text-slate-800 font-mono-code">{{ $order->po_number ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Order Date</span>
                <span class="text-sm font-bold text-slate-800">{{ $order->order_date ? $order->order_date->format('d/m/Y') : 'N/A' }}</span>
            </div>
            <div>
                <span class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Target Dispatch Date</span>
                <span class="text-sm font-bold text-amber-600 font-mono-code">{{ $order->delivery_date ? $order->delivery_date->format('d/m/Y') : 'As Scheduled' }}</span>
            </div>
        </div>

        <!-- Client & Plant Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 rounded-xl border border-slate-200">
            <div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Client Details</h3>
                <div class="text-base font-extrabold text-slate-900">{{ $order->client ? $order->client->company_name : 'N/A' }}</div>
                <div class="text-xs text-slate-600 mt-1 font-medium">Contact: {{ $order->client->contact_person ?? 'N/A' }} | {{ $order->client->phone ?? 'N/A' }}</div>
                @if(!empty($order->client->gstin))
                    <div class="text-xs font-mono text-slate-500 mt-0.5">GSTIN: {{ $order->client->gstin }}</div>
                @endif
            </div>
            <div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Delivery Location / Plant</h3>
                <div class="text-base font-bold text-slate-800">{{ $order->plant ? $order->plant->plant_name : 'Main Plant' }}</div>
                <div class="text-xs text-slate-600 mt-1">{{ $order->plant->shipping_address ?? ($order->client->address ?? 'Factory Premises') }}</div>
                @if(!empty($order->plant->state))
                    <div class="text-xs font-semibold text-blue-600 mt-0.5">State Code: {{ $order->plant->state }}</div>
                @endif
            </div>
        </div>

        <!-- Section 1: Finished Goods Manufacturing Specifications -->
        <div class="space-y-2">
            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wide flex items-center">
                    <span class="w-2.5 h-2.5 bg-blue-600 rounded-full mr-2"></span>
                    1. Finished Goods Manufacturing Specifications
                </h2>
                <span class="text-xs font-medium text-slate-500">Total Items: {{ $order->items->count() }}</span>
            </div>

            <table class="w-full text-left text-xs border-collapse border border-slate-300">
                <thead>
                    <tr class="bg-slate-900 text-white font-bold uppercase tracking-wider print-bg-dark">
                        <th class="p-2.5 border border-slate-700 w-12 text-center">#</th>
                        <th class="p-2.5 border border-slate-700">Product Name & Specifications</th>
                        <th class="p-2.5 border border-slate-700 w-28 text-center">Ordered Qty</th>
                        <th class="p-2.5 border border-slate-700 w-28 text-center">Finished Stock</th>
                        <th class="p-2.5 border border-slate-700 w-32 text-center">Build Required</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($fgStatus['items'] as $index => $item)
                        <tr class="hover:bg-slate-50">
                            <td class="p-2.5 border border-slate-300 font-bold text-center text-slate-500">{{ $index + 1 }}</td>
                            <td class="p-2.5 border border-slate-300">
                                <div class="font-bold text-slate-900 text-sm">{{ $item['product_name'] }}</div>
                                @if(!empty($item['sku']))
                                    <div class="text-[11px] font-mono text-slate-500">SKU: {{ $item['sku'] }}</div>
                                @endif
                            </td>
                            <td class="p-2.5 border border-slate-300 text-center font-bold text-slate-900 text-sm">
                                {{ number_format($item['ordered_quantity']) }} <span class="text-[10px] font-normal text-slate-500">{{ $item['billing_uom'] }}</span>
                            </td>
                            <td class="p-2.5 border border-slate-300 text-center font-semibold text-slate-700">
                                {{ number_format($item['available_stock']) }}
                            </td>
                            <td class="p-2.5 border border-slate-300 text-center font-bold font-mono-code {{ $item['missing_quantity'] > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                                {{ $item['missing_quantity'] > 0 ? '+' . number_format($item['missing_quantity']) : 'FULLY STOCKED' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Section 2: Storekeeper Raw Material Issue Slip (MRP Calculations) -->
        <div class="space-y-2 pt-2">
            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wide flex items-center">
                    <span class="w-2.5 h-2.5 bg-amber-500 rounded-full mr-2"></span>
                    2. Storekeeper Raw Material Draw Slip (MRP Rules)
                </h2>
                <span class="text-xs font-medium text-slate-500">Calculated with Waste %</span>
            </div>

            @if(empty($mrpData['mrp_list']))
                <div class="p-4 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-800 font-medium">
                    ⚠️ No Bill of Materials (BOM) configured for products in this order. Please configure product BOMs to enable material draw slips.
                </div>
            @else
                <table class="w-full text-left text-xs border-collapse border border-slate-300">
                    <thead>
                        <tr class="bg-slate-800 text-white font-bold uppercase tracking-wider print-bg-dark">
                            <th class="p-2.5 border border-slate-700 w-12 text-center">#</th>
                            <th class="p-2.5 border border-slate-700">Raw Material Name</th>
                            <th class="p-2.5 border border-slate-700 w-24 text-center">Unit</th>
                            <th class="p-2.5 border border-slate-700 w-28 text-center">Calc. Required</th>
                            <th class="p-2.5 border border-slate-700 w-28 text-center">Stock Level</th>
                            <th class="p-2.5 border border-slate-700 w-32 text-center">Issued Qty (Store)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($mrpData['mrp_list'] as $idx => $mrp)
                            <tr class="hover:bg-slate-50">
                                <td class="p-2.5 border border-slate-300 font-bold text-center text-slate-500">{{ $idx + 1 }}</td>
                                <td class="p-2.5 border border-slate-300 font-bold text-slate-900">{{ $mrp['material_name'] }}</td>
                                <td class="p-2.5 border border-slate-300 text-center font-semibold text-slate-600">{{ $mrp['unit'] }}</td>
                                <td class="p-2.5 border border-slate-300 text-center font-bold text-blue-700 font-mono-code text-sm">
                                    {{ number_format($mrp['total_required'], 2) }}
                                </td>
                                <td class="p-2.5 border border-slate-300 text-center font-semibold {{ $mrp['is_sufficient'] ? 'text-slate-700' : 'text-rose-600 font-bold' }}">
                                    {{ number_format($mrp['current_stock'], 2) }}
                                    @if(!$mrp['is_sufficient'])
                                        <span class="block text-[10px] text-rose-500 font-normal">(Short {{ number_format($mrp['shortage'], 2) }})</span>
                                    @endif
                                </td>
                                <td class="p-2.5 border border-slate-300 text-center font-mono text-slate-400">
                                    [ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ]
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if(!empty($mrpData['missing_boms']))
                <div class="mt-2 text-[11px] text-amber-700 italic">
                    * Note: The following items do not have BOM formulas defined: 
                    <strong>{{ implode(', ', array_column($mrpData['missing_boms'], 'product_name')) }}</strong>
                </div>
            @endif
        </div>

        @if(!empty($order->notes))
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                <span class="block text-[10px] font-bold text-slate-400 uppercase">Special Instructions / Notes</span>
                <p class="text-xs text-slate-700 font-medium italic mt-0.5">{{ $order->notes }}</p>
            </div>
        @endif

        <!-- Section 3: Shop Floor & Quality Sign-Off Table -->
        <div class="pt-4 border-t-2 border-slate-300 space-y-4">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Quality Inspection & Sign-off Register</h3>
            
            <div class="grid grid-cols-3 gap-4">
                <!-- Storekeeper Sign -->
                <div class="p-4 border border-slate-300 rounded-xl space-y-8">
                    <div class="flex items-center justify-between text-[11px] font-bold text-slate-700">
                        <span>Storekeeper Issue</span>
                        <span class="text-slate-400">[ Sign ]</span>
                    </div>
                    <div class="border-b border-dashed border-slate-400 pt-6"></div>
                    <div class="text-[10px] text-slate-500 flex justify-between">
                        <span>Date: ___/___/2026</span>
                        <span>Time: ___:___</span>
                    </div>
                </div>

                <!-- Welder / Supervisor Sign -->
                <div class="p-4 border border-slate-300 rounded-xl space-y-8">
                    <div class="flex items-center justify-between text-[11px] font-bold text-slate-700">
                        <span>Shop Floor Supervisor</span>
                        <span class="text-slate-400">[ Sign ]</span>
                    </div>
                    <div class="border-b border-dashed border-slate-400 pt-6"></div>
                    <div class="text-[10px] text-slate-500 flex justify-between">
                        <span>Welder: ____________</span>
                        <span>Bay #: ______</span>
                    </div>
                </div>

                <!-- Quality Control Inspector Sign -->
                <div class="p-4 border border-slate-300 rounded-xl space-y-8">
                    <div class="flex items-center justify-between text-[11px] font-bold text-slate-700">
                        <span>Quality & Dispatch Inspector</span>
                        <span class="text-slate-400">[ Stamp ]</span>
                    </div>
                    <div class="border-b border-dashed border-slate-400 pt-6"></div>
                    <div class="text-[10px] text-slate-500 flex justify-between">
                        <span>QC Status: [ &nbsp;] Pass</span>
                        <span>[ &nbsp;] Hold</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center pt-2 text-[10px] text-slate-400 border-t border-slate-100">
            Praful Welding Works ERP • Generated automatically on {{ date('d-M-Y h:i A') }} • Document #JC-{{ $order->order_number }}
        </div>

    </div>

</body>
</html>
