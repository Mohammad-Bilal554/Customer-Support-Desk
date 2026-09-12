<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Permission extends Model
{
    protected static string $table      = 'permissions';
    protected static string $primaryKey = 'id';

    protected static array $fillable = [
        'module', 'key_name', 'name', 'description',
    ];

    /**
     * Get all permissions grouped by module.
     */
    public static function getAllGroupedByModule(): array
    {
        $permissions = static::all([], 'module ASC, id ASC');
        $grouped     = [];

        foreach ($permissions as $p) {
            $module = $p['module'] ?? 'General';
            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }
            $grouped[$module][] = $p;
        }

        return $grouped;
    }

    /**
     * Get matrix of role -> array of assigned permission IDs.
     */
    public static function getRolePermissionsMatrix(): array
    {
        $db   = static::db();
        $rows = $db->fetchAll("SELECT role, permission_id FROM role_permissions");

        $matrix = [
            'super_admin' => [],
            'employee'    => [],
            'client'      => [],
        ];

        foreach ($rows as $r) {
            $role = $r['role'];
            if (isset($matrix[$role])) {
                $matrix[$role][] = (int)$r['permission_id'];
            }
        }

        return $matrix;
    }

    /**
     * Get array of permission key_names for a given role.
     */
    public static function getPermissionKeysForRole(string $role): array
    {
        if ($role === 'super_admin') {
            $rows = static::db()->fetchAll("SELECT key_name FROM permissions");
            return array_column($rows, 'key_name');
        }

        $rows = static::db()->fetchAll(
            "SELECT p.key_name 
             FROM role_permissions rp 
             JOIN permissions p ON p.id = rp.permission_id 
             WHERE rp.role = ?",
            [$role]
        );

        return array_column($rows, 'key_name');
    }

    /**
     * Check if a role has a specific permission key.
     */
    public static function roleHas(string $role, string $permissionKey): bool
    {
        // Super Admin bypass
        if ($role === 'super_admin') {
            return true;
        }

        $count = (int)static::db()->fetchColumn(
            "SELECT COUNT(*) 
             FROM role_permissions rp 
             JOIN permissions p ON p.id = rp.permission_id 
             WHERE rp.role = ? AND p.key_name = ?",
            [$role, $permissionKey]
        );

        return $count > 0;
    }

    /**
     * Update/Sync role permissions for a role.
     */
    public static function syncRolePermissions(string $role, array $permissionIds): bool
    {
        $db = static::db();

        return $db->transaction(function(Database $db) use ($role, $permissionIds) {
            // Remove existing permissions for this role
            $db->delete('role_permissions', ['role' => $role]);

            // Re-insert selected permissions
            foreach ($permissionIds as $pid) {
                $pid = (int)$pid;
                if ($pid > 0) {
                    $db->insert('role_permissions', [
                        'role'          => $role,
                        'permission_id' => $pid,
                    ]);
                }
            }

            return true;
        });
    }

    /**
     * Get permission overrides for a specific user.
     * Returns array [permission_id => is_granted (int 0 or 1)]
     */
    public static function getUserPermissions(int $userId): array
    {
        $rows = static::db()->fetchAll(
            "SELECT permission_id, is_granted FROM user_permissions WHERE user_id = ?",
            [$userId]
        );

        $overrides = [];
        foreach ($rows as $r) {
            $overrides[(int)$r['permission_id']] = (int)$r['is_granted'];
        }

        return $overrides;
    }

    /**
     * Check if a specific user has a permission key (considering role + user overrides).
     */
    public static function userHasPermission(int $userId, string $role, string $permissionKey): bool
    {
        // Super Admin bypass
        if ($role === 'super_admin') {
            return true;
        }

        // Check explicit user override first
        $row = static::db()->fetchOne(
            "SELECT up.is_granted 
             FROM user_permissions up 
             JOIN permissions p ON p.id = up.permission_id 
             WHERE up.user_id = ? AND p.key_name = ?",
            [$userId, $permissionKey]
        );

        if ($row !== null) {
            return (bool)$row['is_granted'];
        }

        // Fallback to role permissions
        return static::roleHas($role, $permissionKey);
    }

    /**
     * Sync user permission overrides for a specific user.
     * $overrides is associative array [permission_id => '1'|'0'|'inherit'|1|0]
     */
    public static function syncUserPermissions(int $userId, array $overrides): bool
    {
        $db = static::db();

        return $db->transaction(function(Database $db) use ($userId, $overrides) {
            // Clear existing overrides for this user
            $db->delete('user_permissions', ['user_id' => $userId]);

            // Re-insert explicit overrides ('1' / '0')
            foreach ($overrides as $pid => $val) {
                $pid = (int)$pid;
                $valStr = (string)$val;
                if ($pid > 0 && ($valStr === '1' || $valStr === '0')) {
                    $db->insert('user_permissions', [
                        'user_id'       => $userId,
                        'permission_id' => $pid,
                        'is_granted'    => (int)$valStr,
                    ]);
                }
            }

            return true;
        });
    }
}
