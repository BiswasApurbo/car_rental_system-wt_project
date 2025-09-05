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
if (strtolower($_SESSION['role']) !== 'admin') {
    header('location: ../view/login.php?error=badrequest');
    exit;
}

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
$id = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
} else {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
}

if ($id <= 0) {
    header('Location: admin_panel_user_management.php');
    exit;
}

$user = getUserById($id);
if (!$user) {
    header('Location: admin_panel_user_management.php');
    exit;
}

$errors = ['username'=>'', 'email'=>'', 'profile'=>'', 'general'=>''];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($username === '') {
        $errors['username'] = 'Please enter username!';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email!';
    }
    if ($errors['email'] === '') {
        $all = getAlluser();
        foreach ($all as $u) {
            if (isset($u['email']) && $u['email'] === $email) {
                $otherId = isset($u['id']) ? (int)$u['id'] : 0;
                if ($otherId !== $id) {
                    $errors['email'] = 'This email is already registered to another user!';
                    break;
                }
            }
        }
    }
    $profilePath = $user['profile'] ?? '';
    if (isset($_FILES['profile']) && $_FILES['profile']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['profile']['error'] !== UPLOAD_ERR_OK) {
            $errors['profile'] = 'Upload failed. Try again.';
        } else {
            $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
            $tmpName = $_FILES['profile']['tmp_name'];
            $mime = mime_content_type($tmpName);
            if (!in_array($mime, $allowed, true)) {
                $errors['profile'] = 'Only JPG, PNG, GIF, or WEBP allowed.';
            }
            if ($_FILES['profile']['size'] > 2 * 1024 * 1024) {
                $errors['profile'] = 'Max file size is 2MB.';
            }
            if ($errors['profile'] === '') {
                $uploadsDir = __DIR__ . '/../asset/uploads';
                if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
                $ext = pathinfo($_FILES['profile']['name'], PATHINFO_EXTENSION);
                $safeName = 'profile_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $dest = $uploadsDir . '/' . $safeName;
                if (!move_uploaded_file($tmpName, $dest)) {
                    $errors['profile'] = 'Unable to save uploaded file.';
                } else {
                    $profilePath = 'asset/uploads/' . $safeName;
                }
            }
        }
    }
    if ($errors['username']==='' && $errors['email']==='' && $errors['profile']==='') {
        $con = getConnection();
        $safeUser = mysqli_real_escape_string($con, $username);
        $safeEmail = mysqli_real_escape_string($con, $email);
        $sql = "UPDATE users SET username='{$safeUser}', email='{$safeEmail}'";
        if ($profilePath !== '') {
            $safeProfile = mysqli_real_escape_string($con, $profilePath);
            $sql .= ", profile='{$safeProfile}'";
        }
        $sql .= " WHERE id=" . (int)$id;
        if (mysqli_query($con, $sql)) {
            $success = 'User updated successfully.';
            $user = getUserById($id);
        } else {
            $errors['general'] = 'Update failed. Try again.';
            error_log("update user failed: " . mysqli_error($con));
        }
    }
} 
$formUsername = $user['username'] ?? '';
$formEmail = $user['email'] ?? '';
$formProfile = $user['profile'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit User (Admin)</title>
    <link rel="stylesheet" type="text/css" href="../asset/auth.css">
    <style>
        .err { color:red; font-weight:600; margin:6px 0; }
        .ok { color:green; font-weight:700; margin:8px 0; text-align:center; }
        #profile-preview { border-radius:8px; border:1px solid #ccc; object-fit:cover; width:120px; height:120px; display:block; margin-bottom:8px; }
        fieldset.form-fieldset { width:60%; min-width:320px; margin:20px auto; padding:18px; box-sizing:border-box; }
        label { font-weight:bold; display:block; margin-top:8px; }
        input[type="text"], input[type="email"], input[type="file"], input[type="password"], select, textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 10px 8px;
            margin-top:6px;
            font-size: 1em;
            border: 1.5px solid #aaa;
            border-radius: 6px;
        }
        input[type="submit"], input[type="button"] {
            margin-top:12px;
            padding:10px 18px;
            background: #1976d2;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
        }
        input[type="submit"]:hover, input[type="button"]:hover {
            background: #145a86;
        }
    </style>
</head>
<body>
    <h1>Edit User</h1>

    <?php if ($success): ?>
        <p class="ok"><?= h($success) ?></p>
    <?php endif; ?>
    <?php if ($errors['general']): ?>
        <p class="err"><?= h($errors['general']) ?></p>
    <?php endif; ?>

    <form method="post" action="" enctype="multipart/form-data" onsubmit="return editUserCheck()">
        <fieldset class="form-fieldset">
            <input type="hidden" name="id" value="<?= (int)$id ?>">

            <label>Username:</label>
            <input type="text" id="username" name="username" value="<?= h($formUsername) ?>" onblur="copyUsernameToEmail()">
            <p id="usernameErr" class="err"><?= h($errors['username']) ?></p>

            <label>Email:</label>
            <input type="email" id="email" name="email" value="<?= h($formEmail) ?>">
            <p id="emailErr" class="err"><?= h($errors['email']) ?></p>

            <label>Profile Picture:</label>
            <?php if (!empty($formProfile)): ?>
                <img src="../<?= h($formProfile) ?>" id="profile-preview" alt="Profile">
            <?php endif; ?>
            <input type="file" id="profile" name="profile" accept="image/*">
            <p id="profileErr" class="err"><?= h($errors['profile']) ?></p>

            <br>
            <input type="submit" name="submit" value="Update User">
            <input type="button" value="Back to Users" onclick="window.location.href='admin_panel.php'">
        </fieldset>
    </form>

    <script>
        function copyUsernameToEmail() {
            var u = document.getElementById('username').value.trim();
            var e = document.getElementById('email').value.trim();
            if (u !== '' && e === '') {
                document.getElementById('email').value = u;
            }
            checkUsername();
            checkEmail();
        }
        function checkUsername() {
            var v = document.getElementById('username').value.trim();
            document.getElementById('usernameErr').innerText = v === '' ? 'Please enter username!' : '';
        }
        function checkEmail() {
            var v = document.getElementById('email').value.trim();
            var ok = v !== '' && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
            document.getElementById('emailErr').innerText = ok ? '' : 'Please enter a valid email!';
        }
        function editUserCheck() {
            checkUsername();
            checkEmail();
            var usernameOk = document.getElementById('usernameErr').innerText === '';
            var emailOk = document.getElementById('emailErr').innerText === '';
            return usernameOk && emailOk;
        }
    </script>
</body>
</html>
