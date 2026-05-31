<?php

session_start();

include("../config/database.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.html");
    exit();
}

$query = "SELECT * FROM students ORDER BY id DESC";
$result = mysqli_query($conn, $query);

$students = [];
while($row = mysqli_fetch_assoc($result)) {
    $students[] = $row;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Records — MCC</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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

html, body { height: 100%; font-family: var(--font); font-size: 14px; color: var(--text); background: var(--bg); -webkit-font-smoothing: antialiased; }

/* ── LAYOUT ── */
.layout { display: flex; min-height: 100vh; }

/* ── SIDEBAR ── */
.sidebar {
    width: var(--sidebar-w);
    background: var(--sidebar-bg);
    display: flex; flex-direction: column;
    position: fixed; top: 0; left: 0;
    height: 100vh; overflow-y: auto; z-index: 100;
    border-right: 1px solid rgba(255,255,255,.04);
}
.sidebar::-webkit-scrollbar { width: 4px; }
.sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.08); border-radius: 4px; }

.sb-brand {
    padding: 20px 18px 18px;
    border-bottom: 1px solid rgba(255,255,255,.06);
    display: flex; align-items: center; gap: 11px; flex-shrink: 0;
}
.sb-icon {
    width: 34px; height: 34px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border-radius: var(--radius-md);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 15px; flex-shrink: 0;
}
.sb-brand-text { line-height: 1; }
.sb-brand-text strong { display: block; font-size: 13px; font-weight: 600; color: #fff; letter-spacing: .02em; }
.sb-brand-text span  { font-size: 11px; color: var(--sidebar-text); margin-top: 2px; display: block; }

.sb-nav { flex: 1; padding: 12px 0 20px; }
.sb-section { font-size: 10px; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--sidebar-section); padding: 14px 18px 5px; }

.sb-link {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 18px; color: var(--sidebar-text);
    text-decoration: none; font-size: 13.5px; font-weight: 400;
    transition: background .15s, color .15s; position: relative;
}
.sb-link:hover { background: var(--sidebar-hover-bg); color: #e2e8f0; }
.sb-link.active { background: var(--sidebar-active-bg); color: var(--sidebar-active); font-weight: 500; }
.sb-link.active::before { content: ''; position: absolute; left: 0; top: 4px; bottom: 4px; width: 3px; background: #3b82f6; border-radius: 0 3px 3px 0; }
.sb-link i { width: 18px; text-align: center; font-size: 14px; opacity: .8; }
.sb-link.active i { opacity: 1; }
.sb-link.logout { color: #f87171; margin-top: 4px; }
.sb-link.logout:hover { background: rgba(248,113,113,.08); color: #fca5a5; }

/* ── MAIN ── */
.main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; min-height: 100vh; }

/* ── TOPBAR ── */
.topbar {
    background: var(--surface); border-bottom: 1px solid var(--border);
    padding: 0 28px; height: 58px;
    display: flex; align-items: center; justify-content: space-between;
    position: sticky; top: 0; z-index: 50;
}
.topbar-left { display: flex; align-items: center; gap: 10px; }
.topbar-title { font-size: 16px; font-weight: 600; color: var(--text); }
.topbar-right { display: flex; align-items: center; gap: 10px; }

.bell-btn {
    position: relative; background: none; border: 1px solid var(--border);
    border-radius: var(--radius-md); width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; color: var(--text2); font-size: 16px;
    transition: background .15s, border-color .15s;
}
.bell-btn:hover { background: var(--surface2); border-color: var(--border2); }
.bell-badge {
    position: absolute; top: -5px; right: -5px;
    background: #ef4444; color: #fff; font-size: 9px; font-weight: 700;
    min-width: 16px; height: 16px; border-radius: 10px; padding: 0 3px;
    display: none; align-items: center; justify-content: center;
    border: 2px solid var(--surface); font-family: var(--mono);
}
.bell-badge.show { display: flex; }

.admin-chip {
    display: flex; align-items: center; gap: 8px;
    background: var(--surface2); border: 1px solid var(--border);
    border-radius: 20px; padding: 4px 12px 4px 5px;
}
.admin-avatar {
    width: 26px; height: 26px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 11px; font-weight: 600;
}
.admin-chip span { font-size: 12.5px; font-weight: 500; color: var(--text); }

/* ── CONTENT ── */
.content { padding: 24px 28px 40px; flex: 1; }

/* ── PAGE HEADER ── */
.page-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 20px;
}
.page-header h1 { font-size: 20px; font-weight: 600; color: var(--text); }

.btn-add {
    display: inline-flex; align-items: center; gap: 7px;
    background: var(--accent); color: #fff;
    border: none; border-radius: var(--radius-md);
    padding: 8px 16px; font-size: 13.5px; font-weight: 500;
    font-family: var(--font); cursor: pointer;
    transition: background .15s, transform .1s;
}
.btn-add:hover { background: #1e40af; }
.btn-add:active { transform: scale(.98); }

/* ── FILTERS ── */
.filters-bar {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-lg); padding: 14px 18px;
    margin-bottom: 20px; box-shadow: var(--shadow-sm);
    display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;
}
.fbar-group { display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 140px; }
.fbar-group label { font-size: 11px; font-weight: 600; color: var(--text3); text-transform: uppercase; letter-spacing: .05em; }

.fbar-group input,
.fbar-group select {
    padding: 7px 10px; border: 1px solid var(--border);
    border-radius: var(--radius-sm); background: var(--surface2);
    color: var(--text); font-family: var(--font); font-size: 13px;
    appearance: none; transition: border-color .15s;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' fill='none'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%2398a2b3' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 10px center;
}
.fbar-group input { background-image: none; }
.fbar-group input:focus,
.fbar-group select:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(29,78,216,.08); }

.btn-reset {
    padding: 7px 14px; background: var(--surface2); border: 1px solid var(--border);
    border-radius: var(--radius-sm); font-size: 13px; font-family: var(--font);
    color: var(--text2); cursor: pointer; white-space: nowrap;
    transition: background .15s, border-color .15s; align-self: flex-end;
}
.btn-reset:hover { background: var(--border); border-color: var(--border2); }

/* ── TABLE CARD ── */
.table-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.table-card table { width: 100%; border-collapse: collapse; }

.table-card thead th {
    background: #0f172a; color: #94a3b8;
    font-size: 11px; font-weight: 600;
    text-transform: uppercase; letter-spacing: .07em;
    padding: 12px 14px; white-space: nowrap;
    border-bottom: 1px solid rgba(255,255,255,.05);
}

.table-card tbody tr {
    border-bottom: 1px solid var(--border);
    transition: background .12s;
}
.table-card tbody tr:last-child { border-bottom: none; }
.table-card tbody tr:hover { background: var(--surface2); }

.table-card tbody td {
    padding: 11px 14px; font-size: 13.5px; color: var(--text);
    vertical-align: middle;
}

.td-id { font-family: var(--mono); font-size: 12px; color: var(--text3); }
.td-sid { font-family: var(--mono); font-size: 12.5px; font-weight: 500; color: var(--accent); }
.td-name { font-weight: 500; }

.risk-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 20px;
    font-size: 11.5px; font-weight: 600;
}
.risk-low    { background: var(--green-bg); color: var(--green); }
.risk-medium { background: var(--amber-bg); color: var(--amber); }
.risk-high   { background: var(--red-bg);   color: var(--red); }

