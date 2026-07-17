<?php
require_once __DIR__ . '/Person.php';
require_once __DIR__ . '/../config/Encryption.php';

/**
 * User (customer account)
 * Demonstrates INHERITANCE: extends the abstract Person class.
 * Demonstrates POLYMORPHISM: implements getDashboardUrl() and getRole()
 * differently from Admin/SuperAdmin.
 */
class User extends Person
{
    private string $phone;
    private ?string $address;
    private string $status;

    public function __construct(string $fullName, string $email, string $phone, ?string $address = null, string $passwordHash = '')
    {
        parent::__construct($fullName, $email, $passwordHash);
        $this->phone = $phone;
        $this->address = $address;
        $this->status = 'active';
    }

    // POLYMORPHISM: overrides abstract method from Person
    public function getDashboardUrl(): string
    {
        return '/views/user/dashboard.php';
    }

    public function getRole(): string
    {
        return 'user';
    }

    /**
     * Registers a new customer account.
     * Sensitive fields (phone, address) are AES-256-CBC encrypted
     * before being persisted, per assignment security requirements.
     */
    public function register(string $plainPassword): array
    {
        // duplicate email check
        $check = $this->db->prepare('SELECT user_id FROM users WHERE email = ?');
        $check->execute([$this->email]);
        if ($check->fetch()) {
            return ['success' => false, 'message' => 'Email already registered.'];
        }

        $this->setPassword($plainPassword);
        $encryptedPhone = Encryption::encrypt($this->phone);
        $encryptedAddress = $this->address ? Encryption::encrypt($this->address) : null;

        $stmt = $this->db->prepare(
            'INSERT INTO users (full_name, email, phone_encrypted, address_encrypted, password_hash)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$this->fullName, $this->email, $encryptedPhone, $encryptedAddress, $this->passwordHash]);
        $this->id = (int) $this->db->lastInsertId();

        return ['success' => true, 'message' => 'Registration successful.', 'user_id' => $this->id];
    }

    public static function login(string $email, string $password): ?array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT * FROM users WHERE email = ? AND status = "active"');
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
        $stmt = $db->prepare('SELECT * FROM users WHERE user_id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $row['phone'] = Encryption::decrypt($row['phone_encrypted']);
            $row['address'] = Encryption::decrypt($row['address_encrypted']);
        }
        return $row ?: null;
    }
}
