<?php

function auditLog(
    $conn,
    $user_id,
    $full_name,
    $role,
    $action
)
{
    $ip_address =
    $_SERVER['REMOTE_ADDR'];

    mysqli_query(
        $conn,
        "INSERT INTO audit_logs
        (
            user_id,
            full_name,
            role,
            action,
            ip_address
        )
        VALUES
        (
            '$user_id',
            '$full_name',
            '$role',
            '$action',
            '$ip_address'
        )"
    );
}
?>