<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new AuthController();
    $result = $controller->registerUser();
    if ($result['success']) {
        header('Location: ' . BASE_URL . 'views/public/login.php?registered=1');
        exit;
    }
}
$pageTitle = 'Register - THE SPARKS';
include __DIR__ . '/../layouts/header.php';
?>
<div class="container py-5" style="max-width:520px;">
    <div class="card p-4">
        <h4 class="mb-1">Create your THE SPARKS account</h4>
        <p class="text-muted small mb-4">Customer registration — for purchasing products &amp; requesting repairs.</p>

        <?php if ($result && !$result['success']): ?>
            <div class="alert alert-danger py-2">
                <?php foreach (($result['errors'] ?? [$result['message'] ?? 'Registration failed.']) as $e): ?>
                    <div><?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control" required minlength="3" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Phone (e.g. 0712345678)</label>
                <input type="text" name="phone" class="form-control" required pattern="^(0|\+255)[67]\d{8}$" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Address (optional)</label>
                <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required minlength="6">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>
        <p class="text-center small mt-3 mb-0">Already have an account? <a href="login.php">Login</a></p>
        <p class="text-center small text-muted mt-1 mb-0">Staff? <a href="admin_login.php">Admin login</a></p>
    </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
