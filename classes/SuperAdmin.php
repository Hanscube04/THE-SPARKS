<?php
require_once __DIR__ . '/Admin.php';

/**
 * SuperAdmin
 * INHERITANCE: extends Admin (which itself extends Person) -> multi-level inheritance.
 * POLYMORPHISM: overrides getRole() and getDashboardUrl() again with its own values.
 * This is the ONLY class permitted to create new Admin accounts, enforcing
 * the assignment's rule that admins cannot self-register.
 */
class SuperAdmin extends Admin
{
    public function __construct(string $fullName, string $email, string $passwordHash = '')
    {
        parent::__construct($fullName, $email, $passwordHash);
        $this->role = 'super_admin';
    }

    public function getRole(): string
    {
        return 'super_admin';
    }

    public function getDashboardUrl(): string
    {
        return '/views/superadmin/dashboard.php';
    }

    /**
     * Creates a new Admin account. Only callable by an object of type SuperAdmin.
     */
    public function createAdmin(string $fullName, string $email, string $plainPassword): array
    {
        $check = $this->db->prepare('SELECT admin_id FROM admins WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) {
            return ['success' => false, 'message' => 'An admin with this email already exists.'];
        }

        $hash = password_hash($plainPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            'INSERT INTO admins (full_name, email, password_hash, role, created_by, status)
             VALUES (?, ?, ?, "admin", ?, "active")'
        );
        $stmt->execute([$fullName, $email, $hash, $this->id]);

        return ['success' => true, 'message' => 'Admin account created successfully.', 'admin_id' => (int) $this->db->lastInsertId()];
    }

    public function disableAdmin(int $adminId): bool
    {
        $stmt = $this->db->prepare('UPDATE admins SET status = "disabled" WHERE admin_id = ? AND role = "admin"');
        return $stmt->execute([$adminId]);
    }

    public function enableAdmin(int $adminId): bool
    {
        $stmt = $this->db->prepare('UPDATE admins SET status = "active" WHERE admin_id = ? AND role = "admin"');
        return $stmt->execute([$adminId]);
    }
}
