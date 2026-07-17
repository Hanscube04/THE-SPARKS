<?php
require_once __DIR__ . '/Person.php';
require_once __DIR__ . '/../config/Encryption.php';

/**
 * Admin (staff account)
 * INHERITANCE: extends Person.
 * IMPORTANT (assignment rule): Admin accounts are NEVER self-registered.
 * They can only be created by an authenticated SuperAdmin via
 * SuperAdmin::createAdmin().
 */
class Admin extends Person
{
    protected string $role = 'admin';
    protected ?int $createdBy;
    protected string $status;

    public function __construct(string $fullName, string $email, string $passwordHash = '', ?int $createdBy = null)
    {
        parent::__construct($fullName, $email, $passwordHash);
        $this->createdBy = $createdBy;
        $this->status = 'active';
    }

    // POLYMORPHISM: different dashboard from User
    public function getDashboardUrl(): string
    {
        return '/views/admin/dashboard.php';
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public static function login(string $email, string $password): ?array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT * FROM admins WHERE email = ? AND status = "active"');
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        if ($row && password_verify($password, $row['password_hash'])) {
            return $row;
        }
        return null;
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT * FROM admins WHERE admin_id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function all(): array
    {
        $db = Database::getInstance()->getConnection();
        return $db->query('SELECT admin_id, full_name, email, role, status, created_at FROM admins ORDER BY created_at DESC')->fetchAll();
    }
}
