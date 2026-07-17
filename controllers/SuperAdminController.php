<?php
require_once __DIR__ . '/../config/config.php';

/**
 * SuperAdminController
 * Enforces the assignment rule: "admin asiweze kujiregister, had aongezwe
 * na admin ambae ni super admin" -> only a logged-in super_admin can reach
 * these actions (Auth::requireRole guard on every method).
 */
class SuperAdminController
{
    public function addAdmin(): array
    {
        Auth::requireRole(['super_admin']);

        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $errors = [];
        if (strlen($fullName) < 3) $errors[] = 'Full name must be at least 3 characters.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Rebuild the acting SuperAdmin object from session to call the instance method
        $sessionAdmin = Auth::user();
        $superAdmin = new SuperAdmin($sessionAdmin['name'], $sessionAdmin['email']);
        $superAdmin->setId($sessionAdmin['id']);

        return $superAdmin->createAdmin($fullName, $email, $password);
    }

    public function disableAdmin(int $adminId): array
    {
        Auth::requireRole(['super_admin']);
        $sessionAdmin = Auth::user();
        $superAdmin = new SuperAdmin($sessionAdmin['name'], $sessionAdmin['email']);
        return ['success' => $superAdmin->disableAdmin($adminId)];
    }

    public function enableAdmin(int $adminId): array
    {
        Auth::requireRole(['super_admin']);
        $sessionAdmin = Auth::user();
        $superAdmin = new SuperAdmin($sessionAdmin['name'], $sessionAdmin['email']);
        return ['success' => $superAdmin->enableAdmin($adminId)];
    }

    public function listAdmins(): array
    {
        Auth::requireRole(['super_admin']);
        return Admin::all();
    }
}
