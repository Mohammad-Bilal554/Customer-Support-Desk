<?php
/**
 * Role Permissions View
 * @var array $groupedPermissions
 * @var array $matrix
 * @var string $activeRole
 */
use App\Core\Session;
use App\Core\Csrf;

$session    = Session::getInstance();
$csrfToken  = Csrf::getToken();
$title      = 'Role Permissions';

$roleLabels = [
    'employee'    => ['label' => 'Employee',    'icon' => 'bi-person-badge', 'bg' => '#dbeafe', 'color' => '#1d4ed8'],
    'client'      => ['label' => 'Client',      'icon' => 'bi-building',     'bg' => '#d1fae5', 'color' => '#065f46'],
    'super_admin' => ['label' => 'Super Admin', 'icon' => 'bi-shield-check', 'bg' => '#ede9fe', 'color' => '#6d28d9'],
];

ob_start();
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-shield-lock-fill text-primary me-2"></i>Role Permissions</h1>
        <p class="page-subtitle">Configure module permissions role-wise across the support portal.</p>
    </div>
</div>

<!-- Role Tabs -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <ul class="nav nav-pills custom-pills" id="roleTabs" role="tablist">
        <?php foreach ($roleLabels as $rKey => $rMeta): ?>
        <li class="nav-item" role="presentation">
            <a class="nav-link <?= $activeRole === $rKey ? 'active' : '' ?>"
               href="<?= url('admin/permissions?role=' . $rKey) ?>"
               style="font-weight:600;font-size:.875rem;">
                <i class="bi <?= $rMeta['icon'] ?> me-1.5"></i>
                <?= $rMeta['label'] ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>

    <div class="d-flex align-items-center gap-2">
        <span class="badge px-3 py-2" style="background:<?= $roleLabels[$activeRole]['bg'] ?>;color:<?= $roleLabels[$activeRole]['color'] ?>;font-size:.8rem;font-weight:600;">
            Editing: <?= $roleLabels[$activeRole]['label'] ?>
        </span>
    </div>
</div>

<?php if ($activeRole === 'super_admin'): ?>
<div class="alert alert-info border-0 shadow-sm mb-4 d-flex align-items-center gap-3">
    <i class="bi bi-info-circle-fill fs-4 flex-shrink-0 text-info"></i>
    <div style="font-size:.875rem;">
        <strong>Super Admin Info:</strong> Super Admin has unrestricted access to all portal features by default. Custom permission selections saved here will apply if specific module overrides are evaluated.
    </div>
</div>
<?php endif; ?>

<form action="<?= url('admin/permissions') ?>" method="POST" id="permissionsForm">
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
                    Updating permission matrix for <strong><?= $roleLabels[$activeRole]['label'] ?></strong>.
                </span>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= url('admin/permissions') ?>" class="btn btn-outline-secondary btn-sm px-3" style="border-radius:8px;">
                    Reset
                </a>
                <button type="submit" class="btn btn-primary btn-sm px-4" id="saveBtn" style="border-radius:8px;font-weight:600;">
                    <i class="bi bi-check-lg me-1"></i>Save Permissions
                </button>
            </div>
        </div>
    </div>
</form>

<style>
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
    box-shadow: 0 4px 12px rgba(13,110,253,0.25);
}
.hover-bg-light:hover {
    background-color: #f8fafc !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // Module Select All / Deselect All
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

    // Form submit AJAX
    const form = document.getElementById('permissionsForm');
    const saveBtn = document.getElementById('saveBtn');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const originalHtml = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(data => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalHtml;

            if (data.success) {
                SupportPortal.showToast(data.message || 'Permissions updated successfully!', 'success');
            } else {
                SupportPortal.showToast(data.message || 'Failed to save permissions.', 'danger');
            }
        })
        .catch(err => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalHtml;
            SupportPortal.showToast('Error saving permissions.', 'danger');
        });
    });
});
</script>

<?php
$content = ob_get_clean();
ob_start();
require base_path('resources/views/layouts/app.php');
echo ob_get_clean();
