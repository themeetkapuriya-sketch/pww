<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use App\Services\RolePermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

class SettingsController extends Controller
{
    /**
     * Display Unified System Settings Hub page.
     */
    public function index()
    {
        $users = User::orderBy('name')->get();
        $roles = RolePermissionService::getRoles();
        $permissionsList = RolePermissionService::getPermissionsList();

        // Get active module states
        $modules = [
            'module_invoices' => Setting::get('module_invoices', 'true') === 'true',
            'module_orders' => Setting::get('module_orders', 'true') === 'true',
            'module_purchases' => Setting::get('module_purchases', 'true') === 'true',
            'module_clients' => Setting::get('module_clients', 'true') === 'true',
            'module_production' => Setting::get('module_production', 'true') === 'true',
            'module_bom' => Setting::get('module_bom', 'true') === 'true',
            'module_inventory' => Setting::get('module_inventory', 'true') === 'true',
            'module_payroll' => Setting::get('module_payroll', 'true') === 'true',
            'module_expenses' => Setting::get('module_expenses', 'true') === 'true',
        ];

        return view('pages.settings', compact('users', 'roles', 'permissionsList', 'modules'));
    }

    /**
     * Update active ERP module toggles.
     */
    public function updateModuleToggles(Request $request)
    {
        try {
            $moduleKeys = [
                'module_invoices',
                'module_orders',
                'module_purchases',
                'module_clients',
                'module_production',
                'module_bom',
                'module_inventory',
                'module_payroll',
                'module_expenses',
            ];

            foreach ($moduleKeys as $key) {
                $isSet = $request->has($key) ? 'true' : 'false';
                Setting::updateOrCreate(['key' => $key], ['value' => $isSet]);
            }

            return back()->with('success', 'Active ERP module visibility updated successfully! The sidebar navigation has updated.');
        } catch (Throwable $e) {
            Log::error("Failed to update module toggles: " . $e->getMessage());
            return back()->with('error', 'Failed to update modules: ' . $e->getMessage());
        }
    }

    /**
     * Create a new System User Account.
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:super_admin,admin,accountant,production_manager,view_only,custom,staff',
            'permissions' => 'nullable|array',
        ]);

        try {
            $permissions = $validated['role'] === 'custom'
                ? ($request->input('permissions') ?? [])
                : RolePermissionService::getDefaultPermissionsForRole($validated['role']);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'status' => 'active',
                'permissions' => $permissions,
            ]);

            return back()->with('success', "User account for '{$user->name}' ({$user->email}) created successfully!");
        } catch (Throwable $e) {
            Log::error("Failed to create user account: " . $e->getMessage());
            return back()->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing User Account & Permission Matrix.
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$id}",
            'password' => 'nullable|string|min:6',
            'role' => 'required|string|in:super_admin,admin,accountant,production_manager,view_only,custom,staff',
            'status' => 'required|in:active,inactive',
            'permissions' => 'nullable|array',
        ]);

        try {
            $permissions = $validated['role'] === 'custom'
                ? ($request->input('permissions') ?? [])
                : RolePermissionService::getDefaultPermissionsForRole($validated['role']);

            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->role = $validated['role'];
            $user->status = $validated['status'];
            $user->permissions = $permissions;

            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();

            return back()->with('success', "User account '{$user->name}' updated successfully!");
        } catch (Throwable $e) {
            Log::error("Failed to update user: " . $e->getMessage());
            return back()->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Delete a System User Account.
     */
    public function deleteUser($id)
    {
        if ((int) Auth::id() === (int) $id) {
            return back()->with('error', 'You cannot delete your own logged-in user account!');
        }

        try {
            $user = User::findOrFail($id);
            $userName = $user->name;
            $user->delete();

            return back()->with('success', "User account '{$userName}' deleted successfully.");
        } catch (Throwable $e) {
            Log::error("Failed to delete user: " . $e->getMessage());
            return back()->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }

    /**
     * Update Business Profile & Branding.
     */
    public function updateBusinessProfile(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'business_subtitle' => 'nullable|string|max:255',
            'business_email' => 'required|email|max:255',
            'business_mobile' => 'nullable|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'gstin' => 'nullable|string|max:255',
            'msme_number' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'signature' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        try {
            Setting::updateOrCreate(['key' => 'business_name'], ['value' => $request->business_name]);
            Setting::updateOrCreate(['key' => 'business_subtitle'], ['value' => $request->business_subtitle ?? '']);
            Setting::updateOrCreate(['key' => 'business_email'], ['value' => $request->business_email]);
            Setting::updateOrCreate(['key' => 'business_mobile'], ['value' => $request->business_mobile ?? '']);
            Setting::updateOrCreate(['key' => 'address_line_1'], ['value' => $request->address_line_1]);
            Setting::updateOrCreate(['key' => 'address'], ['value' => $request->address_line_1]);
            Setting::updateOrCreate(['key' => 'gstin'], ['value' => strtoupper($request->gstin ?? '')]);
            Setting::updateOrCreate(['key' => 'msme_number'], ['value' => strtoupper($request->msme_number ?? '')]);

            if ($request->hasFile('logo')) {
                $filename = 'logo_' . time() . '.' . $request->file('logo')->getClientOriginalExtension();
                $request->file('logo')->move(public_path('uploads'), $filename);
                Setting::updateOrCreate(['key' => 'logo_path'], ['value' => 'uploads/' . $filename]);
            }

            if ($request->hasFile('signature')) {
                $filename = 'signature_' . time() . '.' . $request->file('signature')->getClientOriginalExtension();
                $request->file('signature')->move(public_path('uploads'), $filename);
                Setting::updateOrCreate(['key' => 'signature_path'], ['value' => 'uploads/' . $filename]);
            }

            return back()->with('success', 'Business profile & branding updated successfully!');
        } catch (Throwable $e) {
            Log::error("Failed to update business profile: " . $e->getMessage());
            return back()->with('error', 'Failed to update profile: ' . $e->getMessage());
        }
    }

    /**
     * Update Bank & Billing Defaults.
     */
    public function updateBankDefaults(Request $request)
    {
        $request->validate([
            'bank_name' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_no' => 'nullable|string|max:255',
            'bank_ifsc' => 'nullable|string|max:255',
            'invoice_prefix' => 'nullable|string|max:20',
            'order_prefix' => 'nullable|string|max:20',
            'terms_and_conditions' => 'nullable|string',
        ]);

        try {
            Setting::updateOrCreate(['key' => 'bank_name'], ['value' => $request->bank_name ?? '']);
            Setting::updateOrCreate(['key' => 'bank_account_name'], ['value' => $request->bank_account_name ?? '']);
            Setting::updateOrCreate(['key' => 'bank_account_no'], ['value' => $request->bank_account_no ?? '']);
            Setting::updateOrCreate(['key' => 'bank_ifsc'], ['value' => strtoupper($request->bank_ifsc ?? '')]);
            Setting::updateOrCreate(['key' => 'invoice_prefix'], ['value' => $request->invoice_prefix ?? 'PWW-']);
            Setting::updateOrCreate(['key' => 'order_prefix'], ['value' => $request->order_prefix ?? 'PWW-ORD-']);
            Setting::updateOrCreate(['key' => 'terms_and_conditions'], ['value' => $request->terms_and_conditions ?? '']);

            return back()->with('success', 'Bank details & billing defaults updated successfully!');
        } catch (Throwable $e) {
            Log::error("Failed to update bank defaults: " . $e->getMessage());
            return back()->with('error', 'Failed to update bank defaults: ' . $e->getMessage());
        }
    }
}
