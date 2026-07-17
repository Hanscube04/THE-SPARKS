<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * RepairRequest
 * Handles the "maintenance and repair" side of THE SPARKS: customers submit
 * devices for repair, admins/technicians update status through a workflow,
 * and every change is recorded in repair_status_history (audit trail).
 */
class RepairRequest
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(int $userId, array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO repair_requests (user_id, device_type, brand_model, issue_description, status)
             VALUES (?, ?, ?, ?, "submitted")'
        );
        $stmt->execute([$userId, $data['device_type'], $data['brand_model'], $data['issue_description']]);
        $repairId = (int) $this->db->lastInsertId();

        $this->logStatus($repairId, 'submitted', 'Repair request submitted by customer.', null);
        return $repairId;
    }

    public function assignTechnician(int $repairId, int $adminId): bool
    {
        $stmt = $this->db->prepare('UPDATE repair_requests SET technician_id = ? WHERE repair_id = ?');
        return $stmt->execute([$adminId, $repairId]);
    }

    public function updateStatus(int $repairId, string $status, ?string $notes, int $adminId, ?float $estimatedCost = null): bool
    {
        $sql = 'UPDATE repair_requests SET status = ?';
        $params = [$status];

        if ($estimatedCost !== null) {
            $sql .= ', estimated_cost = ?';
            $params[] = $estimatedCost;
        }
        if ($status === 'completed') {
            $sql .= ', completed_at = NOW()';
        }
        $sql .= ' WHERE repair_id = ?';
        $params[] = $repairId;

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($params);

        $this->logStatus($repairId, $status, $notes, $adminId);
        return $result;
    }

    private function logStatus(int $repairId, string $status, ?string $notes, ?int $adminId): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO repair_status_history (repair_id, status, notes, updated_by) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$repairId, $status, $notes, $adminId]);
    }

    public function findById(int $repairId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, u.full_name AS customer_name, u.email AS customer_email, a.full_name AS technician_name
             FROM repair_requests r
             JOIN users u ON r.user_id = u.user_id
             LEFT JOIN admins a ON r.technician_id = a.admin_id
             WHERE r.repair_id = ?'
        );
        $stmt->execute([$repairId]);
        return $stmt->fetch() ?: null;
    }

    public function getHistory(int $repairId): array
    {
        $stmt = $this->db->prepare(
            'SELECT h.*, a.full_name AS updated_by_name FROM repair_status_history h
             LEFT JOIN admins a ON h.updated_by = a.admin_id
             WHERE h.repair_id = ? ORDER BY h.updated_at ASC'
        );
        $stmt->execute([$repairId]);
        return $stmt->fetchAll();
    }

    public function allForUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM repair_requests WHERE user_id = ? ORDER BY requested_at DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function all(): array
    {
        return $this->db->query(
            'SELECT r.*, u.full_name AS customer_name FROM repair_requests r
             JOIN users u ON r.user_id = u.user_id ORDER BY r.requested_at DESC'
        )->fetchAll();
    }

    public function search(string $keyword): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.*, u.full_name AS customer_name FROM repair_requests r
             JOIN users u ON r.user_id = u.user_id
             WHERE r.device_type LIKE ? OR r.issue_description LIKE ? OR u.full_name LIKE ?
             ORDER BY r.requested_at DESC'
        );
        $like = "%{$keyword}%";
        $stmt->execute([$like, $like, $like]);
        return $stmt->fetchAll();
    }
}
