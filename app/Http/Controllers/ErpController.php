<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RawMaterial;
use App\Models\Product;
use App\Models\Client;
use App\Models\ClientPlant;
use App\Models\DeliveryChallan;
use App\Models\DeliveryChallanItem;
use App\Models\Invoice;
use App\Models\StaffProfile;
use App\Models\LaborLog;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Payment;
use App\Models\User;
use App\Models\BillOfMaterial;
use App\Models\ProductionLog;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Services\ProductionService;
use App\Services\PayrollService;
use App\Services\BillingService;
use App\Services\FinancialService;
use App\Exceptions\InsufficientStockException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Exception;

class ErpController extends Controller
{
    protected $productionService;
    protected $payrollService;
    protected $billingService;
    protected $financialService;

    public function __construct(
        ProductionService $productionService,
        PayrollService $payrollService,
        BillingService $billingService,
        FinancialService $financialService
    ) {
        $this->productionService = $productionService;
        $this->payrollService = $payrollService;
        $this->billingService = $billingService;
        $this->financialService = $financialService;
    }

    /**
     * Helper to get standard date range parameters.
     */
    private function getDateRange(Request $request)
    {
        $reportType = $request->input('report_type', 'invoice');
        $defaultPeriod = ($reportType === 'gst') ? 'month' : 'all';

        $period = $request->input('filter_period', $defaultPeriod);
        
        if ($request->has('start_date') && $request->has('end_date') && !$request->has('filter_period')) {
            $period = 'custom';
        }

        $filterMonth = $request->input('filter_month', Carbon::now()->format('Y-m'));
        $filterYear = $request->input('filter_year', date('Y'));

        switch ($period) {
            case 'all':
                $startDate = '2020-01-01'; // Default broad range
                $endDate = Carbon::now()->toDateString();
                break;
            case 'month':
                try {
                    $monthCarbon = Carbon::parse($filterMonth . '-01');
                    $startDate = $monthCarbon->startOfMonth()->toDateString();
                    $endDate = $monthCarbon->endOfMonth()->toDateString();
                } catch (\Exception $e) {
                    $startDate = Carbon::now()->startOfMonth()->toDateString();
                    $endDate = Carbon::now()->endOfMonth()->toDateString();
                }
                break;
            case 'year':
                $now = Carbon::now();
                $fyStartYear = ($now->month >= 4) ? $now->year : ($now->year - 1);
                $targetYear = (int)$request->input('filter_year', $fyStartYear);
                $startDate = Carbon::create($targetYear, 4, 1)->toDateString();
                $endDate = Carbon::create($targetYear + 1, 3, 31)->toDateString();
                break;
            case 'custom':
            default:
                $startDate = $request->input('start_date', Carbon::now()->subDays(30)->toDateString());
                $endDate = $request->input('end_date', Carbon::now()->toDateString());
                break;
        }

        return [$startDate, $endDate, $period, $filterMonth, $filterYear];
    }

    /**
     * 1. Overview Dashboard.
     */
    public function overview(Request $request)
    {
        return view('dashboard.overview');
    }

    /**
     * 2. Inventory Management.
     */
    public function inventory(Request $request)
    {
        $tab = $request->input('tab', 'materials');
        
        if ($tab === 'materials') {
            $rawMaterials = RawMaterial::orderBy('material_name')->paginate(20);
            return view('dashboard.raw_materials', compact('rawMaterials'));
        }

        $finishedGoods = Product::orderBy('product_name')->paginate(20);
        return view('dashboard.products', compact('finishedGoods'));
    }

