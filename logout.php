<?php
session_start();
session_destroy();
header("Location: ../index.html");
exit();
?><?php
session_start();
session_destroy();
setcookie('remember_user', '', time() - 3600, "/");
header("Location: ../index.php");
exit();
?>