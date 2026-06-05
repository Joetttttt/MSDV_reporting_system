<?php
session_start();
require_once '../db/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php'); exit;
}

// UPDATE STATUS
if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $daId      = (int)$_POST['da_id'];
    $newStatus = $_POST['new_status'];

    $curr = $pdo->prepare("SELECT * FROM disciplinary_actions WHERE id=?");
    $curr->execute([$daId]);
    $da = $curr->fetch();

    if ($da) {
        $allowed = true;
        // Status flow: pending -> ongoing -> completed (no going back)
        $flow = ['pending'=>0,'ongoing'=>1,'completed'=>2];
        if (($flow[$newStatus] ?? -1) <= ($flow[$da['status']] ?? 0)) {
            $allowed = false;
            $_SESSION['err'] = 'Cannot move status backwards.';
        }

        if ($allowed) {
            $startDate = $da['start_date'];
            $endDate   = $da['end_date'];

            if ($newStatus === 'ongoing' && !$startDate) {
                $startDate = date('Y-m-d');
            }
            if ($newStatus === 'completed' && !$endDate) {
                $endDate = date('Y-m-d');
            }

            $pdo->prepare("UPDATE disciplinary_actions SET status=?, start_date=?, end_date=? WHERE id=?")
                ->execute([$newStatus, $startDate, $endDate, $daId]);

            // Sync violation status
            $pdo->prepare("UPDATE violations SET status=? WHERE id=?")
                ->execute([$newStatus, $da['violation_id']]);

            // Notify student