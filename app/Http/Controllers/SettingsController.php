<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Module;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\Setting;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\BackupService;
use App\Services\CategoryService;
use App\Services\RolePermissionService;
use Carbon\Carbon;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class SettingsController extends Controller
{
    /**
     * Check if current user has administrative permissions.
     */
    private function authorizeAdmin(?Request $request = null)
    {
        $user = Auth::user();
        $userRole = strtolower(trim($user->role ?? ''));
        if (! $user || (! RolePermissionService::userHasPermission($user, 'backups_settings_manage') && ! in_array($userRole, ['super_admin', 'admin', 'administrator', 'owner', 'master']))) {
            $req = $request ?: request();
            if ($req->expectsJson() || $req->ajax() || $req->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access Denied: Apart from Admin and Super Admin, no one has access to System Settings.',
                ], 403);
            }

            return redirect()->route('overview')->with('error', 'Access Denied: Apart from Admin and Super Admin, no one has access to System Settings.');
        }

        return null;
    }

    /**
     * Display Unified System Settings Hub page.
     */
    public function index()
    {
        if ($res = $this->authorizeAdmin()) {
            return $res;
        }

        $rawUsers = User::orderByRaw("CASE WHEN role = 'super_admin' THEN 0 ELSE 1 END")
            ->orderBy('name', 'asc')
            ->get();
        $users = UserResource::collection($rawUsers);
        $roles = RolePermissionService::getRoles();
        $permissionsList = RolePermissionService::getPermissionsList();

        // Fetch custom dynamic roles from database
        $customRolesList = Role::orderBy('name')->get();

        // Get active module states
        $modules = [
            'simplified_billing_mode' => Setting::get('simplified_billing_mode', 'false') === 'true',
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
            'module_global_search' => Setting::get('module_global_search', 'true') === 'true',
            'track_stock' => Setting::get('track_stock', 'true') === 'true',
            'track_payments' => Setting::get('track_payments', 'true') === 'true',
        ];

        // Fetch database backup files list
        $backups = [];
        try {
            $backups = app(BackupService::class)->listLocalBackups();
        } catch (Throwable $e) {
            Log::error('Failed to load backups list: '.$e->getMessage());
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
                'simplified_billing_mode',
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
                'module_global_search',
                'track_stock',
                'track_payments',
            ];

            $oldSimplified = Setting::get('simplified_billing_mode', 'false') === 'true';
            $isSimplified = in_array(strtolower((string) $request->input('simplified_billing_mode')), ['true', '1', 'yes', 'on'], true);

            Setting::updateOrCreate(['key' => 'simplified_billing_mode'], ['value' => $isSimplified ? 'true' : 'false']);

            if ($isSimplified) {
                Setting::updateOrCreate(['key' => 'track_stock'], ['value' => 'false']);
                Setting::updateOrCreate(['key' => 'module_orders'], ['value' => 'false']);
                Setting::updateOrCreate(['key' => 'module_production'], ['value' => 'false']);
                Setting::updateOrCreate(['key' => 'module_bom'], ['value' => 'false']);
                Setting::updateOrCreate(['key' => 'module_inventory'], ['value' => 'false']);
                Setting::updateOrCreate(['key' => 'module_payroll'], ['value' => 'false']);
                Setting::updateOrCreate(['key' => 'module_invoices'], ['value' => 'true']);
                Setting::updateOrCreate(['key' => 'module_purchases'], ['value' => 'true']);
                Setting::updateOrCreate(['key' => 'module_expenses'], ['value' => 'true']);
                Setting::updateOrCreate(['key' => 'module_clients'], ['value' => 'true']);
                Setting::updateOrCreate(['key' => 'module_reports'], ['value' => 'true']);
            } elseif ($oldSimplified && ! $isSimplified) {
                foreach ($moduleKeys as $key) {
                    if ($key !== 'simplified_billing_mode') {
                        Setting::updateOrCreate(['key' => $key], ['value' => 'true']);
                    }
                }
            } else {
                // Check if this is a full form post or a single-toggle AJAX update
                $submittedModuleKeys = array_intersect(array_keys($request->all()), $moduleKeys);
                $isFullForm = count($submittedModuleKeys) > 1 || $request->has('_full_modules_form');

                if ($isFullForm) {
                    foreach ($moduleKeys as $key) {
                        $val = $request->input($key);
                        $isSet = ($val !== null)
                            ? (in_array(strtolower((string) $val), ['true', '1', 'yes', 'on'], true) ? 'true' : 'false')
                            : ($request->has($key) ? 'true' : 'false');
                        Setting::updateOrCreate(['key' => $key], ['value' => $isSet]);
                    }
                } else {
                    // Single-key AJAX toggle: only update the specific module that was submitted
                    foreach ($moduleKeys as $key) {
                        if ($request->has($key)) {
                            $val = $request->input($key);
                            $isSet = in_array(strtolower((string) $val), ['true', '1', 'yes', 'on'], true) ? 'true' : 'false';
                            Setting::updateOrCreate(['key' => $key], ['value' => $isSet]);
                        }
                    }
                }
            }

            AuditLogService::log('Settings', 'updated', 'Updated ERP module visibility & feature toggles matrix');

            $modulesState = [];
            foreach ($moduleKeys as $key) {
                $default = in_array($key, ['simplified_billing_mode'], true) ? 'false' : 'true';
                $modulesState[$key] = Setting::get($key, $default) === 'true';
            }

            return $this->respond($request, true, 'Active ERP module visibility updated successfully! Sidebar navigation updated.', [
                'modules' => $modulesState,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to update module toggles: '.$e->getMessage());

            return $this->respond($request, false, 'Failed to update modules. Please try again.');
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
            $currentUser = auth()->user();

            if ($roleKey === 'super_admin' && $currentUser?->role !== 'super_admin') {
                return $this->respond($request, false, 'Unauthorized: Only Super Admin can create Super Admin accounts.');
            }

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

            AuditLogService::log('Settings', 'created', "Created user account '{$user->name}' ({$user->email}, Role: {$user->role})");

            return $this->respond($request, true, "User account for '{$user->name}' created successfully!");
        } catch (Throwable $e) {
            Log::error('Failed to create user account: '.$e->getMessage());

            return $this->respond($request, false, 'Failed to create user account. Please try again.');
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

            AuditLogService::log('Settings', 'updated', "Approved pending user account '{$user->name}' as ".ucfirst(str_replace('_', ' ', $user->role)));

            return $this->respond($request, true, "User account '{$user->name}' has been approved successfully as ".ucfirst(str_replace('_', ' ', $user->role)).'!');
        } catch (Throwable $e) {
            Log::error('Failed to approve user: '.$e->getMessage());

            return $this->respond($request, false, 'Failed to approve user. Please try again.');
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

            AuditLogService::log('Settings', 'updated', "Toggled user account '{$user->name}' status to ".strtoupper($newStatus));

            return $this->respond($request, true, "User account '{$user->name}' is now ".strtoupper($newStatus).'.', [
                'is_active' => (bool) $user->is_active,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to toggle user status: '.$e->getMessage());

            return $this->respond($request, false, 'Failed to update user status. Please try again.');
        }
    }

    /**
     * Update an existing User Account & Permission Matrix.
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $currentUser = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$id}",
            'password' => 'nullable|string|min:6',
            'role' => 'required|string',
            'status' => 'nullable|in:active,pending,inactive',
            'permissions' => 'nullable|array',
        ]);

        // Prevent non-super-admins from modifying an existing Super Admin account
        if ($user->role === 'super_admin' && $currentUser?->role !== 'super_admin') {
            return $this->respond($request, false, 'Unauthorized: Only a Super Admin can modify a Super Admin account.');
        }

        // Prevent non-super-admins from promoting anyone to Super Admin
        if ($validated['role'] === 'super_admin' && $currentUser?->role !== 'super_admin') {
            return $this->respond($request, false, 'Unauthorized: Only Super Admin can promote users to Super Admin.');
        }

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
            } elseif (! empty($validated['status'])) {
                $user->status = $validated['status'];
                $user->is_active = ($validated['status'] === 'active');
            }
            $user->permissions = $permissions;

            if (! empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();

            return $this->respond($request, true, "User account '{$user->name}' updated successfully!");
        } catch (Throwable $e) {
            Log::error('Failed to update user: '.$e->getMessage());

            return $this->respond($request, false, 'Failed to update user. Please try again.');
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
            $slug = Str::slug($validated['name'], '_');
            Role::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? '',
                    'is_active' => true,
                ]
            );

            return $this->respond($request, true, "Role '{$validated['name']}' created successfully!");
        } catch (Throwable $e) {
            Log::error('Failed to create role: '.$e->getMessage());

            return $this->respond($request, false, 'Failed to create role. Please try again.');
        }
    }

    /**
     * Real-time AJAX Toggle Single Role Permission.
     */
    public function toggleRolePermission(Request $request)
    {
        if (! $request->has('enabled') && $request->has('is_enabled')) {
            $request->merge(['enabled' => $request->input('is_enabled')]);
        }

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
                RolePermission::firstOrCreate([
                    'role_slug' => $roleSlug,
                    'permission_key' => $permKey,
                ]);
            } else {
                RolePermission::where('role_slug', $roleSlug)
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
            Log::error('Failed to toggle permission: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update permission. Please try again.',
            ], 500);
        }
    }

    /**
     * Delete a System or Custom Role (Except Super Admin).
     */
    public function deleteRole(Request $request, $id)
    {
        try {
            /** @var Role|null $role */
            $role = Role::where('id', $id)->orWhere('slug', $id)->first();

            if (($role && $role->slug === 'super_admin') || $id === 'super_admin') {
                return $this->respond($request, false, 'Super Admin owner role cannot be deleted!');
            }

            $slug = $role ? $role->slug : $id;
            $name = $role ? $role->name : ucfirst(str_replace('_', ' ', $id));

            RolePermission::where('role_slug', $slug)->delete();
            if ($role) {
                $role->delete();
            }

            return $this->respond($request, true, "Role '{$name}' deleted successfully!");
        } catch (Throwable $e) {
            Log::error('Failed to delete role: '.$e->getMessage());

            return $this->respond($request, false, 'Failed to delete role. Please try again.');
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
            /** @var Role|null $role */
            $role = Role::where('slug', $slug)->first();
            if (! $role) {
                $rolesDict = RolePermissionService::getRoles();
                $name = $rolesDict[$slug]['name'] ?? ucfirst(str_replace('_', ' ', $slug));
                $role = Role::create([
                    'name' => $name,
                    'slug' => $slug,
                    'is_active' => true,
                ]);
            }

            $role->is_active = ! $role->is_active;
            $role->save();

            $statusText = $role->is_active ? 'ACTIVATED' : 'DEACTIVATED';

            return $this->respond($request, true, "Role '{$role->name}' is now {$statusText}.", [
                'is_active' => (bool) $role->is_active,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to toggle role status: '.$e->getMessage());

            return $this->respond($request, false, 'Failed to update role status. Please try again.');
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
                RolePermission::where('role_slug', $roleSlug)->delete();
                if (is_array($permissionKeys)) {
                    foreach ($permissionKeys as $permKey => $val) {
                        if ($val) {
                            RolePermission::create([
                                'role_slug' => $roleSlug,
                                'permission_key' => $permKey,
                            ]);
                        }
                    }
                }
            }

            return $this->respond($request, true, 'Role permissions matrix updated successfully!');
        } catch (Throwable $e) {
            Log::error('Failed to save permissions matrix: '.$e->getMessage());

            return $this->respond($request, false, 'Failed to save permissions matrix. Please try again.');
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
            Module::create([
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
            Log::error('Failed to create module: '.$e->getMessage());

            return $this->respond($request, false, 'Failed to create module. Please try again.');
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
            Log::error('Failed to delete user: '.$e->getMessage());

            return $this->respond($req, false, 'Failed to delete user. Please try again.');
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
            'business_mobile' => 'required|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'required|string|max:255',
            'pincode' => 'nullable|string|max:20',
            'gstin' => 'required|string|max:255',
            'msme_number' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'signature' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ], [
            'logo.max' => 'The logo field must not be greater than 2 MB.',
            'logo.image' => 'The logo field must be a valid image file (JPEG, PNG, JPG, SVG).',
            'signature.max' => 'The signature field must not be greater than 2 MB.',
            'signature.image' => 'The signature field must be a valid image file (JPEG, PNG, JPG, SVG).',
        ]);

        try {
            Setting::updateOrCreate(['key' => 'business_name'], ['value' => $request->business_name]);
            Setting::updateOrCreate(['key' => 'business_subtitle'], ['value' => $request->business_subtitle ?? '']);
            Setting::updateOrCreate(['key' => 'business_email'], ['value' => $request->business_email]);
            Setting::updateOrCreate(['key' => 'business_mobile'], ['value' => $request->business_mobile ?? '']);
            Setting::updateOrCreate(['key' => 'address_line_1'], ['value' => $request->address_line_1]);
            Setting::updateOrCreate(['key' => 'address'], ['value' => $request->address_line_1]);
            if ($request->has('city')) {
                Setting::updateOrCreate(['key' => 'city'], ['value' => $request->city ?? 'Rajkot']);
            }
            if ($request->has('state')) {
                Setting::updateOrCreate(['key' => 'state'], ['value' => $request->state ?? 'Gujarat (24)']);
            }
            if ($request->has('pincode')) {
                Setting::updateOrCreate(['key' => 'pincode'], ['value' => $request->pincode ?? '360003']);
            }
            Setting::updateOrCreate(['key' => 'gstin'], ['value' => strtoupper($request->gstin ?? '')]);
            Setting::updateOrCreate(['key' => 'msme_number'], ['value' => strtoupper($request->msme_number ?? '')]);

            if ($request->boolean('remove_logo')) {
                Setting::updateOrCreate(['key' => 'logo_path'], ['value' => 'none']);
            } elseif ($request->hasFile('logo')) {
                File::ensureDirectoryExists(public_path('uploads'));
                $filename = 'logo_'.time().'.'.$request->file('logo')->getClientOriginalExtension();
                $request->file('logo')->move(public_path('uploads'), $filename);
                Setting::updateOrCreate(['key' => 'logo_path'], ['value' => 'uploads/'.$filename]);
            }

            if ($request->boolean('remove_signature')) {
                Setting::updateOrCreate(['key' => 'signature_path'], ['value' => 'none']);
            } elseif ($request->hasFile('signature')) {
                File::ensureDirectoryExists(public_path('uploads'));
                $filename = 'signature_'.time().'.'.$request->file('signature')->getClientOriginalExtension();
                $request->file('signature')->move(public_path('uploads'), $filename);
                Setting::updateOrCreate(['key' => 'signature_path'], ['value' => 'uploads/'.$filename]);
            }

            AuditLogService::log('Settings', 'updated', "Updated business profile and company branding ('{$request->business_name}')");

            $currentLogo = Setting::get('logo_path', 'logo.jpg');
            $hasActiveLogo = $currentLogo && $currentLogo !== 'none' && file_exists(public_path($currentLogo));
            $currentSig = Setting::get('signature_path');
            $hasActiveSig = $currentSig && $currentSig !== 'none' && file_exists(public_path($currentSig));

            return $this->respond($request, true, 'Business profile & branding updated successfully!', [
                'data' => [
                    'logo_url' => $hasActiveLogo ? asset($currentLogo) : null,
                    'has_logo' => $hasActiveLogo,
                    'signature_url' => $hasActiveSig ? asset($currentSig) : null,
                    'has_signature' => $hasActiveSig,
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to update business profile: '.$e->getMessage());

            return $this->respond($request, false, 'Failed to update profile. Please try again.');
        }
    }

    /**
     * Update Bank & Billing Defaults.
     */
    public function updateBankDefaults(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'bank_account_name' => 'required|string|max:255',
            'bank_account_no' => 'required|string|max:255',
            'bank_ifsc' => 'required|string|max:255',
            'terms_and_conditions' => 'required|string',
        ]);

        try {
            Setting::updateOrCreate(['key' => 'bank_name'], ['value' => $request->bank_name ?? '']);
            Setting::updateOrCreate(['key' => 'bank_account_name'], ['value' => $request->bank_account_name ?? '']);
            Setting::updateOrCreate(['key' => 'bank_account_no'], ['value' => $request->bank_account_no ?? '']);
            Setting::updateOrCreate(['key' => 'bank_ifsc'], ['value' => strtoupper($request->bank_ifsc ?? '')]);
            Setting::updateOrCreate(['key' => 'terms_and_conditions'], ['value' => $request->terms_and_conditions ?? '']);

            AuditLogService::log('Settings', 'updated', 'Updated bank details and billing terms & conditions');

            return $this->respond($request, true, 'Bank details & billing defaults updated successfully!');
        } catch (Throwable $e) {
            Log::error('Failed to update bank defaults: '.$e->getMessage());

            return $this->respond($request, false, 'Failed to update bank defaults. Please try again.');
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

            AuditLogService::log('Settings', 'updated', 'Updated invoice & sales order auto-increment serial settings');

            return $this->respond($request, true, 'Document prefix & auto-increment serial settings updated successfully!');
        } catch (Throwable $e) {
            Log::error('Failed to update serial settings: '.$e->getMessage());

            return $this->respond($request, false, 'Failed to update serial settings. Please try again.');
        }
    }

    /**
     * Update Tax & Financial Configuration Settings.
     */
    public function updateFinancialSettings(Request $request)
    {
        $request->validate([
            'home_state' => 'nullable|string|max:100',
            'default_gst_rate' => 'required|numeric|in:0,5,12,18,28',
            'financial_year_start_month' => 'required|integer|min:1|max:12',
            'number_format_style' => 'required|string|in:indian,international',
        ]);

        try {
            if ($request->filled('home_state')) {
                Setting::set('home_state', trim($request->home_state));
            }
            Setting::set('default_gst_rate', (string) $request->default_gst_rate);
            Setting::set('financial_year_start_month', (string) $request->financial_year_start_month);
            Setting::set('number_format_style', $request->number_format_style);

            AuditLogService::log('Settings', 'updated', "Updated default GST rate ({$request->default_gst_rate}%), Home State (".($request->home_state ?? 'Gujarat').') and financial settings');

            return $this->respond($request, true, 'Financial & Tax configuration updated successfully!');
        } catch (Throwable $e) {
            Log::error('Failed to update financial settings: '.$e->getMessage());

            return $this->respond($request, false, 'Failed to update financial settings. Please try again.');
        }
    }

    /**
     * Lock or Unlock a Financial Year Period.
     */
    public function toggleFinancialYearLock(Request $request)
    {
        $request->validate([
            'year_key' => 'required|string|max:20',
            'lock_action' => 'required|string|in:lock,unlock',
        ]);

        try {
            $yearKey = trim($request->year_key);
            $isLocking = ($request->lock_action === 'lock');

            if ($isLocking) {
                \App\Services\FinancialYearService::lockFinancialYear($yearKey);
                AuditLogService::log('Settings', 'updated', "LOCKED Financial Year '{$yearKey}' for tax audit compliance");
                $msg = "Financial Year '{$yearKey}' has been LOCKED. Historical records are now protected from edits and deletions.";
            } else {
                \App\Services\FinancialYearService::unlockFinancialYear($yearKey);
                AuditLogService::log('Settings', 'updated', "UNLOCKED Financial Year '{$yearKey}'");
                $msg = "Financial Year '{$yearKey}' has been UNLOCKED. Editing is temporarily re-enabled.";
            }

            return $this->respond($request, true, $msg, [
                'year_key' => $yearKey,
                'is_locked' => $isLocking,
                'redirect' => route('settings.index', ['tab' => 'other', 'sub' => 'financial']),
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to toggle financial year lock: '.$e->getMessage());

            return $this->respond($request, false, 'Failed to update period lock status. Please try again.');
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
                Setting::set('mail_password', Crypt::encryptString($cleanPassword));
            }
            Setting::set('mail_encryption', $request->mail_encryption);
            Setting::set('mail_from_address', $fromAddress);
            Setting::set('mail_from_name', trim($request->mail_from_name));

            AuditLogService::log('Settings', 'updated', "Updated SMTP email delivery settings ('{$fromAddress}')");

            return $this->respond($request, true, 'Email (SMTP) delivery settings saved successfully!');
        } catch (Throwable $e) {
            Log::error('Failed to update email settings: '.$e->getMessage());

            return $this->respond($request, false, 'Failed to update email settings. Please try again.');
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

            $encryptedPassword = Setting::get('mail_password', '');
            if (empty($encryptedPassword)) {
                return $this->respond($request, false, 'SMTP Authentication Error: Email App Password is missing! Please enter your 16-character Google App Password in settings and click "Save Email Settings" first.');
            }

            // Decrypt stored password (with fallback for legacy plain-text values)
            try {
                $mailPassword = Crypt::decryptString($encryptedPassword);
            } catch (DecryptException $e) {
                // Fallback: password was stored before encryption was added
                $mailPassword = $encryptedPassword;
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

            Mail::raw("Hello!\n\nThis is a test delivery email from your Praful Welding Works ERP system settings hub. If you received this email, your SMTP configuration is working correctly!\n\nSent at: ".date('Y-m-d H:i:s'), function ($message) use ($to, $fromAddr, $fromName) {
                $message->to($to)
                    ->from($fromAddr, $fromName)
                    ->subject('SMTP Diagnostic Test Email - Praful Welding Works ERP');
            });

            return $this->respond($request, true, "Test email sent successfully to '{$to}'! Please check your inbox.");
        } catch (Throwable $e) {
            $msg = $e->getMessage();
            Log::error('Failed to send test email: '.$msg);

            if (str_contains($msg, 'BadCredentials') || str_contains($msg, '535')) {
                return $this->respond($request, false, 'Google Password Rejected: Please enter your 16-character Google App Password (not your personal Gmail password) and click "Save Email Settings" first.');
            }

            if (str_contains($msg, '530') || str_contains($msg, 'Authentication Required')) {
                return $this->respond($request, false, 'Authentication Required: Please enter your 16-character Google App Password and click "Save Email Settings" before sending a test email.');
            }

            return $this->respond($request, false, 'Failed to send email: '.(strlen($msg) > 120 ? substr($msg, 0, 120).'...' : $msg));
        }
    }

    /**
     * Update Security & System Backup Settings.
     */
    public function updateSecuritySettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_timeout_minutes' => 'required|integer|min:15|max:1440',
            'max_login_attempts' => 'nullable|integer|min:3|max:10',
            'auto_email_backup' => 'nullable|string|in:true,false',
            'auto_backup_frequency' => 'required|string|in:daily,weekly,monthly,disabled',
            'auto_backup_retention' => 'required|string|in:1_month,3_months,6_months,1_year,never',
            'auto_backup_time' => 'nullable|string',
            'auto_backup_day' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->respond($request, false, $validator->errors()->first());
        }

        try {
            Setting::set('session_timeout_minutes', (string) $request->session_timeout_minutes);
            if ($request->has('max_login_attempts')) {
                Setting::set('max_login_attempts', (string) $request->max_login_attempts);
            }

            $freq = $request->auto_backup_frequency;
            $enabled = ($freq !== 'disabled') ? 'true' : 'false';
            Setting::set('auto_backup_enabled', $enabled);
            Setting::set('auto_backup_frequency', $freq === 'disabled' ? 'monthly' : $freq);
            Setting::set('auto_email_backup', $request->input('auto_email_backup', 'true'));
            Setting::set('auto_backup_retention', $request->auto_backup_retention);
            Setting::set('auto_backup_time', $request->input('auto_backup_time', '18:00'));
            Setting::set('auto_backup_day', $request->input('auto_backup_day', 'Wednesday'));

            // Run catch-up cleanup if retention rule changed
            app(BackupService::class)->cleanOldBackups();

            AuditLogService::log('Settings', 'updated', "Updated security policies (Session Timeout: {$request->session_timeout_minutes} mins, Auto Backup: {$freq}, Retention: {$request->auto_backup_retention})");

            return $this->respond($request, true, 'Security & backup schedule preferences saved successfully!');
        } catch (Throwable $e) {
            Log::error('Failed to update security settings: '.$e->getMessage());

            return $this->respond($request, false, 'Failed to update security settings. Please try again.');
        }
    }

    /**
     * 1-Click System Self-Healing & Cache Re-Sync.
     */
    public function resyncCache(Request $request)
    {
        try {
            Artisan::call('optimize:clear');
            Artisan::call('view:cache');

            AuditLogService::log('System', 'maintenance', 'Performed 1-Click System Health & Cache Re-Sync');

            return $this->respond($request, true, '⚡ System cache cleared & compiled successfully! All application views and routes are in sync.');
        } catch (Throwable $e) {
            Log::error('1-Click Re-sync failed: '.$e->getMessage());

            return $this->respond($request, false, 'Failed to re-sync cache: '.$e->getMessage());
        }
    }

    /**
     * Auto-Prune Old Audit Logs & Storage Temp Files.
     */
    public function pruneSystemLogs(Request $request)
    {
        $days = (int) $request->input('retention_days', Setting::get('audit_log_retention_days', '90'));
        if ($days < 7) {
            $days = 90;
        }

        try {
            Setting::set('audit_log_retention_days', (string) $days);

            $cutoffDate = Carbon::now()->subDays($days);
            $deletedCount = 0;
            if (Schema::hasTable('activity_logs')) {
                $deletedCount = DB::table('activity_logs')
                    ->where('created_at', '<', $cutoffDate)
                    ->delete();
            }

            AuditLogService::log('System', 'cleaned', "Cleaned {$deletedCount} activity logs older than {$days} days");

            return $this->respond($request, true, "🧹 Storage cleaned! {$deletedCount} old activity log entries older than {$days} days were removed.");
        } catch (Throwable $e) {
            Log::error('Clean system logs failed: '.$e->getMessage());

            return $this->respond($request, false, 'Failed to clean system logs: '.$e->getMessage());
        }
    }

    /**
     * Add Purchase / Expense / Material Category.
     */
    public function storeCategory(Request $request)
    {
        $type = $request->input('type', 'purchase'); // purchase, expense, or material
        $label = trim($request->input('label'));
        $icon = trim($request->input('icon', '📦')) ?: '📦';

        if (empty($label)) {
            return $this->respond($request, false, 'Category label cannot be empty!');
        }

        $key = Str::slug($label, '_');

        if ($type === 'material') {
            $categories = CategoryService::getMaterialCategories();
            foreach ($categories as $cat) {
                if (strtolower($cat['label']) === strtolower($label) || $cat['key'] === $key) {
                    return $this->respond($request, false, "Material category '{$label}' already exists!");
                }
            }
            $newCat = ['key' => $key, 'label' => $label, 'icon' => $icon, 'color' => 'blue', 'protected' => false];
            $categories[] = $newCat;
            CategoryService::saveMaterialCategories($categories);
            AuditLogService::log('Settings', 'created', "Created new material category '{$label}'");

            return $this->respond($request, true, "Material category '{$label}' created successfully!", [
                'category' => array_merge($newCat, ['type' => 'material']),
            ]);
        } elseif ($type === 'purchase') {
            $categories = CategoryService::getPurchaseCategories();
            foreach ($categories as $cat) {
                if (strtolower($cat['label']) === strtolower($label) || $cat['key'] === $key) {
                    return $this->respond($request, false, "Purchase category '{$label}' already exists!");
                }
            }
            $newCat = ['key' => $key, 'label' => $label, 'protected' => false];
            $categories[] = $newCat;
            CategoryService::savePurchaseCategories($categories);
            AuditLogService::log('Settings', 'created', "Created new purchase category '{$label}'");

            return $this->respond($request, true, "Purchase category '{$label}' created successfully!", [
                'category' => array_merge($newCat, ['type' => 'purchase']),
            ]);
        } else {
            $categories = CategoryService::getExpenseCategories();
            foreach ($categories as $cat) {
                if (strtolower($cat['label']) === strtolower($label) || $cat['key'] === $key) {
                    return $this->respond($request, false, "Expense category '{$label}' already exists!");
                }
            }
            $newCat = ['key' => $key, 'label' => $label, 'protected' => false];
            $categories[] = $newCat;
            CategoryService::saveExpenseCategories($categories);
            AuditLogService::log('Settings', 'created', "Created new expense category '{$label}'");

            return $this->respond($request, true, "Expense category '{$label}' created successfully!", [
                'category' => array_merge($newCat, ['type' => 'expense']),
            ]);
        }
    }

    /**
     * Update Purchase / Expense / Material Category Label.
     */
    public function updateCategory(Request $request)
    {
        $type = $request->input('type', 'purchase');
        $key = $request->input('key');
        $newLabel = trim($request->input('label'));
        $icon = $request->input('icon');

        if (empty($newLabel)) {
            return $this->respond($request, false, 'Category label cannot be empty!');
        }

        if ($type === 'material') {
            $categories = CategoryService::getMaterialCategories();
            $updated = false;
            $finalIcon = '📦';
            foreach ($categories as &$cat) {
                if ($cat['key'] === $key) {
                    $cat['label'] = $newLabel;
                    if ($icon !== null && !empty(trim($icon))) {
                        $cat['icon'] = trim($icon);
                    }
                    $finalIcon = $cat['icon'] ?? '📦';
                    $updated = true;
                    break;
                }
            }
            if ($updated) {
                CategoryService::saveMaterialCategories($categories);
                AuditLogService::log('Settings', 'updated', "Updated material category key '{$key}' label to '{$newLabel}'");

                return $this->respond($request, true, 'Material category updated successfully!', [
                    'category' => [
                        'type' => 'material',
                        'key' => $key,
                        'label' => $newLabel,
                        'icon' => $finalIcon,
                    ],
                ]);
            }
        } elseif ($type === 'purchase') {
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
                AuditLogService::log('Settings', 'updated', "Updated purchase category key '{$key}' label to '{$newLabel}'");

                return $this->respond($request, true, 'Purchase category updated successfully!', [
                    'category' => [
                        'type' => 'purchase',
                        'key' => $key,
                        'label' => $newLabel,
                    ],
                ]);
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
                AuditLogService::log('Settings', 'updated', "Updated expense category key '{$key}' label to '{$newLabel}'");

                return $this->respond($request, true, 'Expense category updated successfully!', [
                    'category' => [
                        'type' => 'expense',
                        'key' => $key,
                        'label' => $newLabel,
                    ],
                ]);
            }
        }

        return $this->respond($request, false, 'Category not found.');
    }

    /**
     * Delete Purchase / Expense / Material Category (Enforces System Protection Rules).
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

        if ($type === 'material') {
            $categories = CategoryService::getMaterialCategories();
            $filtered = array_values(array_filter($categories, fn ($cat) => $cat['key'] !== $key));
            CategoryService::saveMaterialCategories($filtered);
            AuditLogService::log('Settings', 'deleted', "Deleted material category key '{$key}'");

            return $this->respond($request, true, 'Material category deleted successfully!');
        } elseif ($type === 'purchase') {
            $categories = CategoryService::getPurchaseCategories();
            $filtered = array_values(array_filter($categories, fn ($cat) => $cat['key'] !== $key));
            CategoryService::savePurchaseCategories($filtered);
            AuditLogService::log('Settings', 'deleted', "Deleted purchase category key '{$key}'");

            return $this->respond($request, true, 'Purchase category deleted successfully!');
        } else {
            $categories = CategoryService::getExpenseCategories();
            $filtered = array_values(array_filter($categories, fn ($cat) => $cat['key'] !== $key));
            CategoryService::saveExpenseCategories($filtered);
            AuditLogService::log('Settings', 'deleted', "Deleted expense category key '{$key}'");

            return $this->respond($request, true, 'Expense category deleted successfully!');
        }
    }
}
