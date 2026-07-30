<?php

namespace App\Services;

use App\Models\User;

class RolePermissionService
{
    /**
     * Get list of standard system roles with descriptions.
     */
    public static function getRoles(): array
    {
        return [
            'super_admin' => [
                'name' => 'Super Admin (Owner)',
                'description' => 'Full 100% access to all ERP modules, user management, backups, and system settings.',
            ],
            'accountant' => [
                'name' => 'Accountant / Billing Staff',
                'description' => 'Can manage Invoices, Sales Orders, Purchases, Expenses, and Client Ledgers. Cannot delete records or access system settings.',
            ],
            'production_manager' => [
                'name' => 'Production Manager',
                'description' => 'Can manage Production Runs, Raw Materials, Products, and BOM. Cannot view billing amounts or profit reports.',
            ],
            'view_only' => [
                'name' => 'View Only / Auditor',
                'description' => 'Read-only access to allowed modules and reports. Cannot insert, edit, or delete any records.',
            ],
            'custom' => [
                'name' => 'Custom Permissions',
                'description' => 'Tailored permission checkboxes selected by the Admin.',
            ],
        ];
    }

    /**
     * Get dictionary of all fine-grained permission keys.
     */
    public static function getPermissionsList(): array
    {
        return [
            'billing_manage' => 'Create & Edit Invoices & Sales Orders',
            'billing_delete' => 'Delete Invoices & Sales Orders',
            'purchases_manage' => 'Create & Edit Purchases',
            'purchases_delete' => 'Delete Purchases',
            'clients_manage' => 'Create & Edit Clients & Plants',
            'clients_delete' => 'Delete Clients & Plants',
            'inventory_manage' => 'Create & Edit Raw Materials & Products',
            'inventory_delete' => 'Delete Materials & Products',
            'production_manage' => 'Log & Edit Production Runs & BOM',
            'production_delete' => 'Delete Production Logs',
            'payroll_manage' => 'Manage Employees & Payroll Payouts',
            'expenses_manage' => 'Log & Edit Operational Expenses',
            'financials_view' => 'View Financial Profit Reports & GST Ledgers',
            'backups_settings_manage' => 'Access System Settings, User Accounts & Backups',
        ];
    }

    /**
     * Get default permissions array for a given role key.
     */
    public static function getDefaultPermissionsForRole(string $roleKey): array
    {
        $allPermissions = array_keys(self::getPermissionsList());

        switch ($roleKey) {
            case 'super_admin':
            case 'admin':
                return $allPermissions;

            case 'accountant':
                return [
                    'billing_manage',
                    'purchases_manage',
                    'clients_manage',
                    'expenses_manage',
                    'financials_view',
                ];

            case 'production_manager':
                return [
                    'inventory_manage',
                    'production_manage',
                ];

            case 'view_only':
                return [
                    'financials_view',
                ];

            case 'staff':
            default:
                return [
                    'billing_manage',
                    'purchases_manage',
                    'clients_manage',
                    'inventory_manage',
                    'production_manage',
                ];
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

        // Super admins and admin role always have 100% permissions
        if ($user->role === 'super_admin' || $user->role === 'admin') {
            return true;
        }

        // Check custom JSON permissions column if present
        $userPermissions = $user->permissions ?? null;
        if (is_array($userPermissions) && count($userPermissions) > 0) {
            return in_array($permissionKey, $userPermissions);
        }

        // Fallback to role defaults
        $defaultPermissions = self::getDefaultPermissionsForRole($user->role ?? 'staff');
        return in_array($permissionKey, $defaultPermissions);
    }
}
