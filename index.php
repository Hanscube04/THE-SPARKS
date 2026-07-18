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
                    <div class="ratio ratio-1x1 bg-light d-flex align-items-center justify-content-center" style="cursor:pointer;"
                         onclick='openProductView(<?= json_encode([
                            'name'  => $p['product_name'],
                            'category' => $p['category_name'],
                            'price' => number_format($p['price'], 2),
                            'stock' => (int) $p['stock_quantity'],
                            'description' => $p['description'] ?? '',
                            'specifications' => $p['specifications'] ?? '',
                            'image' => !empty($p['image_path']) ? BASE_URL . $p['image_path'] : null,
                         ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
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
                        <p class="small text-muted mb-2">Stock: <?= (int) $p['stock_quantity'] ?></p>
                        <button type="button" class="btn btn-sm btn-outline-primary w-100"
                            onclick='openProductView(<?= json_encode([
                                'name'  => $p['product_name'],
                                'category' => $p['category_name'],
                                'price' => number_format($p['price'], 2),
                                'stock' => (int) $p['stock_quantity'],
                                'description' => $p['description'] ?? '',
                                'specifications' => $p['specifications'] ?? '',
                                'image' => !empty($p['image_path']) ? BASE_URL . $p['image_path'] : null,
                             ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                            <i class="bi bi-eye"></i> View
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- PRODUCT VIEW / ZOOM MODAL -->
<div class="modal fade" id="productViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="pv_name">Product</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div id="pv_image_wrap" class="bg-light rounded-3 d-flex align-items-center justify-content-center overflow-hidden" style="height:320px; cursor:zoom-in;">
                            <img id="pv_image" src="" alt="" style="max-width:100%; max-height:100%; object-fit:contain; transition: transform .25s ease;">
                        </div>
                        <p class="small text-muted mt-2 mb-0"><i class="bi bi-zoom-in"></i> Bofya picha ku-zoom</p>
                    </div>
                    <div class="col-md-6">
                        <span id="pv_category" class="badge bg-secondary mb-2"></span>
                        <p class="fw-bold text-primary fs-5 mb-1">TZS <span id="pv_price"></span></p>
                        <p class="small text-muted mb-3">Stock: <span id="pv_stock"></span></p>
                        <h6 class="mb-1">Description</h6>
                        <p id="pv_description" class="small mb-3">-</p>
                        <h6 class="mb-1">Specifications</h6>
                        <p id="pv_specifications" class="small" style="white-space:pre-line;">-</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function openProductView(p) {
    document.getElementById('pv_name').textContent = p.name;
    document.getElementById('pv_category').textContent = p.category;
    document.getElementById('pv_price').textContent = p.price;
    document.getElementById('pv_stock').textContent = p.stock;
    document.getElementById('pv_description').textContent = p.description ? p.description : 'Hakuna maelezo.';
    document.getElementById('pv_specifications').textContent = p.specifications ? p.specifications : 'Hakuna specifications zilizowekwa.';

    var img = document.getElementById('pv_image');
    img.src = p.image ? p.image : '';
    img.style.display = p.image ? 'block' : 'none';
    img.style.transform = 'scale(1)';
    img.dataset.zoomed = '0';

    new bootstrap.Modal(document.getElementById('productViewModal')).show();
}

document.getElementById('pv_image_wrap').addEventListener('click', function () {
    var img = document.getElementById('pv_image');
    if (!img.src) return;
    var zoomed = img.dataset.zoomed === '1';
    img.style.transform = zoomed ? 'scale(1)' : 'scale(2)';
    img.style.cursor = zoomed ? 'zoom-in' : 'zoom-out';
    img.dataset.zoomed = zoomed ? '0' : '1';
});
</script>
<?php include __DIR__ . '/views/layouts/footer.php'; ?>
