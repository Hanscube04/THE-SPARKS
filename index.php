<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controllers/ProductController.php';

$keyword = trim($_GET['q'] ?? '');
$controller = new ProductController();
$products = $controller->search($keyword);

$pageTitle = 'THE SPARKS - Computer Sales, Maintenance & Repair';
include __DIR__ . '/views/layouts/header.php';
?>
<div class="container py-5">
    <div class="p-5 mb-4 rounded-4 text-white" style="background: linear-gradient(135deg,#0b1220,#0d6efd);">
        <h1 class="fw-bold">THE SPARKS</h1>
        <p class="mb-0">Your trusted partner for computer sales, maintenance and repair services.</p>
    </div>

    <form method="GET" class="mb-4">
        <div class="input-group" style="max-width:420px;">
            <input type="text" name="q" class="form-control" placeholder="Search products..." value="<?= htmlspecialchars($keyword) ?>">
            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Search</button>
        </div>
    </form>

    <div class="row g-4">
        <?php if (empty($products)): ?>
            <p class="text-muted">No products found.</p>
        <?php endif; ?>
        <?php foreach ($products as $p): ?>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="ratio ratio-1x1 bg-light d-flex align-items-center justify-content-center">
                        <?php if (!empty($p['image_path'])): ?>
                            <img src="<?= BASE_URL . htmlspecialchars($p['image_path']) ?>" class="w-100 h-100" style="object-fit:cover;">
                        <?php else: ?>
                            <i class="bi bi-pc-display fs-1 text-secondary"></i>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <span class="badge bg-secondary mb-2"><?= htmlspecialchars($p['category_name']) ?></span>
                        <h6 class="mb-1"><?= htmlspecialchars($p['product_name']) ?></h6>
                        <p class="fw-bold text-primary mb-1">TZS <?= number_format($p['price'], 2) ?></p>
                        <p class="small text-muted mb-0">Stock: <?= (int) $p['stock_quantity'] ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php include __DIR__ . '/views/layouts/footer.php'; ?>
