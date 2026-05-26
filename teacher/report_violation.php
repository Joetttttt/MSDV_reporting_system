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
<html>
<head>

<title>Report Violation</title>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f5f6fa;
}

.sidebar{
    height:100vh;
    background:#111827;
}

.sidebar a{
    color:white;
    text-decoration:none;
    display:block;
    padding:15px;
}

.sidebar a:hover{
    background:#1f2937;
}

.main-card{
    border-radius:15px;
    overflow:hidden;
}

.header-bg{
    background:#05056b;
}

#signature-pad{
    background:white;
    border:1px solid #ccc;
}

video{
    object-fit:cover;
}

</style>

</head>

<body>

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-2 sidebar p-0">

            <h4 class="text-white text-center py-4">
                TEACHER PANEL
            </h4>

            <a href="report_violation.php">
                Report Violation
            </a>

            <a href="my_reports.php">
                My Reports
            </a>

            <a href="../auth/logout.php">
                Logout
            </a>

        </div>

        <!-- CONTENT -->
        <div class="col-md-10 p-4">

            <div class="card shadow border-0 main-card">

                <!-- HEADER -->
                <div class="card-header header-bg text-white p-4">

                    <h3 class="mb-0">
                        Report Student Violation
                    </h3>

                </div>

                <!-- BODY -->
                <div class="card-body">

                    <form
                        action="save_violation.php"
                        method="POST"
                        enctype="multipart/form-data"
                    >

                        <!-- STUDENT ID -->
                        <div class="mb-3">

                            <label class="form-label">
                                Student ID
                            </label>

                            <input
                                type="text"
                                name="student_id"
                                id="student_id"
                                class="form-control"
                                placeholder="Enter Student ID"
                                required
                            >

                        </div>

                        <!-- STUDENT NAME -->
                        <div class="mb-3">

                            <label class="form-label">
                                Student Name
                            </label>

                            <input
                                type="text"
                                name="student_name"
                                id="student_name"
                                class="form-control"
                                readonly
                            >

                        </div>

                        <!-- COURSE -->
                        <div class="mb-3">

                            <label class="form-label">
                                Course
                            </label>

                            <input
                                type="text"
                                name="course"
                                id="course"
                                class="form-control"
                                readonly
                            >

                        </div>

                        <!-- YEAR LEVEL -->
                        <div class="mb-3">

                            <label class="form-label">
                                Year Level
                            </label>

                            <input
                                type="text"
                                name="year_level"
                                id="year_level"
                                class="form-control"
                                readonly
                            >

                        </div>

                        <!-- DEPARTMENT -->
                        <div class="mb-3">

                            <label class="form-label">
                                Department
                            </label>

                            <input
                                type="text"
                                name="department"
                                id="department"
                                class="form-control"
                                readonly
                            >

                        </div>

                        <!-- VIOLATION CATEGORY -->
                        <div class="mb-3">

                            <label class="form-label fw-bold">

                                Violation Category

                            </label>

                            <br>

                            <input
                                type="radio"
                                name="violation_category"
                                value="Minor"
                                id="minor"
                                required
                            >

                            <label for="minor">
                                Minor Offense
                            </label>

                            &nbsp;&nbsp;

                            <input
                                type="radio"
                                name="violation_category"
                                value="Major"
                                id="major"
                                required
                            >

                            <label for="major">
                                Major Offense
                            </label>

                        </div>

                        <!-- VIOLATION -->
                        <div class="mb-3">

                            <label class="form-label">

                                Violation

                            </label>

                            <select
                                name="violation_type"
                                id="violation_type"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Violation
                                </option>

                            </select>

                        </div>

                        <!-- DESCRIPTION -->
                        <div class="mb-3">

                            <label class="form-label">

                                Description

                            </label>

                            <textarea
                                name="description"
                                class="form-control"
                                rows="5"
                                placeholder="Enter incident details"
                                required
                            ></textarea>

                        </div>

                        <!-- EVIDENCE -->
                        <div class="mb-4">

                            <label class="form-label fw-bold">

                                Upload Evidence

                            </label>

                            <input
                                type="file"
                                name="evidence"
                                class="form-control"
                            >

                        </div>

                        <!-- CAMERA CAPTURE -->
                        <div class="mb-4">

                            <label class="form-label fw-bold">

                                Camera Capture

                            </label>

                            <video
                                id="video"
                                width="100%"
                                height="300"
                                autoplay
                                class="border rounded">
                            </video>

                            <button
                                type="button"
                                class="btn btn-primary mt-2"
                                id="captureBtn"
                            >

                                Capture Photo

                            </button>

                            <canvas
                                id="canvas"
                                width="400"
                                height="300"
                                class="border rounded mt-3 d-none">
                            </canvas>

                            <input
                                type="hidden"
                                name="camera_capture"
                                id="camera_capture"
                            >

                        </div>

                        <!-- E-SIGNATURE -->
                        <div class="mb-4">

                            <label class="form-label fw-bold">

                                E-Signature

                            </label>

                            <br>

                            <canvas
                                id="signature-pad"
                                width="500"
                                height="200">
                            </canvas>

                            <br>

                            <button
                                type="button"
                                class="btn btn-secondary mt-2"
                                id="clear-signature"
                            >

                                Clear Signature

                            </button>

                            <input
                                type="hidden"
                                name="e_signature"
                                id="e_signature"
                            >

                        </div>

                        <!-- SUBMIT -->
                        <button
                            type="submit"
                            class="btn btn-danger"
                        >

                            Submit Report

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

