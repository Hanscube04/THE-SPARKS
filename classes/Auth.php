<?php
/**
 * Auth
 * Central session/role guard used by every view. Kept separate from
 * User/Admin/SuperAdmin so that session concerns don't leak into the
 * domain model classes (separation of concerns).
 */
class Auth
{
    public static function loginUser(array $row): void
    {
        $_SESSION['auth'] = [
            'id'    => $row['user_id'],
            'name'  => $row['full_name'],
            'email' => $row['email'],
            'role'  => 'user',
        ];
    }

    public static function loginAdmin(array $row): void
    {
        $_SESSION['auth'] = [
            'id'    => $row['admin_id'],
            'name'  => $row['full_name'],
            'email' => $row['email'],
            'role'  => $row['role'], // 'admin' or 'super_admin'
        ];
    }

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['auth']);
    }

    public static function role(): ?string
    {
        return $_SESSION['auth']['role'] ?? null;
    }

    public static function id(): ?int
    {
        return $_SESSION['auth']['id'] ?? null;
    }

    public static function user(): ?array
    {
        return $_SESSION['auth'] ?? null;
    }

    /** Redirects away unless the current session matches one of the allowed roles. */
    public static function requireRole(array $allowedRoles, string $redirectTo = '/views/public/login.php'): void
    {
        if (!self::isLoggedIn() || !in_array(self::role(), $allowedRoles, true)) {
            header('Location: ' . $redirectTo);
            exit;
        }
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}