    /**
     * Create Raw Material (AJAX).
     */
    public function storeRawMaterial(Request $request)
    {
        $validated = $request->validate([
            'material_name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'current_stock' => 'nullable|numeric|min:0',
            'safety_threshold' => 'required|numeric|min:0',
            'average_purchase_price' => 'required|numeric|min:0',
        ]);
        $addedQty = (float) $request->input('current_stock', 0);
        $validated['current_stock'] = $addedQty;

        // Auto-restock if material already exists
        $existing = RawMaterial::where('material_name', $validated['material_name'])->first();

        if ($existing) {
            $existing->current_stock += $addedQty;
            $existing->safety_threshold = $validated['safety_threshold'];
            $existing->average_purchase_price = $validated['average_purchase_price'];
            $existing->unit = $validated['unit'];
            $existing->save();

            return response()->json([
                'success' => true,
                'message' => "Restocked " . number_format($addedQty, 2) . " {$existing->unit} for '{$existing->material_name}'! Updated Total Stock: " . number_format($existing->current_stock, 2) . " {$existing->unit}.",
                'data' => $existing
            ]);
        }

        $material = RawMaterial::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Raw Material '{$material->material_name}' logged successfully!",
            'data' => $material
        ]);
    }

    /**
     * Update Raw Material Item (AJAX).
     */
    public function updateRawMaterial(Request $request, $id)
    {
        $material = RawMaterial::findOrFail($id);

        $validated = $request->validate([
            'material_name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'safety_threshold' => 'required|numeric|min:0',
            'average_purchase_price' => 'required|numeric|min:0',
        ]);

        $material->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Raw Material '{$material->material_name}' updated successfully!",
            'data' => $material
        ]);
    }

    /**
     * Delete Raw Material Item (AJAX).
     */
    public function deleteRawMaterial($id)
    {
        $material = RawMaterial::findOrFail($id);
        $name = $material->material_name;
        $material->delete();

        return response()->json([
            'success' => true,
            'message' => "Raw Material '{$name}' deleted successfully!"
        ]);
    }

    /**
     * Create Finished Good (AJAX).
     */
    public function storeFinishedGood(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'sku' => 'required|string|unique:finished_goods,sku|max:100',
            'hsn_code' => 'required|string|max:50',
            'uom' => 'required|string|in:piece,kg',
            'current_stock' => 'nullable|integer|min:0',
            'selling_price' => 'required|numeric|min:0',
        ]);

        $validated['current_stock'] = $request->input('current_stock', 0);
        $validated['uom'] = $request->input('uom', 'piece');

        $good = Product::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Product '{$good->product_name}' cataloged successfully!",
            'data' => $good
        ]);
    }

    /**
     * Update Finished Good Product (AJAX).
     */
    public function updateFinishedGood(Request $request, $id)
    {
        $good = Product::findOrFail($id);

        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:finished_goods,sku,' . $id,
            'hsn_code' => 'required|string|max:50',
            'uom' => 'required|string|in:piece,kg',
            'current_stock' => 'nullable|integer|min:0',
            'selling_price' => 'required|numeric|min:0',
        ]);

        if (!array_key_exists('current_stock', $validated) || is_null($validated['current_stock'])) {
            unset($validated['current_stock']);
        }

        $good->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Product '{$good->product_name}' updated successfully!",
            'data' => $good
        ]);
    }

    /**
     * Delete Finished Good Product (AJAX).
     */
    public function deleteFinishedGood($id)
    {
        $good = Product::findOrFail($id);
        $name = $good->product_name;
        $good->delete();

        return response()->json([
            'success' => true,
            'message' => "Product '{$name}' deleted successfully!"
        ]);
    }

    /**
     * 3. Bill of Materials (BOM).
     */
    public function bom()
    {
        $finishedGoods = Product::with('billOfMaterials.rawMaterial')->get();
        $rawMaterials = RawMaterial::all();
        return view('dashboard.bom', compact('finishedGoods', 'rawMaterials'));
    }

    /**
     * Store BOM Item (AJAX).
     */
    public function storeBom(Request $request)
    {
        $validated = $request->validate([
            'finished_good_id' => 'required|exists:finished_goods,id',
            'raw_material_id' => 'required|exists:raw_materials,id',
            'required_quantity' => 'required|numeric|min:0.0001',
            'waste_percentage' => 'required|numeric|min:0',
        ]);

        $bom = BillOfMaterial::updateOrCreate(
            [
                'finished_good_id' => $validated['finished_good_id'],
                'raw_material_id' => $validated['raw_material_id'],
            ],
            [
                'required_quantity' => $validated['required_quantity'],
                'waste_percentage' => $validated['waste_percentage'],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "BOM component mapping assigned successfully!",
            'data' => $bom
        ]);
    }

    /**
     * 4. Production Logs.
     */
    public function production()
    {
        $productionLogs = ProductionLog::with('finishedGood', 'recordedByUser')->orderBy('production_date', 'desc')->paginate(20);
        $finishedGoods = Product::all();
        $staffProfiles = StaffProfile::all();
        $users = User::all();
        return view('dashboard.production', compact('productionLogs', 'finishedGoods', 'staffProfiles', 'users'));
    }

    /**
     * Log a production run (AJAX).
     */
    public function logProduction(Request $request)
    {
        $validated = $request->validate([
            'finished_good_id' => 'required|exists:finished_goods,id',
            'quantity_manufactured' => 'required|integer|min:1',
            'quantity_rejected' => 'required|integer|min:0',
            'recorded_by' => 'required|exists:users,id',
            'production_date' => 'required|date',
            'labor' => 'nullable|array',
        ]);

        try {
            $laborData = [];
            if (!empty($validated['labor'])) {
                foreach ($validated['labor'] as $profileId => $units) {
                    if ($units > 0) {
                        $laborData[] = [
                            'staff_profile_id' => $profileId,
                            'units_completed' => (int) $units
                        ];
                    }
                }
            }

            $log = $this->productionService->logProduction(
                (int) $validated['finished_good_id'],
                (int) $validated['quantity_manufactured'],
                (int) $validated['quantity_rejected'],
                (int) $validated['recorded_by'],
                $validated['production_date'],
                $laborData
            );

            return response()->json([
                'success' => true,
                'message' => "Production batch {$log->id} logged. Stock auto-deductions processed!",
                'data' => $log
            ]);
        } catch (InsufficientStockException $e) {
            return response()->json([
                'success' => false,
                'errors' => [$e->getMessage()]
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Execution failed: ' . $e->getMessage()]
            ], 500);
        }
    }

    /**
     * 5. Clients & Plants.
     */
    public function clients()
    {
        $clients = Client::with('plants')->orderBy('company_name')->get();
        return view('dashboard.clients', compact('clients'));
    }

    /**
     * Create Client (AJAX) - supports 1-click primary plant creation.
     */
    public function storeClient(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'client_email' => 'required|email|max:255',
            'gst_number' => 'required|string|max:50',
            'corporate_address' => 'required|string',
            'create_primary_plant' => 'nullable|boolean',
            'plant_name' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:100',
            'plant_gst_number' => 'nullable|string|max:50',
            'shipping_address' => 'nullable|string',
        ]);

        $clientData = [
            'company_name' => $validated['company_name'],
            'client_email' => $validated['client_email'],
            'gst_number' => $validated['gst_number'],
            'corporate_address' => $validated['corporate_address'],
        ];

        $shouldCreatePlant = $request->boolean('create_primary_plant', true);
        if ($shouldCreatePlant && !empty($validated['state'])) {
            $plantState = $validated['state'];
            $expectedCode = self::getGstStateCode($plantState);
            $gstInput = !empty($validated['plant_gst_number']) ? $validated['plant_gst_number'] : $validated['gst_number'];
            if (!str_starts_with(strtoupper($gstInput), $expectedCode)) {
                return response()->json([
                    'success' => false,
                    'errors' => ['gst_number' => ["GSTIN for {$plantState} must start with State Code {$expectedCode} (e.g. {$expectedCode}AAAAB1111A1Z5). Entered: {$gstInput}"]]
                ], 422);
            }
        }

        DB::transaction(function () use ($validated, $clientData, $request, &$client) {
            $client = Client::create($clientData);

            $shouldCreatePlant = $request->boolean('create_primary_plant', true);
            if ($shouldCreatePlant) {
                $plantName = !empty($validated['plant_name']) ? $validated['plant_name'] : ($validated['company_name'] . ' Main Plant');
                $state = !empty($validated['state']) ? $validated['state'] : 'Gujarat';
                $shippingAddress = !empty($validated['shipping_address']) ? $validated['shipping_address'] : $validated['corporate_address'];
                $plantGst = !empty($validated['plant_gst_number']) ? $validated['plant_gst_number'] : $validated['gst_number'];

                ClientPlant::create([
                    'client_id' => $client->id,
                    'plant_name' => $plantName,
                    'state' => $state,
                    'gst_number' => $plantGst,
                    'shipping_address' => $shippingAddress,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Client profile '{$client->company_name}' registered successfully!",
            'data' => $client
        ]);
    }

    /**
     * Update Client (AJAX).
     */
    public function updateClient(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'client_email' => 'required|email|max:255',
            'gst_number' => 'required|string|max:50',
            'corporate_address' => 'required|string',
        ]);

        $client->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Client '{$client->company_name}' updated successfully!",
            'data' => $client
        ]);
    }

    /**
     * Delete Client (AJAX).
     */
    public function deleteClient($id)
    {
        $client = Client::findOrFail($id);
        $clientName = $client->company_name;

        DB::transaction(function () use ($client) {
            $client->plants()->delete();
            $client->delete();
        });

        return response()->json([
            'success' => true,
            'message' => "Client '{$clientName}' and its associated plants deleted successfully!"
        ]);
    }

    public static function getGstStateCode(string $stateName): string
    {
        $gstStateCodes = [
            'Jammu & Kashmir' => '01', 'Himachal Pradesh' => '02', 'Punjab' => '03', 'Chandigarh' => '04',
            'Uttarakhand' => '05', 'Haryana' => '06', 'Delhi' => '07', 'Rajasthan' => '08',
            'Uttar Pradesh' => '09', 'Bihar' => '10', 'Sikkim' => '11', 'Arunachal Pradesh' => '12',
            'Nagaland' => '13', 'Manipur' => '14', 'Mizoram' => '15', 'Tripura' => '16',
            'Meghalaya' => '17', 'Assam' => '18', 'West Bengal' => '19', 'Jharkhand' => '20',
            'Odisha' => '21', 'Chhattisgarh' => '22', 'Madhya Pradesh' => '23', 'Gujarat' => '24',
            'Daman & Diu' => '25', 'Dadra & Nagar Haveli' => '26', 'Maharashtra' => '27', 'Andhra Pradesh' => '28',
            'Karnataka' => '29', 'Goa' => '30', 'Lakshadweep' => '31', 'Kerala' => '32',
            'Tamil Nadu' => '33', 'Puducherry' => '34', 'Andaman & Nicobar' => '35', 'Telangana' => '36', 'Ladakh' => '37'
        ];
        return $gstStateCodes[trim($stateName)] ?? '24';
    }

    /**
     * Create Plant (AJAX).
     */
    public function storePlant(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'plant_name' => 'required|string|max:255',
            'state' => 'required|string|max:100',
            'gst_number' => 'nullable|string|max:50',
            'shipping_address' => 'required|string',
        ]);

        $state = $validated['state'];
        $expectedCode = self::getGstStateCode($state);
        $client = Client::findOrFail($validated['client_id']);
        $clientGstCode = substr($client->gst_number, 0, 2);
        $gstInput = !empty($validated['gst_number']) ? trim($validated['gst_number']) : null;

        if (!empty($gstInput)) {
            if (!str_starts_with(strtoupper($gstInput), $expectedCode)) {
                return response()->json([
                    'success' => false,
                    'errors' => ['gst_number' => ["GSTIN for {$state} plant must start with State Code {$expectedCode} (e.g. {$expectedCode}AAAAB1111A1Z5). Entered: {$gstInput}"]]
                ], 422);
            }
        } elseif ($clientGstCode !== $expectedCode) {
            return response()->json([
                'success' => false,
                'errors' => ['gst_number' => ["Plant GSTIN is REQUIRED for out-of-state plant in {$state}. State Code {$expectedCode} is required (cannot use Main GSTIN {$client->gst_number})."]]
            ], 422);
        }

        $plant = ClientPlant::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Client Plant '{$plant->plant_name}' created successfully!",
            'data' => $plant
        ]);
    }

    /**
     * Update Plant (AJAX).
     */
    public function updatePlant(Request $request, $id)
    {
        $plant = ClientPlant::findOrFail($id);

        $validated = $request->validate([
            'plant_name' => 'required|string|max:255',
            'state' => 'required|string|max:100',
            'gst_number' => 'nullable|string|max:50',
            'shipping_address' => 'required|string',
        ]);

        $state = $validated['state'];
        $expectedCode = self::getGstStateCode($state);
        $client = $plant->client;
        $clientGstCode = $client ? substr($client->gst_number, 0, 2) : '24';
        $gstInput = !empty($validated['gst_number']) ? trim($validated['gst_number']) : null;

        if (!empty($gstInput)) {
            if (!str_starts_with(strtoupper($gstInput), $expectedCode)) {
                return response()->json([
                    'success' => false,
                    'errors' => ['gst_number' => ["GSTIN for {$state} plant must start with State Code {$expectedCode} (e.g. {$expectedCode}AAAAB1111A1Z5). Entered: {$gstInput}"]]
                ], 422);
            }
        } elseif ($clientGstCode !== $expectedCode) {
            return response()->json([
                'success' => false,
                'errors' => ['gst_number' => ["Plant GSTIN is REQUIRED for out-of-state plant in {$state}. State Code {$expectedCode} is required (cannot use Main GSTIN {$client->gst_number})."]]
            ], 422);
        }

        $plant->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Plant '{$plant->plant_name}' updated successfully!",
            'data' => $plant
        ]);
    }

    /**
     * Delete Plant (AJAX).
     */
    public function deletePlant($id)
    {
        $plant = ClientPlant::findOrFail($id);
        $plantName = $plant->plant_name;
        $plant->delete();

        return response()->json([
            'success' => true,
            'message' => "Plant '{$plantName}' deleted successfully!"
        ]);
    }

    /**
     * 6. Invoices & Billing.
     */
    public function invoices(Request $request)
    {
        $invoices = Invoice::with(['deliveryChallans.plant', 'deliveryChallan.client'])->orderBy('created_at', 'desc')->paginate(20);
        $finishedGoods = Product::all();
        $clients = Client::with('plants')->get();

        $prefillOrder = null;
        if ($request->has('order_id')) {
            $prefillOrder = SalesOrder::with(['items.finishedGood', 'client', 'plant'])->find($request->input('order_id'));
        }

        return view('dashboard.invoices', compact('invoices', 'finishedGoods', 'clients', 'prefillOrder'));
    }

    /**
     * Generate Custom Direct Invoice (AJAX).
     */
    public function generateCustomInvoice(Request $request)
    {
        $validated = $request->validate([
            'invoice_number' => 'required|string|unique:invoices,invoice_number',
            'plant_id' => 'required|exists:client_plants,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'invoice_date' => 'nullable|date',
            'vehicle_number' => ['nullable', 'string', 'regex:/^[A-Z]{2}[ -]?[0-9O]{1,2}[ -]?[A-Z]{0,3}[ -]?[0-9O]{1,4}$|^[0-9O]{2}[ -]?BH[ -]?[0-9O]{1,4}[ -]?[A-Z]{1,2}$/i'],
            'due_date' => 'nullable|date',
            'finished_good_ids' => 'required|array|min:1',
            'finished_good_ids.*' => 'required|exists:finished_goods,id',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'required|integer|min:1',
            'unit_prices' => 'required|array|min:1',
            'unit_prices.*' => 'required|numeric|min:0',
        ], [
            'vehicle_number.regex' => 'Enter valid vehicle number',
        ]);

        try {
            $invoice = DB::transaction(function () use ($validated) {
                $plant = ClientPlant::findOrFail($validated['plant_id']);
                $isGujarat = strcasecmp(trim($plant->state), 'Gujarat') === 0;

                // Calculate taxable subtotal
                $taxable = 0.00;
                foreach ($validated['finished_good_ids'] as $idx => $fgId) {
                    $taxable += $validated['quantities'][$idx] * $validated['unit_prices'][$idx];
                }

                $cgst = 0.00;
                $sgst = 0.00;
                $igst = 0.00;

                if ($isGujarat) {
                    $cgst = round($taxable * 0.09, 2);
                    $sgst = round($taxable * 0.09, 2);
                } else {
                    $igst = round($taxable * 0.18, 2);
                }

                $total = $taxable + $cgst + $sgst + $igst;
                $invDate = $validated['invoice_date'] ?? date('Y-m-d');
                $dueDate = !empty($validated['due_date']) ? $validated['due_date'] : date('Y-m-d', strtotime($invDate . ' +30 days'));

                // Create dummy delivery challan for manual items
                $challan = \App\Models\DeliveryChallan::create([
                    'client_id' => $plant->client_id,
                    'plant_id' => $plant->id,
                    'challan_number' => 'DC-M-' . $validated['invoice_number'],
                    'dispatch_date' => $invDate,
                    'status' => 'invoiced',
                ]);

                // Save items
                foreach ($validated['finished_good_ids'] as $idx => $fgId) {
                    \App\Models\DeliveryChallanItem::create([
                        'delivery_challan_id' => $challan->id,
                        'finished_good_id' => $fgId,
                        'quantity' => $validated['quantities'][$idx],
                        'unit_price' => $validated['unit_prices'][$idx],
                    ]);
                }

                $invoice = Invoice::create([
                    'delivery_challan_id' => $challan->id,
                    'invoice_number' => $validated['invoice_number'],
                    'vehicle_number' => $validated['vehicle_number'] ?? null,
                    'invoice_date' => $invDate,
                    'total_taxable_value' => $taxable,
                    'cgst' => $cgst,
                    'sgst' => $sgst,
                    'igst' => $igst,
                    'total_amount' => $total,
                    'payment_status' => 'unpaid',
                    'paid_amount' => 0.00,
                    'due_date' => $dueDate,
                    'created_at' => $invDate . ' ' . now()->format('H:i:s'),
                ]);

                $challan->update(['invoice_id' => $invoice->id]);

                if (!empty($validated['sales_order_id'])) {
                    SalesOrder::where('id', $validated['sales_order_id'])->update(['status' => 'dispatched']);
                }

                return $invoice;
            });

            return response()->json([
                'success' => true,
                'message' => "Custom Tax Invoice '{$invoice->invoice_number}' logged successfully!",
                'data' => $invoice
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Failed to log invoice: ' . $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Record payment against an invoice (Full or Partial with payment method & ref #).
     */
    public function recordInvoicePayment(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:bank_transfer,cheque,upi,cash',
            'account_type' => 'required|in:bank,cash',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $invoice = Invoice::with(['deliveryChallan.client', 'deliveryChallans.client'])->findOrFail($id);
            $primaryChallan = $invoice->deliveryChallan ?? $invoice->deliveryChallans->first();
            $clientId = $primaryChallan ? $primaryChallan->client_id : null;

            $amount = (float) $validated['amount'];
            $remaining = (float) $invoice->remaining_balance;

            if ($amount > ($remaining + 0.01)) {
                return response()->json([
                    'success' => false,
                    'errors' => ['amount' => ["Payment amount (₹" . number_format($amount, 2) . ") cannot exceed remaining invoice balance (₹" . number_format($remaining, 2) . ")."]]
                ], 422);
            }

            DB::transaction(function () use ($invoice, $validated, $amount, $clientId, $plantId) {
                // 1. Create Payment Voucher
                Payment::create([
                    'payment_number' => Payment::generatePaymentNumber('received'),
                    'payment_type' => 'received',
                    'invoice_id' => $invoice->id,
                    'client_id' => $clientId,
                    'plant_id' => $plantId,
                    'amount' => $amount,
                    'payment_date' => $validated['payment_date'],
                    'payment_method' => $validated['payment_method'],
                    'account_type' => $validated['account_type'],
                    'reference_number' => $validated['reference_number'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]);

                // 2. Update Invoice Paid Amount & Status
                $newPaidAmount = round((float)$invoice->paid_amount + $amount, 2);
                $totalAmount = (float)$invoice->total_amount;

                $newStatus = 'partially_paid';
                if ($newPaidAmount >= ($totalAmount - 0.01)) {
                    $newPaidAmount = $totalAmount;
                    $newStatus = 'paid';
                }

                $invoice->update([
                    'paid_amount' => $newPaidAmount,
                    'payment_status' => $newStatus,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => "Payment of ₹" . number_format($amount, 2) . " recorded successfully for Invoice '{$invoice->invoice_number}'!"
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Failed to record payment: ' . $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Mark an invoice as Paid (Quick action).
     */
    public function payInvoice($id)
    {
        try {
            $invoice = Invoice::with(['deliveryChallan.client', 'deliveryChallans.client'])->findOrFail($id);
            $remaining = (float) $invoice->remaining_balance;

            if ($remaining <= 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Invoice '{$invoice->invoice_number}' is already fully paid."
                ]);
            }

            $primaryChallan = $invoice->deliveryChallan ?? $invoice->deliveryChallans->first();
            $clientId = $primaryChallan ? $primaryChallan->client_id : null;
            $plantId = $primaryChallan ? $primaryChallan->plant_id : null;

            DB::transaction(function () use ($invoice, $remaining, $clientId, $plantId) {
                Payment::create([
                    'payment_number' => Payment::generatePaymentNumber('received'),
                    'payment_type' => 'received',
                    'invoice_id' => $invoice->id,
                    'client_id' => $clientId,
                    'plant_id' => $plantId,
                    'amount' => $remaining,
                    'payment_date' => date('Y-m-d'),
                    'payment_method' => 'bank_transfer',
                    'account_type' => 'bank',
                    'notes' => 'Quick marked as fully paid',
                ]);

                $invoice->update([
                    'payment_status' => 'paid',
                    'paid_amount' => $invoice->total_amount,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => "Invoice '{$invoice->invoice_number}' marked as fully paid successfully!"
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Failed to update payment status: ' . $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Record payment to vendor for purchase.
     */
    public function recordPurchasePayment(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:bank_transfer,cheque,upi,cash',
            'account_type' => 'required|in:bank,cash',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $purchase = Purchase::findOrFail($id);
            $amount = (float) $validated['amount'];
            $remaining = (float) $purchase->remaining_balance;

            if ($amount > ($remaining + 0.01)) {
                return response()->json([
                    'success' => false,
                    'errors' => ['amount' => ["Payment amount (₹" . number_format($amount, 2) . ") cannot exceed purchase balance (₹" . number_format($remaining, 2) . ")."]]
                ], 422);
            }

            DB::transaction(function () use ($purchase, $validated, $amount) {
                Payment::create([
                    'payment_number' => Payment::generatePaymentNumber('paid'),
                    'payment_type' => 'paid',
                    'purchase_id' => $purchase->id,
                    'vendor_name' => $purchase->vendor_name,
                    'amount' => $amount,
                    'payment_date' => $validated['payment_date'],
                    'payment_method' => $validated['payment_method'],
                    'account_type' => $validated['account_type'],
                    'reference_number' => $validated['reference_number'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]);

                $newPaidAmount = round((float)($purchase->paid_amount ?? 0) + $amount, 2);
                $totalAmount = (float)$purchase->total_amount;

                $newStatus = 'partially_paid';
                if ($newPaidAmount >= ($totalAmount - 0.01)) {
                    $newPaidAmount = $totalAmount;
                    $newStatus = 'paid';
                }

                $purchase->update([
                    'paid_amount' => $newPaidAmount,
                    'payment_status' => $newStatus,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => "Payment of ₹" . number_format($amount, 2) . " recorded for vendor '{$purchase->vendor_name}'!"
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Failed to record vendor payment: ' . $e->getMessage()]
            ], 500);
        }
    }

    /**
     * View Client Account Ledger page.
     */
    public function clientLedger(Request $request, $id)
    {
        [$startDate, $endDate, $period, $filterMonth, $filterYear] = $this->getDateRange($request);
        $plantId = $request->input('plant_id') ? (int)$request->input('plant_id') : null;
        $ledgerData = $this->financialService->getClientLedger($id, $startDate, $endDate, $plantId);
        
        return view('dashboard.client_ledger', array_merge($ledgerData, [
            'period' => $period,
            'filterMonth' => $filterMonth,
            'filterYear' => $filterYear,
            'plant_id' => $plantId,
        ]));
    }

    /**
     * Download Client Account Statement PDF.
     */
    public function downloadClientLedgerPdf(Request $request, $id)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $plantId = $request->input('plant_id') ? (int)$request->input('plant_id') : null;
        $ledgerData = $this->financialService->getClientLedger($id, $startDate, $endDate, $plantId);

        $pdf = Pdf::loadView('pdf.client_ledger_pdf', $ledgerData)
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);

        $clientName = str_replace(' ', '_', $ledgerData['client']->company_name);
        $plantSuffix = $ledgerData['selected_plant'] ? '_' . str_replace(' ', '_', $ledgerData['selected_plant']->plant_name) : '';
        $filename = "Statement_of_Account_{$clientName}{$plantSuffix}_" . date('Ymd') . ".pdf";

        return $pdf->download($filename);
    }

    /**
     * Delete an invoice (AJAX).
     */
    public function deleteInvoice($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);
            $invNum = $invoice->invoice_number;

            DB::transaction(function () use ($invoice) {
                // 1. Unlink any aggregated challans and revert status to pending_invoice
                DeliveryChallan::where('invoice_id', $invoice->id)->update([
                    'invoice_id' => null, 
                    'status' => 'pending_invoice'
                ]);

                // 2. Delete linked payment voucher entries
                Payment::where('invoice_id', $invoice->id)->delete();

                // 3. Save primary challan ID & unlink from invoice to prevent foreign key constraint
                $primaryChallanId = $invoice->delivery_challan_id;
                $invoice->update(['delivery_challan_id' => null]);

                // 4. Delete custom primary challan & items if generated for this invoice
                if ($primaryChallanId) {
                    $primaryChallan = DeliveryChallan::find($primaryChallanId);
                    if ($primaryChallan) {
                        $primaryChallan->items()->delete();
                        $primaryChallan->delete();
                    }
                }

                // 5. Delete invoice record
                $invoice->delete();
            });

            return response()->json([
                'success' => true,
                'message' => "Invoice '{$invNum}' deleted successfully!"
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Failed to delete invoice: ' . $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Print / Save PDF representation of the invoice.
     */
    public function printInvoice($id)
    {
        $invoice = Invoice::with([
            'deliveryChallan.client', 
            'deliveryChallan.plant', 
            'deliveryChallan.items.finishedGood',
            'deliveryChallans.plant',
            'deliveryChallans.items.finishedGood'
        ])->findOrFail($id);

        $primaryChallan = $invoice->deliveryChallan;
        $client = $primaryChallan ? $primaryChallan->client : null;
        $plant = $primaryChallan ? $primaryChallan->plant : null;

        if (!$client && $invoice->deliveryChallans->isNotEmpty()) {
            $first = $invoice->deliveryChallans->first();
            $client = $first->client;
            $plant = $first->plant;
        }

        $items = collect();
        if ($primaryChallan) {
            $items = $items->concat($primaryChallan->items);
        }
        foreach ($invoice->deliveryChallans as $dc) {
            if ($dc->id !== ($primaryChallan->id ?? null)) {
                $items = $items->concat($dc->items);
            }
        }

        $groupedItems = $items->groupBy('finished_good_id')->map(function($group) {
            $firstItem = $group->first();
            return (object)[
                'product_name' => $firstItem->finishedGood->product_name ?? 'Custom Product',
                'sku' => $firstItem->finishedGood->sku ?? 'N/A',
                'quantity' => $group->sum('quantity'),
                'unit_price' => $firstItem->unit_price,
                'total' => $group->sum(function($item) { return $item->quantity * $item->unit_price; })
            ];
        });

        return view('dashboard.invoice_print', compact('invoice', 'client', 'plant', 'groupedItems'));
    }

    /**
     * Preview Invoice page (Frest Style).
     */
    public function previewInvoice($id)
    {
        $invoice = Invoice::with([
            'deliveryChallan.client', 
            'deliveryChallan.plant', 
            'deliveryChallan.items.finishedGood',
            'deliveryChallans.client',
            'deliveryChallans.plant',
            'deliveryChallans.items.finishedGood'
        ])->findOrFail($id);

        $primaryChallan = $invoice->deliveryChallan;
        $client = $primaryChallan ? $primaryChallan->client : null;
        $plant = $primaryChallan ? $primaryChallan->plant : null;

        if (!$client && $invoice->deliveryChallans->isNotEmpty()) {
            $first = $invoice->deliveryChallans->first();
            $client = $first->client;
            $plant = $first->plant;
        }

        $items = collect();
        if ($primaryChallan) {
            $items = $items->concat($primaryChallan->items);
        }
        foreach ($invoice->deliveryChallans as $dc) {
            if ($dc->id !== ($primaryChallan->id ?? null)) {
                $items = $items->concat($dc->items);
            }
        }

        $groupedItems = $items->groupBy('finished_good_id')->map(function($group) {
            $firstItem = $group->first();
            return (object)[
                'product_name' => $firstItem->finishedGood->product_name ?? 'Custom Product',
                'sku' => $firstItem->finishedGood->sku ?? 'N/A',
                'quantity' => $group->sum('quantity'),
                'unit_price' => $firstItem->unit_price,
                'total' => $group->sum(function($item) { return $item->quantity * $item->unit_price; })
            ];
        });

        return view('dashboard.invoice_preview', compact('invoice', 'client', 'plant', 'groupedItems'));
    }

    /**
     * Download Invoice as PDF document.
     */
    public function downloadInvoicePdf($id)
    {
        $invoice = Invoice::with([
            'deliveryChallan.client', 
            'deliveryChallan.plant', 
            'deliveryChallan.items.finishedGood',
            'deliveryChallans.client',
            'deliveryChallans.plant',
            'deliveryChallans.items.finishedGood'
        ])->findOrFail($id);

        $primaryChallan = $invoice->deliveryChallan;
        $client = $primaryChallan ? $primaryChallan->client : null;
        $plant = $primaryChallan ? $primaryChallan->plant : null;

        if (!$client && $invoice->deliveryChallans->isNotEmpty()) {
            $first = $invoice->deliveryChallans->first();
            $client = $first->client;
            $plant = $first->plant;
        }

        $items = collect();
        if ($primaryChallan) {
            $items = $items->concat($primaryChallan->items);
        }
        foreach ($invoice->deliveryChallans as $dc) {
            if ($dc->id !== ($primaryChallan->id ?? null)) {
                $items = $items->concat($dc->items);
            }
        }

        $groupedItems = $items->groupBy('finished_good_id')->map(function($group) {
            $firstItem = $group->first();
            return (object)[
                'product_name' => $firstItem->finishedGood->product_name ?? 'Custom Product',
                'sku' => $firstItem->finishedGood->sku ?? 'N/A',
                'quantity' => $group->sum('quantity'),
                'unit_price' => $firstItem->unit_price,
                'total' => $group->sum(function($item) { return $item->quantity * $item->unit_price; })
            ];
        });

        $isPdf = true;
        $pdf = Pdf::loadView('dashboard.invoice_print', compact('invoice', 'client', 'plant', 'groupedItems', 'isPdf'));
        return $pdf->download("Invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Send Invoice Email to recipient with attached PDF.
     */
    public function sendInvoiceEmail(Request $request, $id)
    {
        try {
            $request->validate([
                'recipient_email' => 'required|email',
                'subject' => 'required|string|max:255',
                'message_body' => 'required|string',
            ]);

            $invoice = Invoice::with([
                'deliveryChallan.client', 
                'deliveryChallan.plant', 
                'deliveryChallan.items.finishedGood',
                'deliveryChallans.client',
                'deliveryChallans.plant',
                'deliveryChallans.items.finishedGood'
            ])->findOrFail($id);

            $primaryChallan = $invoice->deliveryChallan;
            $client = $primaryChallan ? $primaryChallan->client : null;
            $plant = $primaryChallan ? $primaryChallan->plant : null;

            if (!$client && $invoice->deliveryChallans->isNotEmpty()) {
                $first = $invoice->deliveryChallans->first();
                $client = $first->client;
                $plant = $first->plant;
            }

            $items = collect();
            if ($primaryChallan) {
                $items = $items->concat($primaryChallan->items);
            }
            foreach ($invoice->deliveryChallans as $dc) {
                if ($dc->id !== ($primaryChallan->id ?? null)) {
                    $items = $items->concat($dc->items);
                }
            }

            $groupedItems = $items->groupBy('finished_good_id')->map(function($group) {
                $firstItem = $group->first();
                return (object)[
                    'product_name' => $firstItem->finishedGood->product_name ?? 'Custom Product',
                    'sku' => $firstItem->finishedGood->sku ?? 'N/A',
                    'quantity' => $group->sum('quantity'),
                    'unit_price' => $firstItem->unit_price,
                    'total' => $group->sum(function($item) { return $item->quantity * $item->unit_price; })
                ];
            });

            $isPdf = true;
            $pdfContent = Pdf::loadView('dashboard.invoice_print', compact('invoice', 'client', 'plant', 'groupedItems', 'isPdf'))
                ->setOption([
                    'isRemoteEnabled' => false,
                    'isFontSubsettingEnabled' => true,
                    'dpi' => 96
                ])
                ->output();

            Mail::to($request->recipient_email)->queue(
                new InvoiceMail($invoice, $request->subject, $request->message_body, $pdfContent, $client, $plant, $groupedItems)
            );

            return response()->json([
                'success' => true,
                'message' => "Invoice #{$invoice->invoice_number} emailed successfully to {$request->recipient_email}!"
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 8. Employees Directory.
     */
    public function employees()
    {
        $staffProfiles = StaffProfile::orderBy('full_name')->paginate(20);
        return view('dashboard.employees', compact('staffProfiles'));
    }

    /**
     * Create Employee Profile (AJAX).
     */
    public function storeEmployee(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'wage_type' => 'required|in:fixed,per-day',
            'monthly_salary' => 'nullable|required_if:wage_type,fixed|numeric|min:0',
            'piece_rate_per_unit' => 'nullable|required_if:wage_type,per-day|numeric|min:0',
        ]);

        $staff = StaffProfile::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Employee profile for '{$staff->full_name}' created successfully!",
            'data' => $staff
        ]);
    }

    /**
     * Update employee profile (AJAX).
     */
    public function updateEmployee(Request $request, $id)
    {
        $staff = StaffProfile::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'wage_type' => 'required|in:fixed,per-day',
            'monthly_salary' => 'nullable|required_if:wage_type,fixed|numeric|min:0',
            'piece_rate_per_unit' => 'nullable|required_if:wage_type,per-day|numeric|min:0',
        ]);

        if ($validated['wage_type'] === 'fixed') {
            $validated['piece_rate_per_unit'] = null;
        } else {
            $validated['monthly_salary'] = null;
        }

        $staff->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Employee profile for '{$staff->full_name}' updated successfully!",
            'data' => $staff
        ]);
    }

    /**
     * Delete employee profile (AJAX).
     */
    public function deleteEmployee($id)
    {
        $staff = StaffProfile::findOrFail($id);
        $name = $staff->full_name;
        $staff->delete();

        return response()->json([
            'success' => true,
            'message' => "Employee '{$name}' deleted successfully!"
        ]);
    }

    /**
     * Disburse payroll (AJAX).
     */
    public function payPayroll(Request $request)
    {
        $validated = $request->validate([
            'labor_log_ids' => 'required|array|min:1',
            'labor_log_ids.*' => 'required|exists:labor_logs,id',
        ]);

        try {
            $count = $this->payrollService->markWagesAsPaid($validated['labor_log_ids']);
            return response()->json([
                'success' => true,
                'message' => "Successfully paid compiled wages for {$count} logged runs!"
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * 9. Operational Expenses.
     */
    public function expenses()
    {
        $expenses = Expense::orderBy('expense_date', 'desc')->paginate(20);
        return view('dashboard.expenses', compact('expenses'));
    }

    /**
     * Log Expense (AJAX).
     */
    public function logExpense(Request $request)
    {
        $validated = $request->validate([
            'expense_category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $expense = Expense::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Expense logged successfully in category '" . str_replace('_', ' ', $expense->expense_category) . "'!",
            'data' => $expense
        ]);
    }

    /**
     * Purchase Ledger Page.
     */
    public function purchases()
    {
        $purchases = \App\Models\Purchase::with('rawMaterial')->orderBy('purchase_date', 'desc')->paginate(20);
        $rawMaterials = RawMaterial::orderBy('material_name')->get();
        return view('dashboard.purchases', compact('purchases', 'rawMaterials'));
    }

    /**
     * Store Purchase Record (AJAX).
     */
    public function storePurchase(Request $request)
    {
        $validated = $request->validate([
            'bill_number' => 'nullable|string|max:100',
            'vendor_name' => 'required|string|max:255',
            'purchase_type' => 'required|in:raw_material,machinery,supplies,others',
            'raw_material_id' => 'nullable|required_if:purchase_type,raw_material|exists:raw_materials,id',
            'item_name' => 'nullable|string|max:255',
            'quantity' => 'nullable|numeric|min:0.0001',
            'unit' => 'nullable|string|max:50',
            'total_amount' => 'required|numeric|min:0',
            'gst_rate' => 'required|numeric|in:0,5,12,18,28',
            'purchase_date' => 'required|date',
        ]);

        $gstRate = (float) $validated['gst_rate'];
        $totalAmt = (float) $validated['total_amount'];
        $validated['gst_amount'] = round($totalAmt * ($gstRate / 100), 2);

        if (empty($validated['quantity'])) {
            $validated['quantity'] = 1.0;
        }

        if ($validated['purchase_type'] === 'raw_material' && !empty($validated['raw_material_id'])) {
            $material = RawMaterial::find($validated['raw_material_id']);
            if ($material) {
                if (empty($validated['item_name'])) {
                    $validated['item_name'] = $material->material_name;
                }
                if (empty($validated['unit'])) {
                    $validated['unit'] = $material->unit;
                }
            }
        }

        if (empty($validated['item_name'])) {
            $validated['item_name'] = 'Purchased Item';
        }
        if (empty($validated['unit'])) {
            $validated['unit'] = 'pcs';
        }

        $purchase = DB::transaction(function() use ($validated) {
            $pur = \App\Models\Purchase::create($validated);

            // Auto-restock if raw material purchase
            if ($validated['purchase_type'] === 'raw_material' && !empty($validated['raw_material_id'])) {
                $material = RawMaterial::find($validated['raw_material_id']);
                if ($material) {
                    $material->current_stock += (float) $validated['quantity'];
                    $material->save();
                }
            }

            return $pur;
        });

        return response()->json([
            'success' => true,
            'message' => "Purchase record '{$purchase->item_name}' logged successfully! Stock & accounting updated.",
            'data' => $purchase
        ]);
    }

    /**
     * 10. Reports Page.
     */
    public function reports(Request $request)
    {
        [$startDate, $endDate, $period, $filterMonth, $filterYear] = $this->getDateRange($request);
        $reportType = $request->input('report_type', 'invoice');

        // 1. Fetch Invoices
        $invoices = Invoice::with(['deliveryChallan.client', 'deliveryChallans.plant'])
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('invoice_date', [$startDate, $endDate])
                  ->orWhere(function($sub) use ($startDate, $endDate) {
                      $sub->whereNull('invoice_date')
                          ->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);
                  });
            })
            ->orderBy('invoice_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Fetch Purchases
        $purchases = \App\Models\Purchase::whereBetween('purchase_date', [$startDate, $endDate])
            ->orderBy('purchase_date', 'desc')
            ->get();

        // 3. Fetch Financials
        $financials = $this->financialService->getFinancialSummary($startDate, $endDate);

        // 4. Calculate Summaries
        $invoiceSummary = [
            'total_taxable' => $invoices->sum('total_taxable_value'),
            'total_cgst' => $invoices->sum('cgst'),
            'total_sgst' => $invoices->sum('sgst'),
            'total_igst' => $invoices->sum('igst'),
            'total_amount' => $invoices->sum('total_amount'),
        ];
        $invoiceSummary['total_gst'] = $invoiceSummary['total_cgst'] + $invoiceSummary['total_sgst'] + $invoiceSummary['total_igst'];

        $purchaseSummary = [
            'total_spent' => $purchases->sum('total_amount'),
            'total_gst' => $purchases->sum('gst_amount'),
            'total_raw_material' => $purchases->where('purchase_type', 'raw_material')->sum('total_amount'),
            'total_machinery' => $purchases->where('purchase_type', 'machinery')->sum('total_amount'),
            'total_supplies' => $purchases->where('purchase_type', 'supplies')->sum('total_amount'),
        ];

        $gstSummary = [
            'sales_cgst' => $invoiceSummary['total_cgst'],
            'sales_sgst' => $invoiceSummary['total_sgst'],
            'sales_igst' => $invoiceSummary['total_igst'],
            'sales_total_gst' => $invoiceSummary['total_gst'],
            'purchase_total_gst' => $purchaseSummary['total_gst'],
        ];
        $gstSummary['net_gst_payable'] = $gstSummary['sales_total_gst'] - $gstSummary['purchase_total_gst'];

        return view('dashboard.reports', compact(
            'startDate', 'endDate', 'period', 'reportType',
            'invoices', 'purchases', 'financials',
            'invoiceSummary', 'purchaseSummary', 'gstSummary',
            'filterMonth', 'filterYear'
        ));
    }

    /**
     * Export Reports Data to CSV.
     */
    public function exportCsv(Request $request)
    {
        [$startDate, $endDate, $period, $filterMonth, $filterYear] = $this->getDateRange($request);
        
        $financials = $this->financialService->getFinancialSummary($startDate, $endDate);
        
        // Exact same query as Reports UI invoices load
        $invoices = Invoice::where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('invoice_date', [$startDate, $endDate])
                  ->orWhere(function($sub) use ($startDate, $endDate) {
                      $sub->whereNull('invoice_date')
                          ->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);
                  });
            })
            ->orderBy('invoice_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])->get();

        $response = new StreamedResponse(function() use ($startDate, $endDate, $financials, $invoices, $expenses) {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Section 1: Title
            fputcsv($handle, ['PRAFUL WELDING WORKS (PWW) - FINANCIAL AUDIT REPORT']);
            fputcsv($handle, ['Period:', $startDate, 'to', $endDate]);
            fputcsv($handle, []);

            // Section 2: Statement of Profit & Loss
            fputcsv($handle, ['STATEMENT OF NET PROFIT & LOSS']);
            fputcsv($handle, ['Line Item', 'Accounting Detail', 'Amount (INR)']);
            fputcsv($handle, ['Total Sales Revenue (A)', 'Taxable invoiced amounts (excl. GST)', $financials['revenue']]);
            fputcsv($handle, ['Cost of Goods Sold (B)', 'Weighted raw material stock consumption + waste', $financials['cogs']]);
            fputcsv($handle, ['Direct Wages Paid (C)', 'Piece-rate labor log disbursements', $financials['direct_wages']]);
            fputcsv($handle, ['Operational Overheads (D)', 'Electricity, gas, rent, admin, transport', $financials['overheads']]);
            fputcsv($handle, ['Machinery Depreciation (E)', 'Wear and tear schedules', $financials['depreciation']]);
            fputcsv($handle, ['NET CORPORATE PROFIT', 'Calculation: A - B - C - D - E', $financials['net_profit']]);
            fputcsv($handle, ['Gross profit margin (%)', 'Margin ratio', $financials['gross_profit_margin'] . '%']);
            fputcsv($handle, []);

            // Section 3: Invoices Audit Ledger
            fputcsv($handle, ['INVOICES LEDGER AUDIT']);
            fputcsv($handle, ['Invoice No', 'Taxable Value', 'CGST', 'SGST', 'IGST', 'Total Amount', 'Due Date', 'Status']);
            foreach ($invoices as $inv) {
                fputcsv($handle, [
                    $inv->invoice_number,
                    $inv->total_taxable_value,
                    $inv->cgst,
                    $inv->sgst,
                    $inv->igst,
                    $inv->total_amount,
                    $inv->due_date ? $inv->due_date->toDateString() : ($inv->invoice_date ? Carbon::parse($inv->invoice_date)->toDateString() : 'N/A'),
                    $inv->payment_status
                ]);
            }
            fputcsv($handle, []);

            // Section 4: Expenses audit ledger
            fputcsv($handle, ['EXPENSES LEDGER AUDIT']);
            fputcsv($handle, ['Date', 'Expense Category', 'Memo Description', 'Amount (INR)']);
            foreach ($expenses as $exp) {
                fputcsv($handle, [
                    $exp->expense_date->toDateString(),
                    ucwords(str_replace('_', ' ', $exp->expense_category)),
                    $exp->description,
                    $exp->amount
                ]);
            }

            fclose($handle);
        });

        // Determine filename dynamically based on period
        switch ($period) {
            case 'all':
                $filename = "PWW-ERP-Audit-Report-All-Records.csv";
                break;
            case 'month':
                $filename = "PWW-ERP-Audit-Report-Month-{$filterMonth}.csv";
                break;
            case 'year':
                $nextYearShort = substr((int)$filterYear + 1, 2, 2);
                $filename = "PWW-ERP-Audit-Report-FY-{$filterYear}-{$nextYearShort}.csv";
                break;
            case 'custom':
            default:
                $filename = "PWW-ERP-Audit-Report-{$startDate}-to-{$endDate}.csv";
                break;
        }

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    /**
     * Trigger Database Re-seeding for demonstration.
     */
    public function resetData()
    {
        try {
            Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
            
            // Automatically log in the seed user to maintain session after migrate:fresh
            $seedUser = User::where('email', 'pww@example.com')->first();
            if ($seedUser) {
                auth()->login($seedUser);
            }

            return response()->json([
                'success' => true,
                'message' => 'Database reset and seeded with production-grade demo data successfully!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Database reset failed: ' . $e->getMessage()]
            ], 500);
        }
    }

    /**
     * 11. Profile Management.
     */
    public function profile()
    {
        return view('dashboard.profile');
    }

    /**
     * Update Profile Information.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile information updated successfully!'
        ]);
    }

    /**
     * Update Password.
     */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'errors' => ['current_password' => ['The provided current password does not match our records.']]
            ], 422);
        }

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($validated['new_password'])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully!'
        ]);
    }

    /**
     * Update Business Profile and Settings.
     */
    public function updateBusinessSettings(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'business_subtitle' => 'required|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'required|string|max:255',
            'gstin' => 'required|string|max:255',
            'msme_number' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'bank_name' => 'required|string|max:255',
            'bank_account_name' => 'required|string|max:255',
            'bank_account_no' => 'required|string|max:255',
            'bank_ifsc' => 'required|string|max:255',
        ]);

        try {
            \App\Models\Setting::set('business_name', $validated['business_name']);
            \App\Models\Setting::set('business_subtitle', $validated['business_subtitle']);
            \App\Models\Setting::set('address_line_1', $validated['address_line_1']);
            \App\Models\Setting::set('address_line_2', $validated['address_line_2']);
            \App\Models\Setting::set('gstin', $validated['gstin']);
            \App\Models\Setting::set('msme_number', $request->input('msme_number', ''));
            \App\Models\Setting::set('bank_name', $validated['bank_name']);
            \App\Models\Setting::set('bank_account_name', $validated['bank_account_name']);
            \App\Models\Setting::set('bank_account_no', $validated['bank_account_no']);
            \App\Models\Setting::set('bank_ifsc', $validated['bank_ifsc']);

            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads'), $filename);
                \App\Models\Setting::set('logo_path', 'uploads/' . $filename);
            }

            return response()->json([
                'success' => true,
                'message' => 'Business settings updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Failed to save business settings: ' . $e->getMessage()]
            ], 500);
        }
    }

    /**
     * View Sales Orders page.
     */
    public function orders(Request $request)
    {
        $status = $request->input('status', 'all');
        $query = SalesOrder::with(['client', 'plant', 'items.finishedGood'])->orderBy('order_date', 'desc')->orderBy('id', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $orders = $query->get();
        $clients = Client::with('plants')->orderBy('company_name')->get();
        $finishedGoods = Product::orderBy('product_name')->get();

        $stats = [
            'total' => SalesOrder::count(),
            'pending' => SalesOrder::where('status', 'pending')->count(),
            'in_production' => SalesOrder::where('status', 'in_production')->count(),
            'ready' => SalesOrder::where('status', 'ready_for_dispatch')->count(),
            'completed' => SalesOrder::whereIn('status', ['dispatched', 'completed'])->count(),
        ];

        return view('dashboard.orders', compact('orders', 'clients', 'finishedGoods', 'status', 'stats'));
    }

    /**
     * Store new Sales Order (AJAX).
     */
    public function storeOrder(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'plant_id' => 'nullable|exists:client_plants,id',
            'po_number' => 'nullable|string|max:100',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'finished_good_ids' => 'required|array|min:1',
            'finished_good_ids.*' => 'required|exists:finished_goods,id',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'required|numeric|min:0.01',
            'unit_prices' => 'required|array|min:1',
            'unit_prices.*' => 'required|numeric|min:0',
        ]);

        try {
            $orderNumber = SalesOrder::generateNextOrderNumber();
            $totalAmount = 0.00;

            DB::transaction(function () use ($validated, $orderNumber, &$totalAmount) {
                $order = SalesOrder::create([
                    'order_number' => $orderNumber,
                    'po_number' => $validated['po_number'] ?? null,
                    'client_id' => $validated['client_id'],
                    'plant_id' => $validated['plant_id'] ?? null,
                    'order_date' => $validated['order_date'],
                    'delivery_date' => $validated['delivery_date'] ?? null,
                    'status' => 'pending',
                    'total_amount' => 0.00,
                    'notes' => $validated['notes'] ?? null,
                ]);

                foreach ($validated['finished_good_ids'] as $idx => $fgId) {
                    $qty = (float) $validated['quantities'][$idx];
                    $price = (float) $validated['unit_prices'][$idx];
                    $lineTotal = round($qty * $price, 2);
                    $totalAmount += $lineTotal;

                    SalesOrderItem::create([
                        'sales_order_id' => $order->id,
                        'finished_good_id' => $fgId,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'total_price' => $lineTotal,
                    ]);
                }

                $order->update(['total_amount' => round($totalAmount, 2)]);
            });

            return response()->json([
                'success' => true,
                'message' => "Sales Order '{$orderNumber}' created successfully!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Failed to create sales order: ' . $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Update Sales Order status (AJAX).
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_production,ready_for_dispatch,dispatched,completed,cancelled',
        ]);

        try {
            $order = SalesOrder::findOrFail($id);
            $order->update(['status' => $validated['status']]);

            return response()->json([
                'success' => true,
                'message' => "Order '{$order->order_number}' status updated to '" . $order->formatted_status . "'."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Failed to update order status: ' . $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Convert Sales Order into Delivery Challan (AJAX).
     */
    public function convertOrderToChallan($id)
    {
        try {
            $order = SalesOrder::with(['items.finishedGood'])->findOrFail($id);

            if ($order->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'errors' => ['Cancelled orders cannot be converted into Delivery Challans.']
                ], 422);
            }

            $challanNumber = DeliveryChallan::generateNextChallanNumber();

            DB::transaction(function () use ($order, $challanNumber) {
                $dc = DeliveryChallan::create([
                    'challan_number' => $challanNumber,
                    'client_id' => $order->client_id,
                    'plant_id' => $order->plant_id,
                    'dispatch_date' => date('Y-m-d'),
                    'status' => 'pending_invoice',
                ]);

                foreach ($order->items as $item) {
                    DeliveryChallanItem::create([
                        'delivery_challan_id' => $dc->id,
                        'finished_good_id' => $item->finished_good_id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                    ]);

                    // Deduct product stock
                    $good = Product::find($item->finished_good_id);
                    if ($good) {
                        $good->current_stock = max(0, $good->current_stock - $item->quantity);
                        $good->save();
                    }
                }

                $order->update(['status' => 'dispatched']);
            });

            return response()->json([
                'success' => true,
                'message' => "Delivery Challan generated successfully from Order #{$order->order_number}!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Failed to convert order to delivery challan: ' . $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Delete Sales Order (AJAX).
     */
    public function deleteOrder($id)
    {
        try {
            $order = SalesOrder::findOrFail($id);
            $orderNumber = $order->order_number;
            $order->delete();

            return response()->json([
                'success' => true,
                'message' => "Sales Order '{$orderNumber}' deleted successfully."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Failed to delete order: ' . $e->getMessage()]
            ], 500);
        }
    }
}
