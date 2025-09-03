<?php
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
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

$name = 'Apurbo Biswas';
$email = 'apurbobiswas32@gmail.com';
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name === '') {
        $errors['name'] = 'Please enter name!';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter valid email!';
    }

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $errors['avatar'] = 'Upload failed. Try again.';
        } else {
            $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
            if (!in_array(mime_content_type($_FILES['avatar']['tmp_name']), $allowed, true)) {
                $errors['avatar'] = 'Only JPG, PNG, GIF, or WEBP allowed.';
            }
            if ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
                $errors['avatar'] = 'Max file size is 2MB.';
            }
        }
    }

    if (!$errors) {
        $success = 'Profile updated!';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="../asset/auth.css">
    <style>
        .err { color:red; font-weight:600; margin:4px 0; }
        .ok { color:green; font-weight:700; margin-top:10px; }
        .center-success { text-align:center; font-weight:bold; color:green; margin:8px 0 16px; }
    </style>
</head>
<body>
    <h1>Edit Profile</h1>

    <?php if ($success): ?>
        <p class="center-success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post" action="" enctype="multipart/form-data" onsubmit="return editProfileCheck()">
        <fieldset>
            Name:
            <input type="text" id="editName" name="name" value="<?= htmlspecialchars($name) ?>" onblur="checkEditName()">
            <p id="nameError" class="err"><?= isset($errors['name']) ? htmlspecialchars($errors['name']) : '' ?></p>

            Email:
            <input type="text" id="editEmail" name="email" value="<?= htmlspecialchars($email) ?>" onblur="checkEditEmail()">
            <p id="emailError" class="err"><?= isset($errors['email']) ? htmlspecialchars($errors['email']) : '' ?></p>

            Profile Picture:
            <input type="file" id="editAvatar" name="avatar" accept="image/*">
            <p id="avatarError" class="err"><?= isset($errors['avatar']) ? htmlspecialchars($errors['avatar']) : '' ?></p>

            <input type="submit" value="Save Changes">
            <input type="button" value="Back to Dashboard" onclick="window.location.href='user_dashboard.php'">
            <p id="saveSuccess" class="ok"></p>
        </fieldset>
    </form>

    <script>
        function checkEditName() {
            const name = document.getElementById('editName').value.trim();
            document.getElementById('nameError').innerHTML = name === "" ? "Please enter name!" : "";
        }
        function checkEditEmail() {
            const email = document.getElementById('editEmail').value.trim();
            const valid = email !== "" && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            document.getElementById('emailError').innerHTML = valid ? "" : "Please enter valid email!";
        }
        function editProfileCheck() {
            checkEditName();
            checkEditEmail();
            const nameOk = document.getElementById('nameError').innerHTML === "";
            const emailOk = document.getElementById('emailError').innerHTML === "";
            if (nameOk && emailOk) {
                document.getElementById('saveSuccess').innerHTML = "Submitting…";
                return true;
            }
            document.getElementById('saveSuccess').innerHTML = "";
            return false;
        }
    </script>
</body>
</html>
