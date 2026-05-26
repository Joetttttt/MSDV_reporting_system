<?php

session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include("../config/database.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
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

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --sidebar-w: 240px;
    --bg: #f0f2f5;
    --surface: #ffffff;
    --surface2: #f7f8fa;
    --border: #e4e7ec;
    --border2: #d0d5dd;
    --text: #101828;
    --text2: #475467;
    --text3: #98a2b3;
    --accent: #1d4ed8;
    --accent-light: #eff4ff;
    --accent-mid: #b2ccff;

    --blue:    #1d4ed8;
    --blue-bg: #eff4ff;
    --red:     #c0392b;
    --red-bg:  #fdf3f3;
    --amber:   #b45309;
    --amber-bg:#fffbeb;
    --green:   #166534;
    --green-bg:#f0fdf4;

    --radius-sm: 6px;
    --radius-md: 10px;
    --radius-lg: 14px;
    --radius-xl: 18px;
    --shadow-sm: 0 1px 3px rgba(16,24,40,.06), 0 1px 2px rgba(16,24,40,.04);
    --shadow-md: 0 4px 8px rgba(16,24,40,.08), 0 2px 4px rgba(16,24,40,.04);
    --font: 'DM Sans', sans-serif;
    --mono: 'DM Mono', monospace;

    --sidebar-bg: #0f172a;
    --sidebar-text: #94a3b8;
    --sidebar-active: #ffffff;
    --sidebar-hover-bg: rgba(255,255,255,.06);
    --sidebar-active-bg: rgba(255,255,255,.10);
    --sidebar-section: #334155;
}

html, body {
    height: 100%;
    font-family: var(--font);
    font-size: 14px;
    color: var(--text);
    background: var(--bg);
    -webkit-font-smoothing: antialiased;
}

/* ═══════════════════════════ LAYOUT ═══════════════════════════ */
.layout {
    display: flex;
    min-height: 100vh;
}

/* ═══════════════════════════ SIDEBAR ══════════════════════════ */
.sidebar {
    width: var(--sidebar-w);
    background: var(--sidebar-bg);
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0; left: 0;
    height: 100vh;
    overflow-y: auto;
    z-index: 100;
    border-right: 1px solid rgba(255,255,255,.04);
}

.sidebar::-webkit-scrollbar { width: 4px; }
.sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.08); border-radius: 4px; }

.sb-brand {
    padding: 20px 18px 18px;
    border-bottom: 1px solid rgba(255,255,255,.06);
    display: flex;
    align-items: center;
    gap: 11px;
    flex-shrink: 0;
}

.sb-icon {
    width: 34px; height: 34px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border-radius: var(--radius-md);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 15px;
    flex-shrink: 0;
}

.sb-brand-text { line-height: 1; }
.sb-brand-text strong { display: block; font-size: 13px; font-weight: 600; color: #fff; letter-spacing: .02em; }
.sb-brand-text span  { font-size: 11px; color: var(--sidebar-text); margin-top: 2px; display: block; }

.sb-nav { flex: 1; padding: 12px 0 20px; }

.sb-section {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--sidebar-section);
    padding: 14px 18px 5px;
}

.sb-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 18px;
    color: var(--sidebar-text);
    text-decoration: none;
    font-size: 13.5px;
    font-weight: 400;
    transition: background .15s, color .15s;
    border-radius: 0;
    position: relative;
}

.sb-link:hover {
    background: var(--sidebar-hover-bg);
    color: #e2e8f0;
}

.sb-link.active {
    background: var(--sidebar-active-bg);
    color: var(--sidebar-active);
    font-weight: 500;
}

.sb-link.active::before {
    content: '';
    position: absolute;
    left: 0; top: 4px; bottom: 4px;
    width: 3px;
    background: #3b82f6;
    border-radius: 0 3px 3px 0;
}

.sb-link i { width: 18px; text-align: center; font-size: 14px; opacity: .8; }
.sb-link.active i { opacity: 1; }

