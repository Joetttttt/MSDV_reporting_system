<?php
session_start();
require_once '../../db/connection.php';
if (!isset($_SESSION['user_id'])) { http_response_code(403); exit; }

$action = $_GET['action'] ?? '';
if ($action === 'read') {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    echo json_encode(['ok'=>true]);
}
?>