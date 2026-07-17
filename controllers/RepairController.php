<?php
require_once __DIR__ . '/../config/config.php';

/**
 * RepairController - the "maintenance and repair" side of THE SPARKS.
 */
class RepairController
{
    private RepairRequest $repairModel;

    public function __construct()
    {
        $this->repairModel = new RepairRequest();
    }

    public function submit(): array
    {
        Auth::requireRole(['user']);

        $deviceType = trim($_POST['device_type'] ?? '');
        $issue = trim($_POST['issue_description'] ?? '');

        $errors = [];
        if ($deviceType === '') $errors[] = 'Device type is required.';
        if (strlen($issue) < 10) $errors[] = 'Please describe the issue in more detail (min 10 characters).';

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $id = $this->repairModel->create(Auth::id(), [
            'device_type'        => $deviceType,
            'brand_model'        => trim($_POST['brand_model'] ?? ''),
            'issue_description'  => $issue,
        ]);

        return ['success' => true, 'repair_id' => $id];
    }

    public function assign(int $repairId): array
    {
        Auth::requireRole(['admin', 'super_admin']);
        $technicianId = (int) ($_POST['technician_id'] ?? Auth::id());
        return ['success' => $this->repairModel->assignTechnician($repairId, $technicianId)];
    }

    public function updateStatus(int $repairId): array
    {
        Auth::requireRole(['admin', 'super_admin']);
        $status = $_POST['status'] ?? '';
        $notes = trim($_POST['notes'] ?? '');
        $cost = isset($_POST['estimated_cost']) && $_POST['estimated_cost'] !== '' ? (float) $_POST['estimated_cost'] : null;

        $allowed = ['submitted', 'diagnosing', 'in_progress', 'awaiting_parts', 'completed', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            return ['success' => false, 'message' => 'Invalid status.'];
        }

        $ok = $this->repairModel->updateStatus($repairId, $status, $notes, Auth::id(), $cost);
        return ['success' => $ok];
    }

    public function myRepairs(): array
    {
        Auth::requireRole(['user']);
        return $this->repairModel->allForUser(Auth::id());
    }

    public function allRepairs(): array
    {
        Auth::requireRole(['admin', 'super_admin']);
        return $this->repairModel->all();
    }

    public function search(string $keyword): array
    {
        Auth::requireRole(['admin', 'super_admin']);
        return $keyword === '' ? $this->repairModel->all() : $this->repairModel->search($keyword);
    }
}
