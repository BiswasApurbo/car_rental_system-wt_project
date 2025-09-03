<?php
session_start();

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$remember = isset($_POST['remember']) && $_POST['remember'] === '1';

if ($username === '' && $password === '' && isset($_SESSION['pending_login'])) {
    $handoff = $_SESSION['pending_login'];
    $username = trim($handoff['username'] ?? '');
    $password = trim($handoff['password'] ?? '');
    $remember = isset($handoff['remember']) && $handoff['remember'] === '1';
    unset($_SESSION['pending_login']);
}

if ($username === '' || $password === '') {
    header('Location: ../view/login.php?error=badrequest');
    exit;
}

if ($username === 'admin' && $password === 'admin') {
    session_regenerate_id(true);
    $_SESSION['status'] = true;
    $_SESSION['username'] = $username;
    $_SESSION['role'] = 'admin';

    if ($remember) {
        $exp = time() + (86400 * 30);
        setcookie('status', '1', $exp, '/');
        setcookie('remember_user', $username, $exp, '/');
        setcookie('remember_role', 'admin', $exp, '/');
    }

    header('Location: ../view/admin_dashboard.php');
    exit;
}

if ($username === $password) {
    session_regenerate_id(true);
    $_SESSION['status'] = true;
    $_SESSION['username'] = $username;
    $_SESSION['role'] = 'user';

    if ($remember) {
        $exp = time() + (86400 * 30);
        setcookie('status', '1', $exp, '/');
        setcookie('remember_user', $username, $exp, '/');
        setcookie('remember_role', 'user', $exp, '/');
    }

    header('Location: ../view/user_dashboard.php');
    exit;
}

header('Location: ../view/login.php?error=Invalid_user');
exit;
