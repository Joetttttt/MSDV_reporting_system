<?php

session_start();

include("../config/database.php");

if(!isset($_SESSION['user_id'])){

    header("Location: ../index.html");
    exit();

}

if($_SESSION['role'] != 'admin'){

    header("Location: ../index.php");
    exit();

}

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $report_id =
    mysqli_real_escape_string(
        $conn,
        $_POST['report_id']
    );

    $admin_password =
    $_POST['admin_password'];

    // GET ADMIN
    $query = "

    SELECT *
    FROM users

    WHERE role='admin'

    LIMIT 1

    ";

    $result =
    mysqli_query($conn, $query);

    $admin =
    mysqli_fetch_assoc($result);

    // VERIFY PASSWORD
    if(
        password_verify(
            $admin_password,
            $admin['password']
        )
    ){

        // DELETE REPORT
        $delete = "

        DELETE FROM violations

        WHERE id='$report_id'

        ";

        mysqli_query($conn, $delete);

        echo "

        <script>

            alert('Report Deleted Successfully');

            window.location='reports.php';

        </script>

        ";

    }

    else{

        echo "

        <script>

            alert('Incorrect Admin Password');

            window.location='reports.php';

        </script>

        ";

    }

}

?>