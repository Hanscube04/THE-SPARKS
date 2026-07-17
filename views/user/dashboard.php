<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/OrderController.php';
require_once __DIR__ . '/../../controllers/RepairController.php';
Auth::requireRole(['user']);

$orderController = new OrderController();
$repairController = new RepairController();
$repairMsg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_repair'])) {
    $repairMsg = $repairController->submit();
}

$myOrders = $orderController->myOrders();
$myRepairs = $repairController->myRepairs();

$pageTitle = 'My Dashboard - THE SPARKS';
include __DIR__ . '/../layouts/header.php';
?>
<div class="container py-4">
    <h4 class="mb-4">Welcome, <?= htmlspecialchars(Auth::user()['name']) ?></h4>

    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#orders">My Orders</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#repairs">My Repairs</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#newrepair">Request Repair</button></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>index.php">Shop Products</a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="orders">
            <div class="card p-3">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>#</th><th>Date</th><th>Total (TZS)</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($myOrders as $o): ?>
                        <tr>
                            <td>#<?= $o['order_id'] ?></td>
                            <td><?= $o['order_date'] ?></td>
                            <td><?= number_format($o['total_amount'], 2) ?></td>
                            <td><span class="badge bg-info text-dark"><?= htmlspecialchars($o['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($myOrders)): ?><tr><td colspan="4" class="text-muted text-center">No orders yet.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="repairs">
            <div class="card p-3">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>#</th><th>Device</th><th>Issue</th><th>Status</th><th>Est. Cost</th></tr></thead>
                    <tbody>
                    <?php foreach ($myRepairs as $r): ?>
                        <tr>
                            <td>#<?= $r['repair_id'] ?></td>
                            <td><?= htmlspecialchars($r['device_type']) ?></td>
                            <td><?= htmlspecialchars(mb_strimwidth($r['issue_description'], 0, 40, '...')) ?></td>
                            <td><span class="badge bg-warning text-dark"><?= htmlspecialchars($r['status']) ?></span></td>
                            <td><?= $r['estimated_cost'] ? number_format($r['estimated_cost'], 2) : '-' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($myRepairs)): ?><tr><td colspan="5" class="text-muted text-center">No repair requests yet.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="newrepair">
            <div class="card p-3" style="max-width:520px;">
                <?php if ($repairMsg): ?>
                    <?php if ($repairMsg['success']): ?>
                        <div class="alert alert-success py-2">Repair request #<?= $repairMsg['repair_id'] ?> submitted.</div>
                    <?php else: ?>
                        <div class="alert alert-danger py-2">
                            <?php foreach (($repairMsg['errors'] ?? [$repairMsg['message'] ?? 'Failed.']) as $e): ?>
                                <div><?= htmlspecialchars($e) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="submit_repair" value="1">
                    <div class="mb-3">
                        <label class="form-label">Device Type</label>
                        <select name="device_type" class="form-select" required>
                            <option value="">-- select --</option>
                            <option>Laptop</option><option>Desktop</option><option>Printer</option><option>Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Brand / Model</label>
                        <input type="text" name="brand_model" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Issue Description</label>
                        <textarea name="issue_description" class="form-control" rows="4" required minlength="10"></textarea>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Submit Request</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
