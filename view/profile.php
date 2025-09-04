<?php
session_start();

if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) {
    if (isset($_COOKIE['status']) && $_COOKIE['status'] === '1') {
        $_SESSION['status'] = true;
        if (!isset($_SESSION['username']) && isset($_COOKIE['remember_user'])) {
            $_SESSION['username'] = $_COOKIE['remember_user'];
        }
    } else {
        header('location: ../view/login.php?error=badrequest');
        exit;
    }
}

$profile = $_SESSION['profile'] ?? [];
$name    = trim($profile['name']  ?? ($_SESSION['username'] ?? 'User'));
$email   = trim($profile['email'] ?? '');
$avatar  = trim($profile['avatar'] ?? '../asset/img/a.jpg');

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>View Profile</title>
    <link rel="stylesheet" type="text/css" href="../asset/auth.css">
    <style>
        .profile-card{max-width:520px;margin:20px auto;padding:20px;border:1px solid #ddd;border-radius:10px}
        .profile-actions input{margin-right:8px;margin-top:8px}
        .avatar{border-radius:8px;object-fit:cover;border:1px solid #ccc}
        .meta{color:#444}
    </style>
</head>
<body>
    <h1>My Profile</h1>
    <form class="profile-card">
        <fieldset>
            <img src="<?= h($avatar) ?>" alt="Profile Picture" id="profilePic" width="120" height="120" class="avatar"><br><br>
            <div class="meta"><strong>Name:</strong> <?= h($name) ?></div><br>
            <div class="meta"><strong>Email:</strong> <?= h($email ?: '—') ?></div>
        </fieldset>
        <fieldset class="profile-actions">
            <input type="button" value="Edit Profile" onclick="window.location.href='edit_profile.php'">
            <input type="button" value="Update Password" onclick="window.location.href='update_password.php'">
            <input type="button" value="Back to Dashboard" onclick="window.location.href='user_dashboard.php'"> <br> <br> <br>
            <div>Click here for services:</div>
            <input type="button" value="Customer Services" onclick="window.location.href='customer_services.php'">
        </fieldset>
    </form>
</body>
</html>
