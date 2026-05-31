<?php

require_once "../config/database.php";
require_once "../config/session.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin')
{
    header("Location: ../index.php");
    exit();
}