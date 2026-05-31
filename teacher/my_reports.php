<?php

session_start();

include("../config/database.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

if ($_SESSION['role'] != 'teacher') {
    header("Location: ../index.html");
    exit();
}

$fullname = $_SESSION['fullname'];

$query = "SELECT * FROM violations
          WHERE reported_by='$fullname'
          ORDER BY id DESC";

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">
<head>

<title>My Reports</title>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>

    :root {
        --navy:       #0d2254;
        --navy-mid:   #1a2f6e;
        --navy-light: #1f3a80;
        --red:        #c0192c;
        --red-dark:   #a5151f;
        --sidebar-w:  260px;
    }

    *, *::before, *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Barlow', sans-serif;
        background: #eef0f7;
        min-height: 100vh;
    }

    /* ── SIDEBAR ─────────────────────────────── */

    .sidebar {
        position: fixed;
        top: 0; left: 0;
        width: var(--sidebar-w);
        height: 100vh;
        background: var(--navy);
        display: flex;
        flex-direction: column;
        z-index: 1000;
        transition: transform 0.3s ease;
    }

    .sidebar-brand {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 28px 20px 20px;
        border-bottom: 1px solid rgba(255,255,255,0.08);
    }

    .sidebar-logo {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: var(--navy-light);
        border: 3px solid var(--red);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        overflow: hidden;
    }

    .sidebar-logo svg {
        width: 48px;
        height: 48px;
    }

    .sidebar-title {
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        text-align: center;
        line-height: 1.4;
    }

    .sidebar-role {
        color: var(--red);
        font-size: 10.5px;
        font-weight: 600;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        margin-top: 4px;
    }

    .sidebar-nav {
        flex: 1;
        padding: 20px 0;
        overflow-y: auto;
    }

    .nav-item a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 24px;
        color: rgba(255,255,255,0.7);
        text-decoration: none;
        font-size: 13.5px;
        font-weight: 500;
        letter-spacing: 0.02em;
        transition: background 0.15s, color 0.15s, border-left 0.15s;
        border-left: 3px solid transparent;
    }

    .nav-item a:hover,
    .nav-item a.active {
        background: rgba(255,255,255,0.06);
        color: #fff;
        border-left-color: var(--red);
    }

    .nav-item a svg {
        width: 17px;
        height: 17px;
        flex-shrink: 0;
        opacity: 0.75;
    }

    .nav-item a:hover svg,
    .nav-item a.active svg {
        opacity: 1;
    }

    .nav-divider {
        height: 1px;
        background: rgba(255,255,255,0.07);
        margin: 8px 20px;
    }

    .sidebar-footer {
        padding: 16px 0;
        border-top: 1px solid rgba(255,255,255,0.08);
    }

    /* ── MOBILE OVERLAY ──────────────────────── */

    .sidebar-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 999;
    }

    /* ── TOPBAR (mobile) ─────────────────────── */

    .topbar {
        display: none;
        position: sticky;
        top: 0;
        z-index: 998;
        background: var(--navy);
        padding: 14px 16px;
        align-items: center;
        gap: 14px;
        border-bottom: 3px solid var(--red);
    }

    .topbar-toggle {
        background: none;
        border: none;
        color: #fff;
        cursor: pointer;
        padding: 2px;
    }

    .topbar-title {
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    /* ── MAIN CONTENT ────────────────────────── */

    .main-content {
        margin-left: var(--sidebar-w);
        min-height: 100vh;
        padding: 32px 28px;
    }

    /* ── PAGE HEADER ─────────────────────────── */

    .page-header {
        background: var(--navy);
        border-radius: 10px 10px 0 0;
        padding: 22px 28px;
        display: flex;
        align-items: center;
        gap: 14px;
        border-left: 5px solid var(--red);
    }

    .page-header-icon {
        width: 42px;
        height: 42px;
        background: rgba(192,25,44,0.18);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .page-header-icon svg {
        width: 22px;
        height: 22px;
        color: #f87185;
    }

    .page-header h3 {
        color: #fff;
        font-size: 18px;
        font-weight: 700;
        letter-spacing: 0.03em;
        margin: 0;
    }

    .page-header p {
        color: rgba(255,255,255,0.5);
        font-size: 12.5px;
        margin: 2px 0 0;
    }

    /* ── TABLE CARD ──────────────────────────── */

    .table-card {
        background: #fff;
        border-radius: 0 0 10px 10px;
        box-shadow: 0 6px 32px rgba(13,34,84,0.10);
        overflow-x: auto;
    }

    /* ── TABLE ───────────────────────────────── */

    .mcc-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13.5px;
    }

    .mcc-table thead tr {
        background: #f0f2f9;
        border-bottom: 2px solid #dde1ee;
    }

    .mcc-table thead th {
        padding: 13px 16px;
        font-size: 10.5px;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--navy-mid);
        white-space: nowrap;
    }

    .mcc-table tbody tr {
        border-bottom: 1px solid #eef0f7;
        transition: background 0.15s;
    }

    .mcc-table tbody tr:last-child {
        border-bottom: none;
    }

    .mcc-table tbody tr:hover {
        background: #f5f7fc;
    }

    .mcc-table td {
        padding: 13px 16px;
        color: #374151;
        vertical-align: middle;
    }

    .td-id {
        font-weight: 700;
        color: var(--navy-mid);
        font-size: 13px;
    }

    .td-student-name {
        font-weight: 600;
        color: #1f2937;
    }

    .td-student-id {
        font-size: 11.5px;
        color: #9aa0b5;
        margin-top: 2px;
    }

    .td-violation {
        font-weight: 600;
        color: #374151;
    }

    .td-description {
        color: #6b7280;
        font-size: 13px;
        max-width: 220px;
    }

    .td-date {
        font-size: 12.5px;
        color: #6b7280;
        white-space: nowrap;
    }

    /* ── BADGES ──────────────────────────────── */

    .badge-mcc {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 11px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .badge-pending {
        background: #fff7ed;
        color: #b45309;
        border: 1px solid #fcd34d;
    }

    .badge-resolved {
        background: #f0fdf4;
        color: #166534;
        border: 1px solid #86efac;
    }

    /* ── BTN VIEW FILE ───────────────────────── */

    .btn-view {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        background: var(--navy);
        color: #fff;
        border: none;
        border-radius: 5px;
        font-family: 'Barlow', sans-serif;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        letter-spacing: 0.03em;
        transition: background 0.15s;
        white-space: nowrap;
    }

    .btn-view:hover {
        background: var(--navy-mid);
        color: #fff;
    }

    .no-file {
        font-size: 12px;
        color: #c4c9d9;
        font-style: italic;
    }

    /* ── EMPTY STATE ─────────────────────────── */

    .empty-state {
        text-align: center;
        padding: 56px 20px;
        color: #9aa0b5;
    }

    .empty-state svg {
        width: 48px;
        height: 48px;
        margin-bottom: 14px;
        opacity: 0.35;
    }

    .empty-state p {
        font-size: 14px;
        font-weight: 500;
    }

    /* ── RESPONSIVE ──────────────────────────── */

    @media (max-width: 767.98px) {

        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.open {
            transform: translateX(0);
        }

        .sidebar-overlay.open {
            display: block;
        }

        .topbar {
            display: flex;
        }

        .main-content {
            margin-left: 0;
            padding: 20px 14px 32px;
        }

        .page-header {
            padding: 16px 18px;
        }

        .mcc-table thead th,
        .mcc-table td {
            padding: 10px 12px;
        }

    }

</style>

</head>
<body>

<!-- MOBILE TOPBAR -->
<div class="topbar" id="topbar">
    <button class="topbar-toggle" id="sidebarToggle" aria-label="Open menu">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>
    <span class="topbar-title">MCC Discipline System</span>
</div>

<!-- SIDEBAR OVERLAY -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">

    <div class="sidebar-brand">
        <div class="sidebar-logo">
            <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="46" fill="#0d2254" stroke="#c8a227" stroke-width="2"/>
                <path d="M50 18 L72 28 L72 58 Q72 76 50 84 Q28 76 28 58 L28 28 Z" fill="#f0f0f0" stroke="#c8a227" stroke-width="1.5"/>
                <ellipse cx="50" cy="32" rx="5" ry="8" fill="#f5a623" opacity="0.9"/>
                <ellipse cx="50" cy="36" rx="3" ry="5" fill="#e05c00"/>
                <rect x="48" y="38" width="4" height="14" rx="1" fill="#8B5E3C"/>
                <rect x="37" y="54" width="26" height="16" rx="2" fill="#1a3a8f"/>
                <line x1="50" y1="54" x2="50" y2="70" stroke="#fff" stroke-width="1.2"/>
                <text x="50" y="78" text-anchor="middle" font-size="6" fill="#c8a227" font-weight="bold" font-family="sans-serif">2005</text>
            </svg>
        </div>
        <div class="sidebar-title">MCC Discipline<br>System</div>
        <div class="sidebar-role">Teacher Panel</div>
    </div>

    <nav class="sidebar-nav">

        <div class="nav-item">
            <a href="report_violation.php">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
                Report Violation
            </a>
        </div>

        <div class="nav-item">
            <a href="my_reports.php" class="active">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
                </svg>
                My Reports
            </a>
        </div>

        <div class="nav-divider"></div>

        <div class="nav-item sidebar-footer">
            <a href="../auth/logout.php">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                </svg>
                Logout
            </a>
        </div>

    </nav>

</aside>

<!-- MAIN CONTENT -->
<main class="main-content">

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
            </svg>
        </div>
        <div>
            <h3>My Submitted Reports</h3>
            <p>All violation reports you have filed</p>
        </div>
    </div>

    <!-- Table Card -->
    <div class="table-card">

        <table class="mcc-table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Violation</th>
                    <th>Description</th>
                    <th>Evidence</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>

            <tbody>

            <?php
            $has_rows = false;
            while($row = mysqli_fetch_assoc($result)):
                $has_rows = true;
            ?>

                <tr>

                    <td class="td-id">#<?= $row['id']; ?></td>

                    <td>
                        <div class="td-student-name"><?= htmlspecialchars($row['student_name']); ?></div>
                        <div class="td-student-id"><?= htmlspecialchars($row['student_id']); ?></div>
                    </td>

                    <td class="td-violation"><?= htmlspecialchars($row['violation_type']); ?></td>

                    <td class="td-description"><?= htmlspecialchars($row['description']); ?></td>

                    <td>
                        <?php if($row['evidence'] != ""): ?>
                            <a href="../uploads/<?= $row['evidence']; ?>" target="_blank" class="btn-view">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                View File
                            </a>
                        <?php else: ?>
                            <span class="no-file">No file</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php if($row['status'] == 'Pending'): ?>
                            <span class="badge-mcc badge-pending">
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path stroke-linecap="round" d="M12 6v6l4 2"/>
                                </svg>
                                Pending
                            </span>
                        <?php else: ?>
                            <span class="badge-mcc badge-resolved">
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                </svg>
                                Resolved
                            </span>
                        <?php endif; ?>
                    </td>

                    <td class="td-date"><?= htmlspecialchars($row['created_at']); ?></td>

                </tr>

            <?php endwhile; ?>

            <?php if(!$has_rows): ?>
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                            </svg>
                            <p>No reports submitted yet.</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>

            </tbody>

        </table>

    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* MOBILE SIDEBAR TOGGLE */
const sidebar        = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');
const sidebarToggle  = document.getElementById('sidebarToggle');

sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    sidebarOverlay.classList.toggle('open');
});

sidebarOverlay.addEventListener('click', () => {
    sidebar.classList.remove('open');
    sidebarOverlay.classList.remove('open');
});
</script>

</body>
</html>