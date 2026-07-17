<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new AuthController();
    $result = $controller->loginUser();
    if ($result['success']) {
        header('Location: ' . BASE_URL . 'views/user/dashboard.php');
        exit;
    }
    $error = $result['message'];
}
$pageTitle = 'Login - THE SPARKS';
include __DIR__ . '/../layouts/header.php';
?>
<div class="container py-5" style="max-width:420px;">
    <div class="card p-4">
        <h4 class="mb-1">Customer Login</h4>
        <p class="text-muted small mb-4">Welcome back to THE SPARKS.</p>

        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success py-2">Registration successful. Please log in.</div>
        <?php endif; ?>
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
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <p class="text-center small mt-3 mb-0">No account? <a href="register.php">Register here</a></p>
        <p class="text-center small text-muted mt-1 mb-0">Staff? <a href="admin_login.php">Admin login</a></p>
    </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
