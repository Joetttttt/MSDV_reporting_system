<?php

session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include("../config/database.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.html");
    exit();
}

/* TOTAL STUDENTS */
$totalStudentsQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM students");
$totalStudents = mysqli_fetch_assoc($totalStudentsQuery)['total'];

/* TOTAL VIOLATIONS */
$totalViolationsQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM violations");
$totalViolations = mysqli_fetch_assoc($totalViolationsQuery)['total'];

/* PENDING SANCTIONS */
$pendingQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM violations WHERE case_status='Pending'");
$pendingSanctions = mysqli_fetch_assoc($pendingQuery)['total'];

/* COMPLETED SANCTIONS */
$completedQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM violations WHERE case_status='Completed'");
$completedSanctions = mysqli_fetch_assoc($completedQuery)['total'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard — MCC</title>

<link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --navy:        #0d2254;
    --navy-mid:    #1a2f6e;
    --navy-light:  #1f3a80;
    --red:         #c0192c;
    --red-dark:    #a5151f;
    --sidebar-w:   260px;
    --bg:          #eef0f7;
    --surface:     #ffffff;
    --border:      #dde1ee;
    --text:        #1f2937;
    --text2:       #4b5563;
    --text3:       #9aa0b5;
}

html, body {
    height: 100%;
    font-family: 'Barlow', sans-serif;
    font-size: 14px;
    color: var(--text);
    background: var(--bg);
    -webkit-font-smoothing: antialiased;
}

/* ── SIDEBAR ─────────────────────────────────────────── */
.sidebar {
    width: var(--sidebar-w);
    background: var(--navy);
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0; left: 0;
    height: 100vh;
    overflow-y: auto;
    z-index: 100;
}

.sidebar::-webkit-scrollbar { width: 4px; }
.sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 4px; }

