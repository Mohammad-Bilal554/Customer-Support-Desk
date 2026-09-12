<?php
declare(strict_types=1);
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Permission;
use App\Models\User;

class PermissionController extends Controller
{
    // GET /admin/permissions
    public function index(Request $request): string
    {
        $this->requireLogin();
        $this->authorize($this->isAdmin() || has_permission('permissions.manage'));

        $tab = $request->query('tab', $request->query('type', 'role'));
        if (!in_array($tab, ['role', 'user'], true)) {
            $tab = 'role';
        }

        $groupedPermissions = Permission::getAllGroupedByModule();
        $matrix             = Permission::getRolePermissionsMatrix();
        
        $activeRole = $request->query('role', 'employee');
        if (!in_array($activeRole, ['super_admin', 'employee', 'client'], true)) {
            $activeRole = 'employee';
        }

        // Fetch users for User-wise permissions tab
        $users = User::all([], 'first_name ASC, last_name ASC');
        $selectedUserId = (int)$request->query('user_id', 0);
        
        if ($selectedUserId === 0 && !empty($users)) {
            // Default to first user or first non-super_admin user
            $nonAdmin = array_filter($users, fn($u) => $u['role'] !== 'super_admin');
            $defaultUser = !empty($nonAdmin) ? reset($nonAdmin) : $users[0];
            $selectedUserId = (int)$defaultUser['id'];
        }

        $selectedUser  = $selectedUserId > 0 ? User::find($selectedUserId) : null;
        $userOverrides = $selectedUserId > 0 ? Permission::getUserPermissions($selectedUserId) : [];

        return $this->view('admin.permissions.index', [
            'title'              => 'Permissions Management',
            'tab'                => $tab,
            'groupedPermissions' => $groupedPermissions,
            'matrix'             => $matrix,
            'activeRole'         => $activeRole,
            'users'              => $users,
            'selectedUserId'     => $selectedUserId,
            'selectedUser'       => $selectedUser,
            'userOverrides'      => $userOverrides,
            'breadcrumbs'        => [['label' => 'Admin'], ['label' => 'Permissions']],
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

        $this->redirect(url('admin/permissions?tab=role&role=' . $role));
    }

    // POST /admin/permissions/user
    public function updateUserPermissions(Request $request): string
    {
        $this->requireLogin();
        $this->authorize($this->isAdmin() || has_permission('permissions.manage'));

        $userId    = (int)$request->input('user_id', 0);
        $overrides = $request->input('user_permissions', []);

        $targetUser = $userId > 0 ? User::find($userId) : null;
        if (!$targetUser) {
            if ($this->isAjax()) {
                return $this->json(['success' => false, 'message' => 'User not found.']);
            }
            $this->session->error('User not found.');
            $this->redirect(url('admin/permissions?tab=user'));
        }

        $success = Permission::syncUserPermissions($userId, is_array($overrides) ? $overrides : []);

        $userName = User::fullName($targetUser);

        if ($this->isAjax()) {
            return $this->json([
                'success' => $success,
                'message' => $success ? "Permission overrides for {$userName} updated successfully." : 'Failed to update user permissions.'
            ]);
        }

        if ($success) {
            $this->session->success("Permission overrides for {$userName} updated successfully.");
        } else {
            $this->session->error('Failed to update user permissions.');
        }

        $this->redirect(url('admin/permissions?tab=user&user_id=' . $userId));
    }
}
