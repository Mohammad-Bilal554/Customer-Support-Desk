<?php
declare(strict_types=1);
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Permission;

class PermissionController extends Controller
{
    // GET /admin/permissions
    public function index(Request $request): string
    {
        $this->requireLogin();
        $this->authorize($this->isAdmin() || has_permission('permissions.manage'));

        $groupedPermissions = Permission::getAllGroupedByModule();
        $matrix             = Permission::getRolePermissionsMatrix();
        $activeRole         = $request->query('role', 'employee');
        if (!in_array($activeRole, ['super_admin', 'employee', 'client'], true)) {
            $activeRole = 'employee';
        }

        return $this->view('admin.permissions.index', [
            'title'              => 'Role Permissions',
            'groupedPermissions' => $groupedPermissions,
            'matrix'             => $matrix,
            'activeRole'         => $activeRole,
            'breadcrumbs'        => [['label' => 'Admin'], ['label' => 'Role Permissions']],
        ]);
    }

    // POST /admin/permissions
    public function update(Request $request): string
    {
        $this->requireLogin();
        $this->authorize($this->isAdmin() || has_permission('permissions.manage'));

        $role          = $request->input('role');
        $permissionIds = $request->input('permissions', []);

        if (!in_array($role, ['super_admin', 'employee', 'client'], true)) {
            if ($this->isAjax()) {
                return $this->json(['success' => false, 'message' => 'Invalid role.']);
            }
            $this->session->error('Invalid role selected.');
            $this->redirect(url('admin/permissions'));
        }

        // Convert permission IDs to array of integers
        $permissionIds = is_array($permissionIds) ? array_map('intval', $permissionIds) : [];

        $success = Permission::syncRolePermissions($role, $permissionIds);

        if ($this->isAjax()) {
            return $this->json([
                'success' => $success,
                'message' => $success ? ucfirst(str_replace('_', ' ', $role)) . ' permissions updated successfully.' : 'Failed to update permissions.'
            ]);
        }

        if ($success) {
            $this->session->success(ucfirst(str_replace('_', ' ', $role)) . ' permissions updated successfully.');
        } else {
            $this->session->error('Failed to update permissions.');
        }

        $this->redirect(url('admin/permissions?role=' . $role));
    }
}
