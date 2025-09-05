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
if ($id === 0 && isset($_SESSION['username'])) {
    $tmp = getUserByUsername($_SESSION['username'] ?? '');
    $id = isset($tmp['id']) ? (int)$tmp['id'] : 0;
}

$user = $id ? getUserById($id) : [];
$name  = $user['username'] ?? ($_SESSION['username'] ?? '');
$email = $user['email'] ?? '';
$avatar = $user['profile'] ?? '';

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

    // NEW: check if email already exists for another user
    if (empty($errors['email'])) {
        $allUsers = getAlluser();
        foreach ($allUsers as $u) {
            if (isset($u['email']) && $u['email'] === $email) {
                // if it's not the current user, it's a collision
                $otherId = isset($u['id']) ? (int)$u['id'] : 0;
                if ($otherId !== $id) {
                    $errors['email'] = 'This email is already registered to another user!';
                    break;
                }
            }
        }
    }

    // handle file upload if any
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $errors['avatar'] = 'Upload failed. Try again.';
        } else {
            $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
            $tmpName = $_FILES['avatar']['tmp_name'];
            $mime = mime_content_type($tmpName);
            if (!in_array($mime, $allowed, true)) {
                $errors['avatar'] = 'Only JPG, PNG, GIF, or WEBP allowed.';
            }
            if ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
                $errors['avatar'] = 'Max file size is 2MB.';
            }

            if (empty($errors['avatar'])) {
                $uploadsDir = __DIR__ . '/../asset/uploads';
                if (!is_dir($uploadsDir)) {
                    @mkdir($uploadsDir, 0755, true);
                }
                $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $safeName = 'profile_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $destPath = $uploadsDir . '/' . $safeName;
                if (!move_uploaded_file($tmpName, $destPath)) {
                    $errors['avatar'] = 'Unable to save uploaded file.';
                } else {
                    $avatar = 'asset/uploads/' . $safeName;
                }
            }
        }
    }

    if (!$errors) {
        $update = ['id' => $id, 'username' => $name, 'email' => $email];
        if ($avatar !== '') $update['profile'] = $avatar;
        if (updateUser($update)) {
            $success = 'Profile updated!';
            // reload user
            $user = getUserById($id);
            $name = $user['username'];
            $email = $user['email'];
            $avatar = $user['profile'] ?? $avatar;
        } else {
            $errors['general'] = 'Update failed. Try again.';
        }
    }
}

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Edit Profile</title>
    <link rel="stylesheet" type="text/css" href="../asset/auth.css">
    <style>
        .err { color:red; font-weight:600; margin:4px 0; }
        .ok { color:green; font-weight:700; margin-top:10px; }
        #profile-preview { border-radius:8px; border:1px solid #ccc; object-fit:cover; width:120px; height:120px; }
    </style>
</head>
<body>
    <h1>Edit Profile</h1>

    <?php if ($success): ?>
        <p class="ok" style="text-align:center;"><?= h($success) ?></p>
    <?php endif; ?>

    <?php if (isset($errors['general'])): ?>
        <p class="err"><?= h($errors['general']) ?></p>
    <?php endif; ?>

    <form method="post" action="" enctype="multipart/form-data" onsubmit="return editProfileCheck()">
        <fieldset>
            Name:
            <input type="text" id="editName" name="name" value="<?= h($name) ?>" onblur="checkEditName()">
            <p id="nameError" class="err"><?= isset($errors['name']) ? h($errors['name']) : '' ?></p>

            Email:
            <input type="text" id="editEmail" name="email" value="<?= h($email) ?>" onblur="checkEditEmail()">
            <p id="emailError" class="err"><?= isset($errors['email']) ? h($errors['email']) : '' ?></p>

            Profile Picture:
            <?php if ($avatar): ?>
                <div><img src="../<?= h($avatar) ?>" id="profile-preview" alt="Avatar"></div><br>
            <?php endif; ?>
            <input type="file" id="editAvatar" name="avatar" accept="image/*">
            <p id="avatarError" class="err"><?= isset($errors['avatar']) ? h($errors['avatar']) : '' ?></p>

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
