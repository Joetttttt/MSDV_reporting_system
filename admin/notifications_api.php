<?php
/*
|--------------------------------------------------------------------------
| notifications_api.php
| Place this file in: admin/notifications_api.php
|--------------------------------------------------------------------------
*/

session_start();
include("../config/database.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? 'get_notifications';

/* ─────────────────────────────────────────
   ACTION: get_notifications
───────────────────────────────────────── */
if ($action === 'get_notifications') {

    $query  = "SELECT * FROM notifications ORDER BY created_at DESC LIMIT 50";
    $result = mysqli_query($conn, $query);

    $notifications = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $notifications[] = $row;
    }

    echo json_encode(['notifications' => $notifications]);
    exit();
}

/* ─────────────────────────────────────────
   ACTION: unread_count
───────────────────────────────────────── */
if ($action === 'unread_count') {

    $new_violations = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT COUNT(*) c FROM notifications WHERE type='new_violation' AND created_at >= NOW() - INTERVAL 7 DAY")
    )['c'];

    echo json_encode([
        'new_violations' => $new_violations,
    ]);
    exit();
}

echo json_encode(['error' => 'Unknown action']);