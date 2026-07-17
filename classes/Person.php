<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * Person (ABSTRACT CLASS)
 * Demonstrates ABSTRACTION: defines the common contract that every
 * "person" in the system (User, Admin, SuperAdmin) must implement,
 * without exposing implementation detail here.
 *
 * Demonstrates ENCAPSULATION: properties are protected/private and only
 * reachable through getters or controlled methods.
 */
abstract class Person
{
    protected int $id = 0;
    protected string $fullName;
    protected string $email;
    protected string $passwordHash;
    protected PDO $db;

    public function __construct(string $fullName, string $email, string $passwordHash = '')
    {
        $this->fullName = $fullName;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->db = Database::getInstance()->getConnection();
    }

    // Every subclass MUST define its own dashboard route (POLYMORPHISM entry point)
    abstract public function getDashboardUrl(): string;

    // Every subclass MUST define its own role label
    abstract public function getRole(): string;

    public function getId(): int
    {
        return $this->id;
    }

    /** Allows a freshly-loaded session/DB row to rehydrate the object's identity. */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    protected function setPassword(string $plainPassword): void
    {
        $this->passwordHash = password_hash($plainPassword, PASSWORD_BCRYPT);
    }

    public function verifyPassword(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->passwordHash);
    }
}
