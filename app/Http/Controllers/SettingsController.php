<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use App\Services\BackupService;
use App\Services\CategoryService;
use App\Services\RolePermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class SettingsController extends Controller
{
    /**
     * Display Unified System Settings Hub page.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['super_admin', 'admin'])) {
            return redirect()->route('overview')->with('error', 'Access Denied: Apart from Admin and Super Admin, no one has access to System Settings.');
        }

        $users = User::orderByRaw("CASE WHEN role = 'super_admin' THEN 0 ELSE 1 END")
            ->orderBy('name', 'asc')
            ->get();
        $roles = RolePermissionService::getRoles();
        $permissionsList = RolePermissionService::getPermissionsList();

        // Fetch custom dynamic roles from database
        $customRolesList = \App\Models\Role::orderBy('name')->get();

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
            'module_reports' => Setting::get('module_reports', 'true') === 'true',
            'module_backups' => Setting::get('module_backups', 'true') === 'true',
            'module_activity_logs' => Setting::get('module_activity_logs', 'true') === 'true',
            'track_stock' => Setting::get('track_stock', 'true') === 'true',
            'track_payments' => Setting::get('track_payments', 'true') === 'true',
        ];

        // Fetch database backup files list
        $backups = [];
        try {
            $backups = app(BackupService::class)->listLocalBackups();
        } catch (Throwable $e) {
            Log::error("Failed to load backups list: " . $e->getMessage());
        }

        return view('pages.settings', compact('users', 'roles', 'customRolesList', 'permissionsList', 'modules', 'backups'));
    }

    /**
     * Helper response method for AJAX and Web requests.
     */
    private function respond(Request $request, bool $success, string $message, array $extra = [])
    {
        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json(array_merge([
                'success' => $success,
                'message' => $message,
            ], $extra), $success ? 200 : 422);
        }

        return back()->with($success ? 'success' : 'error', $message);
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
                'module_reports',
                'module_backups',
                'module_activity_logs',
                'track_stock',
                'track_payments',
            ];

            foreach ($moduleKeys as $key) {
                $isSet = $request->has($key) ? 'true' : 'false';
                Setting::updateOrCreate(['key' => $key], ['value' => $isSet]);
            }

            \App\Services\AuditLogService::log('Settings', 'updated', "Updated ERP module visibility & feature toggles matrix");

            return $this->respond($request, true, 'Active ERP module visibility updated successfully! Sidebar navigation updated.');
        } catch (Throwable $e) {
            Log::error("Failed to update module toggles: " . $e->getMessage());
            return $this->respond($request, false, 'Failed to update modules: ' . $e->getMessage());
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
            'role' => 'required|string',
            'status' => 'nullable|string|in:active,pending,inactive',
            'is_active' => 'nullable|boolean',
            'permissions' => 'nullable|array',
        ]);

        try {
            $roleKey = $validated['role'];
            $status = $validated['status'] ?? ($roleKey === 'pending' ? 'pending' : 'active');
            $isActive = $validated['is_active'] ?? ($status === 'active' && $roleKey !== 'pending');

            $permissions = $roleKey === 'custom'
                ? ($request->input('permissions') ?? [])
                : RolePermissionService::getDefaultPermissionsForRole($roleKey);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $roleKey,
                'status' => $status,
                'is_active' => $isActive,
                'permissions' => $permissions,
            ]);

            \App\Services\AuditLogService::log('Settings', 'created', "Created user account '{$user->name}' ({$user->email}, Role: {$user->role})");

            return $this->respond($request, true, "User account for '{$user->name}' created successfully!");
        } catch (Throwable $e) {
            Log::error("Failed to create user account: " . $e->getMessage());
            return $this->respond($request, false, 'Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Approve a Pending User Account & Assign Active Role.
     */
    public function approveUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'role' => 'required|string',
        ]);

        try {
            $user->role = $validated['role'];
            $user->status = 'active';
            $user->is_active = true;
            $user->permissions = RolePermissionService::getDefaultPermissionsForRole($validated['role']);
            $user->save();

            \App\Services\AuditLogService::log('Settings', 'updated', "Approved pending user account '{$user->name}' as " . ucfirst(str_replace('_', ' ', $user->role)));

            return $this->respond($request, true, "User account '{$user->name}' has been approved successfully as " . ucfirst(str_replace('_', ' ', $user->role)) . "!");
        } catch (Throwable $e) {
            Log::error("Failed to approve user: " . $e->getMessage());
            return $this->respond($request, false, 'Failed to approve user: ' . $e->getMessage());
        }
    }

    /**
     * Toggle Active / Inactive Status of a User Account.
     */
    public function toggleUserStatus(Request $request, $id)
    {
        if ((int) Auth::id() === (int) $id) {
            return $this->respond($request, false, 'You cannot deactivate your own logged-in user account!');
        }

        $user = User::findOrFail($id);

        if ($user->role === 'super_admin') {
            return $this->respond($request, false, 'Super Admin (Owner) accounts are protected and can never be deactivated!');
        }

        try {
            $newStatus = ($user->status === 'active' && $user->is_active) ? 'inactive' : 'active';
            $user->status = $newStatus;
            $user->is_active = ($newStatus === 'active');
            $user->save();

            \App\Services\AuditLogService::log('Settings', 'updated', "Toggled user account '{$user->name}' status to " . strtoupper($newStatus));

            return $this->respond($request, true, "User account '{$user->name}' is now " . strtoupper($newStatus) . ".");
        } catch (Throwable $e) {
            Log::error("Failed to toggle user status: " . $e->getMessage());
            return $this->respond($request, false, 'Failed to update user status: ' . $e->getMessage());
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
            'role' => 'required|string',
            'status' => 'nullable|in:active,pending,inactive',
            'permissions' => 'nullable|array',
        ]);

        try {
            $permissions = $validated['role'] === 'custom'
                ? ($request->input('permissions') ?? [])
                : RolePermissionService::getDefaultPermissionsForRole($validated['role']);

            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->role = $validated['role'];

            if ($user->role === 'super_admin') {
                $user->status = 'active';
                $user->is_active = true;
            } else if (!empty($validated['status'])) {
                $user->status = $validated['status'];
                $user->is_active = ($validated['status'] === 'active');
            }
            $user->permissions = $permissions;

            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();

            return $this->respond($request, true, "User account '{$user->name}' updated successfully!");
        } catch (Throwable $e) {
            Log::error("Failed to update user: " . $e->getMessage());
            return $this->respond($request, false, 'Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Create a new Dynamic Role.
     */
    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $slug = \Illuminate\Support\Str::slug($validated['name'], '_');
            \App\Models\Role::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? '',
                    'is_active' => true,
                ]
            );

            return $this->respond($request, true, "Role '{$validated['name']}' created successfully!");
        } catch (Throwable $e) {
            Log::error("Failed to create role: " . $e->getMessage());
            return $this->respond($request, false, 'Failed to create role: ' . $e->getMessage());
        }
    }

    /**
     * Real-time AJAX Toggle Single Role Permission.
     */
    public function toggleRolePermission(Request $request)
    {
        $validated = $request->validate([
            'role_slug' => 'required|string',
            'permission_key' => 'required|string',
            'enabled' => 'required|in:true,false,1,0',
        ]);

        try {
            $roleSlug = $validated['role_slug'];
            $permKey = $validated['permission_key'];
            $enabled = in_array($validated['enabled'], ['true', '1', 1, true], true);

            if ($enabled) {
                \App\Models\RolePermission::firstOrCreate([
                    'role_slug' => $roleSlug,
                    'permission_key' => $permKey,
                ]);
            } else {
                \App\Models\RolePermission::where('role_slug', $roleSlug)
                    ->where('permission_key', $permKey)
                    ->delete();
            }

            $permLabels = [
                'page_overview' => 'Overview',
                'page_orders' => 'Sales Orders',
                'page_invoices' => 'Invoices & Billing',
                'page_purchases' => 'Purchase Ledger',
                'page_expenses' => 'Expense Ledger',
                'page_rawmaterial' => 'Raw Materials',
                'page_product' => 'Finished Goods',
                'page_bom' => 'Bill of Materials',
                'page_production' => 'Production Logs',
                'page_clients' => 'Clients & Plants',
                'page_employees' => 'Employee Payroll',
                'page_reports' => 'Reports',
                'action_insert' => 'Create Data',
                'action_update' => 'Edit Data',
                'action_delete' => 'Delete Data',
            ];

            $roleName = ucwords(str_replace('_', ' ', $roleSlug));
            $permName = $permLabels[$permKey] ?? ucwords(str_replace(['page_', 'action_', '_'], ['', '', ' '], $permKey));
            $statusText = $enabled ? 'enabled' : 'disabled';

            return response()->json([
                'success' => true,
                'enabled' => $enabled,
                'message' => "'{$permName}' permission {$statusText} for {$roleName}!",
            ]);
        } catch (Throwable $e) {
            Log::error("Failed to toggle permission: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update permission: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a System or Custom Role (Except Super Admin).
     */
    public function deleteRole(Request $request, $id)
    {
        try {
            $role = \App\Models\Role::where('id', $id)->orWhere('slug', $id)->first();
            
            if (($role && $role->slug === 'super_admin') || $id === 'super_admin') {
                return $this->respond($request, false, 'Super Admin owner role cannot be deleted!');
            }

            $slug = $role ? $role->slug : $id;
            $name = $role ? $role->name : ucfirst(str_replace('_', ' ', $id));

            \App\Models\RolePermission::where('role_slug', $slug)->delete();
            if ($role) {
                $role->delete();
            }

            return $this->respond($request, true, "Role '{$name}' deleted successfully!");
        } catch (Throwable $e) {
            Log::error("Failed to delete role: " . $e->getMessage());
            return $this->respond($request, false, 'Failed to delete role: ' . $e->getMessage());
        }
    }

    /**
     * Toggle Active / Inactive Status of a System or Custom Role.
     */
    public function toggleRoleStatus(Request $request, $slug)
    {
        if ($slug === 'super_admin') {
            return $this->respond($request, false, 'Super Admin owner role status cannot be modified!');
        }

        try {
            $role = \App\Models\Role::where('slug', $slug)->first();
            if (!$role) {
                $rolesDict = RolePermissionService::getRoles();
                $name = $rolesDict[$slug]['name'] ?? ucfirst(str_replace('_', ' ', $slug));
                $role = \App\Models\Role::create([
                    'name' => $name,
                    'slug' => $slug,
                    'is_active' => true,
                ]);
            }

            $role->is_active = !$role->is_active;
            $role->save();

            $statusText = $role->is_active ? 'ACTIVATED' : 'DEACTIVATED';
            return $this->respond($request, true, "Role '{$role->name}' is now {$statusText}.");
        } catch (Throwable $e) {
            Log::error("Failed to toggle role status: " . $e->getMessage());
            return $this->respond($request, false, 'Failed to update role status: ' . $e->getMessage());
        }
    }

    /**
     * Save Role-Permissions Matrix Checkboxes.
     */
    public function saveRolePermissionsMatrix(Request $request)
    {
        $matrix = $request->input('matrix', []);

        try {
            foreach ($matrix as $roleSlug => $permissionKeys) {
                \App\Models\RolePermission::where('role_slug', $roleSlug)->delete();
                if (is_array($permissionKeys)) {
                    foreach ($permissionKeys as $permKey => $val) {
                        if ($val) {
                            \App\Models\RolePermission::create([
                                'role_slug' => $roleSlug,
                                'permission_key' => $permKey,
                            ]);
                        }
                    }
                }
            }

            return $this->respond($request, true, 'Role permissions matrix updated successfully!');
        } catch (Throwable $e) {
            Log::error("Failed to save permissions matrix: " . $e->getMessage());
            return $this->respond($request, false, 'Failed to save permissions matrix: ' . $e->getMessage());
        }
    }

    /**
     * Create a Navigation Module item.
     */
    public function storeModule(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'route_name' => 'nullable|string|max:255',
            'icon_class' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:modules,id',
            'permission_key' => 'nullable|string|max:255',
            'order_weight' => 'nullable|integer',
        ]);

        try {
            \App\Models\Module::create([
                'title' => $validated['title'],
                'route_name' => $validated['route_name'] ?? null,
                'icon_class' => $validated['icon_class'] ?? 'M4 6h16M4 12h16M4 18h16',
                'parent_id' => $validated['parent_id'] ?? null,
                'permission_key' => $validated['permission_key'] ?? null,
                'order_weight' => $validated['order_weight'] ?? 0,
                'is_active' => true,
            ]);

            return $this->respond($request, true, "Navigation module '{$validated['title']}' created successfully!");
        } catch (Throwable $e) {
            Log::error("Failed to create module: " . $e->getMessage());
            return $this->respond($request, false, 'Failed to create module: ' . $e->getMessage());
        }
    }

    /**
     * Delete a System User Account.
     */
    public function deleteUser($id)
    {
        $req = request();
        if ((int) Auth::id() === (int) $id) {
            return $this->respond($req, false, 'You cannot delete your own logged-in user account!');
        }

        try {
            $user = User::findOrFail($id);
            $userName = $user->name;
            $user->delete();

            return $this->respond($req, true, "User account '{$userName}' deleted successfully.");
        } catch (Throwable $e) {
            Log::error("Failed to delete user: " . $e->getMessage());
            return $this->respond($req, false, 'Failed to delete user: ' . $e->getMessage());
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

            \App\Services\AuditLogService::log('Settings', 'updated', "Updated business profile and company branding ('{$request->business_name}')");

            return $this->respond($request, true, 'Business profile & branding updated successfully!');
        } catch (Throwable $e) {
            Log::error("Failed to update business profile: " . $e->getMessage());
            return $this->respond($request, false, 'Failed to update profile: ' . $e->getMessage());
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
            'terms_and_conditions' => 'nullable|string',
        ]);

        try {
            Setting::updateOrCreate(['key' => 'bank_name'], ['value' => $request->bank_name ?? '']);
            Setting::updateOrCreate(['key' => 'bank_account_name'], ['value' => $request->bank_account_name ?? '']);
            Setting::updateOrCreate(['key' => 'bank_account_no'], ['value' => $request->bank_account_no ?? '']);
            Setting::updateOrCreate(['key' => 'bank_ifsc'], ['value' => strtoupper($request->bank_ifsc ?? '')]);
            Setting::updateOrCreate(['key' => 'terms_and_conditions'], ['value' => $request->terms_and_conditions ?? '']);

            \App\Services\AuditLogService::log('Settings', 'updated', "Updated bank details and billing terms & conditions");

            return $this->respond($request, true, 'Bank details & billing defaults updated successfully!');
        } catch (Throwable $e) {
            Log::error("Failed to update bank defaults: " . $e->getMessage());
            return $this->respond($request, false, 'Failed to update bank defaults: ' . $e->getMessage());
        }
    }

    /**
     * Update Auto-Increment Serial & Custom Prefix Settings.
     */
    public function updateSerialSettings(Request $request)
    {
        $request->validate([
            'invoice_prefix' => 'nullable|string|max:20',
            'order_prefix' => 'nullable|string|max:20',
            'invoice_next_sequence' => 'required|integer|min:1',
            'order_next_sequence' => 'required|integer|min:1',
            'serial_date_format' => 'required|string|in:Ymd,Ym,ym,FY,none',
            'serial_number_digits' => 'required|integer|in:1,3,4,5,6',
            'serial_reset_frequency' => 'required|string|in:financial_year,monthly,never',
        ]);

        try {
            Setting::set('invoice_prefix', strtoupper(trim($request->input('invoice_prefix') ?? '')));
            Setting::set('order_prefix', strtoupper(trim($request->input('order_prefix') ?? '')));
            Setting::set('invoice_next_sequence', (string) $request->invoice_next_sequence);
            Setting::set('order_next_sequence', (string) $request->order_next_sequence);
            Setting::set('serial_date_format', $request->serial_date_format);
            Setting::set('serial_number_digits', (string) $request->serial_number_digits);
            Setting::set('serial_reset_frequency', $request->serial_reset_frequency);

            \App\Services\AuditLogService::log('Settings', 'updated', "Updated invoice & sales order auto-increment serial settings");

            return $this->respond($request, true, 'Document prefix & auto-increment serial settings updated successfully!');
        } catch (Throwable $e) {
            Log::error("Failed to update serial settings: " . $e->getMessage());
            return $this->respond($request, false, 'Failed to update serial settings: ' . $e->getMessage());
        }
    }

    /**
     * Update Tax & Financial Configuration Settings.
     */
    public function updateFinancialSettings(Request $request)
    {
        $request->validate([
            'default_gst_rate' => 'required|numeric|in:0,5,12,18,28',
            'financial_year_start_month' => 'required|integer|min:1|max:12',
            'number_format_style' => 'required|string|in:indian,international',
        ]);

        try {
            Setting::set('default_gst_rate', (string) $request->default_gst_rate);
            Setting::set('financial_year_start_month', (string) $request->financial_year_start_month);
            Setting::set('number_format_style', $request->number_format_style);

            \App\Services\AuditLogService::log('Settings', 'updated', "Updated default GST rate ({$request->default_gst_rate}%) and financial year settings");

            return $this->respond($request, true, 'Financial & Tax configuration updated successfully!');
        } catch (Throwable $e) {
            Log::error("Failed to update financial settings: " . $e->getMessage());
            return $this->respond($request, false, 'Failed to update financial settings: ' . $e->getMessage());
        }
    }

    /**
     * Update Email / SMTP Configuration Settings.
     */
    public function updateEmailSettings(Request $request)
    {
        $request->validate([
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'required|string|in:tls,ssl,none',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
        ]);

        try {
            $fromAddress = trim($request->mail_from_address);
            $username = trim($request->mail_username ?? '');
            if (empty($username)) {
                $username = $fromAddress;
            }

            Setting::set('mail_host', trim($request->mail_host));
            Setting::set('mail_port', (string) $request->mail_port);
            Setting::set('mail_username', $username);
            if ($request->filled('mail_password')) {
                // Strip spaces if user pasted a Google App Password with spaces (e.g. "abcd efgh ijkl mnop")
                $cleanPassword = str_replace(' ', '', trim($request->mail_password));
                Setting::set('mail_password', $cleanPassword);
            }
            Setting::set('mail_encryption', $request->mail_encryption);
            Setting::set('mail_from_address', $fromAddress);
            Setting::set('mail_from_name', trim($request->mail_from_name));

            \App\Services\AuditLogService::log('Settings', 'updated', "Updated SMTP email delivery settings ('{$fromAddress}')");

            return $this->respond($request, true, 'Email (SMTP) delivery settings saved successfully!');
        } catch (Throwable $e) {
            Log::error("Failed to update email settings: " . $e->getMessage());
            return $this->respond($request, false, 'Failed to update email settings: ' . $e->getMessage());
        }
    }

    /**
     * Send Test Delivery Email to verify SMTP configuration.
     */
    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            $to = $request->test_email;
            $fromAddr = Setting::get('mail_from_address', 'vekariyah@gmail.com');
            $fromName = Setting::get('mail_from_name', 'Praful Welding Works');
            $mailUsername = Setting::get('mail_username');
            if (empty($mailUsername)) {
                $mailUsername = $fromAddr;
            }

            $mailPassword = Setting::get('mail_password', '');
            if (empty($mailPassword)) {
                return $this->respond($request, false, 'SMTP Authentication Error: Email App Password is missing! Please enter your 16-character Google App Password in settings and click "Save Email Settings" first.');
            }

            // Set dynamic config for immediate delivery
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => Setting::get('mail_host', 'smtp.gmail.com'),
                'mail.mailers.smtp.port' => (int) Setting::get('mail_port', 587),
                'mail.mailers.smtp.username' => $mailUsername,
                'mail.mailers.smtp.password' => $mailPassword,
                'mail.mailers.smtp.encryption' => Setting::get('mail_encryption', 'tls'),
                'mail.from.address' => $fromAddr,
                'mail.from.name' => $fromName,
            ]);

            Mail::raw("Hello!\n\nThis is a test delivery email from your Praful Welding Works ERP system settings hub. If you received this email, your SMTP configuration is working correctly!\n\nSent at: " . date('Y-m-d H:i:s'), function ($message) use ($to, $fromAddr, $fromName) {
                $message->to($to)
                    ->from($fromAddr, $fromName)
                    ->subject("SMTP Diagnostic Test Email - Praful Welding Works ERP");
            });

            return $this->respond($request, true, "Test email sent successfully to '{$to}'! Please check your inbox.");
        } catch (Throwable $e) {
            $msg = $e->getMessage();
            Log::error("Failed to send test email: " . $msg);

            if (str_contains($msg, 'BadCredentials') || str_contains($msg, '535')) {
                return $this->respond($request, false, 'Google Password Rejected: Please enter your 16-character Google App Password (not your personal Gmail password) and click "Save Email Settings" first.');
            }

            if (str_contains($msg, '530') || str_contains($msg, 'Authentication Required')) {
                return $this->respond($request, false, 'Authentication Required: Please enter your 16-character Google App Password and click "Save Email Settings" before sending a test email.');
            }

            return $this->respond($request, false, "Failed to send email: " . (strlen($msg) > 120 ? substr($msg, 0, 120) . '...' : $msg));
        }
    }

    /**
     * Update Security & System Backup Settings.
     */
    public function updateSecuritySettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_timeout_minutes' => 'required|integer|min:15|max:1440',
            'auto_backup_enabled' => 'nullable|string|in:true,false',
            'auto_backup_frequency' => 'required|string|in:daily,weekly,monthly',
            'auto_backup_retention' => 'required|string|in:1_month,3_months,6_months,1_year,never',
            'auto_backup_time' => 'required|string',
            'auto_backup_day' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->respond($request, false, $validator->errors()->first());
        }

        try {
            Setting::set('session_timeout_minutes', (string) $request->session_timeout_minutes);
            Setting::set('auto_backup_enabled', $request->has('auto_backup_enabled') ? 'true' : 'false');
            Setting::set('auto_backup_frequency', $request->auto_backup_frequency);
            Setting::set('auto_backup_retention', $request->auto_backup_retention);
            Setting::set('auto_backup_time', $request->auto_backup_time);
            Setting::set('auto_backup_day', $request->auto_backup_day);

            // Run catch-up cleanup if retention rule changed
            app(BackupService::class)->cleanOldBackups();

            \App\Services\AuditLogService::log('Settings', 'updated', "Updated security policies (Session Inactivity Timeout: {$request->session_timeout_minutes} mins, Auto Backup: {$request->auto_backup_frequency}, Retention: {$request->auto_backup_retention})");

            return $this->respond($request, true, 'Security & backup schedule preferences saved successfully!');
        } catch (Throwable $e) {
            Log::error("Failed to update security settings: " . $e->getMessage());
            return $this->respond($request, false, 'Failed to update security settings: ' . $e->getMessage());
        }
    }

    /**
     * Add Purchase / Expense Category.
     */
    public function storeCategory(Request $request)
    {
        $type = $request->input('type', 'purchase'); // purchase or expense
        $label = trim($request->input('label'));

        if (empty($label)) {
            return $this->respond($request, false, 'Category label cannot be empty!');
        }

        $key = Str::slug($label, '_');

        if ($type === 'purchase') {
            $categories = CategoryService::getPurchaseCategories();
            foreach ($categories as $cat) {
                if (strtolower($cat['label']) === strtolower($label) || $cat['key'] === $key) {
                    return $this->respond($request, false, "Purchase category '{$label}' already exists!");
                }
            }
            $categories[] = ['key' => $key, 'label' => $label, 'protected' => false];
            CategoryService::savePurchaseCategories($categories);
            \App\Services\AuditLogService::log('Settings', 'created', "Created new purchase category '{$label}'");
            return $this->respond($request, true, "Purchase category '{$label}' created successfully!");
        } else {
            $categories = CategoryService::getExpenseCategories();
            foreach ($categories as $cat) {
                if (strtolower($cat['label']) === strtolower($label) || $cat['key'] === $key) {
                    return $this->respond($request, false, "Expense category '{$label}' already exists!");
                }
            }
            $categories[] = ['key' => $key, 'label' => $label, 'protected' => false];
            CategoryService::saveExpenseCategories($categories);
            \App\Services\AuditLogService::log('Settings', 'created', "Created new expense category '{$label}'");
            return $this->respond($request, true, "Expense category '{$label}' created successfully!");
        }
    }

    /**
     * Update Purchase / Expense Category Label.
     */
    public function updateCategory(Request $request)
    {
        $type = $request->input('type', 'purchase');
        $key = $request->input('key');
        $newLabel = trim($request->input('label'));

        if (empty($newLabel)) {
            return $this->respond($request, false, 'Category label cannot be empty!');
        }

        if ($type === 'purchase') {
            $categories = CategoryService::getPurchaseCategories();
            $updated = false;
            foreach ($categories as &$cat) {
                if ($cat['key'] === $key) {
                    $cat['label'] = $newLabel;
                    $updated = true;
                    break;
                }
            }
            if ($updated) {
                CategoryService::savePurchaseCategories($categories);
                \App\Services\AuditLogService::log('Settings', 'updated', "Updated purchase category key '{$key}' label to '{$newLabel}'");
                return $this->respond($request, true, 'Purchase category updated successfully!');
            }
        } else {
            $categories = CategoryService::getExpenseCategories();
            $updated = false;
            foreach ($categories as &$cat) {
                if ($cat['key'] === $key) {
                    $cat['label'] = $newLabel;
                    $updated = true;
                    break;
                }
            }
            if ($updated) {
                CategoryService::saveExpenseCategories($categories);
                \App\Services\AuditLogService::log('Settings', 'updated', "Updated expense category key '{$key}' label to '{$newLabel}'");
                return $this->respond($request, true, 'Expense category updated successfully!');
            }
        }

        return $this->respond($request, false, 'Category not found.');
    }

    /**
     * Delete Purchase / Expense Category (Enforces System Protection Rules).
     */
    public function deleteCategory(Request $request)
    {
        $type = $request->input('type', 'purchase');
        $key = $request->input('key');

        // MANDATORY SYSTEM CATEGORY PROTECTION RULES
        if ($type === 'purchase' && $key === 'raw_material') {
            return $this->respond($request, false, "Cannot delete 'Raw Material Purchase' category! It is a mandatory system category required for automatic inventory restock.");
        }

        if ($type === 'expense' && ($key === 'salary' || $key === 'gst_payment')) {
            return $this->respond($request, false, "Cannot delete 'Salary' or 'GST Payment' categories! They are mandatory system categories required for payroll and tax ledgers.");
        }

        if ($type === 'purchase') {
            $categories = CategoryService::getPurchaseCategories();
            $filtered = array_values(array_filter($categories, fn($cat) => $cat['key'] !== $key));
            CategoryService::savePurchaseCategories($filtered);
            \App\Services\AuditLogService::log('Settings', 'deleted', "Deleted purchase category key '{$key}'");
            return $this->respond($request, true, 'Purchase category deleted successfully!');
        } else {
            $categories = CategoryService::getExpenseCategories();
            $filtered = array_values(array_filter($categories, fn($cat) => $cat['key'] !== $key));
            CategoryService::saveExpenseCategories($filtered);
            \App\Services\AuditLogService::log('Settings', 'deleted', "Deleted expense category key '{$key}'");
            return $this->respond($request, true, 'Expense category deleted successfully!');
        }
    }

    /**
     * Trigger instant manual database SQL backup creation.
     */
    public function triggerManualBackup()
    {
        $req = request();
        try {
            $backupService = app(BackupService::class);
            $sqlContent = $backupService->generateFullSqlDump();
            $filename = "manual_backup_" . date('Ymd_His') . ".sql";
            $filePath = $backupService->getBackupDirectory() . DIRECTORY_SEPARATOR . $filename;
            
            \Illuminate\Support\Facades\File::put($filePath, $sqlContent);

            return $this->respond($req, true, "Manual database backup created successfully! Saved as '{$filename}'.");
        } catch (Throwable $e) {
            Log::error("Manual backup failed: " . $e->getMessage());
            return $this->respond($req, false, 'Failed to generate backup: ' . $e->getMessage());
        }
    }

    /**
     * Download a stored database SQL backup file.
     */
    public function downloadBackup($filename)
    {
        $req = request();
        try {
            $backupService = app(BackupService::class);
            $filePath = $backupService->getBackupDirectory() . DIRECTORY_SEPARATOR . basename($filename);

            if (!\Illuminate\Support\Facades\File::exists($filePath)) {
                return $this->respond($req, false, 'Requested backup file does not exist on server.');
            }

            return response()->download($filePath, basename($filename));
        } catch (Throwable $e) {
            Log::error("Backup download failed: " . $e->getMessage());
            return $this->respond($req, false, 'Failed to download backup: ' . $e->getMessage());
        }
    }

    /**
     * Restore database from an uploaded or existing SQL backup file.
     */
    public function restoreBackup(Request $request)
    {
        $request->validate([
            'backup_file' => 'nullable|file|mimes:sql,txt|max:51200',
            'filename' => 'nullable|string',
        ]);

        try {
            $backupService = app(BackupService::class);
            $targetPath = null;

            if ($request->hasFile('backup_file')) {
                $targetPath = $request->file('backup_file')->getRealPath();
            } elseif ($request->filled('filename')) {
                $targetPath = $backupService->getBackupDirectory() . DIRECTORY_SEPARATOR . basename($request->filename);
            }

            if (!$targetPath || !\Illuminate\Support\Facades\File::exists($targetPath)) {
                return $this->respond($request, false, 'Please select or upload a valid SQL backup file to restore.');
            }

            $backupService->restoreFromSqlFile($targetPath);

            return $this->respond($request, true, 'Database restored successfully! A safety snapshot was automatically recorded before restoring.');
        } catch (Throwable $e) {
            Log::error("Restore failed: " . $e->getMessage());
            return $this->respond($request, false, 'Failed to restore database: ' . $e->getMessage());
        }
    }
}
