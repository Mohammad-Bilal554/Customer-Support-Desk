<?php
declare(strict_types=1);
namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;
use App\Models\Permission;

class PermissionMiddleware
{
    private string $permissionKey;

    public function __construct(string $permissionKey = '')
    {
        $this->permissionKey = $permissionKey;
    }

    public function handle(Request $request, callable $next): void
    {
        $session = Session::getInstance();

        if (!$session->isLoggedIn()) {
            $session->setFlash('intended_url', $request->fullUrl());
            if ($request->isAjax() || $request->isJson()) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unauthenticated.']);
                return;
            }
            redirect(url('auth/login'));
        }

        $user = $session->getUser();
        $role = $user['role'] ?? 'client';

        if (!empty($this->permissionKey) && !Permission::roleHas($role, $this->permissionKey)) {
            if ($request->isAjax() || $request->isJson()) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Forbidden. You do not have permission for this action.']);
                return;
            }

            http_response_code(403);
            $p = base_path('resources/views/errors/403.php');
            if (file_exists($p)) {
                include $p;
            } else {
                echo '<h1>403 Forbidden</h1><p>You do not have permission to access this page.</p>';
            }
            return;
        }

        $next();
    }
}
