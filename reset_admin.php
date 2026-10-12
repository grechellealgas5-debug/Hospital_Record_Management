<?php
// reset_admin.php - run ONCE in the browser to (re)create the admin account.
// It hashes the password with this PHP install, so login is guaranteed to work.
// DELETE THIS FILE after using it.
require_once 'config.php';

$email = 'admin@test.com';
$pass  = 'Admin@123';
$hash  = password_hash($pass, PASSWORD_DEFAULT);

$stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    $stmt = $db->prepare("UPDATE users SET password = ?, role = 'admin' WHERE email = ?");
    $stmt->bind_param('ss', $hash, $email);
} else {
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES ('Admin', ?, ?, 'admin')");
    $stmt->bind_param('ss', $email, $hash);
}
$stmt->execute();

echo "Done. Log in with $email / $pass, then delete reset_admin.php.";
