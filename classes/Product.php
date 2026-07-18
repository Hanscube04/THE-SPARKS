<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * Product
 * Represents laptops, desktops, spare parts, accessories sold by THE SPARKS.
 * Handles full CRUD + search functionality required by the assignment.
 */
class Product
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO products (category_id, product_name, description, specifications, price, stock_quantity, image_path, added_by, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, "available")'
        );
        $stmt->execute([
            $data['category_id'],
            $data['product_name'],
            $data['description'],
            $data['specifications'] ?? null,
            $data['price'],
            $data['stock_quantity'],
            $data['image_path'] ?? null,
            $data['added_by'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $productId, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE products SET category_id = ?, product_name = ?, description = ?, specifications = ?, price = ?, stock_quantity = ?, image_path = COALESCE(?, image_path)
             WHERE product_id = ?'
        );
        return $stmt->execute([
            $data['category_id'],
            $data['product_name'],
            $data['description'],
            $data['specifications'] ?? null,
            $data['price'],
            $data['stock_quantity'],
            $data['image_path'] ?? null,
            $productId,
        ]);
    }

    public function delete(int $productId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM products WHERE product_id = ?');
        return $stmt->execute([$productId]);
    }

    public function findById(int $productId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, c.category_name FROM products p
             JOIN categories c ON p.category_id = c.category_id
             WHERE p.product_id = ?'
        );
        $stmt->execute([$productId]);
        return $stmt->fetch() ?: null;
    }

    public function all(): array
    {
        return $this->db->query(
            'SELECT p.*, c.category_name FROM products p
             JOIN categories c ON p.category_id = c.category_id
             ORDER BY p.created_at DESC'
        )->fetchAll();
    }

    /** Search by name, description or category (form validation happens in the controller) */
    public function search(string $keyword): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, c.category_name FROM products p
             JOIN categories c ON p.category_id = c.category_id
             WHERE p.product_name LIKE ? OR p.description LIKE ? OR c.category_name LIKE ?
             ORDER BY p.product_name ASC'
        );
        $like = "%{$keyword}%";
        $stmt->execute([$like, $like, $like]);
        return $stmt->fetchAll();
    }

    public function reduceStock(int $productId, int $quantity): bool
    {
        $stmt = $this->db->prepare('UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ? AND stock_quantity >= ?');
        return $stmt->execute([$quantity, $productId, $quantity]);
    }

    public function lowStock(int $threshold = 5): array
    {
        $stmt = $this->db->prepare('SELECT * FROM products WHERE stock_quantity <= ? ORDER BY stock_quantity ASC');
        $stmt->execute([$threshold]);
        return $stmt->fetchAll();
    }
}