.sb-link.logout { color: #f87171; margin-top: 4px; }
.sb-link.logout:hover { background: rgba(248,113,113,.08); color: #fca5a5; }

/* ═══════════════════════════ MAIN ══════════════════════════════ */
.main {
    margin-left: var(--sidebar-w);
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* ═══════════════════════════ TOPBAR ════════════════════════════ */
.topbar {
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    padding: 0 28px;
    height: 58px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 50;
}

.topbar-left { display: flex; align-items: center; gap: 10px; }
.topbar-title { font-size: 16px; font-weight: 600; color: var(--text); }
.topbar-date {
    font-size: 12px; color: var(--text3);
    font-family: var(--mono);
    background: var(--surface2);
    border: 1px solid var(--border);
    padding: 3px 10px;
    border-radius: 20px;
}

.topbar-right { display: flex; align-items: center; gap: 10px; }

/* Bell */
.bell-btn {
    position: relative;
    background: none;
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    color: var(--text2);
    font-size: 16px;
    transition: background .15s, border-color .15s;
}

.bell-btn:hover { background: var(--surface2); border-color: var(--border2); }

.bell-badge {
    position: absolute;
    top: -5px; right: -5px;
    background: #ef4444;
    color: #fff;
    font-size: 9px;
    font-weight: 700;
    min-width: 16px; height: 16px;
    border-radius: 10px;
    padding: 0 3px;
    display: none;
    align-items: center;
    justify-content: center;
    border: 2px solid var(--surface);
    font-family: var(--mono);
}

.bell-badge.show { display: flex; }

.admin-chip {
    display: flex; align-items: center; gap: 8px;
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 4px 12px 4px 5px;
}

.admin-avatar {
    width: 26px; height: 26px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 11px; font-weight: 600;
}

.admin-chip span { font-size: 12.5px; font-weight: 500; color: var(--text); }

/* ═══════════════════════════ CONTENT ═══════════════════════════ */
.content {
    padding: 24px 28px 40px;
    flex: 1;
}

/* ═══════════════════════════ STAT CARDS ════════════════════════ */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 18px 20px;
    box-shadow: var(--shadow-sm);
    display: flex;
    flex-direction: column;
    gap: 12px;
    transition: box-shadow .2s, transform .2s;
}

.stat-card:hover { box-shadow: var(--shadow-md); transform: translateY(-1px); }

.stat-top { display: flex; align-items: flex-start; justify-content: space-between; }

.stat-icon {
    width: 38px; height: 38px;
    border-radius: var(--radius-md);
    display: flex; align-items: center; justify-content: center;
    font-size: 17px;
}

.stat-icon.blue  { background: var(--blue-bg);  color: var(--blue); }
.stat-icon.red   { background: var(--red-bg);   color: var(--red); }
.stat-icon.amber { background: var(--amber-bg); color: var(--amber); }
.stat-icon.green { background: var(--green-bg); color: var(--green); }

.stat-label { font-size: 12px; font-weight: 500; color: var(--text3); text-transform: uppercase; letter-spacing: .05em; }
.stat-value { font-size: 32px; font-weight: 600; color: var(--text); font-family: var(--mono); line-height: 1; }
.stat-value span { font-size: 14px; font-weight: 400; color: var(--text3); font-family: var(--font); }

/* ═══════════════════════════ FILTERS ═══════════════════════════ */
.section-head {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 14px;
}

.section-title {
    font-size: 13px; font-weight: 600; color: var(--text2);
    text-transform: uppercase; letter-spacing: .07em;
    display: flex; align-items: center; gap: 7px;
}

.section-title::before {
    content: '';
    width: 3px; height: 14px;
    background: var(--accent);
    border-radius: 2px;
}

.filters-panel {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 18px 20px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-sm);
}

.filters-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 14px;
}

.filter-row-2 {
    display: grid;
    grid-template-columns: repeat(3, 1fr) 1fr;
    gap: 14px;
}

.filter-group label {
    display: block;
    font-size: 11.5px;
    font-weight: 600;
    color: var(--text2);
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: .05em;
}

.filter-group select {
    width: 100%;
    padding: 7px 10px;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    background: var(--surface2);
    color: var(--text);
    font-family: var(--font);
    font-size: 13px;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' fill='none'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%2398a2b3' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    cursor: pointer;
    transition: border-color .15s;
}

