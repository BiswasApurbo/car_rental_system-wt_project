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
$currentPassword = $_SESSION['auth_password'] 
    ?? (($_SESSION['role'] ?? '') === 'admin' ? 'admin' : ($_SESSION['username'] ?? ''));

$errors = ['old' => '', 'new' => ''];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPass = $_POST['old_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';

    if ($oldPass === '') {
        $errors['old'] = 'Enter old password!';
    } elseif ($oldPass !== $currentPassword) {
        $errors['old'] = 'Old password is incorrect!';
    }

    if ($newPass === '') {
        $errors['new'] = 'Enter new password!';
    } elseif (strlen($newPass) < 4) {
        $errors['new'] = 'New password must be at least 4 characters!';
    } elseif ($newPass === $oldPass) {
        $errors['new'] = 'New password must be different from old password!';
    }

    if ($errors['old'] === '' && $errors['new'] === '') {
        $_SESSION['auth_password'] = $newPass;
        $success = 'Password updated successfully!';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Update Password</title>
    <link rel="stylesheet" type="text/css" href="../asset/auth.css">
    <style>
        .error-msg { color: red; font-weight: 600; }
        .center-success { text-align: center; font-weight: bold; color: green; margin: 8px 0 16px; }
    </style>
</head>
<body>
    <h1>Update Password</h1>

    <?php if ($success): ?>
        <p class="center-success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post" action="" onsubmit="return updatePasswordCheck()">
        <fieldset>
            Old Password:
            <input type="password" id="oldPass" name="old_password">
            <p id="oldError" class="error-msg"><?= htmlspecialchars($errors['old']) ?></p>

            New Password:
            <input type="password" id="newPass" name="new_password">
            <p id="newError" class="error-msg"><?= htmlspecialchars($errors['new']) ?></p>

            <input type="submit" value="Update Password">
            <p id="updateSuccess"></p>

            <input type="button" value="Back to Dashboard" onclick="window.location.href='user_dashboard.php'">
        </fieldset>
    </form>

    <script>
        function updatePasswordCheck() {
            const oldPass = document.getElementById('oldPass').value.trim();
            const newPass = document.getElementById('newPass').value.trim();

            let valid = true;
            document.getElementById('oldError').innerHTML = "";
            document.getElementById('newError').innerHTML = "";
            document.getElementById('updateSuccess').innerHTML = "";

            if (oldPass === "") {
                document.getElementById('oldError').innerHTML = "Enter old password!";
                valid = false;
            }
            if (newPass === "") {
                document.getElementById('newError').innerHTML = "Enter new password!";
                valid = false;
            } else if (newPass.length < 4) {
                document.getElementById('newError').innerHTML = "New password must be at least 4 characters!";
                valid = false;
            }
            if (oldPass !== "" && newPass !== "" && oldPass === newPass) {
                document.getElementById('newError').innerHTML = "New password must be different from old password!";
                valid = false;
            }

            if (valid) {
                document.getElementById('updateSuccess').innerHTML = "Submitting…";
                return true;
            }
            return false;
        }
    </script>
</body>
</html>
