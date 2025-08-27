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
?>
<html>
<head>
    <title>View Profile</title>
    <link rel="stylesheet" type="text/css" href="../asset/auth.css">
</head>
<body>
    <h1>My Profile</h1>
    <form>
        <fieldset>
            <img src="../asset/img/a.jpg" alt="Profile Picture" id="profilePic" width="100" height="100"><br><br>
            <strong>Name:</strong> Apurbo Biswas<br><br>
            <strong>Email:</strong> apurbobiswas32@gmail.com
        </fieldset>
        <fieldset>
            <input type="button" value="Edit Profile" onclick="window.location.href='edit_profile.html'" />
            <input type="button" value="Update Password" onclick="window.location.href='update_password.html'" />
            <input type="button" value="Back to Dashboard" onclick="window.location.href='user_dashboard.php'" />
        </fieldset>
    </form>
</body>
</html>
