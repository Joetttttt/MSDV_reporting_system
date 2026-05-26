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

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Violation — MCC</title>

    <style>

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 14px;
            background: #0d2060;
            min-height: 100vh;
        }

        /* ══════════════════════════════
           PAGE WRAPPER
        ══════════════════════════════ */
        .page {
            padding: 20px 12px 60px;
        }

        /* ══════════════════════════════
           TOP BAR
        ══════════════════════════════ */
        .top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 720px;
            margin: 0 auto 18px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .top-bar-title {
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .top-nav {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .top-nav a {
            color: rgba(255,255,255,.65);
            font-size: 12px;
            padding: 5px 12px;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,.15);
            text-decoration: none;
            transition: all .15s;
        }

        .top-nav a:hover {
            background: rgba(255,255,255,.1);
            color: #fff;
        }

        .top-nav a.active {
            background: #b91c1c;
            color: #fff;
            border-color: #b91c1c;
        }

        /* ══════════════════════════════
           FORM HEADER
        ══════════════════════════════ */
        .form-header {
            max-width: 720px;
            margin: 0 auto 0;
            background: #fff;
            border-radius: 12px 12px 0 0;
            border-top: 6px solid #b91c1c;
            padding: 18px 20px 16px;
        }

        .form-header-seal {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .seal-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #0f1f5c;
            border: 2px solid #c8a84b;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
        }

        .seal-circle span {
            font-size: 5.5px;
            font-weight: 700;
            color: #c8a84b;
            text-align: center;
            line-height: 1.3;
            text-transform: uppercase;
        }

        .form-header-text h2 {
            font-size: 16px;
            font-weight: 700;
            color: #0d2060;
            margin-bottom: 2px;
        }

        .form-header-text p {
            font-size: 11px;
            color: #64748b;
            line-height: 1.4;
        }

        .divider {
            height: 1px;
            background: #e2e8f0;
            margin-top: 14px;
        }

        /* ══════════════════════════════
           SECTION CARDS
        ══════════════════════════════ */
        .section-card {
            max-width: 720px;
            margin: 0 auto;
            background: #fff;
            padding: 18px 20px;
            border-top: 1px solid #f1f5f9;
        }

        .section-card.last {
            border-radius: 0 0 12px 12px;
        }

        .section-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .09em;
            text-transform: uppercase;
            color: #b91c1c;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .step-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #b91c1c;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            flex-shrink: 0;
        }

        /* ══════════════════════════════
           FIELD GRID
        ══════════════════════════════ */
        .field-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }

        .field-row.full {
            grid-template-columns: 1fr;
        }

        @media (max-width: 520px) {
            .field-row {
                grid-template-columns: 1fr;
            }
        }

        /* ══════════════════════════════
           FORM ELEMENTS
        ══════════════════════════════ */
        .fg {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .fg label {
            font-size: 12px;
            font-weight: 600;
            color: #374151;
        }

        .fg label .req {
            color: #b91c1c;
            margin-left: 2px;
        }

        .fg input,
        .fg select,
        .fg textarea {
            padding: 9px 11px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 13px;
            color: #1e293b;
            font-family: inherit;
            background: #f8fafc;
            outline: none;
            width: 100%;
            transition: border-color .15s, background .15s;
        }

        .fg input:focus,
        .fg select:focus,
        .fg textarea:focus {
            border-color: #0d2060;
            background: #fff;
        }

        .fg input[readonly] {
            background: #f1f5f9;
            color: #64748b;
            cursor: default;
        }

        .fg textarea {
            resize: vertical;
            min-height: 90px;
        }

        .fg select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%2394a3b8' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' fill='none'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 11px center;
            background-color: #f8fafc;
            padding-right: 30px;
        }

        /* ══════════════════════════════
           RADIO BUTTONS
        ══════════════════════════════ */
        .radio-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 4px;
        }

        .radio-opt {
            display: flex;
            align-items: center;
            gap: 7px;
            cursor: pointer;
            padding: 9px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
            flex: 1;
            min-width: 120px;
            transition: all .15s;
        }

        .radio-opt:hover {
            border-color: #0d2060;
            background: #eff6ff;
        }

        .radio-opt.selected {
            border-color: #b91c1c;
            background: #fff5f5;
        }

        .radio-opt input[type="radio"] {
            accent-color: #b91c1c;
            width: 15px;
            height: 15px;
            flex-shrink: 0;
            padding: 0;
            border: none;
            background: none;
        }

        .radio-opt span {
            font-size: 13px;
            font-weight: 500;
            color: #374151;
        }

        /* ══════════════════════════════
           UPLOAD ZONE
        ══════════════════════════════ */
        .upload-zone {
            border: 2px dashed #cbd5e1;
            border-radius: 10px;
            padding: 18px 12px;
            text-align: center;
            cursor: pointer;
            background: #f8fafc;
            transition: all .15s;
        }

        .upload-zone:hover {
            border-color: #0d2060;
            background: #f0f6ff;
        }

        .upload-zone .uz-icon {
            font-size: 28px;
            color: #94a3b8;
            display: block;
            margin-bottom: 5px;
        }

        .upload-zone p {
            font-size: 13px;
            color: #64748b;
            margin: 0;
        }

        .upload-zone small {
            font-size: 11px;
            color: #94a3b8;
        }

        .upload-zone input[type="file"] {
            display: none;
        }

        .file-name-tag {
            font-size: 12px;
            color: #0d2060;
            margin-top: 5px;
            display: none;
            word-break: break-all;
        }

        /* ══════════════════════════════
           CAMERA
        ══════════════════════════════ */
        .cam-box {
            background: #0f172a;
            border-radius: 10px;
            overflow: hidden;
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
            width: 100%;
        }

        .cam-placeholder {
            text-align: center;
            color: rgba(255,255,255,.3);
            font-size: 12px;
        }

        .cam-placeholder .cam-icon {
            font-size: 28px;
            display: block;
            margin-bottom: 5px;
        }

        #cam-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10px;
        }

        .canvas-preview {
            width: 100%;
            border-radius: 8px;
            border: 1.5px solid #e2e8f0;
            display: none;
            margin-top: 8px;
        }

        /* ══════════════════════════════
           SIGNATURE PAD
        ══════════════════════════════ */
        #signature-pad {
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            width: 100%;
            height: 150px;
            background: #fff;
            cursor: crosshair;
            display: block;
            touch-action: none;
        }

        .sig-hint {
            font-size: 11px;
            color: #94a3b8;
            text-align: center;
            margin-top: 5px;
        }

        /* ══════════════════════════════
           BUTTONS
        ══════════════════════════════ */
        .btn-row {
            display: flex;
            gap: 7px;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        .btn-sm {
            padding: 7px 13px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all .15s;
        }

        .btn-navy {
            background: #0d2060;
            color: #fff;
        }

        .btn-navy:hover {
            background: #0a1a4d;
        }

        .btn-ghost {
            background: #f1f5f9;
            color: #374151;
            border: 1px solid #e2e8f0;
        }

        .btn-ghost:hover {
            background: #e2e8f0;
        }

        /* ══════════════════════════════
           SUBMIT ROW
        ══════════════════════════════ */
        .submit-area {
            max-width: 720px;
            margin: 14px auto 0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-cancel {
            padding: 11px 22px;
            background: rgba(255,255,255,.12);
            color: #fff;
            border: 1px solid rgba(255,255,255,.25);
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
            font-family: inherit;
            transition: background .15s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-cancel:hover {
            background: rgba(255,255,255,.2);
        }

        .btn-submit {
            padding: 11px 26px;
            background: #b91c1c;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: background .15s;
        }

        .btn-submit:hover {
            background: #991b1b;
        }

        @media (max-width: 400px) {
            .btn-cancel,
            .btn-submit {
                flex: 1 1 100%;
                justify-content: center;
            }
        }

        /* ══════════════════════════════
           ALERT (errors/success)
        ══════════════════════════════ */
        .alert {
            max-width: 720px;
            margin: 0 auto 10px;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
        }

        .alert-success {
            background: #dcfce7;
            border: 1px solid #86efac;
            color: #166534;
        }

        .alert-error {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #991b1b;
        }

    </style>

</head>
<body>

<div class="page">

    <!-- TOP BAR -->
    <div class="top-bar">
        <div class="top-bar-title">&#128737; Teacher Panel</div>
        <nav class="top-nav">
            <a href="report_violation.php" class="active">Report Violation</a>
            <a href="my_reports.php">My Reports</a>
            <a href="../auth/logout.php">Logout</a>
        </nav>
    </div>

    <!-- FORM HEADER -->
    <div class="form-header">
        <div class="form-header-seal">
            <div class="seal-circle">
                <span>Mandaue<br>City<br>College<br>2005</span>
            </div>
            <div class="form-header-text">
                <h2>Report Student Violation</h2>
                <p>MCC Discipline System &mdash; Complete all required fields to submit a report</p>
            </div>
        </div>
        <div class="divider"></div>
    </div>

    <form action="save_violation.php" method="POST" enctype="multipart/form-data">

        <!-- SECTION 1: STUDENT INFO -->
        <div class="section-card">

            <div class="section-label">
                <span class="step-badge">1</span> Student Information
            </div>

            <div class="field-row">
                <div class="fg">
                    <label>Student ID <span class="req">*</span></label>
                    <input
                        type="text"
                        name="student_id"
                        id="student_id"
                        placeholder="e.g. 223-09673"
                        inputmode="numeric"
                        required
                    >
                </div>
                <div class="fg">
                    <label>Student Name</label>
                    <input type="text" name="student_name" id="student_name" readonly placeholder="Auto-filled">
                </div>
            </div>

            <div class="field-row">
                <div class="fg">
                    <label>Course</label>
                    <input type="text" name="course" id="course" readonly placeholder="Auto-filled">
                </div>
                <div class="fg">
                    <label>Year Level</label>
                    <input type="text" name="year_level" id="year_level" readonly placeholder="Auto-filled">
                </div>
            </div>

            <div class="field-row full">
                <div class="fg">
                    <label>Department</label>
                    <input type="text" name="department" id="department" readonly placeholder="Auto-filled">
                </div>
            </div>

        </div>

        <!-- SECTION 2: VIOLATION DETAILS -->
        <div class="section-card">

            <div class="section-label">
                <span class="step-badge">2</span> Violation Details
            </div>

            <div class="fg" style="margin-bottom:14px">
                <label>Violation Category <span class="req">*</span></label>
                <div class="radio-group">
                    <label class="radio-opt" id="opt-minor">
                        <input type="radio" name="violation_category" value="Minor" required
                            onchange="loadViolations('minor');setSelected('opt-minor','opt-major')">
                        <span>Minor Offense</span>
                    </label>
                    <label class="radio-opt" id="opt-major">
                        <input type="radio" name="violation_category" value="Major"
                            onchange="loadViolations('major');setSelected('opt-major','opt-minor')">
                        <span>Major Offense</span>
                    </label>
                </div>
            </div>

            <div class="fg" style="margin-bottom:14px">
                <label>Violation Type <span class="req">*</span></label>
                <select name="violation_type" id="violation_type" required>
                    <option value="">Select a category first</option>
                </select>
            </div>

            <div class="fg">
                <label>Incident Description <span class="req">*</span></label>
                <textarea
                    name="description"
                    placeholder="Describe the incident in detail — include date, time, location, and what occurred..."
                    required
                ></textarea>
            </div>

        </div>

        <!-- SECTION 3: EVIDENCE -->
        <div class="section-card">

            <div class="section-label">
                <span class="step-badge">3</span> Evidence
            </div>

            <div class="field-row" style="align-items:start">

                <div class="fg">
                    <label>Upload Evidence</label>
                    <div class="upload-zone" onclick="document.getElementById('file_upload').click()">
                        <span class="uz-icon">&#9729;</span>
                        <p>Tap to upload a file</p>
                        <small>JPG, PNG, PDF up to 10MB</small>
                        <input type="file" id="file_upload" name="evidence" onchange="showFile(this)" accept="image/*,.pdf">
                    </div>
                    <div id="file-name" class="file-name-tag"></div>
                </div>

                <div class="fg">
                    <label>Camera Capture</label>
                    <div class="cam-box" id="cam-box">
                        <div class="cam-placeholder">
                            <span class="cam-icon">&#128247;</span>
                            Camera preview
                        </div>
                    </div>
                    <div class="btn-row">
                        <button type="button" class="btn-sm btn-navy" onclick="startCam()">&#9654; Start Camera</button>
                        <button type="button" class="btn-sm btn-ghost" onclick="captureCam()">&#9679; Capture</button>
                    </div>
                    <canvas id="cam-canvas" class="canvas-preview"></canvas>
                    <input type="hidden" name="camera_capture" id="camera_capture">
                </div>

            </div>

        </div>

        <!-- SECTION 4: E-SIGNATURE -->
        <div class="section-card last">

            <div class="section-label">
                <span class="step-badge">4</span> E-Signature
            </div>

            <div class="fg">
                <label>Sign below to confirm this report <span class="req">*</span></label>
                <canvas id="signature-pad"></canvas>
                <div class="sig-hint">Draw your signature using your mouse or finger</div>
            </div>

            <div class="btn-row">
                <button type="button" class="btn-sm btn-ghost" id="clear-sig">&#10006; Clear Signature</button>
            </div>

            <input type="hidden" name="e_signature" id="e_signature">

        </div>

        <!-- SUBMIT -->
        <div class="submit-area">
            <a href="my_reports.php" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-submit">&#10003; Submit Report</button>
        </div>

    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

<script>

/* ── VIOLATION LISTS ──────────────────────── */
const minorList = [
    'Disruptive Behavior','Littering','Dress Code',
    'Unapproved Absences','Inappropriate Language',
    'Unauthorized Use of College Property','Smoking on Campus',
    'Failure to Display ID','Noise Violations','Minor Vandalism'
];

const majorList = [
    'Academic Dishonesty','Theft','Physical Violence',
    'Substance Abuse','Harassment','Unauthorized Entry',
    'Forgery','Moral Infractions','Weapons Possession',
    'Cyber Bullying','Hazing','Major Dishonesty',
    'Extortion','Sexual Misconduct'
];

function loadViolations(type) {
    const sel = document.getElementById('violation_type');
    const list = type === 'minor' ? minorList : majorList;
    sel.innerHTML = '<option value="">Select violation</option>' +
        list.map(v => `<option value="${v}">${v}</option>`).join('');
}

function setSelected(on, off) {
    document.getElementById(on).classList.add('selected');
    document.getElementById(off).classList.remove('selected');
}

/* ── STUDENT ID AUTO FORMAT + FETCH ──────── */
document.getElementById('student_id').addEventListener('input', function () {

    let v = this.value.replace(/[^0-9]/g, '');
    if (v.length > 3) v = v.slice(0, 3) + '-' + v.slice(3, 10);
    this.value = v;

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'fetch_student.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
        if (this.status === 200 && this.responseText !== '') {
            try {
                const data = JSON.parse(this.responseText);
                document.getElementById('student_name').value = data.fullname || '';
                document.getElementById('course').value       = data.course   || '';
                document.getElementById('year_level').value   = data.year_level || '';
                document.getElementById('department').value   = data.department || '';
            } catch (e) {}
        }
    };
    xhr.send('student_id=' + encodeURIComponent(v));
});

/* ── FILE UPLOAD LABEL ────────────────────── */
function showFile(input) {
    const d = document.getElementById('file-name');
    if (input.files.length) {
        d.style.display = 'block';
        d.textContent   = 'Selected: ' + input.files[0].name;
    }
}

/* ── CAMERA ───────────────────────────────── */
let videoStream = null;

function startCam() {
    const box = document.getElementById('cam-box');
    let vid = document.getElementById('cam-vid');
    if (!vid) {
        vid = document.createElement('video');
        vid.id = 'cam-vid';
        vid.autoplay = true;
        vid.playsInline = true;
        vid.style.cssText = 'width:100%;height:100%;object-fit:cover;border-radius:10px';
        box.innerHTML = '';
        box.appendChild(vid);
    }
    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
        .then(stream => { videoStream = stream; vid.srcObject = stream; })
        .catch(() => {
            box.innerHTML = '<div class="cam-placeholder"><span class="cam-icon">&#128247;</span>Camera unavailable</div>';
        });
}

function captureCam() {
    const vid = document.getElementById('cam-vid');
    const cv  = document.getElementById('cam-canvas');
    if (!vid) return;
    cv.width  = vid.videoWidth  || 400;
    cv.height = vid.videoHeight || 250;
    cv.getContext('2d').drawImage(vid, 0, 0, cv.width, cv.height);
    cv.style.display = 'block';
    document.getElementById('camera_capture').value = cv.toDataURL('image/png');
}

/* ── SIGNATURE PAD ────────────────────────── */
let sigPad = null;

window.addEventListener('load', function () {
    const canvas = document.getElementById('signature-pad');

    function resize() {
        canvas.width  = canvas.offsetWidth;
        canvas.height = 150;
        if (sigPad) sigPad.clear();
    }

    sigPad = new SignaturePad(canvas, { penColor: '#0d2060' });
    resize();
    window.addEventListener('resize', resize);

    document.getElementById('clear-sig').addEventListener('click', function () {
        sigPad.clear();
    });
});

/* ── SAVE SIGNATURE ON SUBMIT ─────────────── */
document.querySelector('form').addEventListener('submit', function () {
    if (sigPad && !sigPad.isEmpty()) {
        document.getElementById('e_signature').value = sigPad.toDataURL();
    }
});

</script>

</body>
</html>