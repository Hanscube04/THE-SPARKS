<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/Product.php';

/**
 * OrderModel
 * (Named OrderModel, not Order, to avoid clashing with MySQL's reserved word
 * and PHP's SPL naming conventions.)
 * Handles the sales side of THE SPARKS: creating orders with multiple line items
 * inside a DB transaction, and reporting.
 */
class OrderModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * @param int $userId
     * @param array $items [ ['product_id' => int, 'quantity' => int], ... ]
     */
    public function createOrder(int $userId, array $items): array
    {
        $productModel = new Product();
        $this->db->beginTransaction();

        try {
            $total = 0;
            $lineItems = [];

            foreach ($items as $item) {
                $product = $productModel->findById($item['product_id']);
                if (!$product || $product['stock_quantity'] < $item['quantity']) {
                    throw new Exception("Insufficient stock for {$product['product_name']}");
                }
                $subtotal = $product['price'] * $item['quantity'];
                $total += $subtotal;
                $lineItems[] = [
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_price' => $product['price'],
                ];
            }

            $stmt = $this->db->prepare('INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, "pending")');
            $stmt->execute([$userId, $total]);
            $orderId = (int) $this->db->lastInsertId();

            $itemStmt = $this->db->prepare(
                'INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)'
            );
            foreach ($lineItems as $line) {
                $itemStmt->execute([$orderId, $line['product_id'], $line['quantity'], $line['unit_price']]);
                $productModel->reduceStock($line['product_id'], $line['quantity']);
            }

            $this->db->commit();
            return ['success' => true, 'order_id' => $orderId, 'total' => $total];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateStatus(int $orderId, string $status, int $adminId): bool
    {
        $stmt = $this->db->prepare('UPDATE orders SET status = ?, handled_by = ? WHERE order_id = ?');
        return $stmt->execute([$status, $adminId, $orderId]);
    }

    public function findById(int $orderId): ?array
    {
        $stmt = $this->db->prepare('SELECT o.*, u.full_name, u.email FROM orders o JOIN users u ON o.user_id = u.user_id WHERE o.order_id = ?');
        $stmt->execute([$orderId]);
        return $stmt->fetch() ?: null;
    }

    public function getItems(int $orderId): array
    {
        $stmt = $this->db->prepare(
            'SELECT oi.*, p.product_name FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?'
        );
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function allForUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function all(): array
    {
        return $this->db->query(
            'SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.user_id ORDER BY o.order_date DESC'
        )->fetchAll();
    }

    /** Reporting: total sales revenue between two dates */
    public function salesReport(string $from, string $to): array
    {
        $stmt = $this->db->prepare(
            "SELECT DATE(order_date) AS day, COUNT(*) AS orders_count, SUM(total_amount) AS revenue
             FROM orders WHERE order_date BETWEEN ? AND ? AND status != 'cancelled'
             GROUP BY DATE(order_date) ORDER BY day ASC"
        );
        $stmt->execute([$from, $to]);
        return $stmt->fetchAll();
    }
}
