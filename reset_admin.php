<?php
require_once 'db/connection.php';

$newHash = password_hash('admin123', PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->execute([$newHash]);

echo "Admin password reset successfully!";
?>