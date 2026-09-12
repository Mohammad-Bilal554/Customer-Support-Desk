<?php

/**
 * Role & User Permissions View
 * @var string $tab
 * @var array $groupedPermissions
 * @var array $matrix
 * @var string $activeRole
 * @var array $users
 * @var int $selectedUserId
 * @var array|null $selectedUser
 * @var array $userOverrides
 */

use App\Core\Session;
use App\Core\Csrf;
use App\Models\User;

$session    = Session::getInstance();
$csrfToken  = Csrf::getToken();
$title      = 'Permissions Management';

$roleLabels = [
    'employee'    => ['label' => 'Employee',    'icon' => 'bi-person-badge', 'bg' => '#dbeafe', 'color' => '#1d4ed8'],
    'client'      => ['label' => 'Client',      'icon' => 'bi-building',     'bg' => '#d1fae5', 'color' => '#065f46'],
    'super_admin' => ['label' => 'Super Admin', 'icon' => 'bi-shield-check', 'bg' => '#ede9fe', 'color' => '#6d28d9'],
];

ob_start();
?>

<div class="page-header mb-3">
    <div>
        <h1 class="page-title"><i class="bi bi-shield-lock-fill text-primary me-2"></i>Permissions Management</h1>
        <p class="page-subtitle">Manage role-wise defaults and user-specific permission overrides across the portal.</p>
    </div>
</div>

<!-- Main Tabs -->
<div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom flex-wrap gap-3">
    <ul class="nav nav-tabs custom-main-tabs mb-0 border-bottom-0">
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'role' ? 'active fw-bold' : 'text-muted' ?>"
                href="<?= url('admin/permissions?tab=role') ?>"
                style="font-size:.95rem;padding:.65rem 1.25rem;">
                <i class="bi bi-shield-lock me-2 text-primary"></i>Role Permissions
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'user' ? 'active fw-bold' : 'text-muted' ?>"
                href="<?= url('admin/permissions?tab=user') ?>"
                style="font-size:.95rem;padding:.65rem 1.25rem;">
                <i class="bi bi-person-gear me-2 text-primary"></i>User-Wise Permissions
            </a>
        </li>
    </ul>
</div>

