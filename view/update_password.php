<?php
session_start();
require_once('../model/userModel.php');

if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) {
    if (isset($_COOKIE['status']) && (string)$_COOKIE['status'] === '1') {
        $_SESSION['status'] = true;
        if (!isset($_SESSION['username']) && isset($_COOKIE['remember_user'])) {
            $_SESSION['username'] = $_COOKIE['remember_user'];
        }
        if (!isset($_SESSION['role']) && isset($_COOKIE['remember_role'])) {
            $c = strtolower(trim((string)$_COOKIE['remember_role']));
            $_SESSION['role'] = ($c === 'admin') ? 'Admin' : 'User';
        }
    } else {
        header('location: ../view/login.php?error=badrequest');
        exit;
    }
}
$id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($id === 0 && !empty($_SESSION['username'])) {
    $all = getAlluser();
    foreach ($all as $u) {
        if (isset($u['username']) && $u['username'] === $_SESSION['username']) {
            $id = (int)($u['id'] ?? 0);
            break;
        }
    }
}
$currentPassword = '';
if ($id > 0) {
    $urow = getUserById($id);
    $currentPassword = isset($urow['password']) ? $urow['password'] : '';
} else {
    $currentPassword = $_SESSION['auth_password'] ?? '';
}

$errors = ['old' => '', 'new' => '', 'general' => ''];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPass = isset($_POST['old_password']) ? trim($_POST['old_password']) : '';
    $newPass = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';

    if ($oldPass === '') {
        $errors['old'] = 'Enter old password!';
    } else {
        $oldOk = false;
        if ($currentPassword !== '') {
            if (function_exists('password_verify') && password_verify($oldPass, $currentPassword)) {
                $oldOk = true;
            } elseif ($oldPass === $currentPassword) {
                $oldOk = true;
            }
        } else {
            $oldOk = false;
        }

        if (!$oldOk) {
            $errors['old'] = 'Old password is incorrect!';
        }
    }

    if ($newPass === '') {
        $errors['new'] = 'Enter new password!';
    } elseif (strlen($newPass) < 4) {
        $errors['new'] = 'New password must be at least 4 characters!';
    } elseif ($oldPass !== '' && $oldPass === $newPass) {
        $errors['new'] = 'New password must be different from old password!';
    }

    if ($errors['old'] === '' && $errors['new'] === '') {
        if ($id > 0) {
            $con = getConnection();
            if (!$con) {
                $errors['general'] = 'Database connection failed.';
            } else {
                $toStore = $newPass;
                if (function_exists('password_hash')) {
                    $info = password_get_info($currentPassword);
                    if (isset($info['algo']) && $info['algo'] !== 0) {
                        $toStore = password_hash($newPass, PASSWORD_DEFAULT);
                    }
                }

                $safePass = mysqli_real_escape_string($con, $toStore);
                $sql = "UPDATE users SET password='{$safePass}' WHERE id=" . (int)$id;
                if (mysqli_query($con, $sql)) {
                    $success = 'Password updated successfully!';
                    $currentPassword = $toStore;
                    $_SESSION['auth_password'] = $toStore;
                } else {
                    $errors['general'] = 'Unable to update password. Try again.';
                    error_log("update password failed: " . mysqli_error($con));
                }
            }
        } else {
            $_SESSION['auth_password'] = $newPass;
            $success = 'Password updated successfully!';
            $currentPassword = $newPass;
        }
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

    <?php if (!empty($success)): ?>
        <p class="center-success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <?php if (!empty($errors['general'])): ?>
        <p class="error-msg" style="text-align:center;"><?= htmlspecialchars($errors['general']) ?></p>
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
