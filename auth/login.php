<?php

session_start();

include("../config/database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {

        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'admin') {
                header("Location: ../admin/dashboard.php");
            }

            elseif ($user['role'] == 'teacher') {
                header("Location: ../teacher/report_violation.php");
            }

            elseif ($user['role'] == 'csu') {
                header("Location: ../csu/report_violation.php");
            }

            elseif ($user['role'] == 'jassu') {
                header("Location: ../jassu/report_violation.php");
            }

        } else {
            header("Location: ../index.php?error=invalid_password");
            exit();
        }

    } else {
        header("Location: ../index.php?error=user_not_found");
        exit();
    }

}

?>