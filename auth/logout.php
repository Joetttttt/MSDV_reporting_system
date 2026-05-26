<?php

session_start();

$_SESSION = array();

session_destroy();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

header("Location: ../index.html");
exit();

?>