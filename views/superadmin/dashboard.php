<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/SuperAdminController.php';
Auth::requireRole(['super_admin']);

$controller = new SuperAdminController();
$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_admin'])) {
        $msg = $controller->addAdmin();
    } elseif (isset($_POST['toggle_admin'])) {
        if ($_POST['action'] === 'disable') {
            $controller->disableAdmin((int) $_POST['admin_id']);
        } else {
            $controller->enableAdmin((int) $_POST['admin_id']);
        }
    }
}

$admins = $controller->listAdmins();
$pageTitle = 'Super Admin - THE SPARKS';
include __DIR__ . '/../layouts/header.php';
?>
<div class="container py-4">
    <h4 class="mb-4">Super Admin — Manage Staff Accounts</h4>
    <p class="text-muted small">As Super Admin, you are the only role permitted to create Admin accounts. Admins cannot self-register.</p>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card p-3">
                <h6>Add New Admin</h6>
                <?php if ($msg): ?>
                    <?php if ($msg['success']): ?>
                        <div class="alert alert-success py-2 small">Admin account created.</div>
                    <?php else: ?>
                        <div class="alert alert-danger py-2 small">
                            <?php foreach (($msg['errors'] ?? [$msg['message'] ?? 'Failed.']) as $e): ?>
                                <div><?= htmlspecialchars($e) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="add_admin" value="1">
                    <div class="mb-2"><input class="form-control form-control-sm" name="full_name" placeholder="Full name" required></div>
                    <div class="mb-2"><input type="email" class="form-control form-control-sm" name="email" placeholder="Email" required></div>
                    <div class="mb-2"><input type="password" class="form-control form-control-sm" name="password" placeholder="Temporary password" required minlength="6"></div>
                    <button class="btn btn-sm btn-dark w-100" type="submit">Create Admin</button>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card p-3">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($admins as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['full_name']) ?></td>
                            <td><?= htmlspecialchars($a['email']) ?></td>
                            <td><span class="badge bg-dark"><?= htmlspecialchars($a['role']) ?></span></td>
                            <td><span class="badge bg-<?= $a['status'] === 'active' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($a['status']) ?></span></td>
                            <td>
                                <?php if ($a['role'] === 'admin'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="toggle_admin" value="1">
                                        <input type="hidden" name="admin_id" value="<?= $a['admin_id'] ?>">
                                        <input type="hidden" name="action" value="<?= $a['status'] === 'active' ? 'disable' : 'enable' ?>">
                                        <button class="btn btn-sm btn-outline-<?= $a['status'] === 'active' ? 'danger' : 'success' ?>">
                                            <?= $a['status'] === 'active' ? 'Disable' : 'Enable' ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <a href="<?= BASE_URL ?>views/admin/dashboard.php" class="btn btn-sm btn-outline-primary mt-3">Go to Admin Dashboard (Products/Orders/Repairs)</a>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
