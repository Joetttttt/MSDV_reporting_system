<?php

session_start();
include("../config/database.php");

if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit(); }
if ($_SESSION['role'] != 'admin') { header("Location: ../index.php"); exit(); }

$query = "
SELECT *,
(SELECT COUNT(*) FROM violations v2
 WHERE v2.student_id = violations.student_id
 AND v2.violation_category = violations.violation_category
 AND v2.id <= violations.id) AS tally
FROM violations
ORDER BY id DESC
";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Violation History — MCC</title>

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
    --sidebar-section: #334155;
}

html, body { height: 100%; font-family: var(--font); font-size: 14px; color: var(--text); background: var(--bg); -webkit-font-smoothing: antialiased; }

.layout { display: flex; min-height: 100vh; }

/* ── SIDEBAR ── */
.sidebar { width: var(--sidebar-w); background: var(--sidebar-bg); display: flex; flex-direction: column; position: fixed; top: 0; left: 0; height: 100vh; overflow-y: auto; z-index: 100; border-right: 1px solid rgba(255,255,255,.04); }
.sidebar::-webkit-scrollbar { width: 4px; }
.sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.08); border-radius: 4px; }
.sb-brand { padding: 20px 18px 18px; border-bottom: 1px solid rgba(255,255,255,.06); display: flex; align-items: center; gap: 11px; flex-shrink: 0; }
.sb-icon { width: 34px; height: 34px; background: linear-gradient(135deg,#3b82f6,#1d4ed8); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 15px; flex-shrink: 0; }
.sb-brand-text strong { display: block; font-size: 13px; font-weight: 600; color: #fff; letter-spacing: .02em; }
.sb-brand-text span { font-size: 11px; color: var(--sidebar-text); margin-top: 2px; display: block; }
.sb-nav { flex: 1; padding: 12px 0 20px; }
.sb-section { font-size: 10px; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--sidebar-section); padding: 14px 18px 5px; }
.sb-link { display: flex; align-items: center; gap: 10px; padding: 9px 18px; color: var(--sidebar-text); text-decoration: none; font-size: 13.5px; transition: background .15s, color .15s; position: relative; }
.sb-link:hover { background: rgba(255,255,255,.06); color: #e2e8f0; }
.sb-link.active { background: rgba(255,255,255,.10); color: #fff; font-weight: 500; }
.sb-link.active::before { content: ''; position: absolute; left: 0; top: 4px; bottom: 4px; width: 3px; background: #3b82f6; border-radius: 0 3px 3px 0; }
.sb-link i { width: 18px; text-align: center; font-size: 14px; opacity: .8; }
.sb-link.active i { opacity: 1; }
.sb-link.logout { color: #f87171; margin-top: 4px; }
.sb-link.logout:hover { background: rgba(248,113,113,.08); color: #fca5a5; }

/* ── MAIN ── */
.main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; min-height: 100vh; }

/* ── TOPBAR ── */
.topbar { background: var(--surface); border-bottom: 1px solid var(--border); padding: 0 28px; height: 58px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 50; }
.topbar-title { font-size: 16px; font-weight: 600; color: var(--text); }
.topbar-right { display: flex; align-items: center; gap: 10px; }
.bell-btn { position: relative; background: none; border: 1px solid var(--border); border-radius: var(--radius-md); width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text2); font-size: 16px; transition: background .15s; }
.bell-btn:hover { background: var(--surface2); }
.bell-badge { position: absolute; top: -5px; right: -5px; background: #ef4444; color: #fff; font-size: 9px; font-weight: 700; min-width: 16px; height: 16px; border-radius: 10px; padding: 0 3px; display: none; align-items: center; justify-content: center; border: 2px solid var(--surface); font-family: var(--mono); }
.bell-badge.show { display: flex; }
.admin-chip { display: flex; align-items: center; gap: 8px; background: var(--surface2); border: 1px solid var(--border); border-radius: 20px; padding: 4px 12px 4px 5px; }
.admin-avatar { width: 26px; height: 26px; background: linear-gradient(135deg,#3b82f6,#1d4ed8); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 11px; font-weight: 600; }
.admin-chip span { font-size: 12.5px; font-weight: 500; color: var(--text); }

/* ── CONTENT ── */
.content { padding: 24px 28px 40px; flex: 1; }

.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.page-header h1 { font-size: 20px; font-weight: 600; color: var(--text); }

/* ── FILTERS ── */
.filters-bar { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 14px 18px; margin-bottom: 20px; box-shadow: var(--shadow-sm); display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; }
.fbar-group { display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 140px; }
.fbar-group label { font-size: 11px; font-weight: 600; color: var(--text3); text-transform: uppercase; letter-spacing: .05em; }
.fbar-group input,
.fbar-group select { padding: 7px 10px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--surface2); color: var(--text); font-family: var(--font); font-size: 13px; transition: border-color .15s; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' fill='none'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%2398a2b3' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; }
.fbar-group input { background-image: none; }
.fbar-group input:focus, .fbar-group select:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(29,78,216,.08); }
.btn-reset { padding: 7px 14px; background: var(--surface2); border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 13px; font-family: var(--font); color: var(--text2); cursor: pointer; white-space: nowrap; transition: background .15s; align-self: flex-end; }
.btn-reset:hover { background: var(--border); }

/* ── TABLE ── */
.table-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); overflow: hidden; }
.table-card table { width: 100%; border-collapse: collapse; }
.table-card thead th { background: #0f172a; color: #94a3b8; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .07em; padding: 12px 14px; white-space: nowrap; border-bottom: 1px solid rgba(255,255,255,.05); }
.table-card tbody tr { border-bottom: 1px solid var(--border); transition: background .12s; }
.table-card tbody tr:last-child { border-bottom: none; }
.table-card tbody tr:hover { background: var(--surface2); }
.table-card tbody td { padding: 11px 14px; font-size: 13.5px; color: var(--text); vertical-align: middle; }

.td-sid { font-family: var(--mono); font-size: 12.5px; font-weight: 500; color: var(--accent); }
.td-name { font-weight: 500; }
.td-date { font-family: var(--mono); font-size: 12px; color: var(--text3); }

/* Category badges */
.cat-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; letter-spacing: .03em; }
.cat-minor { background: var(--blue-bg); color: var(--blue); }
.cat-major { background: var(--red-bg);  color: var(--red); }

/* Status badges */
.status-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.status-pending   { background: var(--amber-bg); color: var(--amber); }
.status-ongoing   { background: var(--blue-bg);  color: var(--blue); }
.status-completed { background: var(--green-bg); color: var(--green); }
.status-unknown   { background: var(--surface2); color: var(--text3); }

/* Tally dots */
.tally-wrap { display: flex; align-items: center; gap: 4px; }
.tally-dot { width: 11px; height: 11px; border-radius: 3px; }
.tally-filled-minor { background: var(--blue); }
.tally-filled-major { background: var(--red); }
.tally-empty { background: var(--border2); }
.tally-count { font-family: var(--mono); font-size: 11px; color: var(--text3); margin-left: 3px; }
.tally-max-minor { background: var(--blue-bg); color: var(--blue); padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
.tally-max-major { background: var(--red-bg);  color: var(--red);  padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }

.btn-view { display: inline-flex; align-items: center; gap: 5px; padding: 5px 12px; background: var(--surface2); border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 12.5px; font-weight: 500; color: var(--text); font-family: var(--font); cursor: pointer; transition: background .15s, border-color .15s; }
.btn-view:hover { background: var(--accent-light); border-color: #b2ccff; color: var(--accent); }

/* ── MODALS ── */
.modal-content { border: 1px solid var(--border) !important; border-radius: var(--radius-xl) !important; overflow: hidden; font-family: var(--font); box-shadow: 0 20px 60px rgba(16,24,40,.16) !important; }
.modal-header { padding: 16px 20px !important; border-bottom: 1px solid var(--border) !important; }
.modal-body { padding: 20px !important; background: var(--bg); }
.modal-footer { padding: 14px 20px !important; background: var(--surface); border-top: 1px solid var(--border) !important; }

.modal-header.dark-head { background: #0f172a; }
.modal-header.dark-head .modal-title { color: #fff; font-size: 15px; font-weight: 600; }
.modal-header.dark-head small { color: #94a3b8; font-size: 12px; display: block; margin-top: 2px; }
.modal-header.dark-head .btn-close { filter: invert(1) brightness(2); opacity: .7; }
.modal-header.red-head { background: var(--red-bg); border-bottom: 1px solid #fca5a5 !important; }
.modal-header.red-head .modal-title { color: var(--red); font-size: 15px; font-weight: 600; }

.modal-section { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; margin-bottom: 14px; }
.modal-section-head { padding: 11px 16px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 8px; }
.modal-section-head.blue-sh { background: var(--blue-bg); }
.modal-section-head.red-sh  { background: var(--red-bg); }
.modal-section-head.green-sh { background: var(--green-bg); }
.modal-section-head.amber-sh { background: var(--amber-bg); }
.modal-section-head h6 { font-size: 13px; font-weight: 600; margin: 0; }
.modal-section-head.blue-sh  h6 { color: var(--blue); }
.modal-section-head.red-sh   h6 { color: var(--red); }
.modal-section-head.green-sh h6 { color: var(--green); }
.modal-section-head.amber-sh h6 { color: var(--amber); }
.modal-section-body { padding: 14px 16px; }

.info-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 24px; }
.info-item label { display: block; font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: var(--text3); margin-bottom: 3px; }
.info-item p { margin: 0; font-size: 13.5px; font-weight: 500; color: var(--text); }

.desc-box { background: var(--surface2); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 12px 14px; font-size: 13.5px; color: var(--text2); line-height: 1.6; }

.evidence-box { background: var(--surface2); border: 1px dashed var(--border2); border-radius: var(--radius-md); padding: 20px; text-align: center; color: var(--text3); font-size: 13px; }
.evidence-box i { font-size: 24px; margin-bottom: 6px; display: block; opacity: .4; }
.preview-img { width: 100%; border-radius: var(--radius-md); border: 1px solid var(--border); display: block; }

.form-group { margin-bottom: 14px; }
.form-group label { display: block; font-size: 11.5px; font-weight: 600; color: var(--text2); margin-bottom: 5px; text-transform: uppercase; letter-spacing: .04em; }
.form-group input { width: 100%; padding: 8px 11px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--surface); color: var(--text); font-family: var(--font); font-size: 13.5px; transition: border-color .15s; }
.form-group input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(29,78,216,.08); }

.btn-modal { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: var(--radius-sm); font-size: 13px; font-weight: 500; font-family: var(--font); cursor: pointer; border: 1px solid transparent; transition: background .15s; }
.btn-danger-m   { background: var(--red-bg); color: var(--red); border-color: #fca5a5; }
.btn-danger-m:hover { background: #fee2e2; }
.btn-secondary-m { background: var(--surface2); color: var(--text2); border-color: var(--border); }
.btn-secondary-m:hover { background: var(--border); }

/* ── NOTIFICATIONS ── */
.notif-overlay { display: none; position: fixed; inset: 0; z-index: 900; }
.notif-overlay.open { display: block; }
.notif-panel { display: none; position: fixed; top: 66px; right: 20px; width: 400px; max-height: 500px; background: var(--surface); border-radius: var(--radius-xl); border: 1px solid var(--border); box-shadow: 0 20px 60px rgba(16,24,40,.14); z-index: 1000; flex-direction: column; overflow: hidden; animation: dropIn .18s ease; }
.notif-panel.open { display: flex; }
@keyframes dropIn { from{opacity:0;transform:translateY(-8px) scale(.98)} to{opacity:1;transform:none} }
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
.ni-icon { width: 36px; height: 36px; border-radius: 50%; background: var(--amber-bg); display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
.ni-body { flex: 1; min-width: 0; }
.ni-title { font-size: 12.5px; font-weight: 600; color: var(--amber); margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ni-msg { font-size: 12px; color: var(--text2); line-height: 1.45; margin-bottom: 3px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.ni-meta { display: flex; align-items: center; gap: 7px; font-size: 11px; color: var(--text3); }
.ni-chip { padding: 1px 7px; border-radius: 10px; font-size: 10px; font-weight: 700; text-transform: uppercase; background: var(--amber-bg); color: var(--amber); }
.ni-dot { width: 6px; height: 6px; background: var(--accent); border-radius: 50%; flex-shrink: 0; margin-top: 7px; }
.notif-empty { text-align: center; padding: 40px 20px; color: var(--text3); font-size: 13px; }
.notif-empty i { font-size: 28px; margin-bottom: 10px; display: block; opacity: .4; }
.skel-wrap { padding: 16px; }
.skel { height: 11px; border-radius: 6px; background: linear-gradient(90deg,#f0f2f5 25%,#e4e7ec 50%,#f0f2f5 75%); background-size: 200% 100%; animation: shimmer 1.2s infinite; margin-bottom: 10px; }
@keyframes shimmer { to{background-position:-200% 0} }
#toast-wrap { position: fixed; bottom: 22px; right: 22px; z-index: 2000; display: flex; flex-direction: column; gap: 8px; pointer-events: none; }
.notif-toast { background: #0f172a; color: #fff; padding: 11px 14px; border-radius: var(--radius-lg); font-size: 13px; display: flex; align-items: flex-start; gap: 10px; max-width: 300px; pointer-events: all; transform: translateX(320px); opacity: 0; transition: all .28s cubic-bezier(.22,1,.36,1); border-left: 3px solid #f59e0b; box-shadow: 0 8px 24px rgba(0,0,0,.25); }
.notif-toast.in { transform: translateX(0); opacity: 1; }
.notif-toast.out { transform: translateX(320px); opacity: 0; }
.toast-title { font-weight: 600; font-size: 12.5px; margin-bottom: 2px; }
.toast-msg { font-size: 11.5px; color: #94a3b8; line-height: 1.4; }
</style>
</head>
<body>

<div class="layout">

    <!-- SIDEBAR -->
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
            <a href="students.php" class="sb-link"><i class="fas fa-user-graduate"></i> Student Records</a>
            <a href="reports.php" class="sb-link active"><i class="fas fa-history"></i> Violation History</a>
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

    <!-- MAIN -->
    <div class="main">

        <!-- TOPBAR -->
        <header class="topbar">
            <span class="topbar-title">Violation History</span>
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

            <div class="page-header">
                <h1>Violation History</h1>
            </div>

            <!-- FILTERS -->
            <div class="filters-bar">
                <div class="fbar-group" style="max-width:260px;">
                    <label>Search</label>
                    <input type="text" id="searchInput" placeholder="Student ID, name, violation…" onkeyup="filterTable()">
                </div>
                <div class="fbar-group">
                    <label>Category</label>
                    <select id="typeFilter" onchange="filterTable()">
                        <option value="">All Types</option>
                        <option value="minor">Minor</option>
                        <option value="major">Major</option>
                    </select>
                </div>
                <div class="fbar-group">
                    <label>Status</label>
                    <select id="statusFilter" onchange="filterTable()">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
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
                                <th>Student ID</th>
                                <th>Full Name</th>
                                <th>Category</th>
                                <th>Violation</th>
                                <th>Date</th>
                                <th>Tally</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="reportTable">
                        <?php while($row = mysqli_fetch_assoc($result)) {
                            $cat    = strtolower($row['violation_category']);
                            $status = strtolower($row['case_status']);
                            $count  = $row['tally'];
                        ?>
                            <tr class="report-row">
                                <td class="td-sid"><?= htmlspecialchars($row['student_id']); ?></td>
                                <td class="td-name"><?= htmlspecialchars($row['student_name']); ?></td>
                                <td>
                                    <?php if ($cat == 'minor'): ?>
                                        <span class="cat-badge cat-minor status-type">Minor</span>
                                    <?php else: ?>
                                        <span class="cat-badge cat-major status-type">Major</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['violation_type']); ?></td>
                                <td class="td-date"><?= date("Y-m-d", strtotime($row['created_at'])); ?></td>
                                <td>
                                <?php
                                if ($cat == 'minor') {
                                    $max = 5;
                                    if ($count >= $max) {
                                        echo '<span class="tally-max-minor">MAX</span>';
                                    } else {
                                        echo '<div class="tally-wrap">';
                                        for ($i = 1; $i <= $max; $i++) echo '<span class="tally-dot '.($i<=$count?'tally-filled-minor':'tally-empty').'"></span>';
                                        echo '<span class="tally-count">'.$count.'/'.$max.'</span></div>';
                                    }
                                } elseif ($cat == 'major') {
                                    $max = 3;
                                    if ($count >= $max) {
                                        echo '<span class="tally-max-major">MAX</span>';
                                    } else {
                                        echo '<div class="tally-wrap">';
                                        for ($i = 1; $i <= $max; $i++) echo '<span class="tally-dot '.($i<=$count?'tally-filled-major':'tally-empty').'"></span>';
                                        echo '<span class="tally-count">'.$count.'/'.$max.'</span></div>';
                                    }
                                }
                                ?>
                                </td>
                                <td>
                                <?php
                                $sc = 'status-unknown';
                                if ($status == 'pending')   $sc = 'status-pending';
                                elseif ($status == 'ongoing')   $sc = 'status-ongoing';
                                elseif ($status == 'completed') $sc = 'status-completed';
                                echo '<span class="status-badge '.$sc.' status-text">'.htmlspecialchars(ucfirst($row['case_status'])).'</span>';
                                ?>
                                </td>
                                <td>
                                    <button class="btn-view" data-bs-toggle="modal" data-bs-target="#viewModal<?= $row['id']; ?>">
                                        <i class="fas fa-eye" style="font-size:12px;"></i> View
                                    </button>
                                </td>
                            </tr>

                            <!-- VIEW MODAL -->
                            <div class="modal fade" id="viewModal<?= $row['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header dark-head">
                                            <div>
                                                <div class="modal-title">Violation Report Details</div>
                                                <small><?= htmlspecialchars($row['student_name']); ?> &nbsp;·&nbsp; <?= htmlspecialchars($row['student_id']); ?></small>
                                            </div>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">

                                            <!-- Student Info -->
                                            <div class="modal-section">
                                                <div class="modal-section-head blue-sh">
                                                    <i class="fas fa-user-graduate" style="color:var(--blue);font-size:13px;"></i>
                                                    <h6>Student Information</h6>
                                                </div>
                                                <div class="modal-section-body">
                                                    <div class="info-row">
                                                        <div class="info-item"><label>Student ID</label><p class="td-sid"><?= htmlspecialchars($row['student_id']); ?></p></div>
                                                        <div class="info-item"><label>Full Name</label><p><?= htmlspecialchars($row['student_name']); ?></p></div>
                                                        <div class="info-item"><label>Course</label><p><?= htmlspecialchars($row['course']); ?></p></div>
                                                        <div class="info-item"><label>Year Level</label><p><?= htmlspecialchars($row['year_level']); ?></p></div>
                                                        <div class="info-item"><label>Department</label><p><?= htmlspecialchars($row['department']); ?></p></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Violation Info -->
                                            <div class="modal-section">
                                                <div class="modal-section-head red-sh">
                                                    <i class="fas fa-exclamation-triangle" style="color:var(--red);font-size:13px;"></i>
                                                    <h6>Violation Information</h6>
                                                </div>
                                                <div class="modal-section-body">
                                                    <div class="info-row">
                                                        <div class="info-item"><label>Category</label><p><?= htmlspecialchars($row['violation_category']); ?></p></div>
                                                        <div class="info-item"><label>Violation Type</label><p><?= htmlspecialchars($row['violation_type']); ?></p></div>
                                                        <div class="info-item"><label>Date Submitted</label><p class="td-date"><?= htmlspecialchars($row['created_at']); ?></p></div>
                                                        <div class="info-item"><label>Status</label><p>
                                                        <?php
                                                        $sc2 = 'status-unknown';
                                                        if ($status=='pending')   $sc2='status-pending';
                                                        elseif($status=='ongoing')   $sc2='status-ongoing';
                                                        elseif($status=='completed') $sc2='status-completed';
                                                        echo '<span class="status-badge '.$sc2.'">'.htmlspecialchars(ucfirst($row['case_status'])).'</span>';
                                                        ?></p></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Description -->
                                            <div class="modal-section">
                                                <div class="modal-section-head amber-sh">
                                                    <i class="fas fa-file-lines" style="color:var(--amber);font-size:13px;"></i>
                                                    <h6>Incident Description</h6>
                                                </div>
                                                <div class="modal-section-body">
                                                    <div class="desc-box"><?= nl2br(htmlspecialchars($row['description'])); ?></div>
                                                </div>
                                            </div>

                                            <!-- Reporter -->
                                            <div class="modal-section">
                                                <div class="modal-section-head green-sh">
                                                    <i class="fas fa-user-shield" style="color:var(--green);font-size:13px;"></i>
                                                    <h6>Reporter Information</h6>
                                                </div>
                                                <div class="modal-section-body">
                                                    <div class="info-row">
                                                        <div class="info-item"><label>Reported By</label><p><?= htmlspecialchars($row['reported_by']); ?></p></div>
                                                        <div class="info-item"><label>Reporter Role</label><p><?= htmlspecialchars(strtoupper($row['reporter_role'])); ?></p></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Evidence -->
                                            <div class="modal-section">
                                                <div class="modal-section-head" style="background:var(--surface2);">
                                                    <i class="fas fa-paperclip" style="color:var(--text3);font-size:13px;"></i>
                                                    <h6 style="color:var(--text2);">Uploaded Evidence</h6>
                                                </div>
                                                <div class="modal-section-body">
                                                    <?php if (!empty($row['evidence'])): ?>
                                                        <img src="../uploads/<?= htmlspecialchars($row['evidence']); ?>" class="preview-img">
                                                    <?php else: ?>
                                                        <div class="evidence-box"><i class="fas fa-image"></i>No evidence uploaded</div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Camera + Signature side by side -->
                                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                                                <div class="modal-section" style="margin-bottom:0;">
                                                    <div class="modal-section-head" style="background:var(--surface2);">
                                                        <i class="fas fa-camera" style="color:var(--text3);font-size:13px;"></i>
                                                        <h6 style="color:var(--text2);">Camera Capture</h6>
                                                    </div>
                                                    <div class="modal-section-body">
                                                        <?php if (!empty($row['camera_capture'])): ?>
                                                            <img src="<?= htmlspecialchars($row['camera_capture']); ?>" class="preview-img">
                                                        <?php else: ?>
                                                            <div class="evidence-box"><i class="fas fa-camera-slash"></i>No camera capture</div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="modal-section" style="margin-bottom:0;">
                                                    <div class="modal-section-head" style="background:var(--surface2);">
                                                        <i class="fas fa-signature" style="color:var(--text3);font-size:13px;"></i>
                                                        <h6 style="color:var(--text2);">E-Signature</h6>
                                                    </div>
                                                    <div class="modal-section-body">
                                                        <?php if (!empty($row['e_signature'])): ?>
                                                            <img src="<?= htmlspecialchars($row['e_signature']); ?>" class="preview-img">
                                                        <?php else: ?>
                                                            <div class="evidence-box"><i class="fas fa-pen-slash"></i>No e-signature</div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn-modal btn-danger-m" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['id']; ?>">
                                                <i class="fas fa-trash"></i> Delete Report
                                            </button>
                                            <button type="button" class="btn-modal btn-secondary-m" data-bs-dismiss="modal">
                                                <i class="fas fa-xmark"></i> Close
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- DELETE MODAL -->
                            <div class="modal fade" id="deleteModal<?= $row['id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="delete_report.php" method="POST">
                                            <div class="modal-header red-head">
                                                <h5 class="modal-title"><i class="fas fa-trash" style="margin-right:7px;"></i>Delete Violation Report</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body" style="background:var(--surface);">
                                                <input type="hidden" name="report_id" value="<?= $row['id']; ?>">
                                                <div style="background:var(--red-bg);border:1px solid #fca5a5;border-radius:var(--radius-md);padding:12px 14px;margin-bottom:16px;font-size:13px;color:var(--red);">
                                                    <i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i>
                                                    This action permanently deletes this violation report.
                                                </div>
                                                <div class="form-group">
                                                    <label>Admin Password</label>
                                                    <input type="password" name="admin_password" required placeholder="Enter password to confirm">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn-modal btn-secondary-m" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn-modal btn-danger-m"><i class="fas fa-trash"></i> Confirm Delete</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div><!-- /content -->
    </div><!-- /main -->
</div><!-- /layout -->

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
function filterTable() {
    const search = document.getElementById("searchInput").value.toLowerCase();
    const type   = document.getElementById("typeFilter").value.toLowerCase();
    const status = document.getElementById("statusFilter").value.toLowerCase();
    document.querySelectorAll(".report-row").forEach(row => {
        const rowText    = row.innerText.toLowerCase();
        const typeEl     = row.querySelector(".status-type");
        const statusEl   = row.querySelector(".status-text");
        const typeText   = typeEl   ? typeEl.innerText.toLowerCase().trim()   : "";
        const statusText = statusEl ? statusEl.innerText.toLowerCase().trim() : "";
        const show = (!search || rowText.includes(search)) &&
                     (!type   || typeText === type)        &&
                     (!status || statusText === status);
        row.style.display = show ? "" : "none";
    });
}
function resetFilters() {
    ["searchInput","typeFilter","statusFilter"].forEach(id => document.getElementById(id).value = "");
    filterTable();
}

/* NOTIFICATIONS */
const NOTIF_API = 'notifications_api.php', POLL_MS = 20000;
let _notifs = [], _readIds = new Set(JSON.parse(localStorage.getItem('notif_read')||'[]')), _knownIds = new Set(JSON.parse(localStorage.getItem('notif_known')||'[]'));
document.addEventListener('DOMContentLoaded', () => { notifLoad(); setInterval(notifPoll, POLL_MS); });
function notifToggle() { const p=document.getElementById('notifPanel'),o=document.getElementById('notifOverlay'); if(p.classList.contains('open')){p.classList.remove('open');o.classList.remove('open');}else{p.classList.add('open');o.classList.add('open');notifLoad();} }
function notifClose() { document.getElementById('notifPanel').classList.remove('open'); document.getElementById('notifOverlay').classList.remove('open'); }
function notifLoad() { fetch(`${NOTIF_API}?action=get_notifications`).then(r=>r.json()).then(data=>{_notifs=data.notifications||[];notifRender();notifUpdateBadge();notifCheckNew();}).catch(()=>{document.getElementById('notifList').innerHTML='<div class="notif-empty"><i class="fas fa-wifi-slash"></i><p>Could not load.</p></div>';}); }
function notifRender() { const list=document.getElementById('notifList'); if(!_notifs.length){list.innerHTML=`<div class="notif-empty"><i class="fas fa-check-circle" style="color:#16a34a;"></i><p>No new notifications.</p></div>`;return;} list.innerHTML=_notifs.map(n=>{const read=_readIds.has(n.id);return`<div class="ni ${read?'':'unread'}" id="ni-${n.id}" onclick="notifMarkRead('${n.id}')"><div class="ni-icon">⚠️</div><div class="ni-body"><div class="ni-title">${esc(n.title)}</div><div class="ni-msg">${esc(n.message)}</div><div class="ni-meta"><span class="ni-chip">New Violation</span><span>${timeAgo(n.created_at)}</span>${n.student_id?`<span>ID: ${esc(n.student_id)}</span>`:''}</div></div>${!read?'<div class="ni-dot"></div>':''}</div>`;}).join('');}
function notifUpdateBadge() { const count=_notifs.filter(n=>!_readIds.has(n.id)).length,badge=document.getElementById('notifBadge');badge.textContent=count>99?'99+':count;count>0?badge.classList.add('show'):badge.classList.remove('show'); }
function notifMarkRead(id) { _readIds.add(id);localStorage.setItem('notif_read',JSON.stringify([..._readIds]));const el=document.getElementById('ni-'+id);if(el){el.classList.remove('unread');const d=el.querySelector('.ni-dot');if(d)d.remove();}notifUpdateBadge(); }
function notifMarkAllRead() { _notifs.forEach(n=>_readIds.add(n.id));localStorage.setItem('notif_read',JSON.stringify([..._readIds]));notifRender();notifUpdateBadge(); }
function notifRefresh() { document.getElementById('notifList').innerHTML=`<div class="skel-wrap"><div class="skel" style="width:55%"></div><div class="skel" style="width:80%"></div><div class="skel" style="width:45%"></div></div>`;notifLoad(); }
function notifCheckNew() { const fresh=_notifs.filter(n=>!_knownIds.has(n.id));fresh.slice(0,3).forEach((n,i)=>setTimeout(()=>showToast(n),i*600));_notifs.forEach(n=>_knownIds.add(n.id));localStorage.setItem('notif_known',JSON.stringify([..._knownIds])); }
function notifPoll() { fetch(`${NOTIF_API}?action=get_notifications`).then(r=>r.json()).then(data=>{_notifs=data.notifications||[];notifUpdateBadge();notifCheckNew();if(document.getElementById('notifPanel').classList.contains('open'))notifRender();}); }
function showToast(n) { const wrap=document.getElementById('toast-wrap'),el=document.createElement('div');el.className='notif-toast';el.innerHTML=`<div style="font-size:18px;flex-shrink:0;">⚠️</div><div><div class="toast-title">${esc(n.title)}</div><div class="toast-msg">${esc((n.message||'').slice(0,80))}${(n.message||'').length>80?'…':''}</div></div>`;wrap.appendChild(el);requestAnimationFrame(()=>el.classList.add('in'));setTimeout(()=>{el.classList.remove('in');el.classList.add('out');setTimeout(()=>el.remove(),320);},5000); }
function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function timeAgo(d){const m=Math.floor((Date.now()-new Date(d))/60000);if(m<1)return'Just now';if(m<60)return m+'m ago';const h=Math.floor(m/60);if(h<24)return h+'h ago';return Math.floor(h/24)+'d ago';}
</script>
</body>
</html>