<?php if ($tab === 'role'): ?>
    <!-- ==================== ROLE PERMISSIONS TAB ==================== -->

    <!-- Role Sub-Pills -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <ul class="nav nav-pills custom-pills" id="roleTabs" role="tablist">
            <?php foreach ($roleLabels as $rKey => $rMeta): ?>
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?= $activeRole === $rKey ? 'active' : '' ?>"
                        href="<?= url('admin/permissions?tab=role&role=' . $rKey) ?>"
                        style="font-weight:600;font-size:.875rem;">
                        <i class="bi <?= $rMeta['icon'] ?> me-1.5"></i>
                        <?= $rMeta['label'] ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="d-flex align-items-center gap-2">
            <span class="badge px-3 py-2" style="background:<?= $roleLabels[$activeRole]['bg'] ?>;color:<?= $roleLabels[$activeRole]['color'] ?>;font-size:.8rem;font-weight:600;">
                Editing Role: <?= $roleLabels[$activeRole]['label'] ?>
            </span>
        </div>
    </div>

    <?php if ($activeRole === 'super_admin'): ?>
        <div class="alert alert-info border-0 shadow-sm mb-4 d-flex align-items-center gap-3">
            <i class="bi bi-info-circle-fill fs-4 flex-shrink-0 text-info"></i>
            <div style="font-size:.875rem;">
                <strong>Super Admin Info:</strong> Super Admin has unrestricted access to all portal features by default. Custom permission selections saved here will apply as baseline role settings.
            </div>
        </div>
    <?php endif; ?>

    <form action="<?= url('admin/permissions') ?>" method="POST" class="permissions-form">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <input type="hidden" name="role" value="<?= htmlspecialchars($activeRole) ?>">

        <div class="row g-4 mb-4">
            <?php foreach ($groupedPermissions as $moduleName => $permissions): ?>
                <?php
                $moduleIcons = [
                    'Tickets'   => 'bi-ticket-perforated-fill',
                    'Users'     => 'bi-people-fill',
                    'Companies' => 'bi-building-fill',
                    'Reports'   => 'bi-bar-chart-fill',
                    'System'    => 'bi-gear-fill',
                ];
                $moduleIcon = $moduleIcons[$moduleName] ?? 'bi-folder-fill';
                $assignedForRole = $matrix[$activeRole] ?? [];
                ?>
                <div class="col-lg-6">
                    <div class="card h-100 shadow-sm border-0" style="border-radius:14px;overflow:hidden;">
                        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom" style="border-top: 3px solid var(--primary);">
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;background:var(--primary-light);color:var(--primary);">
                                    <i class="bi <?= $moduleIcon ?>" style="font-size:1rem;"></i>
                                </div>
                                <h6 class="mb-0 fw-bold" style="color:var(--text-main);"><?= htmlspecialchars($moduleName) ?></h6>
                            </div>
                            <div class="d-flex gap-1">
                                <button type="button"
                                    class="btn btn-link btn-sm text-decoration-none p-0 px-2 select-all-btn"
                                    data-module="<?= htmlspecialchars($moduleName) ?>"
                                    style="font-size:.775rem;font-weight:600;">
                                    Select All
                                </button>
                                <span class="text-muted" style="font-size:.775rem;">|</span>
                                <button type="button"
                                    class="btn btn-link btn-sm text-decoration-none p-0 px-2 text-muted deselect-all-btn"
                                    data-module="<?= htmlspecialchars($moduleName) ?>"
                                    style="font-size:.775rem;font-weight:500;">
                                    Deselect
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <?php foreach ($permissions as $p): ?>
                                    <?php
                                    $isChecked = in_array((int)$p['id'], $assignedForRole, true) || $activeRole === 'super_admin';
                                    ?>
                                    <label class="list-group-item d-flex align-items-center justify-content-between py-3 px-3 cursor-pointer hover-bg-light" style="cursor:pointer;transition:background .2s;">
                                        <div class="me-3">
                                            <div class="fw-semibold text-dark" style="font-size:.875rem;"><?= htmlspecialchars($p['name']) ?></div>
                                            <div class="text-muted" style="font-size:.775rem;margin-top:2px;">
                                                <?= htmlspecialchars($p['description'] ?? '') ?>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch m-0 ms-2">
                                            <input class="form-check-input perm-switch module-<?= htmlspecialchars($moduleName) ?>"
                                                type="checkbox"
                                                name="permissions[]"
                                                value="<?= $p['id'] ?>"
                                                <?= $isChecked ? 'checked' : '' ?>
                                                style="width:2.4em;height:1.2em;cursor:pointer;">
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Sticky Save Bar -->
        <div class="card shadow-lg border-0 sticky-bottom py-3 px-4 mb-4" style="border-radius:14px;background:rgba(255,255,255,0.95);backdrop-filter:blur(10px);z-index:100;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-shield-check text-success fs-5"></i>
                    <span style="font-size:.875rem;color:var(--text-main);font-weight:500;">
                        Updating role permission matrix for <strong><?= $roleLabels[$activeRole]['label'] ?></strong>.
                    </span>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= url('admin/permissions?tab=role&role=' . $activeRole) ?>" class="btn btn-outline-secondary btn-sm px-3" style="border-radius:8px;">
                        Reset
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm px-4 saveBtn" style="border-radius:8px;font-weight:600;">
                        <i class="bi bi-check-lg me-1"></i>Save Role Permissions
                    </button>
                </div>
            </div>
        </div>
    </form>