.sb-brand {
    padding: 22px 20px 18px;
    border-bottom: 1px solid rgba(255,255,255,.08);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.sb-logo {
    width: 64px; height: 64px;
    border-radius: 50%;
    background: var(--navy-light);
    border: 3px solid var(--red);
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
}

.sb-logo svg { width: 48px; height: 48px; }

.sb-brand-text { text-align: center; }
.sb-brand-text strong { display: block; font-size: 13px; font-weight: 700; color: #fff; letter-spacing: .08em; text-transform: uppercase; }
.sb-brand-text span  { font-size: 10.5px; color: var(--red); font-weight: 600; letter-spacing: .12em; text-transform: uppercase; margin-top: 3px; display: block; }

.sb-nav { flex: 1; padding: 14px 0 20px; }

.sb-section {
    font-size: 10px; font-weight: 700;
    letter-spacing: .12em; text-transform: uppercase;
    color: rgba(255,255,255,.3);
    padding: 14px 22px 5px;
}

.sb-link {
    display: flex; align-items: center; gap: 11px;
    padding: 11px 22px;
    color: rgba(255,255,255,.65);
    text-decoration: none;
    font-size: 13.5px; font-weight: 500;
    border-left: 3px solid transparent;
    transition: background .15s, color .15s, border-color .15s;
}

.sb-link:hover { background: rgba(255,255,255,.06); color: #fff; }
.sb-link.active { background: rgba(255,255,255,.08); color: #fff; border-left-color: var(--red); }
.sb-link i { width: 18px; text-align: center; font-size: 14px; opacity: .75; }
.sb-link.active i { opacity: 1; }
.sb-link.logout { color: #f87171; }
.sb-link.logout:hover { background: rgba(248,113,113,.08); color: #fca5a5; }

.sb-divider { height: 1px; background: rgba(255,255,255,.07); margin: 8px 20px; }

/* ── MAIN ─────────────────────────────────────────────── */
.main {
    margin-left: var(--sidebar-w);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* ── TOPBAR ───────────────────────────────────────────── */
.topbar {
    background: var(--navy);
    border-bottom: 3px solid var(--red);
    padding: 0 28px;
    height: 60px;
    display: flex; align-items: center; justify-content: space-between;
    position: sticky; top: 0; z-index: 50;
}

.topbar-left { display: flex; align-items: center; gap: 12px; }

.topbar-title { font-size: 17px; font-weight: 700; color: #fff; letter-spacing: .03em; }

.topbar-date {
    font-size: 11.5px; color: rgba(255,255,255,.5);
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.1);
    padding: 3px 11px; border-radius: 20px;
}

.topbar-right { display: flex; align-items: center; gap: 10px; }

.bell-btn {
    position: relative;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 8px;
    width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; color: rgba(255,255,255,.8); font-size: 15px;
    transition: background .15s;
}

.bell-btn:hover { background: rgba(255,255,255,.14); }

.bell-badge {
    position: absolute; top: -5px; right: -5px;
    background: var(--red); color: #fff;
    font-size: 9px; font-weight: 700;
    min-width: 16px; height: 16px; border-radius: 10px;
    padding: 0 3px; display: none; align-items: center; justify-content: center;
    border: 2px solid var(--navy);
}

.bell-badge.show { display: flex; }

.admin-chip {
    display: flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 20px; padding: 4px 13px 4px 5px;
}

.admin-avatar {
    width: 27px; height: 27px;
    background: var(--red);
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 11px; font-weight: 700;
}

.admin-chip span { font-size: 12.5px; font-weight: 600; color: rgba(255,255,255,.85); }

/* ── CONTENT ──────────────────────────────────────────── */
.content { padding: 26px 28px 40px; flex: 1; }

/* ── SECTION LABEL ────────────────────────────────────── */
.section-label {
    font-size: 10.5px; font-weight: 800;
    letter-spacing: .12em; text-transform: uppercase;
    color: var(--navy-mid);
    margin-bottom: 14px;
    display: flex; align-items: center; gap: 8px;
}

.section-label::before {
    content: '';
    width: 3px; height: 14px;
    background: var(--red); border-radius: 2px;
}

/* ── STAT CARDS ───────────────────────────────────────── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 26px;
}

.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 18px 20px 16px;
    box-shadow: 0 2px 8px rgba(13,34,84,.07);
    display: flex; align-items: center; gap: 16px;
    transition: box-shadow .2s, transform .2s;
    border-top: 3px solid transparent;
}

.stat-card:hover { box-shadow: 0 6px 20px rgba(13,34,84,.12); transform: translateY(-2px); }
.stat-card.blue  { border-top-color: #3b82f6; }
.stat-card.red   { border-top-color: var(--red); }
.stat-card.amber { border-top-color: #d97706; }
.stat-card.green { border-top-color: #16a34a; }

.stat-icon {
    width: 46px; height: 46px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 19px; flex-shrink: 0;
}

.stat-icon.blue  { background: #eff6ff; color: #2563eb; }
.stat-icon.red   { background: #fef2f2; color: var(--red); }
.stat-icon.amber { background: #fffbeb; color: #b45309; }
.stat-icon.green { background: #f0fdf4; color: #16a34a; }

.stat-info { flex: 1; min-width: 0; }
.stat-label { font-size: 11px; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; color: var(--text3); margin-bottom: 4px; }
.stat-value { font-size: 30px; font-weight: 700; color: var(--text); line-height: 1; }

.stat-card.blue  .stat-value { color: #2563eb; }
.stat-card.red   .stat-value { color: var(--red); }
.stat-card.amber .stat-value { color: #b45309; }
.stat-card.green .stat-value { color: #16a34a; }

/* ── CHARTS GRID ──────────────────────────────────────── */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

/* ── CHART CARD ───────────────────────────────────────── */
.chart-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(13,34,84,.07);
    overflow: hidden;
    transition: box-shadow .2s;
}

.chart-card:hover { box-shadow: 0 6px 20px rgba(13,34,84,.12); }

.chart-head {
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 10px;
    background: #f8f9fd;
}

.chart-head-left { flex: 1; min-width: 0; }

.chart-head h6 {
    font-size: 13.5px; font-weight: 700;
    color: var(--navy); margin: 0 0 2px;
}

.chart-head p {
    font-size: 11px; color: var(--text3); margin: 0;
}

/* Per-chart filter controls */
.chart-filter {
    display: flex; align-items: center; gap: 8px; flex-shrink: 0;
}

.chart-filter select {
    padding: 5px 28px 5px 10px;
    border: 1.5px solid var(--border);
    border-radius: 6px;
    background: #fff;
    color: var(--navy-mid);
    font-family: 'Barlow', sans-serif;
    font-size: 12px; font-weight: 600;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='11' height='8' fill='none'%3E%3Cpath d='M1 1l4.5 5 4.5-5' stroke='%231a2f6e' stroke-width='1.6' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 8px center;
    cursor: pointer;
    outline: none;
    transition: border-color .15s;
}

.chart-filter select:focus { border-color: var(--navy-mid); box-shadow: 0 0 0 3px rgba(26,47,110,.1); }

.chart-body { padding: 16px; }
.chart-body canvas { max-height: 220px; }

/* ── NOTIFICATION PANEL ───────────────────────────────── */
.notif-overlay {
    display: none; position: fixed; inset: 0; z-index: 900; background: transparent;
}
.notif-overlay.open { display: block; }

.notif-panel {
    display: none; position: fixed;
    top: 68px; right: 20px;
    width: 380px; max-height: 480px;
    background: var(--surface); border-radius: 12px;
    border: 1px solid var(--border);
    box-shadow: 0 20px 60px rgba(13,34,84,.18);
    z-index: 1000; flex-direction: column; overflow: hidden;
    animation: dropIn .18s ease;
}
.notif-panel.open { display: flex; }

@keyframes dropIn {
    from { opacity: 0; transform: translateY(-8px) scale(.98); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

.notif-head {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    background: var(--navy);
}

.notif-head h6 { font-size: 13.5px; font-weight: 700; color: #fff; }

.notif-head-actions { display: flex; gap: 4px; }

.notif-btn {
    font-size: 11.5px; color: rgba(255,255,255,.7);
    background: rgba(255,255,255,.1); border: none; cursor: pointer;
    font-family: 'Barlow', sans-serif; font-weight: 600;
    padding: 4px 10px; border-radius: 5px;
    transition: background .15s;
}
.notif-btn:hover { background: rgba(255,255,255,.18); color: #fff; }

.notif-scroll { overflow-y: auto; flex: 1; }
.notif-scroll::-webkit-scrollbar { width: 3px; }
.notif-scroll::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

.ni {
    display: flex; gap: 11px;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border);
    cursor: pointer; transition: background .12s;
}
.ni:last-child { border-bottom: none; }
.ni:hover { background: #f5f7fc; }
.ni.unread { background: #f0f3fb; }

.ni-icon {
    width: 36px; height: 36px; border-radius: 50%;
    background: #fffbeb;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0; margin-top: 1px;
}

.ni-body { flex: 1; min-width: 0; }
.ni-title { font-size: 12.5px; font-weight: 700; color: #b45309; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ni-msg { font-size: 12px; color: var(--text2); line-height: 1.45; margin-bottom: 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.ni-meta { display: flex; align-items: center; gap: 6px; font-size: 11px; color: var(--text3); }
.ni-chip { padding: 1px 7px; border-radius: 10px; font-size: 10px; font-weight: 700; text-transform: uppercase; background: #fffbeb; color: #b45309; }
.ni-dot { width: 7px; height: 7px; background: var(--red); border-radius: 50%; flex-shrink: 0; margin-top: 6px; }

.notif-empty { text-align: center; padding: 40px 20px; color: var(--text3); font-size: 13px; }
.notif-empty i { font-size: 32px; margin-bottom: 10px; display: block; opacity: .35; }

.skel-wrap { padding: 16px; }
.skel { height: 11px; border-radius: 6px; background: linear-gradient(90deg, #eef0f7 25%, #dde1ee 50%, #eef0f7 75%); background-size: 200% 100%; animation: shimmer 1.2s infinite; margin-bottom: 10px; }
@keyframes shimmer { to { background-position: -200% 0; } }

/* ── TOAST ─────────────────────────────────────────────── */
#toast-wrap { position: fixed; bottom: 22px; right: 22px; z-index: 2000; display: flex; flex-direction: column; gap: 8px; pointer-events: none; }

.notif-toast {
    background: var(--navy); color: #fff;
    padding: 11px 14px; border-radius: 10px;
    font-size: 13px; display: flex; align-items: flex-start; gap: 10px;
    max-width: 300px; pointer-events: all;
    transform: translateX(320px); opacity: 0;
    transition: all .28s cubic-bezier(.22,1,.36,1);
    border-left: 3px solid var(--red);
    box-shadow: 0 8px 24px rgba(13,34,84,.25);
}
.notif-toast.in  { transform: translateX(0); opacity: 1; }
.notif-toast.out { transform: translateX(320px); opacity: 0; }
.toast-title { font-weight: 700; font-size: 12.5px; margin-bottom: 2px; }
.toast-msg   { font-size: 11.5px; color: rgba(255,255,255,.6); line-height: 1.4; }

/* ── MOBILE TOPBAR ────────────────────────────────────── */
.mobile-topbar {
    display: none; position: sticky; top: 0; z-index: 200;
    background: var(--navy); padding: 13px 16px;
    align-items: center; gap: 12px;
    border-bottom: 3px solid var(--red);
}

.mobile-toggle {
    background: none; border: none; color: #fff; cursor: pointer; padding: 2px;
}

.mobile-topbar-title { color: #fff; font-size: 14px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }

.sidebar-overlay {
    display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 99;
}

/* ── RESPONSIVE ───────────────────────────────────────── */
@media (max-width: 991.98px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .charts-grid { grid-template-columns: 1fr; }
}

@media (max-width: 767.98px) {
    .sidebar { transform: translateX(-100%); transition: transform .3s ease; }
    .sidebar.open { transform: translateX(0); }
    .sidebar-overlay.open { display: block; }
    .mobile-topbar { display: flex; }
    .topbar { display: none; }
    .main { margin-left: 0; }
    .content { padding: 18px 14px 32px; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .stat-value { font-size: 24px; }
    .chart-head { flex-wrap: wrap; gap: 8px; }
    .chart-filter { width: 100%; justify-content: flex-end; }
}

@media (max-width: 480px) {
    .stats-grid { grid-template-columns: 1fr 1fr; }
}
</style>
</head>
<body>

<!-- MOBILE TOPBAR -->
<div class="mobile-topbar" id="mobileTopbar">
    <button class="mobile-toggle" id="mobileToggle">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>
    <span class="mobile-topbar-title">Admin Dashboard</span>
</div>

<!-- SIDEBAR OVERLAY -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">

    <div class="sb-brand">
        <div class="sb-logo">
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
        <div class="sb-brand-text">
            <strong>MCC Discipline</strong>
            <span>Admin Panel</span>
        </div>
    </div>

    <nav class="sb-nav">
        <div class="sb-section">Overview</div>
        <a href="dashboard.php" class="sb-link active">
            <i class="fas fa-chart-line"></i> Dashboard
        </a>

        <div class="sb-section">Students</div>
        <a href="students.php" class="sb-link">
            <i class="fas fa-user-graduate"></i> Student Records
        </a>
        <a href="reports.php" class="sb-link">
            <i class="fas fa-history"></i> Violation History
        </a>

        <div class="sb-section">Discipline</div>
        <a href="disciplinary_actions.php" class="sb-link">
            <i class="fas fa-gavel"></i> Disciplinary Actions
        </a>
        <a href="risk_level_indicator.php" class="sb-link">
            <i class="fas fa-exclamation-triangle"></i> Risk Level Indicator
        </a>

        <div class="sb-section">Backup</div>
        <a href="backup.php" class="sb-link">
            <i class="fas fa-cloud-download-alt"></i> Data Backup
        </a>

        <div class="sb-section">System</div>
        <a href="users.php" class="sb-link">
            <i class="fas fa-user"></i> User Management
        </a>

        <div class="sb-divider"></div>

        <a href="../auth/logout.php" class="sb-link logout">
            <i class="fas fa-sign-out-alt"></i> Log Out
        </a>
    </nav>

</aside>

<!-- MAIN -->
<div class="main">

    <!-- TOPBAR -->
    <header class="topbar">
        <div class="topbar-left">
            <span class="topbar-title">Dashboard</span>
            <span class="topbar-date" id="liveDateBadge"></span>
        </div>
        <div class="topbar-right">
            <button class="bell-btn" onclick="notifToggle()" title="Notifications">
                <i class="fas fa-bell"></i>
                <span class="bell-badge" id="notifBadge">0</span>
            </button>
            <div class="admin-chip">
                <div class="admin-avatar">A</div>
                <span>Admin</span>
            </div>
        </div>
    </header>

    <!-- CONTENT -->
    <div class="content">

        <!-- STAT CARDS -->
        <div class="section-label">Overview</div>
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-icon blue"><i class="fas fa-user-graduate"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Students</div>
                    <div class="stat-value"><?= number_format($totalStudents) ?></div>
                </div>
            </div>
            <div class="stat-card red">
                <div class="stat-icon red"><i class="fas fa-exclamation-circle"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Violations</div>
                    <div class="stat-value"><?= number_format($totalViolations) ?></div>
                </div>
            </div>
            <div class="stat-card amber">
                <div class="stat-icon amber"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Pending Sanctions</div>
                    <div class="stat-value"><?= number_format($pendingSanctions) ?></div>
                </div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Completed Sanctions</div>
                    <div class="stat-value"><?= number_format($completedSanctions) ?></div>
                </div>
            </div>
        </div>

        <!-- CHARTS -->
        <div class="section-label">Violation Trends</div>
        <div class="charts-grid">

            <!-- Chart 1: Minor vs Major -->
            <div class="chart-card">
                <div class="chart-head">
                    <div class="chart-head-left">
                        <h6>Monthly Minor vs Major Violations</h6>
                        <p>Offense type breakdown per month</p>
                    </div>
                    <div class="chart-filter">
                        <select id="year1" onchange="loadMinorMajor()">
                            <option value="">All Years</option>
                        </select>
                    </div>
                </div>
                <div class="chart-body"><canvas id="minorMajorChart"></canvas></div>
            </div>

            <!-- Chart 2: By Department -->
            <div class="chart-card">
                <div class="chart-head">
                    <div class="chart-head-left">
                        <h6>Violations by Department</h6>
                        <p>Total violations per department</p>
                    </div>
                    <div class="chart-filter">
                        <select id="year2" onchange="loadDepartment()">
                            <option value="">All Years</option>
                        </select>
                    </div>
                </div>
                <div class="chart-body"><canvas id="departmentChart"></canvas></div>
            </div>

            <!-- Chart 3: By Course -->
            <div class="chart-card">
                <div class="chart-head">
                    <div class="chart-head-left">
                        <h6>Violations by Course</h6>
                        <p>Distribution across courses</p>
                    </div>
                    <div class="chart-filter">
                        <select id="year3" onchange="loadCourse()">
                            <option value="">All Years</option>
                        </select>
                    </div>
                </div>
                <div class="chart-body"><canvas id="courseChart"></canvas></div>
            </div>

            <!-- Chart 4: Specific Violation -->
            <div class="chart-card">
                <div class="chart-head">
                    <div class="chart-head-left">
                        <h6>Specific Violation by Department</h6>
                        <p>Filter by violation type and year</p>
                    </div>
                    <div class="chart-filter">
                        <select id="year4" onchange="loadSpecificViolation()">
                            <option value="">All Years</option>
                        </select>
                        <select id="violationFilter" onchange="loadSpecificViolation()">
                            <optgroup label="Minor Offenses">
                                <option value="Disruptive Behavior">Disruptive Behavior</option>
                                <option value="Littering">Littering</option>
                                <option value="Dress Code">Dress Code</option>
                                <option value="Unapproved Absences">Unapproved Absences</option>
                                <option value="Inappropriate Language">Inappropriate Language</option>
                                <option value="Unauthorized Use of College Property">Unauthorized Use</option>
                                <option value="Smoking on Campus">Smoking on Campus</option>
                                <option value="Failure to Display ID">Failure to Display ID</option>
                                <option value="Noise Violations">Noise Violations</option>
                                <option value="Minor Vandalism">Minor Vandalism</option>
                            </optgroup>
                            <optgroup label="Major Offenses">
                                <option value="Academic Dishonesty">Academic Dishonesty</option>
                                <option value="Theft">Theft</option>
                                <option value="Physical Violence">Physical Violence</option>
                                <option value="Substance Abuse">Substance Abuse</option>
                                <option value="Harassment">Harassment</option>
                                <option value="Unauthorized Entry">Unauthorized Entry</option>
                                <option value="Forgery">Forgery</option>
                                <option value="Moral Infractions">Moral Infractions</option>
                                <option value="Weapons Possession">Weapons Possession</option>
                                <option value="Cyber Bullying">Cyber Bullying</option>
                                <option value="Hazing">Hazing</option>
                                <option value="Major Dishonesty">Major Dishonesty</option>
                                <option value="Extortion">Extortion</option>
                                <option value="Sexual Misconduct">Sexual Misconduct</option>
                            </optgroup>
                        </select>
                    </div>
                </div>
                <div class="chart-body"><canvas id="specificViolationChart"></canvas></div>
            </div>

        </div><!-- /charts-grid -->
    </div><!-- /content -->
</div><!-- /main -->

<!-- NOTIFICATION PANEL -->
<div class="notif-overlay" id="notifOverlay" onclick="notifClose()"></div>
<div class="notif-panel" id="notifPanel">
    <div class="notif-head">
        <h6><i class="fas fa-bell" style="margin-right:7px;opacity:.75;"></i>Notifications</h6>
        <div class="notif-head-actions">
            <button class="notif-btn" onclick="notifRefresh()"><i class="fas fa-sync-alt" style="font-size:10px;margin-right:3px;"></i>Refresh</button>
            <button class="notif-btn" onclick="notifMarkAllRead()"><i class="fas fa-check" style="font-size:10px;margin-right:3px;"></i>All read</button>
        </div>
    </div>
    <div class="notif-scroll" id="notifList">
        <div class="skel-wrap">
            <div class="skel" style="width:55%"></div>
            <div class="skel" style="width:80%"></div>
            <div class="skel" style="width:45%"></div>
        </div>
    </div>
</div>

<div id="toast-wrap"></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

/* ── DATE BADGE ──────────────────────────────────────── */
(function(){
    const el = document.getElementById('liveDateBadge');
    function tick(){ el.textContent = new Date().toLocaleDateString('en-PH',{weekday:'short',year:'numeric',month:'short',day:'numeric'}); }
    tick(); setInterval(tick, 60000);
})();

/* ── MOBILE SIDEBAR ──────────────────────────────────── */
const sidebar        = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');
document.getElementById('mobileToggle').addEventListener('click', () => {
    sidebar.classList.toggle('open');
    sidebarOverlay.classList.toggle('open');
});
sidebarOverlay.addEventListener('click', () => {
    sidebar.classList.remove('open');
    sidebarOverlay.classList.remove('open');
});

/* ── YEAR DROPDOWNS ──────────────────────────────────── */
['year1','year2','year3','year4'].forEach(id => {
    const sel = document.getElementById(id);
    for(let y = 2024; y <= 2035; y++) sel.innerHTML += `<option value="${y}">${y}</option>`;
});

/* ── CHART DEFAULTS ──────────────────────────────────── */
Chart.defaults.font.family = "'Barlow', sans-serif";
Chart.defaults.font.size   = 12;
Chart.defaults.color       = '#9aa0b5';

const PALETTE_DONUT = ['#1a2f6e','#c0192c','#2563eb','#16a34a','#d97706','#7c3aed','#0891b2','#dc2626'];

let minorMajorChart, departmentChart, courseChart, specificViolationChart;

loadMinorMajor();
loadDepartment();
loadCourse();
loadSpecificViolation();

function loadMinorMajor(){
    fetch("monthly_minor_major.php?year=" + document.getElementById('year1').value)
    .then(r => r.json()).then(data => {
        if(minorMajorChart) minorMajorChart.destroy();
        minorMajorChart = new Chart(document.getElementById('minorMajorChart'), {
            type: 'line',
            data: {
                labels: data.months,
                datasets: [
                    { label:'Minor', data:data.minor, borderColor:'#2563eb', backgroundColor:'rgba(37,99,235,.1)', tension:.4, fill:true, pointBackgroundColor:'#2563eb', pointRadius:3 },
                    { label:'Major', data:data.major, borderColor:'#c0192c', backgroundColor:'rgba(192,25,44,.08)', tension:.4, fill:true, pointBackgroundColor:'#c0192c', pointRadius:3 }
                ]
            },
            options: { plugins:{ legend:{ labels:{ boxWidth:10, color:'#4b5563' } } }, scales:{ x:{ grid:{ color:'rgba(0,0,0,.04)' } }, y:{ grid:{ color:'rgba(0,0,0,.04)' } } } }
        });
    });
}

function loadDepartment(){
    fetch("department_chart.php?year=" + document.getElementById('year2').value)
    .then(r => r.json()).then(data => {
        if(departmentChart) departmentChart.destroy();
        departmentChart = new Chart(document.getElementById('departmentChart'), {
            type: 'bar',
            data: { labels:data.labels, datasets:[{ label:'Violations', data:data.values, backgroundColor:'rgba(26,47,110,.75)', borderRadius:5, borderSkipped:false }] },
            options: { plugins:{ legend:{ display:false } }, scales:{ x:{ grid:{ display:false } }, y:{ grid:{ color:'rgba(0,0,0,.04)' } } } }
        });
    });
}

function loadCourse(){
    fetch("course_chart.php?year=" + document.getElementById('year3').value)
    .then(r => r.json()).then(data => {
        if(courseChart) courseChart.destroy();
        courseChart = new Chart(document.getElementById('courseChart'), {
            type: 'doughnut',
            data: { labels:data.labels, datasets:[{ data:data.values, backgroundColor:PALETTE_DONUT, borderWidth:2, borderColor:'#fff' }] },
            options: { cutout:'62%', plugins:{ legend:{ position:'right', labels:{ boxWidth:10, padding:12, font:{ size:11 } } } } }
        });
    });
}

function loadSpecificViolation(){
    fetch("specific_violation_chart.php?year=" + document.getElementById('year4').value + "&violation=" + encodeURIComponent(document.getElementById('violationFilter').value))
    .then(r => r.json()).then(data => {
        if(specificViolationChart) specificViolationChart.destroy();
        specificViolationChart = new Chart(document.getElementById('specificViolationChart'), {
            type: 'bar',
            data: { labels:data.labels, datasets:[{ label:document.getElementById('violationFilter').value, data:data.values, backgroundColor:'rgba(192,25,44,.75)', borderRadius:5, borderSkipped:false }] },
            options: { plugins:{ legend:{ display:false } }, scales:{ x:{ grid:{ display:false } }, y:{ grid:{ color:'rgba(0,0,0,.04)' } } } }
        });
    });
}

/* ── NOTIFICATION SYSTEM ─────────────────────────────── */
const NOTIF_API = 'notifications_api.php';
const POLL_MS   = 20000;
let _notifs   = [];
let _readIds  = new Set(JSON.parse(localStorage.getItem('notif_read')  || '[]'));
let _knownIds = new Set(JSON.parse(localStorage.getItem('notif_known') || '[]'));

document.addEventListener('DOMContentLoaded', () => { notifLoad(); setInterval(notifPoll, POLL_MS); });

function notifToggle(){
    const p = document.getElementById('notifPanel');
    const o = document.getElementById('notifOverlay');
    if(p.classList.contains('open')){ p.classList.remove('open'); o.classList.remove('open'); }
    else { p.classList.add('open'); o.classList.add('open'); notifLoad(); }
}

function notifClose(){
    document.getElementById('notifPanel').classList.remove('open');
    document.getElementById('notifOverlay').classList.remove('open');
}

function notifLoad(){
    fetch(`${NOTIF_API}?action=get_notifications`)
        .then(r => r.json())
        .then(data => { _notifs = data.notifications || []; notifRender(); notifUpdateBadge(); notifCheckNew(); })
        .catch(() => {
            document.getElementById('notifList').innerHTML = '<div class="notif-empty"><i class="fas fa-wifi-slash"></i><p>Could not load notifications.</p></div>';
        });
}

function notifRender(){
    const list = document.getElementById('notifList');
    if(!_notifs.length){ list.innerHTML = `<div class="notif-empty"><i class="fas fa-check-circle" style="color:#16a34a;opacity:.6;"></i><p>No new notifications.</p></div>`; return; }
    list.innerHTML = _notifs.map(n => {
        const read = _readIds.has(n.id);
        return `<div class="ni ${read?'':'unread'}" id="ni-${n.id}" onclick="notifMarkRead('${n.id}')">
            <div class="ni-icon">⚠️</div>
            <div class="ni-body">
                <div class="ni-title">${esc(n.title)}</div>
                <div class="ni-msg">${esc(n.message)}</div>
                <div class="ni-meta">
                    <span class="ni-chip">New Violation</span>
                    <span>${timeAgo(n.created_at)}</span>
                    ${n.student_id ? `<span>ID: ${esc(n.student_id)}</span>` : ''}
                </div>
            </div>
            ${!read ? '<div class="ni-dot"></div>' : ''}
        </div>`;
    }).join('');
}

function notifUpdateBadge(){
    const count = _notifs.filter(n => !_readIds.has(n.id)).length;
    const badge = document.getElementById('notifBadge');
    badge.textContent = count > 99 ? '99+' : count;
    count > 0 ? badge.classList.add('show') : badge.classList.remove('show');
}

function notifMarkRead(id){
    _readIds.add(id);
    localStorage.setItem('notif_read', JSON.stringify([..._readIds]));
    const el = document.getElementById('ni-' + id);
    if(el){ el.classList.remove('unread'); const d = el.querySelector('.ni-dot'); if(d) d.remove(); }
    notifUpdateBadge();
}

function notifMarkAllRead(){
    _notifs.forEach(n => _readIds.add(n.id));
    localStorage.setItem('notif_read', JSON.stringify([..._readIds]));
    notifRender(); notifUpdateBadge();
}

function notifRefresh(){
    document.getElementById('notifList').innerHTML = `<div class="skel-wrap"><div class="skel" style="width:55%"></div><div class="skel" style="width:80%"></div><div class="skel" style="width:45%"></div></div>`;
    notifLoad();
}

function notifCheckNew(){
    const fresh = _notifs.filter(n => !_knownIds.has(n.id));
    fresh.slice(0,3).forEach((n,i) => setTimeout(() => showToast(n), i * 600));
    _notifs.forEach(n => _knownIds.add(n.id));
    localStorage.setItem('notif_known', JSON.stringify([..._knownIds]));
}

function notifPoll(){
    fetch(`${NOTIF_API}?action=get_notifications`)
        .then(r => r.json())
        .then(data => {
            _notifs = data.notifications || [];
            notifUpdateBadge(); notifCheckNew();
            if(document.getElementById('notifPanel').classList.contains('open')) notifRender();
        });
}

function showToast(n){
    const wrap = document.getElementById('toast-wrap');
    const el   = document.createElement('div');
    el.className = 'notif-toast';
    el.innerHTML = `<div style="font-size:18px;flex-shrink:0;">⚠️</div><div><div class="toast-title">${esc(n.title)}</div><div class="toast-msg">${esc((n.message||'').slice(0,80))}${(n.message||'').length>80?'…':''}</div></div>`;
    wrap.appendChild(el);
    requestAnimationFrame(() => el.classList.add('in'));
    setTimeout(() => { el.classList.remove('in'); el.classList.add('out'); setTimeout(() => el.remove(), 320); }, 5000);
}

function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function timeAgo(d){ const m=Math.floor((Date.now()-new Date(d))/60000); if(m<1) return 'Just now'; if(m<60) return m+'m ago'; const h=Math.floor(m/60); if(h<24) return h+'h ago'; return Math.floor(h/24)+'d ago'; }

</script>
</body>
</html>