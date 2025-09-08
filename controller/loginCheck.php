<?php
session_start();
require_once('../model/userModel.php');

$username = trim($_REQUEST['username'] ?? '');
$password = trim($_REQUEST['password'] ?? '');
$remember = isset($_REQUEST['remember']) && $_REQUEST['remember'] === '1';

if ($username === "" || $password === "") {
    header('Location: ../view/login.php?error=badrequest');
    exit;
}

$user = ['username' => $username, 'password' => $password];
$status = login($user);

if (!$status) {
    header('Location: ../view/login.php?error=Invalid_user');
    exit;
}

$con = getConnection();
if (!$con) {
    header('Location: ../view/error500.php');
    exit;
}

$u_safe = mysqli_real_escape_string($con, $username);
$p_safe = mysqli_real_escape_string($con, $password);
$sql = "SELECT * FROM users WHERE username='{$u_safe}' AND password='{$p_safe}' LIMIT 1";
$result = mysqli_query($con, $sql);

if (!$result) {
    header('Location: ../view/error500.php');
    exit;
}

$row = mysqli_fetch_assoc($result);
if (!$row) {
    header('Location: ../view/login.php?error=Invalid_user');
    exit;
}

$role = (strtolower(trim((string)($row['role'] ?? ''))) === 'admin') ? 'Admin' : 'User';

session_regenerate_id(true);
$_SESSION['status']   = true;
$_SESSION['username'] = $row['username'];
$_SESSION['user_id']  = (int)($row['id'] ?? 0);
$_SESSION['role']     = $role;

if ($remember) {
    $exp = time() + 86400 * 30;
    setcookie('status', '1', $exp, '/');
    setcookie('remember_user', $_SESSION['username'], $exp, '/');
    setcookie('remember_role', $_SESSION['role'], $exp, '/');
}

if (strtolower($_SESSION['role']) === 'admin') {
    header('Location: ../view/admin_dashboard.php');
    exit;
} else {
    header('Location: ../view/user_dashboard.php');
    exit;
}
?>