<?php else: ?>
    <!-- ==================== USER-WISE PERMISSIONS TAB ==================== -->

    <!-- Target User Selector -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius:14px;background:#f8fafc;">
        <div class="card-body py-3 px-4">
            <form method="GET" action="<?= url('admin/permissions') ?>" id="userSelectForm" class="row align-items-center g-3">
                <input type="hidden" name="tab" value="user">
                <div class="col-md-6 col-lg-5">
                    <label class="form-label fw-bold small text-uppercase text-muted mb-1">Select User to Customize</label>
                    <select name="user_id" class="form-select form-select-md" onchange="this.form.submit()" style="border-radius:8px;font-weight:500;">
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= $selectedUserId === (int)$u['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars(User::fullName($u)) ?> (<?= htmlspecialchars($u['email']) ?>) - <?= ucfirst($u['role']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($selectedUser): ?>
                    <div class="col-md-6 col-lg-7 d-flex align-items-center justify-content-md-end gap-3 pt-2 pt-md-0">
                        <div class="d-flex align-items-center gap-3 bg-white p-2 px-3 rounded-3 shadow-sm border">
                            <?= initials_avatar(User::fullName($selectedUser), '#0d6efd', 38) ?>
                            <div>
                                <div class="fw-bold text-dark" style="font-size:.925rem;"><?= htmlspecialchars(User::fullName($selectedUser)) ?></div>
                                <div class="text-muted" style="font-size:.775rem;"><?= htmlspecialchars($selectedUser['email']) ?></div>
                            </div>
                            <div class="ms-2">
                                <?= role_badge($selectedUser['role']) ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <?php if ($selectedUser && $selectedUser['role'] === 'super_admin'): ?>
        <div class="alert alert-info border-0 shadow-sm mb-4 d-flex align-items-center gap-3">
            <i class="bi bi-info-circle-fill fs-4 flex-shrink-0 text-info"></i>
            <div style="font-size:.875rem;">
                <strong>Super Admin User:</strong> This user has full system privileges by default. Custom user-wise overrides below can be set if granular explicit overrides are required.
            </div>
        </div>
    <?php endif; ?>

    <?php if ($selectedUser): ?>
        <form action="<?= url('admin/permissions/user') ?>" method="POST" class="permissions-form">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="user_id" value="<?= $selectedUserId ?>">

            <div class="row g-4 mb-4">
                <?php foreach ($groupedPermissions as $moduleName => $permissions): ?>
                    <?php
                    $moduleIcons = [
                        'Tickets'   => 'bi-ticket-perforated-fill',
                        'Users'     => 'bi-people-fill',
                        'Companies' => 'bi-building-fill',
                        'Reports'   => 'bi-bar-chart-fill',
                        'System'    => 'bi-gear-fill',
                    ];
                    $moduleIcon = $moduleIcons[$moduleName] ?? 'bi-folder-fill';
                    $userRole = $selectedUser['role'];
                    $roleAssignedIds = $matrix[$userRole] ?? [];
                    ?>
                    <div class="col-lg-6">
                        <div class="card h-100 shadow-sm border-0" style="border-radius:14px;overflow:hidden;">
                            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom" style="border-top: 3px solid var(--primary);">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;background:var(--primary-light);color:var(--primary);">
                                        <i class="bi <?= $moduleIcon ?>" style="font-size:1rem;"></i>
                                    </div>
                                    <h6 class="mb-0 fw-bold" style="color:var(--text-main);"><?= htmlspecialchars($moduleName) ?></h6>
                                </div>
                                <div>
                                    <button type="button"
                                        class="btn btn-link btn-sm text-decoration-none p-0 inherit-all-btn"
                                        data-module="<?= htmlspecialchars($moduleName) ?>"
                                        style="font-size:.775rem;font-weight:600;">
                                        Reset to Inherit
                                    </button>
                                </div>
                            </div>

                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <?php foreach ($permissions as $p): ?>
                                        <?php
                                        $pid = (int)$p['id'];
                                        $roleHasPerm = in_array($pid, $roleAssignedIds, true) || $userRole === 'super_admin';
                                        $override = $userOverrides[$pid] ?? 'inherit';
                                        ?>
                                        <div class="list-group-item d-flex align-items-center justify-content-between py-3 px-3 hover-bg-light">
                                            <div class="me-3" style="max-width:55%;">
                                                <div class="fw-semibold text-dark" style="font-size:.875rem;"><?= htmlspecialchars($p['name']) ?></div>
                                                <div class="text-muted" style="font-size:.75rem;margin-top:2px;">
                                                    <?= htmlspecialchars($p['description'] ?? '') ?>
                                                </div>
                                            </div>

                                            <!-- 3-Way Segmented Toggle Group -->
                                            <div class="btn-group btn-group-sm flex-shrink-0 module-group-<?= htmlspecialchars($moduleName) ?>" role="group">
                                                <input type="radio" class="btn-check user-perm-radio"
                                                    name="user_permissions[<?= $pid ?>]"
                                                    id="perm_<?= $pid ?>_inherit"
                                                    value="inherit"
                                                    <?= ($override === 'inherit' || !isset($userOverrides[$pid])) ? 'checked' : '' ?>>
                                                <label class="btn btn-outline-secondary px-2" for="perm_<?= $pid ?>_inherit" style="font-size:.725rem;font-weight:500;">
                                                    Inherit (<?= $roleHasPerm ? 'Allowed' : 'Denied' ?>)
                                                </label>

                                                <input type="radio" class="btn-check user-perm-radio"
                                                    name="user_permissions[<?= $pid ?>]"
                                                    id="perm_<?= $pid ?>_allow"
                                                    value="1"
                                                    <?= (isset($userOverrides[$pid]) && $userOverrides[$pid] === 1) ? 'checked' : '' ?>>
                                                <label class="btn btn-outline-success px-2" for="perm_<?= $pid ?>_allow" style="font-size:.725rem;font-weight:600;">
                                                    <i class="bi bi-check-lg"></i> Allow
                                                </label>

                                                <input type="radio" class="btn-check user-perm-radio"
                                                    name="user_permissions[<?= $pid ?>]"
                                                    id="perm_<?= $pid ?>_deny"
                                                    value="0"
                                                    <?= (isset($userOverrides[$pid]) && $userOverrides[$pid] === 0) ? 'checked' : '' ?>>
                                                <label class="btn btn-outline-danger px-2" for="perm_<?= $pid ?>_deny" style="font-size:.725rem;font-weight:600;">
                                                    <i class="bi bi-x-lg"></i> Deny
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Sticky Save Bar -->
            <div class="card shadow-lg border-0 sticky-bottom py-3 px-4 mb-4" style="border-radius:14px;background:rgba(255,255,255,0.95);backdrop-filter:blur(10px);z-index:100;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-person-check-fill text-primary fs-5"></i>
                        <span style="font-size:.875rem;color:var(--text-main);font-weight:500;">
                            Updating explicit user permission overrides for <strong><?= htmlspecialchars(User::fullName($selectedUser)) ?></strong>.
                        </span>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= url('admin/permissions?tab=user&user_id=' . $selectedUserId) ?>" class="btn btn-outline-secondary btn-sm px-3" style="border-radius:8px;">
                            Reset
                        </a>
                        <button type="submit" class="btn btn-primary btn-sm px-4 saveBtn" style="border-radius:8px;font-weight:600;">
                            <i class="bi bi-check-lg me-1"></i>Save User Overrides
                        </button>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>

<?php endif; ?>

<style>
    .custom-main-tabs .nav-link {
        color: var(--text-muted);
        border: none;
        border-bottom: 3px solid transparent;
        transition: all .2s;
    }

    .custom-main-tabs .nav-link:hover {
        color: var(--primary);
        border-bottom-color: rgba(13, 110, 253, 0.3);
    }

    .custom-main-tabs .nav-link.active {
        color: var(--primary);
        background: transparent;
        border-bottom-color: var(--primary);
    }

    .custom-pills .nav-link {
        color: var(--text-muted);
        border-radius: 10px;
        padding: .5rem 1.1rem;
        transition: all .2s;
    }

    .custom-pills .nav-link:hover {
        background: #f1f5f9;
        color: var(--text-main);
    }

    .custom-pills .nav-link.active {
        background: var(--primary);
        color: #fff;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.25);
    }

    .hover-bg-light:hover {
        background-color: #f8fafc !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // Module Select All / Deselect All for Role tab
        document.querySelectorAll('.select-all-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const mod = this.getAttribute('data-module');
                document.querySelectorAll('.module-' + CSS.escape(mod)).forEach(sw => sw.checked = true);
            });
        });

        document.querySelectorAll('.deselect-all-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const mod = this.getAttribute('data-module');
                document.querySelectorAll('.module-' + CSS.escape(mod)).forEach(sw => sw.checked = false);
            });
        });

        // Reset to Inherit for User tab
        document.querySelectorAll('.inherit-all-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const mod = this.getAttribute('data-module');
                document.querySelectorAll('.module-group-' + CSS.escape(mod) + ' input[value="inherit"]').forEach(r => r.checked = true);
            });
        });

        // Form submit AJAX (handles both Role form and User form)
        document.querySelectorAll('.permissions-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const saveBtn = form.querySelector('.saveBtn');
                const formData = new FormData(form);
                const originalHtml = saveBtn ? saveBtn.innerHTML : '';

                if (saveBtn) {
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';
                }

                fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (saveBtn) {
                            saveBtn.disabled = false;
                            saveBtn.innerHTML = originalHtml;
                        }

                        if (data.success) {
                            SupportPortal.showToast(data.message || 'Permissions updated successfully!', 'success');
                        } else {
                            SupportPortal.showToast(data.message || 'Failed to save permissions.', 'danger');
                        }
                    })
                    .catch(err => {
                        if (saveBtn) {
                            saveBtn.disabled = false;
                            saveBtn.innerHTML = originalHtml;
                        }
                        SupportPortal.showToast('Error saving permissions.', 'danger');
                    });
            });
        });
    });
</script>

<?php
$content = ob_get_clean();
ob_start();
require base_path('resources/views/layouts/app.php');
echo ob_get_clean();
