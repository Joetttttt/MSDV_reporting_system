<?php

include("../config/database.php");

$notificationCount = mysqli_num_rows(
    mysqli_query(
        $conn,
        "SELECT *
         FROM notifications
         WHERE user_id='".$_SESSION['user_id']."'
         AND is_read=0"
    )
);

?>

<div class="bg-dark text-white vh-100 p-3"
style="width:280px;position:fixed;">

    <h4 class="text-center mb-4">
        MSDV Student
    </h4>

    <hr>

    <ul class="nav flex-column">

        <li class="nav-item mb-2">

            <a href="dashboard.php"
               class="nav-link text-white">

                Dashboard

            </a>

        </li>

        <li class="nav-item mb-2">

            <a href="violations.php"
               class="nav-link text-white">

                My Violations

            </a>

        </li>

        <li class="nav-item mb-2">

            <a href="appeals.php"
               class="nav-link text-white">

                Appeals

            </a>

        </li>

        <li class="nav-item mb-2">

            <a href="notifications.php"
               class="nav-link text-white d-flex justify-content-between align-items-center">

                <span>
                    Notifications
                </span>

                <?php if($notificationCount > 0) { ?>

                <span class="badge bg-danger">

                    <?php echo $notificationCount; ?>

                </span>

                <?php } ?>

            </a>

        </li>

        <li class="nav-item mt-4">

            <a href="../logout.php"
               class="btn btn-danger w-100">

                Logout

            </a>

        </li>

    </ul>

</div>