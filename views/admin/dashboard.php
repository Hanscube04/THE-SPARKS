<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/ProductController.php';
require_once __DIR__ . '/../../controllers/OrderController.php';
require_once __DIR__ . '/../../controllers/RepairController.php';
Auth::requireRole(['admin', 'super_admin']);

$productController = new ProductController();
$orderController = new OrderController();
$repairController = new RepairController();

$msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $msg = $productController->store();
    } elseif (isset($_POST['update_product'])) {
        $msg = $productController->update((int) $_POST['product_id']);
    } elseif (isset($_POST['delete_product'])) {
        $productController->destroy((int) $_POST['product_id']);
    } elseif (isset($_POST['update_order_status'])) {
        $orderController->updateStatus((int) $_POST['order_id']);
    } elseif (isset($_POST['update_repair_status'])) {
        $repairController->updateStatus((int) $_POST['repair_id']);
    }
}

$db = Database::getInstance()->getConnection();
$categories = $db->query('SELECT * FROM categories')->fetchAll();
$products = (new Product())->all();
$orders = $orderController->allOrders();
$repairs = $repairController->allRepairs();

$pageTitle = 'Admin Dashboard - THE SPARKS';
include __DIR__ . '/../layouts/header.php';
?>
<div class="container-fluid py-4 px-4">
    <h4 class="mb-4">Admin Dashboard</h4>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#products">Products</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#orders">Orders</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#repairs">Repairs</button></li>
    </ul>

    <div class="tab-content">
        <!-- PRODUCTS -->
        <div class="tab-pane fade show active" id="products">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card p-3">
                        <h6>Add Product</h6>
                        <?php if ($msg && isset($msg['errors'])): ?>
                            <div class="alert alert-danger py-2 small"><?php foreach ($msg['errors'] as $e) echo htmlspecialchars($e) . '<br>'; ?></div>
                        <?php endif; ?>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="add_product" value="1">
                            <div class="mb-2"><input class="form-control form-control-sm" name="product_name" placeholder="Product name" required></div>
                            <div class="mb-2">
                                <select name="category_id" class="form-select form-select-sm" required>
                                    <?php foreach ($categories as $c): ?>
                                        <option value="<?= $c['category_id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-2"><textarea class="form-control form-control-sm" name="description" placeholder="Description" rows="2"></textarea></div>
                            <div class="mb-2"><textarea class="form-control form-control-sm" name="specifications" placeholder="Specifications (e.g. CPU, RAM, Storage, Screen size...)" rows="3"></textarea></div>
                            <div class="mb-2"><input type="number" step="0.01" class="form-control form-control-sm" name="price" placeholder="Price (TZS)" required></div>
                            <div class="mb-2"><input type="number" class="form-control form-control-sm" name="stock_quantity" placeholder="Stock qty" required></div>
                            <div class="mb-2"><input type="file" class="form-control form-control-sm" name="image" accept="image/*"></div>
                            <button class="btn btn-sm btn-primary w-100" type="submit">Add Product</button>
                        </form>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card p-3">
                        <table class="table table-sm align-middle">
                            <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td style="width:56px;">
                                        <?php if (!empty($p['image_path'])): ?>
                                            <img src="<?= BASE_URL . htmlspecialchars($p['image_path']) ?>" style="width:48px;height:48px;object-fit:cover;border-radius:.4rem;">
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center bg-light" style="width:48px;height:48px;border-radius:.4rem;"><i class="bi bi-pc-display text-secondary"></i></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($p['product_name']) ?></td>
                                    <td><?= htmlspecialchars($p['category_name']) ?></td>
                                    <td><?= number_format($p['price'], 2) ?></td>
                                    <td><?= (int) $p['stock_quantity'] ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($p['status']) ?></span></td>
                                    <td class="text-nowrap">
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-edit-product"
                                            data-id="<?= (int) $p['product_id'] ?>"
                                            data-name="<?= htmlspecialchars($p['product_name'], ENT_QUOTES) ?>"
                                            data-category="<?= (int) $p['category_id'] ?>"
                                            data-description="<?= htmlspecialchars($p['description'] ?? '', ENT_QUOTES) ?>"
                                            data-specifications="<?= htmlspecialchars($p['specifications'] ?? '', ENT_QUOTES) ?>"
                                            data-price="<?= htmlspecialchars($p['price'], ENT_QUOTES) ?>"
                                            data-stock="<?= (int) $p['stock_quantity'] ?>"
                                            title="Edit"><i class="bi bi-pencil-square"></i></button>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Una uhakika unataka kufuta bidhaa hii?');">
                                            <input type="hidden" name="delete_product" value="1">
                                            <input type="hidden" name="product_id" value="<?= (int) $p['product_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($products)): ?>
                                <tr><td colspan="7" class="text-muted text-center">No products yet.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- EDIT PRODUCT MODAL -->
        <div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h6 class="modal-title">Edit Product</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="update_product" value="1">
                            <input type="hidden" name="product_id" id="edit_product_id">
                            <div class="mb-2">
                                <label class="form-label small">Product name</label>
                                <input class="form-control form-control-sm" name="product_name" id="edit_product_name" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Category</label>
                                <select name="category_id" id="edit_category_id" class="form-select form-select-sm" required>
                                    <?php foreach ($categories as $c): ?>
                                        <option value="<?= $c['category_id'] ?>"><?= htmlspecialchars($c['category_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Description</label>
                                <textarea class="form-control form-control-sm" name="description" id="edit_description" rows="2"></textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Specifications</label>
                                <textarea class="form-control form-control-sm" name="specifications" id="edit_specifications" rows="3" placeholder="CPU, RAM, Storage, Screen size..."></textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Price (TZS)</label>
                                <input type="number" step="0.01" class="form-control form-control-sm" name="price" id="edit_price" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Stock quantity</label>
                                <input type="number" class="form-control form-control-sm" name="stock_quantity" id="edit_stock" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Replace image (optional)</label>
                                <input type="file" class="form-control form-control-sm" name="image" accept="image/*">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script>
        document.querySelectorAll('.btn-edit-product').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('edit_product_id').value = btn.dataset.id;
                document.getElementById('edit_product_name').value = btn.dataset.name;
                document.getElementById('edit_category_id').value = btn.dataset.category;
                document.getElementById('edit_description').value = btn.dataset.description;
                document.getElementById('edit_specifications').value = btn.dataset.specifications;
                document.getElementById('edit_price').value = btn.dataset.price;
                document.getElementById('edit_stock').value = btn.dataset.stock;
                new bootstrap.Modal(document.getElementById('editProductModal')).show();
            });
        });
        </script>

        <!-- ORDERS -->
        <div class="tab-pane fade" id="orders">
            <div class="card p-3">
                <table class="table table-sm align-middle">
                    <thead><tr><th>#</th><th>Customer</th><th>Total</th><th>Status</th><th>Update</th></tr></thead>
                    <tbody>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td>#<?= $o['order_id'] ?></td>
                            <td><?= htmlspecialchars($o['full_name']) ?></td>
                            <td><?= number_format($o['total_amount'], 2) ?></td>
                            <td><span class="badge bg-info text-dark"><?= htmlspecialchars($o['status']) ?></span></td>
                            <td>
                                <form method="POST" class="d-flex gap-1">
                                    <input type="hidden" name="update_order_status" value="1">
                                    <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                                    <select name="status" class="form-select form-select-sm">
                                        <?php foreach (['pending','confirmed','dispatched','completed','cancelled'] as $s): ?>
                                            <option value="<?= $s ?>" <?= $s === $o['status'] ? 'selected' : '' ?>><?= $s ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-sm btn-outline-primary">Save</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- REPAIRS -->
        <div class="tab-pane fade" id="repairs">
            <div class="card p-3">
                <table class="table table-sm align-middle">
                    <thead><tr><th>#</th><th>Customer</th><th>Device</th><th>Status</th><th>Update</th></tr></thead>
                    <tbody>
                    <?php foreach ($repairs as $r): ?>
                        <tr>
                            <td>#<?= $r['repair_id'] ?></td>
                            <td><?= htmlspecialchars($r['customer_name']) ?></td>
                            <td><?= htmlspecialchars($r['device_type']) ?></td>
                            <td><span class="badge bg-warning text-dark"><?= htmlspecialchars($r['status']) ?></span></td>
                            <td>
                                <form method="POST" class="d-flex gap-1">
                                    <input type="hidden" name="update_repair_status" value="1">
                                    <input type="hidden" name="repair_id" value="<?= $r['repair_id'] ?>">
                                    <select name="status" class="form-select form-select-sm">
                                        <?php foreach (['submitted','diagnosing','in_progress','awaiting_parts','completed','cancelled'] as $s): ?>
                                            <option value="<?= $s ?>" <?= $s === $r['status'] ? 'selected' : '' ?>><?= $s ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="number" step="0.01" name="estimated_cost" class="form-control form-control-sm" placeholder="Cost" style="width:90px;">
                                    <button class="btn btn-sm btn-outline-primary">Save</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
