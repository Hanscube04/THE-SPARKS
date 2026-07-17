<?php
require_once __DIR__ . '/../config/config.php';

/**
 * AuthController
 * Handles: customer self-registration, user login, admin/super_admin login.
 * Admins are deliberately NOT registrable here -> enforces assignment rule
 * "user only can self-register, admin must be added by super admin".
 */
class AuthController
{
    public function registerUser(): array
    {
        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $address  = trim($_POST['address'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        // ---- Form validation ----
        $errors = [];
        if (strlen($fullName) < 3) $errors[] = 'Full name must be at least 3 characters.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
        if (!preg_match('/^(0|\+255)[67]\d{8}$/', $phone)) $errors[] = 'Enter a valid East African phone number.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($password !== $confirm) $errors[] = 'Passwords do not match.';

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $user = new User($fullName, $email, $phone, $address);
        $result = $user->register($password);
        return $result;
    }

    public function loginUser(): array
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $row = User::login($email, $password);
        if (!$row) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }
        Auth::loginUser($row);
        return ['success' => true];
    }

    public function loginAdmin(): array
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $row = Admin::login($email, $password);
        if (!$row) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }
        Auth::loginAdmin($row);
        return ['success' => true, 'role' => $row['role']];
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: ' . BASE_URL . 'views/public/login.php');
        exit;
    }
}
