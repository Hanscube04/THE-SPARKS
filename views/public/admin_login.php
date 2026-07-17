<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new AuthController();
    $result = $controller->loginAdmin();
    if ($result['success']) {
        $target = $result['role'] === 'super_admin'
            ? BASE_URL . 'views/superadmin/dashboard.php'
            : BASE_URL . 'views/admin/dashboard.php';
        header('Location: ' . $target);
        exit;
    }
    $error = $result['message'];
}
$pageTitle = 'Admin Login - THE SPARKS';
include __DIR__ . '/../layouts/header.php';
?>
<div class="container py-5" style="max-width:420px;">
    <div class="card p-4">
        <h4 class="mb-1">Staff Login</h4>
        <p class="text-muted small mb-4">Admin &amp; Super Admin access only. <br>
        <em>Note: staff accounts are created by a Super Admin — there is no self-registration for staff.</em></p>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-dark w-100">Login as Staff</button>
        </form>
        <p class="text-center small mt-3 mb-0"><a href="login.php">&larr; Back to customer login</a></p>
    </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