.filter-group select:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(29,78,216,.08); }
.filter-group select:hover { border-color: var(--border2); }

/* ═══════════════════════════ CHARTS ═══════════════════════════ */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.chart-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: box-shadow .2s;
}

.chart-card:hover { box-shadow: var(--shadow-md); }

.chart-head {
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 9px;
}

.chart-head-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
}

.chart-head h6 {
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
    flex: 1;
}

.chart-head-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 2px 9px;
    border-radius: 20px;
    font-family: var(--mono);
}

.chart-body { padding: 16px 14px; }
.chart-body canvas { max-height: 220px; }

/* Chart accent colors */
.chart-card.c-dark   .chart-head { background: #0f172a; }
.chart-card.c-dark   .chart-head h6 { color: #fff; }
.chart-card.c-dark   .chart-head-dot { background: #3b82f6; }

.chart-card.c-blue   .chart-head { background: #eff4ff; }
.chart-card.c-blue   .chart-head-dot { background: var(--blue); }
.chart-card.c-blue   .chart-head-badge { background: #dbeafe; color: var(--blue); }

.chart-card.c-green  .chart-head { background: #f0fdf4; }
.chart-card.c-green  .chart-head-dot { background: #16a34a; }
.chart-card.c-green  .chart-head-badge { background: #dcfce7; color: #166534; }

.chart-card.c-red    .chart-head { background: #fdf3f3; }
.chart-card.c-red    .chart-head-dot { background: var(--red); }
.chart-card.c-red    .chart-head-badge { background: #fee2e2; color: var(--red); }

/* ═══════════════════════════ NOTIFICATION PANEL ══════════════ */
.notif-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 900;
    background: transparent;
}
.notif-overlay.open { display: block; }

.notif-panel {
    display: none;
    position: fixed;
    top: 66px; right: 20px;
    width: 400px;
    max-height: 500px;
    background: var(--surface);
    border-radius: var(--radius-xl);
    border: 1px solid var(--border);
    box-shadow: 0 20px 60px rgba(16,24,40,.14), 0 4px 16px rgba(16,24,40,.06);
    z-index: 1000;
    flex-direction: column;
    overflow: hidden;
    animation: dropIn .18s ease;
}

.notif-panel.open { display: flex; }

@keyframes dropIn {
    from { opacity: 0; transform: translateY(-8px) scale(.98); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

.notif-head {
    padding: 14px 16px 12px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
}

.notif-head h6 { font-size: 14px; font-weight: 600; color: var(--text); }

.notif-head-actions { display: flex; gap: 4px; }

.notif-btn {
    font-size: 12px; color: var(--accent);
    background: none; border: none; cursor: pointer;
    font-family: var(--font); font-weight: 500;
    padding: 4px 8px; border-radius: var(--radius-sm);
    transition: background .15s;
}

.notif-btn:hover { background: var(--accent-light); }

.notif-scroll { overflow-y: auto; flex: 1; }
.notif-scroll::-webkit-scrollbar { width: 3px; }
.notif-scroll::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

.ni {
    display: flex; gap: 11px;
    padding: 12px 16px;
    border-bottom: 1px solid var(--border);
    cursor: pointer;
    transition: background .12s;
}
.ni:last-child { border-bottom: none; }
.ni:hover { background: var(--surface2); }
.ni.unread { background: #f5f8ff; }
.ni.unread:hover { background: #edf3ff; }

.ni-icon {
    width: 36px; height: 36px; border-radius: 50%;
    background: var(--amber-bg);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0; margin-top: 1px;
}

.ni-body { flex: 1; min-width: 0; }
.ni-title { font-size: 12.5px; font-weight: 600; color: var(--amber); margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ni-msg { font-size: 12px; color: var(--text2); line-height: 1.45; margin-bottom: 3px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.ni-meta { display: flex; align-items: center; gap: 7px; font-size: 11px; color: var(--text3); }
.ni-chip { padding: 1px 7px; border-radius: 10px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; background: var(--amber-bg); color: var(--amber); }
.ni-dot { width: 6px; height: 6px; background: var(--accent); border-radius: 50%; flex-shrink: 0; margin-top: 7px; }

.notif-empty { text-align: center; padding: 40px 20px; color: var(--text3); font-size: 13px; }
.notif-empty i { font-size: 32px; margin-bottom: 10px; display: block; opacity: .4; }

.skel-wrap { padding: 16px; }
.skel { height: 11px; border-radius: 6px; background: linear-gradient(90deg, #f0f2f5 25%, #e4e7ec 50%, #f0f2f5 75%); background-size: 200% 100%; animation: shimmer 1.2s infinite; margin-bottom: 10px; }
@keyframes shimmer { to { background-position: -200% 0; } }

/* ═══════════════════════════ TOAST ══════════════════════════ */
#toast-wrap { position: fixed; bottom: 22px; right: 22px; z-index: 2000; display: flex; flex-direction: column; gap: 8px; pointer-events: none; }

.notif-toast {
    background: #0f172a; color: #fff;
    padding: 11px 14px; border-radius: var(--radius-lg);
    font-size: 13px; display: flex; align-items: flex-start; gap: 10px;
    max-width: 300px; pointer-events: all;
    transform: translateX(320px); opacity: 0;
    transition: all .28s cubic-bezier(.22,1,.36,1);
    border-left: 3px solid #f59e0b;
    box-shadow: 0 8px 24px rgba(0,0,0,.25);
}
.notif-toast.in  { transform: translateX(0); opacity: 1; }
.notif-toast.out { transform: translateX(320px); opacity: 0; }
.toast-title { font-weight: 600; font-size: 12.5px; margin-bottom: 2px; }
.toast-msg   { font-size: 11.5px; color: #94a3b8; line-height: 1.4; }
</style>
</head>
<body>

<div class="layout">

    <!-- ══════════════════ SIDEBAR ══════════════════ -->
    <aside class="sidebar">
        <div class="sb-brand">
            <div class="sb-icon"><i class="fas fa-shield-alt"></i></div>
            <div class="sb-brand-text">
                <strong>Admin Panel</strong>
                <span>MCC Violation System</span>
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
            <a href="../auth/logout.php" class="sb-link logout">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </a>
        </nav>
    </aside>

    <!-- ══════════════════ MAIN ══════════════════════ -->
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
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-top">
                        <div>
                            <div class="stat-label">Total Students</div>
                            <div class="stat-value"><?= $totalStudents ?></div>
                        </div>
                        <div class="stat-icon blue"><i class="fas fa-user-graduate"></i></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-top">
                        <div>
                            <div class="stat-label">Total Violations</div>
                            <div class="stat-value"><?= $totalViolations ?></div>
                        </div>
                        <div class="stat-icon red"><i class="fas fa-exclamation-circle"></i></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-top">
                        <div>
                            <div class="stat-label">Pending Sanctions</div>
                            <div class="stat-value"><?= $pendingSanctions ?></div>
                        </div>
                        <div class="stat-icon amber"><i class="fas fa-clock"></i></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-top">
                        <div>
                            <div class="stat-label">Completed Sanctions</div>
                            <div class="stat-value"><?= $completedSanctions ?></div>
                        </div>
                        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
            </div>

            <!-- FILTERS PANEL -->
            <div class="filters-panel">
                <div class="section-head" style="margin-bottom:16px;">
                    <div class="section-title">Filters</div>
                </div>
                <div class="filters-row">
                    <div class="filter-group">
                        <label>Minor vs Major — Year</label>
                        <select id="year1"><option value="">All Years</option></select>
                    </div>
                    <div class="filter-group">
                        <label>Department Chart — Year</label>
                        <select id="year2"><option value="">All Years</option></select>
                    </div>
                    <div class="filter-group">
                        <label>Course Chart — Year</label>
                        <select id="year3"><option value="">All Years</option></select>
                    </div>
                    <div class="filter-group">
                        <label>Violation Dept — Year</label>
                        <select id="year4"><option value="">All Years</option></select>
                    </div>
                </div>
                <div class="filter-row-2">
                    <div class="filter-group" style="grid-column: span 2;">
                        <label>Select Specific Violation</label>
                        <select id="violationFilter">
                            <optgroup label="— Minor Offenses —">
                                <option value="Disruptive Behavior">Disruptive Behavior</option>
                                <option value="Littering">Littering</option>
                                <option value="Dress Code">Dress Code</option>
                                <option value="Unapproved Absences">Unapproved Absences</option>
                                <option value="Inappropriate Language">Inappropriate Language</option>
                                <option value="Unauthorized Use of College Property">Unauthorized Use of College Property</option>
                                <option value="Smoking on Campus">Smoking on Campus</option>
                                <option value="Failure to Display ID">Failure to Display ID</option>
                                <option value="Noise Violations">Noise Violations</option>
                                <option value="Minor Vandalism">Minor Vandalism</option>
                            </optgroup>
                            <optgroup label="— Major Offenses —">
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
            </div>

            <!-- CHARTS GRID -->
            <div class="charts-grid">

                <div class="chart-card c-dark">
                    <div class="chart-head">
                        <span class="chart-head-dot"></span>
                        <h6 style="color:#fff;">Monthly Minor vs Major Violations</h6>
                        <span class="chart-head-badge" style="background:rgba(255,255,255,.1);color:#94a3b8;">Line</span>
                    </div>
                    <div class="chart-body"><canvas id="minorMajorChart"></canvas></div>
                </div>

                <div class="chart-card c-blue">
                    <div class="chart-head">
                        <span class="chart-head-dot"></span>
                        <h6>Violations by Department</h6>
                        <span class="chart-head-badge">Bar</span>
                    </div>
                    <div class="chart-body"><canvas id="departmentChart"></canvas></div>
                </div>

                <div class="chart-card c-green">
                    <div class="chart-head">
                        <span class="chart-head-dot"></span>
                        <h6>Violations by Course</h6>
                        <span class="chart-head-badge">Doughnut</span>
                    </div>
                    <div class="chart-body"><canvas id="courseChart"></canvas></div>
                </div>

                <div class="chart-card c-red">
                    <div class="chart-head">
                        <span class="chart-head-dot"></span>
                        <h6>Specific Violation by Department</h6>
                        <span class="chart-head-badge">Bar</span>
                    </div>
                    <div class="chart-body"><canvas id="specificViolationChart"></canvas></div>
                </div>

            </div><!-- /charts-grid -->
        </div><!-- /content -->
    </div><!-- /main -->
</div><!-- /layout -->


<!-- ══════════════════ NOTIFICATION PANEL ══════════════════ -->
<div class="notif-overlay" id="notifOverlay" onclick="notifClose()"></div>
<div class="notif-panel" id="notifPanel">
    <div class="notif-head">
        <h6><i class="fas fa-bell" style="margin-right:6px;opacity:.7;"></i>Notifications</h6>
        <div class="notif-head-actions">
            <button class="notif-btn" onclick="notifRefresh()"><i class="fas fa-sync-alt" style="font-size:11px;"></i> Refresh</button>
            <button class="notif-btn" onclick="notifMarkAllRead()"><i class="fas fa-check" style="font-size:11px;"></i> All read</button>
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

/* ── DATE BADGE ─────────────────────────────────────────────── */
(function () {
    const el = document.getElementById('liveDateBadge');
    function tick() {
        el.textContent = new Date().toLocaleDateString('en-PH', { weekday:'short', year:'numeric', month:'short', day:'numeric' });
    }
    tick();
    setInterval(tick, 60000);
})();

/* ── YEAR DROPDOWNS ──────────────────────────────────────────── */
['year1','year2','year3','year4'].forEach(id => {
    const sel = document.getElementById(id);
    for (let y = 2024; y <= 2035; y++) sel.innerHTML += `<option value="${y}">${y}</option>`;
});

/* ── CHART DEFAULTS ──────────────────────────────────────────── */
Chart.defaults.font.family = "'DM Sans', sans-serif";
Chart.defaults.font.size   = 12;
Chart.defaults.color       = '#98a2b3';

const PALETTE_LINE   = ['#3b82f6','#ef4444'];
const PALETTE_BAR    = '#3b82f6';
const PALETTE_DONUT  = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6','#f97316'];
const PALETTE_BAR2   = '#ef4444';

let minorMajorChart, departmentChart, courseChart, specificViolationChart;

loadMinorMajor();
loadDepartment();
loadCourse();
loadSpecificViolation();

year1.addEventListener('change', loadMinorMajor);
year2.addEventListener('change', loadDepartment);
year3.addEventListener('change', loadCourse);
year4.addEventListener('change', loadSpecificViolation);
violationFilter.addEventListener('change', loadSpecificViolation);

function loadMinorMajor() {
    fetch("monthly_minor_major.php?year=" + year1.value)
    .then(r => r.json()).then(data => {
        if (minorMajorChart) minorMajorChart.destroy();
        minorMajorChart = new Chart(document.getElementById('minorMajorChart'), {
            type: 'line',
            data: {
                labels: data.months,
                datasets: [
                    { label:'Minor', data:data.minor, borderColor:'#3b82f6', backgroundColor:'rgba(59,130,246,.12)', tension:.4, fill:true, pointBackgroundColor:'#3b82f6', pointRadius:3 },
                    { label:'Major', data:data.major, borderColor:'#ef4444', backgroundColor:'rgba(239,68,68,.08)', tension:.4, fill:true, pointBackgroundColor:'#ef4444', pointRadius:3 }
                ]
            },
            options: { plugins:{ legend:{ labels:{ color:'#94a3b8', boxWidth:10 } } }, scales:{ x:{ grid:{ color:'rgba(255,255,255,.05)' }, ticks:{ color:'#64748b' } }, y:{ grid:{ color:'rgba(255,255,255,.05)' }, ticks:{ color:'#64748b' } } } }
        });
    });
}

function loadDepartment() {
    fetch("department_chart.php?year=" + year2.value)
    .then(r => r.json()).then(data => {
        if (departmentChart) departmentChart.destroy();
        departmentChart = new Chart(document.getElementById('departmentChart'), {
            type: 'bar',
            data: { labels:data.labels, datasets:[{ label:'Violations', data:data.values, backgroundColor:'rgba(29,78,216,.75)', borderRadius:5, borderSkipped:false }] },
            options: { plugins:{ legend:{ display:false } }, scales:{ x:{ grid:{ display:false } }, y:{ grid:{ color:'rgba(0,0,0,.04)' } } } }
        });
    });
}

function loadCourse() {
    fetch("course_chart.php?year=" + year3.value)
    .then(r => r.json()).then(data => {
        if (courseChart) courseChart.destroy();
        courseChart = new Chart(document.getElementById('courseChart'), {
            type: 'doughnut',
            data: { labels:data.labels, datasets:[{ data:data.values, backgroundColor:PALETTE_DONUT, borderWidth:2, borderColor:'#fff' }] },
            options: { cutout:'65%', plugins:{ legend:{ position:'right', labels:{ boxWidth:10, padding:14, font:{ size:11 } } } } }
        });
    });
}

function loadSpecificViolation() {
    fetch("specific_violation_chart.php?year=" + year4.value + "&violation=" + encodeURIComponent(violationFilter.value))
    .then(r => r.json()).then(data => {
        if (specificViolationChart) specificViolationChart.destroy();
        specificViolationChart = new Chart(document.getElementById('specificViolationChart'), {
            type: 'bar',
            data: { labels:data.labels, datasets:[{ label:violationFilter.value, data:data.values, backgroundColor:'rgba(192,57,43,.75)', borderRadius:5, borderSkipped:false }] },
            options: { plugins:{ legend:{ display:false } }, scales:{ x:{ grid:{ display:false } }, y:{ grid:{ color:'rgba(0,0,0,.04)' } } } }
        });
    });
}

/* ── NOTIFICATION SYSTEM ─────────────────────────────────────── */
const NOTIF_API = 'notifications_api.php';
const POLL_MS   = 20000;
let _notifs   = [];
let _readIds  = new Set(JSON.parse(localStorage.getItem('notif_read')  || '[]'));
let _knownIds = new Set(JSON.parse(localStorage.getItem('notif_known') || '[]'));

document.addEventListener('DOMContentLoaded', () => { notifLoad(); setInterval(notifPoll, POLL_MS); });

function notifToggle() {
    const p = document.getElementById('notifPanel');
    const o = document.getElementById('notifOverlay');
    if (p.classList.contains('open')) { p.classList.remove('open'); o.classList.remove('open'); }
    else { p.classList.add('open'); o.classList.add('open'); notifLoad(); }
}

function notifClose() {
    document.getElementById('notifPanel').classList.remove('open');
    document.getElementById('notifOverlay').classList.remove('open');
}

function notifLoad() {
    fetch(`${NOTIF_API}?action=get_notifications`)
        .then(r => r.json())
        .then(data => {
            _notifs = data.notifications || [];
            notifRender(); notifUpdateBadge(); notifCheckNew();
        })
        .catch(() => {
            document.getElementById('notifList').innerHTML =
                '<div class="notif-empty"><i class="fas fa-wifi-slash"></i><p>Could not load notifications.</p></div>';
        });
}

function notifRender() {
    const list = document.getElementById('notifList');
    if (!_notifs.length) {
        list.innerHTML = `<div class="notif-empty"><i class="fas fa-check-circle" style="color:#16a34a;opacity:.6;"></i><p>No new notifications.</p></div>`;
        return;
    }
    list.innerHTML = _notifs.map(n => {
        const read = _readIds.has(n.id);
        return `<div class="ni ${read ? '' : 'unread'}" id="ni-${n.id}" onclick="notifMarkRead('${n.id}')">
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

function notifUpdateBadge() {
    const count = _notifs.filter(n => !_readIds.has(n.id)).length;
    const badge = document.getElementById('notifBadge');
    badge.textContent = count > 99 ? '99+' : count;
    count > 0 ? badge.classList.add('show') : badge.classList.remove('show');
}

function notifMarkRead(id) {
    _readIds.add(id);
    localStorage.setItem('notif_read', JSON.stringify([..._readIds]));
    const el = document.getElementById('ni-' + id);
    if (el) { el.classList.remove('unread'); const d = el.querySelector('.ni-dot'); if (d) d.remove(); }
    notifUpdateBadge();
}

function notifMarkAllRead() {
    _notifs.forEach(n => _readIds.add(n.id));
    localStorage.setItem('notif_read', JSON.stringify([..._readIds]));
    notifRender(); notifUpdateBadge();
}

function notifRefresh() {
    document.getElementById('notifList').innerHTML =
        `<div class="skel-wrap"><div class="skel" style="width:55%"></div><div class="skel" style="width:80%"></div><div class="skel" style="width:45%"></div></div>`;
    notifLoad();
}

function notifCheckNew() {
    const fresh = _notifs.filter(n => !_knownIds.has(n.id));
    fresh.slice(0, 3).forEach((n, i) => setTimeout(() => showToast(n), i * 600));
    _notifs.forEach(n => _knownIds.add(n.id));
    localStorage.setItem('notif_known', JSON.stringify([..._knownIds]));
}

function notifPoll() {
    fetch(`${NOTIF_API}?action=get_notifications`)
        .then(r => r.json())
        .then(data => {
            _notifs = data.notifications || [];
            notifUpdateBadge(); notifCheckNew();
            if (document.getElementById('notifPanel').classList.contains('open')) notifRender();
        });
}

function showToast(n) {
    const wrap = document.getElementById('toast-wrap');
    const el   = document.createElement('div');
    el.className = 'notif-toast';
    el.innerHTML = `<div style="font-size:18px;flex-shrink:0;">⚠️</div><div><div class="toast-title">${esc(n.title)}</div><div class="toast-msg">${esc((n.message||'').slice(0,80))}${(n.message||'').length>80?'…':''}</div></div>`;
    wrap.appendChild(el);
    requestAnimationFrame(() => el.classList.add('in'));
    setTimeout(() => { el.classList.remove('in'); el.classList.add('out'); setTimeout(() => el.remove(), 320); }, 5000);
}

function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function timeAgo(d) { const m = Math.floor((Date.now()-new Date(d))/60000); if(m<1) return 'Just now'; if(m<60) return m+'m ago'; const h=Math.floor(m/60); if(h<24) return h+'h ago'; return Math.floor(h/24)+'d ago'; }

</script>
</body>
</html>