.btn-view {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px; background: var(--surface2);
    border: 1px solid var(--border); border-radius: var(--radius-sm);
    font-size: 12.5px; font-weight: 500; color: var(--text);
    font-family: var(--font); cursor: pointer;
    transition: background .15s, border-color .15s;
}
.btn-view:hover { background: var(--accent-light); border-color: #b2ccff; color: var(--accent); }

/* ── MODALS ── */
.modal-content {
    border: 1px solid var(--border) !important;
    border-radius: var(--radius-xl) !important;
    overflow: hidden; font-family: var(--font);
    box-shadow: 0 20px 60px rgba(16,24,40,.16) !important;
}

.modal-header {
    padding: 16px 20px !important;
    border-bottom: 1px solid var(--border) !important;
}

.modal-header.dark-head { background: #0f172a; }
.modal-header.dark-head .modal-title { color: #fff; font-size: 15px; font-weight: 600; }
.modal-header.dark-head small { color: #94a3b8; font-size: 12px; display: block; margin-top: 2px; }
.modal-header.dark-head .btn-close { filter: invert(1) brightness(2); opacity: .7; }

.modal-header.blue-head  { background: var(--blue-bg); }
.modal-header.blue-head .modal-title { color: var(--blue); font-size: 15px; font-weight: 600; }
.modal-header.red-head   { background: var(--red-bg); }
.modal-header.red-head .modal-title { color: var(--red); font-size: 15px; font-weight: 600; }

.modal-body { padding: 20px !important; background: var(--bg); }
.modal-footer { padding: 14px 20px !important; background: var(--surface); border-top: 1px solid var(--border) !important; }

/* Student info grid inside modal */
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 24px; }
.info-item label { display: block; font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: var(--text3); margin-bottom: 3px; }
.info-item p { margin: 0; font-size: 14px; font-weight: 500; color: var(--text); }

.modal-section {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius-lg); overflow: hidden; margin-bottom: 14px;
}
.modal-section-head {
    padding: 12px 16px;
    display: flex; align-items: center; justify-content: space-between;
    border-bottom: 1px solid var(--border);
}
.modal-section-head.blue-sh { background: var(--blue-bg); }
.modal-section-head.red-sh  { background: var(--red-bg); }
.modal-section-head h6 { font-size: 13px; font-weight: 600; margin: 0; }
.modal-section-head.blue-sh h6 { color: var(--blue); }
.modal-section-head.red-sh  h6 { color: var(--red); }

.vcount-badge {
    font-size: 11px; font-weight: 700; padding: 2px 10px; border-radius: 20px;
    background: rgba(192,57,43,.1); color: var(--red);
}

.violations-wrap { overflow-x: auto; overflow-y: auto; max-height: 280px; }
.violations-wrap::-webkit-scrollbar { height: 4px; width: 4px; }
.violations-wrap::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

.violations-wrap table { width: 100%; min-width: 860px; border-collapse: collapse; }
.violations-wrap thead th {
    position: sticky; top: 0; z-index: 2;
    background: #0f172a; color: #94a3b8;
    font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em;
    padding: 10px 12px; white-space: nowrap;
}
.violations-wrap tbody tr { border-bottom: 1px solid var(--border); transition: background .12s; }
.violations-wrap tbody tr:last-child { border-bottom: none; }
.violations-wrap tbody tr:hover { background: var(--surface2); }
.violations-wrap tbody td { padding: 9px 12px; font-size: 12.5px; color: var(--text); vertical-align: middle; }

.no-violations { padding: 36px 20px; text-align: center; color: var(--text3); font-size: 13px; }
.no-violations i { font-size: 28px; display: block; margin-bottom: 8px; color: var(--green); opacity: .5; }

/* Modal form */
.form-group { margin-bottom: 14px; }
.form-group label { display: block; font-size: 11.5px; font-weight: 600; color: var(--text2); margin-bottom: 5px; text-transform: uppercase; letter-spacing: .04em; }
.form-group input,
.form-group select {
    width: 100%; padding: 8px 11px;
    border: 1px solid var(--border); border-radius: var(--radius-sm);
    background: var(--surface2); color: var(--text);
    font-family: var(--font); font-size: 13.5px;
    transition: border-color .15s;
    appearance: none;
}
.form-group input:focus,
.form-group select:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(29,78,216,.08); }

.btn-modal {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: var(--radius-sm);
    font-size: 13px; font-weight: 500; font-family: var(--font);
    cursor: pointer; border: 1px solid transparent;
    transition: background .15s, transform .1s;
}
.btn-modal:active { transform: scale(.98); }
.btn-primary-m  { background: var(--accent); color: #fff; border-color: var(--accent); }
.btn-primary-m:hover { background: #1e40af; }
.btn-warning-m  { background: var(--amber-bg); color: var(--amber); border-color: #fcd34d; }
.btn-warning-m:hover { background: #fef3c7; }
.btn-danger-m   { background: var(--red-bg); color: var(--red); border-color: #fca5a5; }
.btn-danger-m:hover { background: #fee2e2; }
.btn-secondary-m { background: var(--surface2); color: var(--text2); border-color: var(--border); }
.btn-secondary-m:hover { background: var(--border); }

/* Status badges */
.status-badge {
    display: inline-block; padding: 2px 9px; border-radius: 20px;
    font-size: 11px; font-weight: 600;
}
.status-pending  { background: var(--amber-bg); color: var(--amber); }
.status-closed   { background: var(--green-bg); color: var(--green); }
.status-ongoing  { background: var(--blue-bg);  color: var(--blue); }
.status-default  { background: var(--surface2); color: var(--text3); }

/* ── NOTIFICATIONS ── */
.notif-overlay { display: none; position: fixed; inset: 0; z-index: 900; }
.notif-overlay.open { display: block; }

.notif-panel {
    display: none; position: fixed;
    top: 66px; right: 20px;
    width: 400px; max-height: 500px;
    background: var(--surface); border-radius: var(--radius-xl);
    border: 1px solid var(--border);
    box-shadow: 0 20px 60px rgba(16,24,40,.14);
    z-index: 1000; flex-direction: column; overflow: hidden;
    animation: dropIn .18s ease;
}
.notif-panel.open { display: flex; }
@keyframes dropIn { from { opacity: 0; transform: translateY(-8px) scale(.98); } to { opacity: 1; transform: none; } }

.notif-head { padding: 14px 16px 12px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
.notif-head h6 { font-size: 14px; font-weight: 600; color: var(--text); }
.notif-head-actions { display: flex; gap: 4px; }
.notif-btn { font-size: 12px; color: var(--accent); background: none; border: none; cursor: pointer; font-family: var(--font); font-weight: 500; padding: 4px 8px; border-radius: var(--radius-sm); transition: background .15s; }
.notif-btn:hover { background: var(--accent-light); }

.notif-scroll { overflow-y: auto; flex: 1; }
.notif-scroll::-webkit-scrollbar { width: 3px; }
.notif-scroll::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

.ni { display: flex; gap: 11px; padding: 12px 16px; border-bottom: 1px solid var(--border); cursor: pointer; transition: background .12s; }
.ni:last-child { border-bottom: none; }
.ni:hover { background: var(--surface2); }
.ni.unread { background: #f5f8ff; }
.ni.unread:hover { background: #edf3ff; }
.ni-icon { width: 36px; height: 36px; border-radius: 50%; background: var(--amber-bg); display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; margin-top: 1px; }
.ni-body { flex: 1; min-width: 0; }
.ni-title { font-size: 12.5px; font-weight: 600; color: var(--amber); margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ni-msg { font-size: 12px; color: var(--text2); line-height: 1.45; margin-bottom: 3px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.ni-meta { display: flex; align-items: center; gap: 7px; font-size: 11px; color: var(--text3); }
.ni-chip { padding: 1px 7px; border-radius: 10px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; background: var(--amber-bg); color: var(--amber); }
.ni-dot { width: 6px; height: 6px; background: var(--accent); border-radius: 50%; flex-shrink: 0; margin-top: 7px; }
.notif-empty { text-align: center; padding: 40px 20px; color: var(--text3); font-size: 13px; }
.notif-empty i { font-size: 28px; margin-bottom: 10px; display: block; opacity: .4; }

.skel-wrap { padding: 16px; }
.skel { height: 11px; border-radius: 6px; background: linear-gradient(90deg,#f0f2f5 25%,#e4e7ec 50%,#f0f2f5 75%); background-size: 200% 100%; animation: shimmer 1.2s infinite; margin-bottom: 10px; }
@keyframes shimmer { to { background-position: -200% 0; } }

#toast-wrap { position: fixed; bottom: 22px; right: 22px; z-index: 2000; display: flex; flex-direction: column; gap: 8px; pointer-events: none; }
.notif-toast { background: #0f172a; color: #fff; padding: 11px 14px; border-radius: var(--radius-lg); font-size: 13px; display: flex; align-items: flex-start; gap: 10px; max-width: 300px; pointer-events: all; transform: translateX(320px); opacity: 0; transition: all .28s cubic-bezier(.22,1,.36,1); border-left: 3px solid #f59e0b; box-shadow: 0 8px 24px rgba(0,0,0,.25); }
.notif-toast.in  { transform: translateX(0); opacity: 1; }
.notif-toast.out { transform: translateX(320px); opacity: 0; }
.toast-title { font-weight: 600; font-size: 12.5px; margin-bottom: 2px; }
.toast-msg   { font-size: 11.5px; color: #94a3b8; line-height: 1.4; }
</style>
</head>
<body>

<div class="layout">

    <!-- ══════════════ SIDEBAR ══════════════ -->
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
            <a href="dashboard.php" class="sb-link"><i class="fas fa-chart-line"></i> Dashboard</a>
            <div class="sb-section">Students</div>
            <a href="students.php" class="sb-link active"><i class="fas fa-user-graduate"></i> Student Records</a>
            <a href="reports.php" class="sb-link"><i class="fas fa-history"></i> Violation History</a>
            <div class="sb-section">Discipline</div>
            <a href="disciplinary_actions.php" class="sb-link"><i class="fas fa-gavel"></i> Disciplinary Actions</a>
            <a href="risk_level_indicator.php" class="sb-link"><i class="fas fa-exclamation-triangle"></i> Risk Level Indicator</a>
            <div class="sb-section">Backup</div>
            <a href="backup.php" class="sb-link"><i class="fas fa-cloud-download-alt"></i> Data Backup</a>
            <div class="sb-section">System</div>
            <a href="users.php" class="sb-link"><i class="fas fa-user"></i> User Management</a>
            <a href="../auth/logout.php" class="sb-link logout"><i class="fas fa-sign-out-alt"></i> Log Out</a>
        </nav>
    </aside>

    <!-- ══════════════ MAIN ══════════════════ -->
    <div class="main">

        <!-- TOPBAR -->
        <header class="topbar">
            <div class="topbar-left">
                <span class="topbar-title">Student Records</span>
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

            <!-- PAGE HEADER -->
            <div class="page-header">
                <h1>Student Records</h1>
                <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                    <i class="fas fa-plus"></i> Add Student
                </button>
            </div>

            <!-- FILTERS BAR -->
            <div class="filters-bar">
                <div class="fbar-group" style="max-width:200px;">
                    <label>Search</label>
                    <input type="text" id="searchInput" placeholder="Name, ID, course…" onkeyup="filterStudents()">
                </div>
                <div class="fbar-group">
                    <label>Course</label>
                    <select id="courseFilter" onchange="filterStudents()">
                        <option value="">All Courses</option>
                        <option value="BSIT">BSIT</option>
                        <option value="BIT (CompTech)">BIT (CompTech)</option>
                        <option value="BIT (ElectronicsTech)">BIT (ElectronicsTech)</option>
                        <option value="BEEd">BEEd</option>
                        <option value="BSEd (English)">BSEd (English)</option>
                        <option value="BSEd (Filipino)">BSEd (Filipino)</option>
                        <option value="BSEd (Mathematics)">BSEd (Mathematics)</option>
                        <option value="DPE">DPE</option>
                        <option value="BSBA (HRM)">BSBA (HRM)</option>
                        <option value="BSBA (MM)">BSBA (MM)</option>
                    </select>
                </div>
                <div class="fbar-group">
                    <label>Year Level</label>
                    <select id="yearFilter" onchange="filterStudents()">
                        <option value="">All Years</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                    </select>
                </div>
                <div class="fbar-group">
                    <label>Department</label>
                    <select id="departmentFilter" onchange="filterStudents()">
                        <option value="">All Departments</option>
                        <option value="School of Technology">School of Technology</option>
                        <option value="School of Education">School of Education</option>
                        <option value="School of Business">School of Business</option>
                    </select>
                </div>
                <button class="btn-reset" onclick="resetFilters()"><i class="fas fa-rotate-left" style="margin-right:5px;font-size:11px;"></i>Reset</button>
            </div>

            <!-- TABLE -->
            <div class="table-card">
                <div style="overflow-x:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student ID</th>
                                <th>Full Name</th>
                                <th>Course</th>
                                <th>Year Level</th>
                                <th>Department</th>
                                <th>Risk Level</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($students as $row): ?>
                            <tr class="student-row">
                                <td class="td-id"><?= $row['id']; ?></td>
                                <td class="td-sid"><?= $row['student_id']; ?></td>
                                <td class="td-name"><?= htmlspecialchars($row['fullname']); ?></td>
                                <td><?= htmlspecialchars($row['course']); ?></td>
                                <td><?= htmlspecialchars($row['year_level']); ?></td>
                                <td><?= htmlspecialchars($row['department']); ?></td>
                                <td>
                                <?php
                                $sid_r = $row['student_id'];
                                $minor_q = mysqli_query($conn, "SELECT COUNT(*) AS total FROM violations WHERE student_id='$sid_r' AND violation_category='Minor'");
                                $minor_c = mysqli_fetch_assoc($minor_q)['total'];
                                $major_q = mysqli_query($conn, "SELECT COUNT(*) AS total FROM violations WHERE student_id='$sid_r' AND violation_category='Major'");
                                $major_c = mysqli_fetch_assoc($major_q)['total'];
                                $score = ($minor_c * 1) + ($major_c * 3);
                                if ($score <= 2)     echo '<span class="risk-badge risk-low"><i class="fas fa-circle" style="font-size:7px;"></i>Low Risk</span>';
                                elseif ($score <= 5) echo '<span class="risk-badge risk-medium"><i class="fas fa-circle" style="font-size:7px;"></i>Medium Risk</span>';
                                else                 echo '<span class="risk-badge risk-high"><i class="fas fa-circle" style="font-size:7px;"></i>High Risk</span>';
                                ?>
                                </td>
                                <td>
                                    <button class="btn-view" data-bs-toggle="modal" data-bs-target="#viewStudent<?= $row['id']; ?>">
                                        <i class="fas fa-eye" style="font-size:12px;"></i> View
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div><!-- /content -->
    </div><!-- /main -->
</div><!-- /layout -->


<!-- ══════════════════════════════════════════
     MODALS
══════════════════════════════════════════ -->

<?php foreach($students as $row):
    $sid     = $row['student_id'];
    $vquery  = "SELECT *, (SELECT COUNT(*) FROM violations v2
                WHERE v2.student_id = violations.student_id
                AND v2.violation_category = violations.violation_category
                AND v2.id <= violations.id) AS offense_count
                FROM violations WHERE student_id = '$sid'
                ORDER BY created_at DESC";
    $vresult = mysqli_query($conn, $vquery);
    $vcount  = mysqli_num_rows($vresult);
?>

<!-- VIEW MODAL -->
<div class="modal fade" id="viewStudent<?= $row['id']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header dark-head">
                <div>
                    <div class="modal-title">Student Complete Record</div>
                    <small><?= htmlspecialchars($row['fullname']); ?> &nbsp;·&nbsp; <?= htmlspecialchars($row['student_id']); ?></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <div class="modal-section" style="margin-bottom:14px;">
                    <div class="modal-section-head blue-sh">
                        <h6><i class="fas fa-user-graduate" style="margin-right:6px;"></i>Student Information</h6>
                    </div>
                    <div style="padding:16px;">
                        <div class="info-grid">
                            <div class="info-item"><label>Student ID</label><p class="td-sid"><?= htmlspecialchars($row['student_id']); ?></p></div>
                            <div class="info-item"><label>Full Name</label><p><?= htmlspecialchars($row['fullname']); ?></p></div>
                            <div class="info-item"><label>Course</label><p><?= htmlspecialchars($row['course']); ?></p></div>
                            <div class="info-item"><label>Year Level</label><p><?= htmlspecialchars($row['year_level']); ?></p></div>
                            <div class="info-item"><label>Department</label><p><?= htmlspecialchars($row['department']); ?></p></div>
                        </div>
                    </div>
                </div>

                <div class="modal-section">
                    <div class="modal-section-head red-sh">
                        <h6><i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i>Violation Records</h6>
                        <span class="vcount-badge"><?= $vcount; ?> Record<?= $vcount !== 1 ? 's' : ''; ?></span>
                    </div>
                    <?php if ($vcount > 0): ?>
                        <div class="violations-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>#</th><th>Date</th><th>Category</th><th>Violation Type</th>
                                        <th>Description</th><th>Reported By</th><th>Role</th>
                                        <th>Status</th><th>Sanction</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php $count = 1; while ($v = mysqli_fetch_assoc($vresult)):
                                    $cbadge = 'status-default';
                                    if ($v['case_status'] == 'Pending')  $cbadge = 'status-pending';
                                    if ($v['case_status'] == 'Closed')   $cbadge = 'status-closed';
                                    if ($v['case_status'] == 'Ongoing')  $cbadge = 'status-ongoing';
                                    $oc  = $v['offense_count'];
                                    $cat = strtolower($v['violation_category']);
                                    $vio = strtolower($v['violation_type']);
                                    $san = '—';
                                    if ($cat == 'minor') {
                                        if ($oc==1)     $san = "Verbal Warning and Counseling";
                                        elseif($oc==2)  $san = "Written Warning and Reflective Essay";
                                        elseif($oc==3)  $san = "Community Service (5-10 hrs) and Parental Notification";
                                        elseif($oc==4)  $san = "Short Term Suspension (1-3 Days) and Mandatory Workshop";
                                        else            $san = "Long Term Suspension (1 Week) and Disciplinary Probation";
                                    } elseif ($cat == 'major') {
                                        if ($oc==1)     $san = ($vio=='academic dishonesty') ? "Failing Grade + Mandatory Ethics Workshop" : "Suspension (1 Week–1 Month) and Mandatory Counseling";
                                        elseif($oc==2)  $san = ($vio=='academic dishonesty') ? "Suspension for 1 Semester" : "Suspension (1 Month–1 Semester) and Extended Counseling";
                                        else            $san = ($vio=='academic dishonesty') ? "Expulsion" : "Expulsion and Notification to Authorities if Applicable";
                                    }
                                ?>
                                    <tr>
                                        <td class="td-id"><?= $count++; ?></td>
                                        <td style="white-space:nowrap;font-family:var(--mono);font-size:11.5px;"><?= htmlspecialchars($v['created_at']); ?></td>
                                        <td><?= htmlspecialchars($v['violation_category']); ?></td>
                                        <td><?= htmlspecialchars($v['violation_type']); ?></td>
                                        <td style="max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?= htmlspecialchars($v['description']); ?>"><?= htmlspecialchars($v['description']); ?></td>
                                        <td><?= htmlspecialchars($v['reported_by']); ?></td>
                                        <td><?= htmlspecialchars($v['reporter_role']); ?></td>
                                        <td><span class="status-badge <?= $cbadge; ?>"><?= htmlspecialchars($v['case_status']); ?></span></td>
                                        <td style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?= htmlspecialchars($san); ?>"><?= htmlspecialchars($san); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-violations">
                            <i class="fas fa-check-circle"></i>
                            No violations recorded for this student.
                        </div>
                    <?php endif; ?>
                </div>

            </div>
            <div class="modal-footer">
                <button class="btn-modal btn-warning-m" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#editStudent<?= $row['id']; ?>">
                    <i class="fas fa-pen"></i> Edit Student
                </button>
                <button class="btn-modal btn-danger-m" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#deleteStudent<?= $row['id']; ?>">
                    <i class="fas fa-trash"></i> Delete Student
                </button>
            </div>
        </div>
    </div>
</div>

<!-- DELETE MODAL -->
<div class="modal fade" id="deleteStudent<?= $row['id']; ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="delete_student.php" method="POST">
                <div class="modal-header red-head">
                    <h5 class="modal-title" style="color:var(--red);font-size:15px;font-weight:600;"><i class="fas fa-trash" style="margin-right:7px;"></i>Delete Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="background:var(--surface);">
                    <input type="hidden" name="student_id" value="<?= $row['student_id']; ?>">
                    <div style="background:var(--red-bg);border:1px solid #fca5a5;border-radius:var(--radius-md);padding:12px 14px;margin-bottom:16px;font-size:13px;color:var(--red);">
                        <i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i>
                        This will permanently delete the student and all related records.
                    </div>
                    <div class="form-group">
                        <label>Admin Deletion Password</label>
                        <input type="password" name="delete_password" required placeholder="Enter password to confirm">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-secondary-m" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-modal btn-danger-m"><i class="fas fa-trash"></i> Delete Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editStudent<?= $row['id']; ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="update_student.php" method="POST">
                <div class="modal-header blue-head">
                    <h5 class="modal-title" style="color:var(--blue);font-size:15px;font-weight:600;"><i class="fas fa-pen" style="margin-right:7px;"></i>Update Student Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="background:var(--surface);">
                    <input type="hidden" name="id" value="<?= $row['id']; ?>">
                    <div class="form-group"><label>Student ID</label><input type="text" name="student_id" value="<?= htmlspecialchars($row['student_id']); ?>" required></div>
                    <div class="form-group"><label>Full Name</label><input type="text" name="fullname" value="<?= htmlspecialchars($row['fullname']); ?>" required></div>
                    <div class="form-group"><label>Course</label><input type="text" name="course" value="<?= htmlspecialchars($row['course']); ?>" required></div>
                    <div class="form-group"><label>Year Level</label><input type="text" name="year_level" value="<?= htmlspecialchars($row['year_level']); ?>" required></div>
                    <div class="form-group"><label>Department</label><input type="text" name="department" value="<?= htmlspecialchars($row['department']); ?>" required></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-secondary-m" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-modal btn-primary-m"><i class="fas fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php endforeach; ?>

<!-- ADD STUDENT MODAL -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="add_student.php" method="POST">
                <div class="modal-header" style="background:var(--green-bg);border-bottom:1px solid #bbf7d0;">
                    <h5 class="modal-title" style="color:var(--green);font-size:15px;font-weight:600;"><i class="fas fa-user-plus" style="margin-right:7px;"></i>Add New Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="background:var(--surface);">
                    <div class="form-group"><label>Student ID</label><input type="text" name="student_id" id="student_id" required maxlength="9" placeholder="e.g. 123-4567"></div>
                    <div class="form-group"><label>Full Name</label><input type="text" name="fullname" required placeholder="Last, First Middle"></div>
                    <div class="form-group">
                        <label>Department</label>
                        <select name="department" id="department" required style="appearance:none;background-image:url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2712%27 height=%278%27 fill=%27none%27%3E%3Cpath d=%27M1 1l5 5 5-5%27 stroke=%27%2398a2b3%27 stroke-width=%271.5%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27/%3E%3C/svg%3E');background-repeat:no-repeat;background-position:right 10px center;">
                            <option value="">Select Department</option>
                            <option value="School of Technology">School of Technology</option>
                            <option value="School of Education">School of Education</option>
                            <option value="School of Business">School of Business</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Course</label>
                        <select name="course" id="course" required style="appearance:none;background-image:url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2712%27 height=%278%27 fill=%27none%27 %3E%3Cpath d=%27M1 1l5 5 5-5%27 stroke=%27%2398a2b3%27 stroke-width=%271.5%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27/%3E%3C/svg%3E');background-repeat:no-repeat;background-position:right 10px center;">
                            <option value="">Select Department First</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Year Level</label>
                        <select name="year_level" required style="appearance:none;background-image:url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2712%27 height=%278%27 fill=%27none%27%3E%3Cpath d=%27M1 1l5 5 5-5%27 stroke=%27%2398a2b3%27 stroke-width=%271.5%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27/%3E%3C/svg%3E');background-repeat:no-repeat;background-position:right 10px center;">
                            <option value="">Select Year Level</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-secondary-m" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-modal btn-primary-m" style="background:var(--green);border-color:var(--green);color:#fff;"><i class="fas fa-save"></i> Save Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- NOTIFICATION PANEL -->
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>

/* ── FILTER / SEARCH ── */
function filterStudents() {
    const search = document.getElementById("searchInput").value.toLowerCase();
    const course = document.getElementById("courseFilter").value.toLowerCase();
    const year   = document.getElementById("yearFilter").value.toLowerCase();
    const dept   = document.getElementById("departmentFilter").value.toLowerCase();
    document.querySelectorAll(".student-row").forEach(row => {
        const t = row.innerText.toLowerCase();
        const show = (!search || t.includes(search)) &&
                     (!course || t.includes(course)) &&
                     (!year   || t.includes(year))   &&
                     (!dept   || t.includes(dept));
        row.style.display = show ? "" : "none";
    });
}

function resetFilters() {
    ["searchInput","courseFilter","yearFilter","departmentFilter"].forEach(id => document.getElementById(id).value = "");
    filterStudents();
}

/* ── STUDENT ID FORMAT ── */
document.getElementById('student_id').addEventListener('input', function(e) {
    let v = e.target.value.replace(/-/g, '');
    if (v.length > 3) v = v.substring(0,3) + '-' + v.substring(3);
    e.target.value = v;
});

/* ── COURSE DROPDOWN ── */
document.getElementById('department').addEventListener('change', function() {
    const courses = {
        'School of Technology': ['BSIT','BIT (CompTech)','BIT (ElectronicsTech)'],
        'School of Education':  ['BEEd','BSEd (English)','BSEd (Filipino)','BSEd (Mathematics)','DPE'],
        'School of Business':   ['BSBA (HRM)','BSBA (MM)']
    };
    const sel = document.getElementById('course');
    const opts = courses[this.value] || [];
    sel.innerHTML = opts.length
        ? `<option value="">Select Course</option>` + opts.map(c => `<option value="${c}">${c}</option>`).join('')
        : `<option value="">Select Department First</option>`;
});

/* ── NOTIFICATIONS ── */
const NOTIF_API = 'notifications_api.php';
const POLL_MS   = 20000;
let _notifs  = [];
let _readIds  = new Set(JSON.parse(localStorage.getItem('notif_read')  || '[]'));
let _knownIds = new Set(JSON.parse(localStorage.getItem('notif_known') || '[]'));

document.addEventListener('DOMContentLoaded', () => { notifLoad(); setInterval(notifPoll, POLL_MS); });

function notifToggle() {
    const p = document.getElementById('notifPanel'), o = document.getElementById('notifOverlay');
    if (p.classList.contains('open')) { p.classList.remove('open'); o.classList.remove('open'); }
    else { p.classList.add('open'); o.classList.add('open'); notifLoad(); }
}
function notifClose() { document.getElementById('notifPanel').classList.remove('open'); document.getElementById('notifOverlay').classList.remove('open'); }

function notifLoad() {
    fetch(`${NOTIF_API}?action=get_notifications`).then(r=>r.json())
    .then(data => { _notifs = data.notifications||[]; notifRender(); notifUpdateBadge(); notifCheckNew(); })
    .catch(() => { document.getElementById('notifList').innerHTML = '<div class="notif-empty"><i class="fas fa-wifi-slash"></i><p>Could not load.</p></div>'; });
}

function notifRender() {
    const list = document.getElementById('notifList');
    if (!_notifs.length) { list.innerHTML = `<div class="notif-empty"><i class="fas fa-check-circle" style="color:#16a34a;"></i><p>No new notifications.</p></div>`; return; }
    list.innerHTML = _notifs.map(n => {
        const read = _readIds.has(n.id);
        return `<div class="ni ${read?'':'unread'}" id="ni-${n.id}" onclick="notifMarkRead('${n.id}')">
            <div class="ni-icon">⚠️</div>
            <div class="ni-body">
                <div class="ni-title">${esc(n.title)}</div>
                <div class="ni-msg">${esc(n.message)}</div>
                <div class="ni-meta"><span class="ni-chip">New Violation</span><span>${timeAgo(n.created_at)}</span>${n.student_id?`<span>ID: ${esc(n.student_id)}</span>`:''}</div>
            </div>${!read?'<div class="ni-dot"></div>':''}
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
    _readIds.add(id); localStorage.setItem('notif_read', JSON.stringify([..._readIds]));
    const el = document.getElementById('ni-'+id);
    if (el) { el.classList.remove('unread'); const d=el.querySelector('.ni-dot'); if(d) d.remove(); }
    notifUpdateBadge();
}
function notifMarkAllRead() { _notifs.forEach(n=>_readIds.add(n.id)); localStorage.setItem('notif_read',JSON.stringify([..._readIds])); notifRender(); notifUpdateBadge(); }
function notifRefresh() { document.getElementById('notifList').innerHTML=`<div class="skel-wrap"><div class="skel" style="width:55%"></div><div class="skel" style="width:80%"></div><div class="skel" style="width:45%"></div></div>`; notifLoad(); }
function notifCheckNew() {
    const fresh = _notifs.filter(n => !_knownIds.has(n.id));
    fresh.slice(0,3).forEach((n,i) => setTimeout(()=>showToast(n), i*600));
    _notifs.forEach(n=>_knownIds.add(n.id)); localStorage.setItem('notif_known',JSON.stringify([..._knownIds]));
}
function notifPoll() {
    fetch(`${NOTIF_API}?action=get_notifications`).then(r=>r.json()).then(data=>{
        _notifs=data.notifications||[]; notifUpdateBadge(); notifCheckNew();
        if(document.getElementById('notifPanel').classList.contains('open')) notifRender();
    });
}
function showToast(n) {
    const wrap=document.getElementById('toast-wrap'), el=document.createElement('div');
    el.className='notif-toast';
    el.innerHTML=`<div style="font-size:18px;flex-shrink:0;">⚠️</div><div><div class="toast-title">${esc(n.title)}</div><div class="toast-msg">${esc((n.message||'').slice(0,80))}${(n.message||'').length>80?'…':''}</div></div>`;
    wrap.appendChild(el);
    requestAnimationFrame(()=>el.classList.add('in'));
    setTimeout(()=>{el.classList.remove('in');el.classList.add('out');setTimeout(()=>el.remove(),320);},5000);
}
function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function timeAgo(d){const m=Math.floor((Date.now()-new Date(d))/60000);if(m<1)return'Just now';if(m<60)return m+'m ago';const h=Math.floor(m/60);if(h<24)return h+'h ago';return Math.floor(h/24)+'d ago';}

</script>
</body>
</html>