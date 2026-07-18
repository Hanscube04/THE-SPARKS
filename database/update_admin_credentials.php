<?php
/**
 * update_admin_credentials.php
 * Use this ONLY if you already ran create_first_superadmin.php before (so a
 * super admin account already exists) and just want to change its login
 * email/password to the ones below. Run once via CLI: php update_admin_credentials.php
 * It does not touch any other data (products, orders, repairs, users, etc.).
 */
require_once __DIR__ . '/../config/config.php';

$oldEmail = 'Groly@gmail.com'; // the email currently used to log in
$newEmail = 'admin@gmail.com';
$newPassword = 'Admin@12';

$db = Database::getInstance()->getConnection();
$hash = password_hash($newPassword, PASSWORD_BCRYPT);

$stmt = $db->prepare('UPDATE admins SET email = ?, password_hash = ? WHERE email = ?');
$stmt->execute([$newEmail, $hash, $oldEmail]);

if ($stmt->rowCount() > 0) {
    echo "Admin credentials updated.\nEmail: {$newEmail}\nPassword: {$newPassword}\n";
} else {
    echo "No admin found with email {$oldEmail}. If you haven't seeded a super admin yet, run create_first_superadmin.php instead.\n";
}
