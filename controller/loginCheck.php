<?php
session_start();

$username = trim($_REQUEST['username'] ?? '');
$password = trim($_REQUEST['password'] ?? '');
$remember = isset($_REQUEST['remember']) && $_REQUEST['remember'] === '1';

if ($username == "" || $password == "") {
    echo "please type username/password first!";
    exit;
} else {
    if ($username === "admin" && $password === "admin") {
        session_regenerate_id(true);
        $_SESSION['status']   = true;
        $_SESSION['username'] = $username;
        $_SESSION['role']     = 'admin';

        if ($remember) {
            setcookie('status', '1', time() + (86400 * 30), '/'); 
            setcookie('remember_user', $username, time() + (86400 * 30), '/');
            setcookie('remember_role', 'admin', time() + (86400 * 30), '/');
        }

        header('location: ../view/admin_dashboard.php');
        exit;
    }
    if ($username == $password) {
        session_regenerate_id(true);
        $_SESSION['status']   = true; 
        $_SESSION['username'] = $username; 
        $_SESSION['role']     = 'user';

        if ($remember) {
            setcookie('status', '1', time() + (86400 * 30), '/'); 
            setcookie('remember_user', $username, time() + (86400 * 30), '/');
            setcookie('remember_role', 'user', time() + (86400 * 30), '/');
        }
        header('location: ../view/user_dashboard.php');
        exit;
    }
    header('location: ../view/error404.php');
    exit;
}
?>
