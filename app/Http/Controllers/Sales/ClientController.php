<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientPlant;
use App\Services\FinancialService;
use App\Services\InvoicePdfService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    protected $financialService;
    protected $pdfService;

    public function __construct(FinancialService $financialService, InvoicePdfService $pdfService)
    {
        $this->financialService = $financialService;
        $this->pdfService = $pdfService;
    }

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
                $startDate = '2020-01-01';
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
     * 5. Clients & Plants.
     */
    public function clients()
    {
        $clients = Client::with('plants')->orderBy('company_name')->get();
        return view('pages.clients', compact('clients'));
    }

    /**
     * Create Client (AJAX) - supports 1-click primary plant creation.
     */
    public function storeClient(Request $request)
    {
        if ($res = \App\Services\RolePermissionService::authorizeAction($request, 'action_insert')) return $res;

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

        $existingClient = Client::where('company_name', $validated['company_name'])
            ->orWhere('gst_number', $validated['gst_number'])
            ->first();
        if ($existingClient) {
            return response()->json([
                'success' => false,
                'message' => "A client profile with company name '{$validated['company_name']}' or GSTIN '{$validated['gst_number']}' already exists!",
                'errors' => ['company_name' => ["A client profile with company name or GSTIN already exists!"]]
            ], 422);
        }

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

        $client = null;
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
        if ($res = \App\Services\RolePermissionService::authorizeAction($request, 'action_update')) return $res;

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
        if ($res = \App\Services\RolePermissionService::authorizeAction(request(), 'action_delete')) return $res;

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
            'email' => 'nullable|email|max:255',
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
        if ($res = \App\Services\RolePermissionService::authorizeAction($request, 'action_update')) return $res;

        $plant = ClientPlant::findOrFail($id);

        $validated = $request->validate([
            'plant_name' => 'required|string|max:255',
            'state' => 'required|string|max:100',
            'gst_number' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
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
        if ($res = \App\Services\RolePermissionService::authorizeAction(request(), 'action_delete')) return $res;

        $plant = ClientPlant::findOrFail($id);
        $plantName = $plant->plant_name;
        $plant->delete();

        return response()->json([
            'success' => true,
            'message' => "Plant '{$plantName}' deleted successfully!"
        ]);
    }

    /**
     * View Client Account Ledger page.
     */
    public function clientLedger(Request $request, $id)
    {
        [$startDate, $endDate, $period, $filterMonth, $filterYear] = $this->getDateRange($request);
        $plantId = $request->input('plant_id');

        $ledgerData = $this->financialService->getClientLedger($id, $startDate, $endDate, $plantId);

        $client = $ledgerData['client'];
        $selectedPlant = $ledgerData['selected_plant'];
        $opening_balance = $ledgerData['opening_balance'] ?? 0.00;
        $total_debit = $ledgerData['total_debit'] ?? $ledgerData['total_invoiced'] ?? 0.00;
        $total_credit = $ledgerData['total_credit'] ?? $ledgerData['total_received'] ?? 0.00;
        $closing_balance = $ledgerData['closing_balance'] ?? 0.00;
        $transactions = $ledgerData['transactions'] ?? collect();
        $entries = $ledgerData['entries'] ?? $transactions;
        $start_date = $startDate;
        $end_date = $endDate;
        $plant_id = $plantId;

        return view('pages.client_ledger', compact(
            'client', 'selectedPlant', 'opening_balance', 'total_debit', 
            'total_credit', 'closing_balance', 'transactions', 'entries', 'start_date', 
            'end_date', 'period', 'plant_id', 'filterMonth', 'filterYear'
        ));
    }

    /**
     * Download Client Ledger Statement PDF.
     */
    public function downloadClientLedgerPdf(Request $request, $id)
    {
        [$startDate, $endDate, $period, $filterMonth, $filterYear] = $this->getDateRange($request);
        $plantId = $request->input('plant_id');

        $ledgerData = $this->financialService->getClientLedger($id, $startDate, $endDate, $plantId);

        $client = $ledgerData['client'];
        $selectedPlant = $ledgerData['selected_plant'];
        $opening_balance = $ledgerData['opening_balance'] ?? 0.00;
        $total_debit = $ledgerData['total_debit'] ?? $ledgerData['total_invoiced'] ?? 0.00;
        $total_credit = $ledgerData['total_credit'] ?? $ledgerData['total_received'] ?? 0.00;
        $closing_balance = $ledgerData['closing_balance'] ?? 0.00;
        $transactions = $ledgerData['transactions'] ?? collect();
        $entries = $ledgerData['entries'] ?? $transactions;
        $start_date = $startDate;
        $end_date = $endDate;
        $plant_id = $plantId;

        $pdfContent = $this->pdfService->renderViewToPdf('pdf.client_ledger_pdf', compact(
            'client', 'selectedPlant', 'opening_balance', 'total_debit', 
            'total_credit', 'closing_balance', 'transactions', 'entries', 'start_date', 
            'end_date', 'period', 'plant_id'
        ));

        $plantSegment = $selectedPlant ? "-" . $selectedPlant->plant_name : "";
        $fileName = "Ledger-Statement-{$client->company_name}{$plantSegment}-{$startDate}-to-{$endDate}.pdf";
        $fileName = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', $fileName);

        return response()->streamDownload(
            fn () => print($pdfContent),
            $fileName,
            ['Content-Type' => 'application/pdf']
        );
    }
}
