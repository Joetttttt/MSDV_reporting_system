<?php

session_start();

include("../config/database.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SESSION['role'] != 'teacher') {
    header("Location: ../index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>

<title>Report Violation</title>

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

    /* ── FORM CARD ───────────────────────────── */

    .form-card {
        background: #fff;
        border-radius: 0 0 10px 10px;
        padding: 28px 28px 32px;
        box-shadow: 0 6px 32px rgba(13,34,84,0.10);
    }

    /* ── SECTION DIVIDER ─────────────────────── */

    .section-label {
        font-size: 10.5px;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--navy-mid);
        border-bottom: 2px solid #e4e7f0;
        padding-bottom: 8px;
        margin-bottom: 18px;
        margin-top: 28px;
    }

    .section-label:first-of-type {
        margin-top: 0;
    }

    /* ── FORM FIELDS ─────────────────────────── */

    .form-label-mcc {
        display: block;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: 0.06em;
        color: var(--navy-mid);
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .form-control-mcc,
    .form-select-mcc {
        width: 100%;
        padding: 11px 14px;
        border: 1.5px solid #dde1ee;
        border-radius: 6px;
        background: #f5f7fc;
        color: #2d3748;
        font-family: 'Barlow', sans-serif;
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        appearance: none;
    }

    .form-control-mcc::placeholder {
        color: #9aa0b5;
    }

    .form-control-mcc:focus,
    .form-select-mcc:focus {
        border-color: var(--navy-mid);
        background: #fff;
        box-shadow: 0 0 0 3px rgba(26,47,110,0.1);
    }

    .form-control-mcc[readonly] {
        background: #eef0f7;
        color: #6b7280;
        cursor: default;
    }

    textarea.form-control-mcc {
        resize: vertical;
        min-height: 110px;
    }

    .form-select-mcc {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%231a2f6e' stroke-width='2.5'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        padding-right: 36px;
    }

    /* ── RADIO BUTTONS ───────────────────────── */

    .radio-group {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .radio-option {
        flex: 1;
        min-width: 120px;
    }

    .radio-option input[type="radio"] {
        display: none;
    }

    .radio-option label {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 11px 16px;
        border: 1.5px solid #dde1ee;
        border-radius: 6px;
        background: #f5f7fc;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
        letter-spacing: 0.03em;
        transition: all 0.15s;
        user-select: none;
    }

    .radio-option input[type="radio"]:checked + label {
        border-color: var(--navy-mid);
        background: var(--navy);
        color: #fff;
    }

    .radio-option input#minor:checked + label {
        border-color: #2563eb;
        background: #1e3a8a;
        color: #fff;
    }

    .radio-option input#major:checked + label {
        border-color: var(--red);
        background: var(--red);
        color: #fff;
    }

    /* ── FILE INPUT ──────────────────────────── */

    .file-input-wrapper {
        border: 2px dashed #c9cfe8;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        background: #f5f7fc;
        transition: border-color 0.2s, background 0.2s;
        cursor: pointer;
        position: relative;
    }

    .file-input-wrapper:hover {
        border-color: var(--navy-mid);
        background: #eef1fb;
    }

    .file-input-wrapper input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }

    .file-input-wrapper svg {
        width: 28px;
        height: 28px;
        color: #9aa0b5;
        margin-bottom: 8px;
    }

    .file-input-wrapper p {
        font-size: 13px;
        color: #6b7280;
        margin: 0;
    }

    .file-input-wrapper span {
        color: var(--navy-mid);
        font-weight: 700;
    }

    /* ── CAMERA ──────────────────────────────── */

    .camera-container {
        border-radius: 8px;
        overflow: hidden;
        border: 2px solid #dde1ee;
        background: #0d0d0d;
        position: relative;
    }

    video {
        width: 100%;
        height: 260px;
        object-fit: cover;
        display: block;
    }

    .btn-capture {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: var(--navy);
        color: #fff;
        border: none;
        border-radius: 6px;
        font-family: 'Barlow', sans-serif;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.04em;
        cursor: pointer;
        margin-top: 10px;
        transition: background 0.2s;
    }

    .btn-capture:hover {
        background: var(--navy-mid);
    }

    canvas#canvas {
        width: 100%;
        height: auto;
        border-radius: 8px;
        border: 2px solid #dde1ee;
        margin-top: 12px;
    }

    /* ── SIGNATURE PAD ───────────────────────── */

    .sig-wrapper {
        border: 2px solid #dde1ee;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
    }

    #signature-pad {
        width: 100%;
        height: 180px;
        display: block;
        background: #fff;
        touch-action: none;
    }

    .btn-clear-sig {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 18px;
        background: #f5f7fc;
        color: var(--navy-mid);
        border: 1.5px solid #dde1ee;
        border-radius: 6px;
        font-family: 'Barlow', sans-serif;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 10px;
        transition: background 0.15s, border-color 0.15s;
    }

    .btn-clear-sig:hover {
        background: #eef0f7;
        border-color: #c9cfe8;
    }

    /* ── SUBMIT BUTTON ───────────────────────── */

    .btn-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        padding: 13px 36px;
        background: var(--red);
        color: #fff;
        border: none;
        border-radius: 7px;
        font-family: 'Barlow', sans-serif;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        cursor: pointer;
        margin-top: 10px;
        transition: background 0.2s, transform 0.1s;
        width: 100%;
    }

    .btn-submit:hover {
        background: var(--red-dark);
    }

    .btn-submit:active {
        transform: scale(0.98);
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

        .form-card {
            padding: 20px 16px 28px;
        }

        video {
            height: 200px;
        }

        #signature-pad {
            height: 140px;
        }

        .btn-submit {
            padding: 13px 20px;
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
            <a href="report_violation.php" class="active">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
                Report Violation
            </a>
        </div>

        <div class="nav-item">
            <a href="my_reports.php">
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
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
            </svg>
        </div>
        <div>
            <h3>Report Student Violation</h3>
            <p>Fill in all required fields to submit a violation report</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="form-card">

        <form action="save_violation.php" method="POST" enctype="multipart/form-data">

            <!-- STUDENT INFO -->
            <div class="section-label">Student Information</div>

            <div class="row g-3">

                <div class="col-12">
                    <label class="form-label-mcc" for="student_id">Student ID</label>
                    <input type="text" name="student_id" id="student_id"
                        class="form-control-mcc"
                        placeholder="e.g. 223-0001" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label-mcc" for="student_name">Student Name</label>
                    <input type="text" name="student_name" id="student_name"
                        class="form-control-mcc" readonly placeholder="Auto-filled">
                </div>

                <div class="col-md-6">
                    <label class="form-label-mcc" for="course">Course</label>
                    <input type="text" name="course" id="course"
                        class="form-control-mcc" readonly placeholder="Auto-filled">
                </div>

                <div class="col-md-6">
                    <label class="form-label-mcc" for="year_level">Year Level</label>
                    <input type="text" name="year_level" id="year_level"
                        class="form-control-mcc" readonly placeholder="Auto-filled">
                </div>

                <div class="col-md-6">
                    <label class="form-label-mcc" for="department">Department</label>
                    <input type="text" name="department" id="department"
                        class="form-control-mcc" readonly placeholder="Auto-filled">
                </div>

            </div>

            <!-- VIOLATION DETAILS -->
            <div class="section-label">Violation Details</div>

            <div class="row g-3">

                <div class="col-12">
                    <label class="form-label-mcc">Violation Category</label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="violation_category" value="Minor" id="minor" required>
                            <label for="minor">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="12" y1="8" x2="12" y2="12"/>
                                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                                </svg>
                                Minor Offense
                            </label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="violation_category" value="Major" id="major">
                            <label for="major">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/>
                                </svg>
                                Major Offense
                            </label>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label-mcc" for="violation_type">Violation</label>
                    <select name="violation_type" id="violation_type" class="form-select-mcc" required>
                        <option value="">Select Violation</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label-mcc" for="description">Description</label>
                    <textarea name="description" id="description"
                        class="form-control-mcc"
                        placeholder="Enter incident details..." required></textarea>
                </div>

            </div>

            <!-- EVIDENCE -->
            <div class="section-label">Evidence</div>

            <div class="row g-3">

                <div class="col-12">
                    <label class="form-label-mcc">Upload Evidence</label>
                    <div class="file-input-wrapper">
                        <input type="file" name="evidence">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                        </svg>
                        <p><span>Click to upload</span> or drag and drop</p>
                        <p style="font-size:11px; margin-top:4px;">JPG, PNG, PDF up to 10MB</p>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label-mcc">Camera Capture</label>
                    <div class="camera-container">
                        <video id="video" autoplay></video>
                    </div>
                    <button type="button" class="btn-capture" id="captureBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/>
                        </svg>
                        Capture Photo
                    </button>
                    <canvas id="canvas" width="400" height="300" class="d-none"></canvas>
                    <input type="hidden" name="camera_capture" id="camera_capture">
                </div>

            </div>

            <!-- SIGNATURE -->
            <div class="section-label">E-Signature</div>

            <div class="sig-wrapper">
                <canvas id="signature-pad" width="700" height="180"></canvas>
            </div>
            <button type="button" class="btn-clear-sig" id="clear-signature">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                </svg>
                Clear Signature
            </button>
            <input type="hidden" name="e_signature" id="e_signature">

            <!-- SUBMIT -->
            <div class="mt-4">
                <button type="submit" class="btn-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                    </svg>
                    Submit Report
                </button>
            </div>

        </form>

    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

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

/* AUTO DASH + AUTO FETCH */
document.getElementById('student_id').addEventListener('input', function(){
    let value = this.value;
    value = value.replace(/[^0-9-]/g,'');
    value = value.replace(/-/g,'');
    if(value.length > 3){
        value = value.substring(0,3) + "-" + value.substring(3);
    }
    this.value = value;

    let xhr = new XMLHttpRequest();
    xhr.open("POST","fetch_student.php",true);
    xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    xhr.onload = function(){
        if(this.status == 200 && this.responseText != ''){
            let data = JSON.parse(this.responseText);
            document.getElementById('student_name').value = data.fullname;
            document.getElementById('course').value       = data.course;
            document.getElementById('year_level').value   = data.year_level;
            document.getElementById('department').value   = data.department;
        }
    };
    xhr.send("student_id=" + value);
});

/* DYNAMIC VIOLATIONS */
const violationType = document.getElementById('violation_type');

const minorViolations = [
    "Disruptive Behavior","Littering","Dress Code","Unapproved Absences",
    "Inappropriate Language","Unauthorized Use of College Property",
    "Smoking on Campus","Failure to Display ID","Noise Violations","Minor Vandalism"
];

const majorViolations = [
    "Academic Dishonesty","Theft","Physical Violence","Substance Abuse",
    "Harassment","Unauthorized Entry","Forgery","Moral Infractions",
    "Weapons Possession","Cyber Bullying","Hazing","Major Dishonesty",
    "Extortion","Sexual Misconduct"
];

function populateViolations(list){
    violationType.innerHTML = '<option value="">Select Violation</option>';
    list.forEach(v => {
        violationType.innerHTML += `<option value="${v}">${v}</option>`;
    });
}

document.getElementById('minor').addEventListener('change', () => populateViolations(minorViolations));
document.getElementById('major').addEventListener('change', () => populateViolations(majorViolations));

/* CAMERA */
const video = document.getElementById('video');
navigator.mediaDevices.getUserMedia({ video: true })
    .then(stream => { video.srcObject = stream; })
    .catch(error => { console.log(error); });

/* CAPTURE PHOTO */
const canvas     = document.getElementById('canvas');
const captureBtn = document.getElementById('captureBtn');

captureBtn.addEventListener('click', function(){
    const context = canvas.getContext('2d');
    context.drawImage(video, 0, 0, 400, 300);
    canvas.classList.remove('d-none');
    document.getElementById('camera_capture').value = canvas.toDataURL('image/png');
});

/* SIGNATURE PAD */
const signatureCanvas = document.getElementById('signature-pad');
const signaturePad    = new SignaturePad(signatureCanvas);

document.getElementById('clear-signature').addEventListener('click', () => {
    signaturePad.clear();
});

/* SAVE SIGNATURE ON SUBMIT */
document.querySelector('form').addEventListener('submit', function(){
    document.getElementById('e_signature').value = signaturePad.toDataURL();
});

/* RESIZE SIGNATURE CANVAS */
function resizeSignaturePad(){
    const ratio  = Math.max(window.devicePixelRatio || 1, 1);
    const canvas = document.getElementById('signature-pad');
    canvas.width  = canvas.offsetWidth  * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext('2d').scale(ratio, ratio);
    signaturePad.clear();
}

window.addEventListener('resize', resizeSignaturePad);
resizeSignaturePad();

</script>

</body>
</html>