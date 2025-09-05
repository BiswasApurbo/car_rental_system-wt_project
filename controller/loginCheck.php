<?php
session_start();
require_once('../model/userModel.php');
$username = trim($_REQUEST['username'] ?? '');
$password = trim($_REQUEST['password'] ?? '');
$remember = isset($_REQUEST['remember']) && $_REQUEST['remember'] === '1';

if ($username == "" || $password == "") {
    header('location: ../view/login.php?error=badrequest');
    exit;
} else {
    $user = ['username'=> $username, 'password'=> $password];
    $status = login($user);

    if ($status) {
        $con = getConnection();
        if (!$con) {
            header('location: ../view/login.php?error=badrequest');
            exit;
        }

        $u_safe = mysqli_real_escape_string($con, $username);
        $p_safe = mysqli_real_escape_string($con, $password);
        $sql = "select * from users where username='{$u_safe}' and password='{$p_safe}' limit 1";
        $result = mysqli_query($con, $sql);
        if ($result === false) {
            header('location: ../view/login.php?error=badrequest');
            exit;
        }

        $row = mysqli_fetch_assoc($result);
        if (!$row) {
            header('location: ../view/login.php?error=Invalid_user');
            exit;
        }
        $roleRaw = isset($row['role']) ? $row['role'] : '';
        $role = (strtolower(trim((string)$roleRaw)) === 'admin') ? 'Admin' : 'User';

        session_regenerate_id(true);
        $_SESSION['status']   = true;
        $_SESSION['username'] = $row['username'];
        $_SESSION['user_id']  = isset($row['id']) ? (int)$row['id'] : 0;
        $_SESSION['role']     = $role;

        if ($remember) {
            $exp = time() + (86400 * 30);
            setcookie('status', '1', $exp, '/');
            setcookie('remember_user', $_SESSION['username'], $exp, '/');
            setcookie('remember_role', $_SESSION['role'], $exp, '/');
        }

        if (strtolower($_SESSION['role']) === 'admin') {
            header('location: ../view/admin_dashboard.php');
            exit;
        } else {
            header('location: ../view/user_dashboard.php');
            exit;
        }
    } else {
        header('location: ../view/login.php?error=Invalid_user');
        exit;
    }
}
