<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\RawMaterial;
use App\Models\SalesOrder;
use App\Models\StaffProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    /**
     * Handle tokenized, multi-keyword intelligent global search across all ERP models.
     */
    public function globalSearch(Request $request): JsonResponse
    {
        $rawQuery = (string) $request->input('q', '');
        $query = trim($rawQuery);

        if (\App\Models\Setting::get('module_global_search', 'true') !== 'true') {
            return response()->json([
                'success' => false,
                'message' => 'Global search is disabled in System Settings.',
                'query' => $query,
                'total' => 0,
                'results' => [],
            ]);
        }

        if ($query === '') {
            return response()->json([
                'success' => true,
                'query' => '',
                'total' => 0,
                'results' => [],
            ]);
        }

        // Split query into individual word tokens
        $tokens = array_values(array_filter(preg_split('/[\s,#]+/', $query), fn ($w) => mb_strlen(trim($w)) > 0));
        $lowerQuery = mb_strtolower($query);

        $results = [];

        // 1. Navigation Shortcuts & Sub-Page Features
        $navItems = [
            // Core ERP Pages
            ['title' => 'Dashboard Overview', 'subtitle' => 'KPIs, production statistics & financial graphs', 'url' => route('overview'), 'icon' => 'chart', 'badge' => 'Page', 'keywords' => 'dashboard overview home analytics kpi stats graphs metrics'],
            ['title' => 'Tax Invoices & Billing', 'subtitle' => 'Create, print, and export GST invoices', 'url' => route('invoices'), 'icon' => 'invoice', 'badge' => 'Billing', 'keywords' => 'invoices bills gst tax billing eway payments sales delivery payment unpaid paid receipts invoice bill'],
            ['title' => 'B2B Sales Orders', 'subtitle' => 'Manage POs, dispatch queue & client orders', 'url' => route('orders'), 'icon' => 'order', 'badge' => 'Sales', 'keywords' => 'sales orders po purchase order dispatch tracking fulfillment quotation client orders salesorder so'],
            ['title' => 'Finished Goods (Products)', 'subtitle' => 'Stock inventory & finished welded products', 'url' => route('product'), 'icon' => 'product', 'badge' => 'Inventory', 'keywords' => 'product products finished goods inventory stock catalog items manufactured fabricated item'],
            ['title' => 'Raw Materials Inventory', 'subtitle' => 'Steel, wire, gas & raw stock ledger', 'url' => route('rawmaterial'), 'icon' => 'material', 'badge' => 'Inventory', 'keywords' => 'raw material materials inventory steel wire coil scrap sheet metals argon co2 gas powder pipes'],
            ['title' => 'Bill of Materials (BOM)', 'subtitle' => 'Product recipes & raw material ratios', 'url' => route('bom'), 'icon' => 'bom', 'badge' => 'Production', 'keywords' => 'bom bill of materials recipe formulation ratio production standard cost recipes'],
            ['title' => 'Daily Production Logs', 'subtitle' => 'Log manufacturing runs & auto-deduct stock', 'url' => route('production'), 'icon' => 'factory', 'badge' => 'Production', 'keywords' => 'production logs factory manufacturing batches output daily work worker fabrication batch'],
            ['title' => 'Purchases & Bills', 'subtitle' => 'Log raw material & asset purchase bills', 'url' => route('purchases'), 'icon' => 'purchase', 'badge' => 'Purchases', 'keywords' => 'purchases purchase bills vendor supplier raw material procurement inbound invoices gst credit'],
            ['title' => 'Expense Ledger', 'subtitle' => 'Operational, machinery & office expenses', 'url' => route('expenses'), 'icon' => 'expense', 'badge' => 'Finance', 'keywords' => 'expenses expense costs ledger cash office rent transport repairs utilities tea electricity maintenance cost'],
            ['title' => 'B2B Clients & Plants', 'subtitle' => 'Manage client profiles, GSTINs & plant addresses', 'url' => route('clients'), 'icon' => 'client', 'badge' => 'Clients', 'keywords' => 'clients client customers buyers b2b plants plant factories gstin addresses location city branches customer'],
            
            // Payroll & HR Sub-Tabs
            ['title' => 'Employee Directory & Staff', 'subtitle' => 'Staff profiles, daily/monthly wages & designations', 'url' => route('employees', ['tab' => 'directory']), 'icon' => 'employee', 'badge' => 'Payroll', 'keywords' => 'employees employee staff workers worker directory profiles wages designation salary payroll'],
            ['title' => 'Daily Attendance Sheet', 'subtitle' => 'Mark daily present/absent & shift hours', 'url' => route('employees', ['tab' => 'attendance']), 'icon' => 'attendance', 'badge' => 'HR', 'keywords' => 'attendance daily present absent overtime shifts hours work logs punch'],
            ['title' => 'Salary Advances & Disbursement', 'subtitle' => 'Salary advances, loans & monthly payroll payouts', 'url' => route('employees', ['tab' => 'payment']), 'icon' => 'payroll', 'badge' => 'Payroll', 'keywords' => 'salary advance advances pay payroll disbursement loans reconciliation payout slip wage'],
            
            // Reports & Security
            ['title' => 'Comprehensive Reports', 'subtitle' => 'GST summary, sales ledger, purchases & profit engine', 'url' => route('reports'), 'icon' => 'report', 'badge' => 'Reports', 'keywords' => 'reports report gst tax summary sales profit ledger export financial analytics balance sheet statement'],
            ['title' => 'Database Backup Vault', 'subtitle' => 'Download SQL dumps & restore system backups', 'url' => route('backup.index'), 'icon' => 'backup', 'badge' => 'Security', 'keywords' => 'backup backups sql database restore export snapshot security dump files recovery'],
            ['title' => 'System Activity Logs', 'subtitle' => 'Audit trail of user actions & timestamps', 'url' => route('activity-logs'), 'icon' => 'audit', 'badge' => 'Audit', 'keywords' => 'activity logs audit trail history actions users security tracking timestamp log'],

            // Settings Hub & All Sub-Pages / Features
            ['title' => 'System Settings Hub', 'subtitle' => 'Branding, logo, signature, bank & modules', 'url' => route('settings.index'), 'icon' => 'settings', 'badge' => 'Settings', 'keywords' => 'settings configuration company logo signature bank prefix serial users rbac roles profile setup'],
            ['title' => 'Business Profile & Branding', 'subtitle' => 'Company name, address, GSTIN, logo & authorized signature', 'url' => route('settings.index', ['tab' => 'profile']), 'icon' => 'settings', 'badge' => 'Settings', 'keywords' => 'profile business company name branding logo signature stamp gstin corporate address phone email contact settings'],
            ['title' => 'Bank & Billing Settings', 'subtitle' => 'Bank account details, IFSC, UPI QR & invoice terms', 'url' => route('settings.index', ['tab' => 'bank']), 'icon' => 'settings', 'badge' => 'Settings', 'keywords' => 'bank billing account ifsc upi qr payment details invoice terms conditions bankaccount settings'],
            ['title' => 'User Management & Roles', 'subtitle' => 'Team login accounts, RBAC permissions & password resets', 'url' => route('settings.index', ['tab' => 'users']), 'icon' => 'settings', 'badge' => 'Settings', 'keywords' => 'users user accounts team roles permissions rbac access control staff login security password add user settings'],
            ['title' => 'Active ERP Modules', 'subtitle' => 'Enable or disable ERP features & module visibility', 'url' => route('settings.index', ['tab' => 'modules']), 'icon' => 'settings', 'badge' => 'Settings', 'keywords' => 'modules features active toggle enable disable settings rbac visibility switch'],
            ['title' => 'Auto Serial & Prefixes', 'subtitle' => 'Configure invoice prefix, PO numbers & document serial formats', 'url' => route('settings.index', ['tab' => 'serials']), 'icon' => 'settings', 'badge' => 'Settings', 'keywords' => 'auto serial prefixes invoice prefix serial numbering format pww prefix series doc number settings starting invoice number'],
            ['title' => 'Tax & Financial Settings', 'subtitle' => 'Default GST rate, financial year format & round-off settings', 'url' => route('settings.index', ['tab' => 'financial']), 'icon' => 'settings', 'badge' => 'Settings', 'keywords' => 'tax financial gst rate default tax rate currency roundoff financial year fy rcm reverse charge settings'],
            ['title' => 'Email (SMTP) Settings', 'subtitle' => 'SMTP server host, port, credentials & email notifications', 'url' => route('settings.index', ['tab' => 'email']), 'icon' => 'settings', 'badge' => 'Settings', 'keywords' => 'email smtp mail mailer host port tls ssl sender from email notifications test email credentials server settings'],
            ['title' => 'Purchase & Expense Categories', 'subtitle' => 'Custom categories for raw material procurement & daily expenses', 'url' => route('settings.index', ['tab' => 'categories']), 'icon' => 'settings', 'badge' => 'Settings', 'keywords' => 'categories purchase category expense categories types custom category classification procurement classification settings'],
            ['title' => 'Security & Automated Backups', 'subtitle' => 'Session timeout, max login attempts & scheduled backup alarms', 'url' => route('settings.index', ['tab' => 'security']), 'icon' => 'settings', 'badge' => 'Settings', 'keywords' => 'security session timeout login attempts auto backup schedule automated backup frequency retention settings'],
            ['title' => 'Cache Re-Sync & Self-Repair', 'subtitle' => '1-Click self-healing: clears compiled views, routes & application cache', 'url' => route('settings.index', ['tab' => 'security']), 'icon' => 'settings', 'badge' => 'Tool', 'keywords' => 'cache resync re-sync re sync clear cache self repair healing system maintenance optimize views route cache settings'],
            ['title' => 'Audit Log Storage Cleanup', 'subtitle' => 'Auto-clean old system activity logs & maintain database speed', 'url' => route('settings.index', ['tab' => 'security']), 'icon' => 'settings', 'badge' => 'Tool', 'keywords' => 'clean logs prune activity audit storage retention cleanup optimize database settings'],
        ];

        $matchedNav = [];
        foreach ($navItems as $item) {
            $itemString = mb_strtolower($item['title'].' '.$item['subtitle'].' '.$item['keywords']);
            $normItemString = preg_replace('/[^a-z0-9]/', '', $itemString);

            $allTokensMatch = true;
            foreach ($tokens as $t) {
                $lowerT = mb_strtolower($t);
                $cleanT = preg_replace('/[^a-z0-9]/', '', $lowerT);

                $hasMatch = str_contains($itemString, $lowerT) || 
                            (!empty($cleanT) && str_contains($normItemString, $cleanT));

                if (! $hasMatch) {
                    $allTokensMatch = false;
                    break;
                }
            }
            if ($allTokensMatch || str_contains($itemString, $lowerQuery)) {
                $matchedNav[] = [
                    'id' => 'nav_'.Str::slug($item['title']),
                    'title' => $item['title'],
                    'subtitle' => $item['subtitle'],
                    'url' => $item['url'],
                    'badge' => $item['badge'],
                    'icon' => $item['icon'],
                    'type' => 'navigation',
                ];
                if (count($matchedNav) >= 4) {
                    break;
                }
            }
        }
        if (! empty($matchedNav)) {
            $results['Navigation & Pages'] = $matchedNav;
        }

        // 2. Invoices & Billing Search (Tokenized Keyword Search)
        $invoiceFillers = ['invoice', 'invoices', 'inv', 'bill', 'bills', 'tax', 'doc', 'no', 'num', 'number'];
        $invTokens = array_values(array_filter($tokens, fn ($t) => ! in_array(mb_strtolower($t), $invoiceFillers)));
        if (empty($invTokens)) {
            $invTokens = $tokens;
        }

        $invoices = Invoice::with(['plant.client', 'items'])
            ->where(function ($queryBuilder) use ($invTokens, $query) {
                // Direct query match
                $queryBuilder->where(function ($subQ) use ($query) {
                    $subQ->where('invoice_number', 'LIKE', "%{$query}%")
                        ->orWhere('custom_client_name', 'LIKE', "%{$query}%")
                        ->orWhere('vehicle_number', 'LIKE', "%{$query}%");
                });

                // OR match all individual significant tokens
                $queryBuilder->orWhere(function ($allTokensQ) use ($invTokens) {
                    foreach ($invTokens as $token) {
                        $allTokensQ->where(function ($tQ) use ($token) {
                            $isNum = is_numeric($token);
                            $tQ->where('invoice_number', 'LIKE', "%{$token}%")
                                ->orWhere('custom_client_name', 'LIKE', "%{$token}%")
                                ->orWhere('custom_buyer_gstin', 'LIKE', "%{$token}%")
                                ->orWhere('vehicle_number', 'LIKE', "%{$token}%")
                                ->orWhere('payment_status', 'LIKE', "%{$token}%")
                                ->orWhereHas('plant', function ($pq) use ($token) {
                                    $pq->where('plant_name', 'LIKE', "%{$token}%")
                                        ->orWhere('shipping_address', 'LIKE', "%{$token}%")
                                        ->orWhere('state', 'LIKE', "%{$token}%")
                                        ->orWhere('gst_number', 'LIKE', "%{$token}%");
                                })
                                ->orWhereHas('plant.client', function ($cq) use ($token) {
                                    $cq->where('company_name', 'LIKE', "%{$token}%")
                                        ->orWhere('corporate_address', 'LIKE', "%{$token}%")
                                        ->orWhere('gst_number', 'LIKE', "%{$token}%");
                                })
                                ->orWhereHas('items', function ($iq) use ($token) {
                                    $iq->where('item_name', 'LIKE', "%{$token}%");
                                });

                            if ($isNum) {
                                $tQ->orWhere('id', intval($token))
                                    ->orWhere('total_amount', 'LIKE', "%{$token}%")
                                    ->orWhere('paid_amount', 'LIKE', "%{$token}%");
                            }
                        });
                    }
                });
            })
            ->latest('id')
            ->limit(6)
            ->get();

        if ($invoices->isNotEmpty()) {
            $results['Invoices & Bills'] = $invoices->map(function ($inv) {
                $clientName = $inv->client->company_name ?? ($inv->custom_client_name ?? 'Client');
                $plantName = $inv->plant->plant_name ?? '';
                $plantLocation = $plantName ? " ({$plantName})" : '';
                $amountFormatted = '₹'.number_format((float) $inv->total_amount, 2);
                $statusBadge = ucfirst($inv->payment_status ?? 'Unpaid');
                $vehInfo = $inv->vehicle_number ? " | Veh: {$inv->vehicle_number}" : '';
                $addrInfo = ($inv->plant && $inv->plant->shipping_address) ? ' | Loc: '.Str::limit($inv->plant->shipping_address, 25) : '';

                return [
                    'id' => 'inv_'.$inv->id,
                    'title' => ($inv->invoice_number ?: 'Invoice #'.$inv->id)." — {$clientName}{$plantLocation}",
                    'subtitle' => "Amount: {$amountFormatted} | Status: {$statusBadge}{$vehInfo}{$addrInfo}",
                    'url' => route('invoices', ['search' => $inv->invoice_number ?: $inv->id]),
                    'print_url' => route('invoice.print', $inv->id),
                    'badge' => $statusBadge,
                    'icon' => 'invoice',
                    'type' => 'invoice',
                ];
            })->all();
        }

        // 3. Sales Orders Search (Tokenized Keyword Search)
        $orderFillers = ['order', 'orders', 'sales', 'po', 'so', 'quotation', 'no', 'num', 'number'];
        $orderTokens = array_values(array_filter($tokens, fn ($t) => ! in_array(mb_strtolower($t), $orderFillers)));
        if (empty($orderTokens)) {
            $orderTokens = $tokens;
        }

        $orders = SalesOrder::with(['client', 'plant'])
            ->where(function ($queryBuilder) use ($orderTokens, $query) {
                $queryBuilder->where(function ($subQ) use ($query) {
                    $subQ->where('order_number', 'LIKE', "%{$query}%")
                        ->orWhere('po_number', 'LIKE', "%{$query}%")
                        ->orWhere('notes', 'LIKE', "%{$query}%");
                });

                $queryBuilder->orWhere(function ($allTokensQ) use ($orderTokens) {
                    foreach ($orderTokens as $token) {
                        $allTokensQ->where(function ($tQ) use ($token) {
                            $isNum = is_numeric($token);
                            $tQ->where('order_number', 'LIKE', "%{$token}%")
                                ->orWhere('po_number', 'LIKE', "%{$token}%")
                                ->orWhere('status', 'LIKE', "%{$token}%")
                                ->orWhere('notes', 'LIKE', "%{$token}%")
                                ->orWhereHas('client', function ($cq) use ($token) {
                                    $cq->where('company_name', 'LIKE', "%{$token}%")
                                        ->orWhere('corporate_address', 'LIKE', "%{$token}%")
                                        ->orWhere('gst_number', 'LIKE', "%{$token}%");
                                })
                                ->orWhereHas('plant', function ($pq) use ($token) {
                                    $pq->where('plant_name', 'LIKE', "%{$token}%")
                                        ->orWhere('shipping_address', 'LIKE', "%{$token}%")
                                        ->orWhere('state', 'LIKE', "%{$token}%");
                                });

                            if ($isNum) {
                                $tQ->orWhere('id', intval($token))
                                    ->orWhere('total_amount', 'LIKE', "%{$token}%");
                            }
                        });
                    }
                });
            })
            ->latest('id')
            ->limit(5)
            ->get();

        if ($orders->isNotEmpty()) {
            $results['Sales Orders'] = $orders->map(function ($order) {
                $clientName = $order->client->company_name ?? 'Client';
                $plantName = $order->plant->plant_name ?? '';
                $plantText = $plantName ? " ({$plantName})" : '';
                $poInfo = $order->po_number ? " | PO: {$order->po_number}" : '';
                $statusFormatted = ucwords(str_replace('_', ' ', $order->status));

                return [
                    'id' => 'order_'.$order->id,
                    'title' => ($order->order_number ?: 'Order #'.$order->id)." — {$clientName}{$plantText}",
                    'subtitle' => "Status: {$statusFormatted}{$poInfo} | Total: ₹".number_format((float) $order->total_amount, 2),
                    'url' => route('orders', ['search' => $order->order_number ?: $order->id]),
                    'badge' => $statusFormatted,
                    'icon' => 'order',
                    'type' => 'order',
                ];
            })->all();
        }

        // 4. B2B Clients & Plants Search (Tokenized Keyword Search)
        $clientFillers = ['client', 'clients', 'customer', 'plant', 'plants', 'buyer', 'party'];
        $clientTokens = array_values(array_filter($tokens, fn ($t) => ! in_array(mb_strtolower($t), $clientFillers)));
        if (empty($clientTokens)) {
            $clientTokens = $tokens;
        }

        $clients = Client::with('plants')
            ->where(function ($queryBuilder) use ($clientTokens, $query) {
                $queryBuilder->where(function ($subQ) use ($query) {
                    $subQ->where('company_name', 'LIKE', "%{$query}%")
                        ->orWhere('gst_number', 'LIKE', "%{$query}%");
                });

                $queryBuilder->orWhere(function ($allTokensQ) use ($clientTokens) {
                    foreach ($clientTokens as $token) {
                        $allTokensQ->where(function ($tQ) use ($token) {
                            $isNum = is_numeric($token);
                            $tQ->where('company_name', 'LIKE', "%{$token}%")
                                ->orWhere('gst_number', 'LIKE', "%{$token}%")
                                ->orWhere('client_email', 'LIKE', "%{$token}%")
                                ->orWhere('corporate_address', 'LIKE', "%{$token}%")
                                ->orWhereHas('plants', function ($pq) use ($token) {
                                    $pq->where('plant_name', 'LIKE', "%{$token}%")
                                        ->orWhere('gst_number', 'LIKE', "%{$token}%")
                                        ->orWhere('shipping_address', 'LIKE', "%{$token}%")
                                        ->orWhere('state', 'LIKE', "%{$token}%")
                                        ->orWhere('email', 'LIKE', "%{$token}%");
                                });

                            if ($isNum) {
                                $tQ->orWhere('id', intval($token));
                            }
                        });
                    }
                });
            })
            ->latest('id')
            ->limit(5)
            ->get();

        if ($clients->isNotEmpty()) {
            $results['Clients & Plants'] = $clients->map(function ($client) {
                $gstInfo = $client->gst_number ? "GST: {$client->gst_number}" : 'Unregistered';
                $plantsList = $client->plants ? $client->plants->pluck('plant_name')->filter()->implode(', ') : '';
                $plantsText = $plantsList ? " | Plants: {$plantsList}" : '';
                $addr = $client->corporate_address ? " — {$client->corporate_address}" : '';

                return [
                    'id' => 'client_'.$client->id,
                    'title' => $client->company_name,
                    'subtitle' => "{$gstInfo}{$plantsText}{$addr}",
                    'url' => route('clients', ['search' => $client->company_name]),
                    'badge' => ($client->plants && $client->plants->count()) ? "{$client->plants->count()} Plants" : 'Client',
                    'icon' => 'client',
                    'type' => 'client',
                ];
            })->all();
        }

        // 5. Finished Goods & Raw Materials (Tokenized Keyword Search)
        $catalogFillers = ['product', 'products', 'item', 'items', 'material', 'materials', 'raw', 'stock', 'goods'];
        $catalogTokens = array_values(array_filter($tokens, fn ($t) => ! in_array(mb_strtolower($t), $catalogFillers)));
        if (empty($catalogTokens)) {
            $catalogTokens = $tokens;
        }

        $products = Product::where(function ($queryBuilder) use ($catalogTokens, $query) {
            $queryBuilder->where('product_name', 'LIKE', "%{$query}%");

            $queryBuilder->orWhere(function ($allTokensQ) use ($catalogTokens) {
                foreach ($catalogTokens as $token) {
                    $allTokensQ->where(function ($tQ) use ($token) {
                        $isNum = is_numeric($token);
                        $tQ->where('product_name', 'LIKE', "%{$token}%")
                            ->orWhere('hsn_code', 'LIKE', "%{$token}%")
                            ->orWhere('sku', 'LIKE', "%{$token}%")
                            ->orWhere('uom', 'LIKE', "%{$token}%");

                        if ($isNum) {
                            $tQ->orWhere('id', intval($token))
                                ->orWhere('selling_price', 'LIKE', "%{$token}%");
                        }
                    });
                }
            });
        })
            ->limit(4)
            ->get();

        $materials = RawMaterial::where(function ($queryBuilder) use ($catalogTokens, $query) {
            $queryBuilder->where('material_name', 'LIKE', "%{$query}%");

            $queryBuilder->orWhere(function ($allTokensQ) use ($catalogTokens) {
                foreach ($catalogTokens as $token) {
                    $allTokensQ->where(function ($tQ) use ($token) {
                        $isNum = is_numeric($token);
                        $tQ->where('material_name', 'LIKE', "%{$token}%")
                            ->orWhere('material_category', 'LIKE', "%{$token}%")
                            ->orWhere('specification', 'LIKE', "%{$token}%")
                            ->orWhere('unit', 'LIKE', "%{$token}%");

                        if ($isNum) {
                            $tQ->orWhere('id', intval($token));
                        }
                    });
                }
            });
        })
            ->limit(4)
            ->get();

        if ($products->isNotEmpty() || $materials->isNotEmpty()) {
            $catalogResults = [];

            foreach ($products as $prod) {
                $stockQty = $prod->current_stock ?? 0;
                $catalogResults[] = [
                    'id' => 'prod_'.$prod->id,
                    'title' => $prod->product_name,
                    'subtitle' => "Finished Good | Stock: {$stockQty} {$prod->uom} | Rate: ₹".number_format((float) $prod->selling_price, 2),
                    'url' => route('product', ['search' => $prod->product_name]),
                    'badge' => "Stock: {$stockQty}",
                    'icon' => 'product',
                    'type' => 'product',
                ];
            }

            foreach ($materials as $mat) {
                $stockQty = $mat->current_stock ?? 0;
                $unit = $mat->unit ?? 'kg';
                $catalogResults[] = [
                    'id' => 'mat_'.$mat->id,
                    'title' => $mat->material_name,
                    'subtitle' => "Raw Material | Stock: {$stockQty} {$unit}".($mat->specification ? " | Spec: {$mat->specification}" : ''),
                    'url' => route('rawmaterial', ['search' => $mat->material_name]),
                    'badge' => "{$stockQty} {$unit}",
                    'icon' => 'material',
                    'type' => 'material',
                ];
            }

            $results['Inventory & Catalog'] = $catalogResults;
        }

        // 6. Purchases & Vendor Procurement Bills
        $purchaseFillers = ['purchase', 'purchases', 'vendor', 'supplier', 'procurement', 'bill', 'bills', 'no'];
        $purchaseTokens = array_values(array_filter($tokens, fn ($t) => ! in_array(mb_strtolower($t), $purchaseFillers)));
        if (empty($purchaseTokens)) {
            $purchaseTokens = $tokens;
        }

        $purchases = Purchase::with('rawMaterial')
            ->where(function ($queryBuilder) use ($purchaseTokens, $query) {
                $queryBuilder->where('bill_number', 'LIKE', "%{$query}%")
                    ->orWhere('vendor_name', 'LIKE', "%{$query}%");

                $queryBuilder->orWhere(function ($allTokensQ) use ($purchaseTokens) {
                    foreach ($purchaseTokens as $token) {
                        $allTokensQ->where(function ($tQ) use ($token) {
                            $isNum = is_numeric($token);
                            $tQ->where('bill_number', 'LIKE', "%{$token}%")
                                ->orWhere('vendor_name', 'LIKE', "%{$token}%")
                                ->orWhere('item_name', 'LIKE', "%{$token}%")
                                ->orWhere('purchase_type', 'LIKE', "%{$token}%")
                                ->orWhereHas('rawMaterial', function ($rq) use ($token) {
                                    $rq->where('material_name', 'LIKE', "%{$token}%");
                                });

                            if ($isNum) {
                                $tQ->orWhere('id', intval($token))
                                    ->orWhere('total_amount', 'LIKE', "%{$token}%");
                            }
                        });
                    }
                });
            })
            ->latest('id')
            ->limit(4)
            ->get();

        if ($purchases->isNotEmpty()) {
            $results['Purchases & Vendor Bills'] = $purchases->map(function ($pch) {
                $vendor = $pch->vendor_name ?: 'Vendor';
                $item = $pch->item_name ?: ($pch->rawMaterial->material_name ?? 'Material');
                $amount = '₹'.number_format((float) $pch->total_amount, 2);

                return [
                    'id' => 'pch_'.$pch->id,
                    'title' => ($pch->bill_number ?: 'Purchase #'.$pch->id)." — {$vendor}",
                    'subtitle' => "Item: {$item} | Amount: {$amount} | Status: ".ucfirst($pch->payment_status ?? 'Unpaid'),
                    'url' => route('purchases', ['search' => $pch->bill_number ?: $vendor]),
                    'badge' => ucfirst($pch->payment_status ?? 'Logged'),
                    'icon' => 'purchase',
                    'type' => 'purchase',
                ];
            })->all();
        }

        // 7. Operational Expenses
        $expenseFillers = ['expense', 'expenses', 'cost', 'costs', 'exp', 'payment'];
        $expenseTokens = array_values(array_filter($tokens, fn ($t) => ! in_array(mb_strtolower($t), $expenseFillers)));
        if (empty($expenseTokens)) {
            $expenseTokens = $tokens;
        }

        $expenses = Expense::where(function ($queryBuilder) use ($expenseTokens, $query) {
            $queryBuilder->where('expense_category', 'LIKE', "%{$query}%")
                ->orWhere('description', 'LIKE', "%{$query}%");

            $queryBuilder->orWhere(function ($allTokensQ) use ($expenseTokens) {
                foreach ($expenseTokens as $token) {
                    $allTokensQ->where(function ($tQ) use ($token) {
                        $isNum = is_numeric($token);
                        $tQ->where('expense_category', 'LIKE', "%{$token}%")
                            ->orWhere('description', 'LIKE', "%{$token}%");

                        if ($isNum) {
                            $tQ->orWhere('id', intval($token))
                                ->orWhere('amount', 'LIKE', "%{$token}%");
                        }
                    });
                }
            });
        })
            ->latest('id')
            ->limit(3)
            ->get();

        if ($expenses->isNotEmpty()) {
            $results['Expenses'] = $expenses->map(function ($exp) {
                $cat = ucwords(str_replace('_', ' ', $exp->expense_category));
                $amount = '₹'.number_format((float) $exp->amount, 2);
                $desc = $exp->description ? " — {$exp->description}" : '';

                return [
                    'id' => 'exp_'.$exp->id,
                    'title' => "Expense: {$cat} ({$amount})",
                    'subtitle' => 'Date: '.($exp->expense_date ? $exp->expense_date->format('d M Y') : 'Recent').$desc,
                    'url' => route('expenses', ['search' => $exp->expense_category]),
                    'badge' => $amount,
                    'icon' => 'expense',
                    'type' => 'expense',
                ];
            })->all();
        }

        // 8. Employees & Staff Search
        $staffFillers = ['employee', 'employees', 'staff', 'worker', 'workers', 'person', 'driver'];
        $staffTokens = array_values(array_filter($tokens, fn ($t) => ! in_array(mb_strtolower($t), $staffFillers)));
        if (empty($staffTokens)) {
            $staffTokens = $tokens;
        }

        $staff = StaffProfile::where(function ($queryBuilder) use ($staffTokens, $query) {
            $queryBuilder->where('full_name', 'LIKE', "%{$query}%");

            $queryBuilder->orWhere(function ($allTokensQ) use ($staffTokens) {
                foreach ($staffTokens as $token) {
                    $allTokensQ->where(function ($tQ) use ($token) {
                        $isNum = is_numeric($token);
                        $tQ->where('full_name', 'LIKE', "%{$token}%")
                            ->orWhere('mobile_number', 'LIKE', "%{$token}%")
                            ->orWhere('wage_type', 'LIKE', "%{$token}%");

                        if ($isNum) {
                            $tQ->orWhere('id', intval($token));
                        }
                    });
                }
            });
        })
            ->limit(4)
            ->get();

        if ($staff->isNotEmpty()) {
            $results['Staff & Payroll'] = $staff->map(function ($emp) {
                $wageInfo = $emp->wage_type === 'monthly'
                    ? '₹'.number_format((float) $emp->monthly_salary, 2).'/mo'
                    : '₹'.number_format((float) $emp->piece_rate_per_unit, 2).'/unit';

                return [
                    'id' => 'staff_'.$emp->id,
                    'title' => $emp->full_name,
                    'subtitle' => "Wage: {$wageInfo}".($emp->mobile_number ? " | Phone: {$emp->mobile_number}" : ''),
                    'url' => route('employees', ['tab' => 'directory', 'search' => $emp->full_name]),
                    'badge' => ucfirst($emp->is_active ? 'Active' : 'Inactive'),
                    'icon' => 'employee',
                    'type' => 'staff',
                ];
            })->all();
        }

        // Compute total items found
        $total = 0;
        foreach ($results as $group) {
            $total += count($group);
        }

        return response()->json([
            'success' => true,
            'query' => $query,
            'total' => $total,
            'results' => $results,
        ]);
    }
}
