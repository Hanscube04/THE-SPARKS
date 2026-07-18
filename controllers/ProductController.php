<?php
require_once __DIR__ . '/../config/config.php';

/**
 * ProductController
 * Admin-side product CRUD + public/customer-side browsing & search.
 */
class ProductController
{
    private Product $productModel;

    public function __construct()
    {
        $this->productModel = new Product();
    }

    public function store(): array
    {
        Auth::requireRole(['admin', 'super_admin']);

        $name  = trim($_POST['product_name'] ?? '');
        $price = $_POST['price'] ?? 0;
        $stock = $_POST['stock_quantity'] ?? 0;

        $errors = [];
        if (strlen($name) < 2) $errors[] = 'Product name is required.';
        if (!is_numeric($price) || $price <= 0) $errors[] = 'Price must be a positive number.';
        if (!is_numeric($stock) || $stock < 0) $errors[] = 'Stock quantity must be zero or more.';
        if (empty($_POST['category_id'])) $errors[] = 'Please select a category.';

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $imagePath = null;
        if (!empty($_FILES['image']['name'])) {
            $imagePath = $this->handleUpload($_FILES['image']);
        }

        $id = $this->productModel->create([
            'category_id'    => $_POST['category_id'],
            'product_name'   => $name,
            'description'    => trim($_POST['description'] ?? ''),
            'specifications' => trim($_POST['specifications'] ?? ''),
            'price'          => $price,
            'stock_quantity' => $stock,
            'image_path'     => $imagePath,
            'added_by'       => Auth::id(),
        ]);

        return ['success' => true, 'product_id' => $id];
    }

    public function update(int $productId): array
    {
        Auth::requireRole(['admin', 'super_admin']);

        $name  = trim($_POST['product_name'] ?? '');
        $price = $_POST['price'] ?? 0;
        $stock = $_POST['stock_quantity'] ?? 0;

        $errors = [];
        if (strlen($name) < 2) $errors[] = 'Product name is required.';
        if (!is_numeric($price) || $price <= 0) $errors[] = 'Price must be a positive number.';
        if (!is_numeric($stock) || $stock < 0) $errors[] = 'Stock quantity must be zero or more.';
        if (empty($_POST['category_id'])) $errors[] = 'Please select a category.';

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $imagePath = null;
        if (!empty($_FILES['image']['name'])) {
            $imagePath = $this->handleUpload($_FILES['image']);
        }

        $ok = $this->productModel->update($productId, [
            'category_id'    => $_POST['category_id'],
            'product_name'   => $name,
            'description'    => trim($_POST['description'] ?? ''),
            'specifications' => trim($_POST['specifications'] ?? ''),
            'price'          => $price,
            'stock_quantity' => $stock,
            'image_path'     => $imagePath,
        ]);

        return ['success' => $ok];
    }

    public function destroy(int $productId): array
    {
        Auth::requireRole(['admin', 'super_admin']);
        return ['success' => $this->productModel->delete($productId)];
    }

    public function search(string $keyword): array
    {
        return $keyword === '' ? $this->productModel->all() : $this->productModel->search($keyword);
    }

    private function handleUpload(array $file): ?string
    {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            return null;
        }
        $newName = uniqid('product_', true) . '.' . $ext;
        $destination = BASE_PATH . '/public/uploads/' . $newName;
        move_uploaded_file($file['tmp_name'], $destination);
        return 'uploads/' . $newName;
    }
}
