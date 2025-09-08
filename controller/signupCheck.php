<?php
session_start();
require_once('../model/userModel.php');

$username = trim($_REQUEST['username'] ?? '');
$password = trim($_REQUEST['password'] ?? '');
$email    = trim($_REQUEST['email'] ?? '');

if ($username === "" || $password === "" || $email === "") {
    header('Location: ../view/signup.php?error=badrequest');
    exit;
}

try {
    $users = getAlluser();
    $exists = false;

    foreach ($users as $u) {
        if (isset($u['email']) && $u['email'] === $email) {
            $exists = true;
            break;
        }
    }

    if ($exists) {
        header('Location: ../view/signup.php?error=email_exists');
        exit;
    }

    $user = [
        'username' => $username,
        'password' => $password,
        'email'    => $email,
        'role'     => 'User'
    ];

    $status = addUser($user);

    if ($status) {
        header('Location: ../view/login.php?success=registered');
        exit;
    } else {
        header('Location: ../view/error500.php');
        exit;
    }

} catch (Throwable $e) {
    header('Location: ../view/error500.php');
    exit;
}
?>
