<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'THE SPARKS - Computer Sales, Maintenance & Repair' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .navbar-brand { font-weight: 700; letter-spacing: .5px; }
        .brand-accent { color: #0d6efd; }
        .card { border: none; box-shadow: 0 2px 10px rgba(0,0,0,.06); border-radius: .75rem; }
        .sidebar { min-height: calc(100vh - 56px); background: #101828; }
        .sidebar a { color: #cbd5e1; }
        .sidebar a.active, .sidebar a:hover { color: #fff; background: #1d2939; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark" style="background:#0b1220;">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="<?= BASE_URL ?>index.php">THE <span class="brand-accent">SPARKS</span></a>
        <div class="d-flex">
            <?php if (Auth::isLoggedIn()): ?>
                <span class="text-light me-3 align-self-center small">
                    <?= htmlspecialchars(Auth::user()['name']) ?> (<?= htmlspecialchars(Auth::role()) ?>)
                </span>
                <a href="<?= BASE_URL ?>controllers/logout.php" class="btn btn-sm btn-outline-light">Logout</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>views/public/login.php" class="btn btn-sm btn-outline-light me-2">Login</a>
                <a href="<?= BASE_URL ?>views/public/register.php" class="btn btn-sm btn-primary">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
