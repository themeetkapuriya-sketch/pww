<?php

namespace App\Services;

use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;

class RolePermissionService
{
    /**
     * Get list of all active system & custom roles.
     */
    public static function getRoles(): array
    {
        $defaultRoles = [
            'super_admin' => [
                'name' => 'Super Admin (Owner)',
                'description' => 'Full 100% control over all modules, user accounts, system settings & backups.',
            ],
            'admin' => [
                'name' => 'Admin',
                'description' => 'Full administrative access to manage users, role permissions, and settings.',
            ],
            'accountant' => [
                'name' => 'Accountant / Billing Staff',
                'description' => 'Access to Invoices, Sales Orders, Purchases, Expenses & Financial Reports.',
            ],
            'production_manager' => [
                'name' => 'Production Manager',
                'description' => 'Access to Production Runs, Raw Materials, Products & BOM.',
            ],
            'view_only' => [
                'name' => 'View Only / Auditor',
                'description' => 'Read-only access to view allowed pages and reports.',
            ],
            'staff' => [
                'name' => 'Staff / Operations',
                'description' => 'Standard operational access to create orders and update inventory.',
            ],
        ];

        try {
            $dbRoles = Role::where('is_active', true)->get();
            foreach ($dbRoles as $role) {
                if (!isset($defaultRoles[$role->slug])) {
                    $defaultRoles[$role->slug] = [
                        'name' => $role->name,
                        'description' => $role->description ?? 'Custom defined system role.',
                    ];
                }
            }
        } catch (\Throwable $e) {}

        return $defaultRoles;
    }

    /**
     * Get dictionary of all fine-grained permission keys.
     */
    public static function getPermissionsList(): array
    {
        return [
            // Page Module Access Toggles
            'page_overview' => 'Overview Dashboard',
            'page_orders' => 'Sales Orders',
            'page_invoices' => 'Invoice Ledger',
            'page_purchases' => 'Purchase Ledger',
            'page_expenses' => 'Expenses Ledger',
            'page_rawmaterial' => 'Raw Materials',
            'page_product' => 'Products',
            'page_bom' => 'Bill of Materials (BOM)',
            'page_production' => 'Production Logs',
            'page_clients' => 'Clients & Plants',
            'page_employees' => 'Employees & Payroll',
            'page_reports' => 'Financial Reports',

            // Action Authorization Toggles
            'action_insert' => 'Insert / Create Records',
            'action_update' => 'Update / Edit Records',
            'action_delete' => 'Delete Records',

            // Settings Access (Restricted to Admin & Super Admin)
            'backups_settings_manage' => 'Access System Settings, Users & Backups',
        ];
    }

    /**
     * Get permissions array for a given role key.
     */
    public static function getDefaultPermissionsForRole(string $roleKey): array
    {
        $allPermissions = array_keys(self::getPermissionsList());

        // Super Admin & Admin always have 100% permissions
        if (in_array($roleKey, ['super_admin', 'admin'])) {
            return $allPermissions;
        }

        try {
            $dbPerms = RolePermission::where('role_slug', $roleKey)->pluck('permission_key')->toArray();
            if (!empty($dbPerms)) {
                return $dbPerms;
            }
        } catch (\Throwable $e) {}

        switch ($roleKey) {
            case 'accountant':
                return ['page_overview', 'page_invoices', 'page_orders', 'page_purchases', 'page_clients', 'page_expenses', 'page_reports', 'action_insert', 'action_update'];

            case 'production_manager':
                return ['page_overview', 'page_rawmaterial', 'page_product', 'page_bom', 'page_production', 'action_insert', 'action_update'];

            case 'view_only':
                return ['page_overview', 'page_reports', 'page_invoices', 'page_orders'];

            case 'pending':
                return [];

            case 'staff':
            default:
                return ['page_overview', 'page_invoices', 'page_orders', 'page_purchases', 'page_rawmaterial', 'page_product', 'page_bom', 'page_production', 'action_insert'];
        }
    }

    /**
     * Check if a given User model instance has a specific permission key.
     */
    public static function userHasPermission(?User $user, string $permissionKey): bool
    {
        if (!$user) {
            return false;
        }

        // If user is pending or inactive, block all permissions
        if ($user->isPending()) {
            return false;
        }

        // System Settings page & backups are strictly restricted to Super Admin & Admin
        if ($permissionKey === 'backups_settings_manage') {
            return in_array($user->role, ['super_admin', 'admin']);
        }

        // Super admins and admin role always have 100% permissions
        if (in_array($user->role, ['super_admin', 'admin'])) {
            return true;
        }

        // Check custom JSON permissions column if present
        $userPermissions = $user->permissions ?? null;
        if (is_array($userPermissions) && count($userPermissions) > 0) {
            return in_array($permissionKey, $userPermissions);
        }

        // Fallback to role permissions matrix
        $defaultPermissions = self::getDefaultPermissionsForRole($user->role ?? 'staff');
        return in_array($permissionKey, $defaultPermissions);
    }
}
