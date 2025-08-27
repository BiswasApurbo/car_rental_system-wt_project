<?php
session_start();
$_SESSION = [];
session_destroy();
setcookie('status', '', time() - 3600, '/');
setcookie('remember_user', '', time() - 3600, '/');
setcookie('remember_role', '', time() - 3600, '/');
setcookie(session_name(), '', time() - 3600, '/');
header('location: ../view/login.php');
exit;
?>
