<?php
/**
 * create_first_superadmin.php
 * Run this ONCE (via CLI: `php create_first_superadmin.php`) to seed the very
 * first Super Admin account. After that, all further admin accounts are
 * created through the Super Admin dashboard — never through this script or
 * any public form. Delete or restrict this file after first use in production.
 */
require_once __DIR__ . '/../config/config.php';

$fullName = 'System Super Admin';
$email = 'Groly@gmail.com';
$plainPassword = 'Ngeleja@12'; // change immediately after first login

$db = Database::getInstance()->getConnection();
$check = $db->prepare('SELECT admin_id FROM admins WHERE email = ?');
$check->execute([$email]);

if ($check->fetch()) {
    echo "A super admin with this email already exists.\n";
    exit;
}

$hash = password_hash($plainPassword, PASSWORD_BCRYPT);
$stmt = $db->prepare(
    'INSERT INTO admins (full_name, email, password_hash, role, status) VALUES (?, ?, ?, "super_admin", "active")'
);
$stmt->execute([$fullName, $email, $hash]);

echo "Super Admin created.\nEmail: {$email}\nPassword: {$plainPassword}\nCHANGE THIS PASSWORD IMMEDIATELY.\n";
