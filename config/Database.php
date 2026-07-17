<?php
/**
 * Database.php
 * Implements the SINGLETON design pattern so that only ONE PDO connection
 * instance exists throughout the entire application lifecycle.
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $connection;

    // Update these to match your MySQL / AWS RDS credentials
    private string $host = 'localhost';
    private string $dbname = 'thesparks_db';
    private string $username = 'root';
    private string $password = '';

    // Private constructor -> prevents "new Database()" from outside the class (Encapsulation)
    private function __construct()
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
            $this->connection = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    // Prevent cloning of the instance
    private function __clone() {}

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}