<script>
    

/* AUTO FETCH STUDENT INFO */

/* AUTO DASH + AUTO FETCH */

document.getElementById('student_id')
.addEventListener('input', function(){

    let value = this.value;

    // REMOVE INVALID CHARACTERS
    value = value.replace(/[^0-9-]/g,'');

    // REMOVE OLD DASH
    value = value.replace(/-/g,'');

    // AUTO ADD DASH AFTER 223
    if(value.length > 3){

        value =
        value.substring(0,3)
        + "-"
        + value.substring(3);

    }

    // UPDATE INPUT
    this.value = value;

    // FETCH STUDENT
    let xhr = new XMLHttpRequest();

    xhr.open(
        "POST",
        "fetch_student.php",
        true
    );

    xhr.setRequestHeader(
        "Content-type",
        "application/x-www-form-urlencoded"
    );

    xhr.onload = function(){

        if(this.status == 200){

            if(this.responseText != ''){

                let data =
                JSON.parse(this.responseText);

                document.getElementById(
                    'student_name'
                ).value = data.fullname;

                document.getElementById(
                    'course'
                ).value = data.course;

                document.getElementById(
                    'year_level'
                ).value = data.year_level;

                document.getElementById(
                    'department'
                ).value = data.department;

            }

        }

    };

    xhr.send(
        "student_id=" + value
    );

});




/* DYNAMIC VIOLATIONS */

const violationType =
document.getElementById('violation_type');

document.getElementById('minor')
.addEventListener('change', function(){

    violationType.innerHTML = `

        <option value="">
            Select Violation
        </option>

        <option value="Disruptive Behavior">
            Disruptive Behavior
        </option>

        <option value="Littering">
            Littering
        </option>

        <option value="Dress Code">
            Dress Code
        </option>

        <option value="Unapproved Absences">
            Unapproved Absences
        </option>

        <option value="Inappropriate Language">
            Inappropriate Language
        </option>

        <option value="Unauthorized Use of College Property">
            Unauthorized Use of College Property
        </option>

        <option value="Smoking on Campus">
            Smoking on Campus
        </option>

        <option value="Failure to Display ID">
            Failure to Display ID
        </option>

        <option value="Noise Violations">
            Noise Violations
        </option>

        <option value="Minor Vandalism">
            Minor Vandalism
        </option>

    `;

});

document.getElementById('major')
.addEventListener('change', function(){

    violationType.innerHTML = `

        <option value="">
            Select Violation
        </option>

        <option value="Academic Dishonesty">
            Academic Dishonesty
        </option>

        <option value="Theft">
            Theft
        </option>

        <option value="Physical Violence">
            Physical Violence
        </option>

        <option value="Substance Abuse">
            Substance Abuse
        </option>

        <option value="Harassment">
            Harassment
        </option>

        <option value="Unauthorized Entry">
            Unauthorized Entry
        </option>

        <option value="Forgery">
            Forgery
        </option>

        <option value="Moral Infractions">
            Moral Infractions
        </option>

        <option value="Weapons Possession">
            Weapons Possession
        </option>

        <option value="Cyber Bullying">
            Cyber Bullying
        </option>

        <option value="Hazing">
            Hazing
        </option>

        <option value="Major Dishonesty">
            Major Dishonesty
        </option>

        <option value="Extortion">
            Extortion
        </option>

        <option value="Sexual Misconduct">
            Sexual Misconduct
        </option>

    `;

});


/* CAMERA ACCESS */

const video = document.getElementById('video');

navigator.mediaDevices.getUserMedia({

    video: true

})

.then(stream => {

    video.srcObject = stream;

})

.catch(error => {

    console.log(error);

});


/* CAPTURE PHOTO */

const canvas = document.getElementById('canvas');

const captureBtn =
document.getElementById('captureBtn');

captureBtn.addEventListener('click', function(){

    const context = canvas.getContext('2d');

    context.drawImage(video, 0, 0, 400, 300);

    canvas.classList.remove('d-none');

    let imageData = canvas.toDataURL('image/png');

    document.getElementById('camera_capture').value = imageData;

});


/* SIGNATURE PAD */

const signatureCanvas =
document.getElementById('signature-pad');

const signaturePad =
new SignaturePad(signatureCanvas);

document.getElementById('clear-signature')
.addEventListener('click', function(){

    signaturePad.clear();

});


/* SAVE SIGNATURE */

document.querySelector('form')
.addEventListener('submit', function(){

    let signatureData =
    signaturePad.toDataURL();

    document.getElementById('e_signature').value =
    signatureData;

});

</script>

</body>
</html>