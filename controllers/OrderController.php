<?php
require_once __DIR__ . '/../config/config.php';

/**
 * OrderController - the "selling" side of THE SPARKS.
 */
class OrderController
{
    private OrderModel $orderModel;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
    }

    public function placeOrder(): array
    {
        Auth::requireRole(['user']);

        $items = json_decode($_POST['items'] ?? '[]', true);
        if (empty($items)) {
            return ['success' => false, 'message' => 'Cart is empty.'];
        }

        return $this->orderModel->createOrder(Auth::id(), $items);
    }

    public function updateStatus(int $orderId): array
    {
        Auth::requireRole(['admin', 'super_admin']);
        $status = $_POST['status'] ?? '';
        $allowed = ['pending', 'confirmed', 'dispatched', 'completed', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            return ['success' => false, 'message' => 'Invalid status.'];
        }
        return ['success' => $this->orderModel->updateStatus($orderId, $status, Auth::id())];
    }

    public function myOrders(): array
    {
        Auth::requireRole(['user']);
        return $this->orderModel->allForUser(Auth::id());
    }

    public function allOrders(): array
    {
        Auth::requireRole(['admin', 'super_admin']);
        return $this->orderModel->all();
    }
}
