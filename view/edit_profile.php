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
    if ($id) $_SESSION['user_id'] = $id;
}

$user   = $id ? getUserById($id) : [];
$name   = $user['username'] ?? ($_SESSION['username'] ?? '');
$email  = $user['email'] ?? '';
$avatar = $user['profile'] ?? '';

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Edit Profile</title>
    <link rel="stylesheet" type="text/css" href="../asset/auth.css">
    <style>
        .err { color:#d00; font-weight:600; margin:4px 0; min-height:1em; }
        .ok { color:#0a0; font-weight:700; margin-top:10px; }
        #profile-preview { border-radius:8px; border:1px solid #ccc; object-fit:cover; width:120px; height:120px; }
        fieldset { max-width: 460px; border:1px solid #ddd; padding:16px; }
        input[type="text"], input[type="file"] { display:block; width:100%; margin:6px 0 10px; padding:8px; }
        .row { margin-bottom:10px; }
        .actions { display:flex; gap:8px; }
        .diag { font-size:12px; color:#555; white-space:pre-wrap; }
    </style>
</head>
<body>
    <h1>Edit Profile</h1>

    <form id="editProfileForm" method="post" enctype="multipart/form-data" novalidate>
        <fieldset>
            <div class="row">
                <label for="editName">Name:</label>
                <input type="text" id="editName" name="name" value="<?= h($name) ?>" onblur="checkEditName()">
                <p id="nameError" class="err"></p>
            </div>

            <div class="row">
                <label for="editEmail">Email:</label>
                <input type="text" id="editEmail" name="email" value="<?= h($email) ?>" onblur="checkEditEmail()">
                <p id="emailError" class="err"></p>
            </div>

            <div class="row">
                <label for="editAvatar">Profile Picture:</label>
                <?php if ($avatar): ?>
                    <div><img src="../<?= h($avatar) ?>" id="profile-preview" alt="Avatar"></div><br>
                <?php else: ?>
                    <div><img src="" id="profile-preview" alt="Avatar" style="display:none;"></div><br>
                <?php endif; ?>
                <input type="file" id="editAvatar" name="avatar" accept="image/*">
                <p id="avatarError" class="err"></p>
            </div>

            <div class="actions">
                <input type="submit" value="Save Changes">
                <input type="button" value="Back to Dashboard" onclick="window.location.href='user_dashboard.php'">
            </div>

            <p id="saveSuccess" class="ok"></p>
            <p id="generalError" class="err"></p>
            <div id="diagnostic" class="diag"></div>
        </fieldset>
    </form>

    <script>
        function checkEditName() {
            const name = document.getElementById('editName').value.trim();
            document.getElementById('nameError').textContent = name === "" ? "Please enter name!" : "";
        }
        function checkEditEmail() {
            const email = document.getElementById('editEmail').value.trim();
            const valid = email !== "" && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            document.getElementById('emailError').textContent = valid ? "" : "Please enter valid email!";
        }
        function clearMessages() {
            document.getElementById('nameError').textContent = "";
            document.getElementById('emailError').textContent = "";
            document.getElementById('avatarError').textContent = "";
            document.getElementById('saveSuccess').textContent = "";
            document.getElementById('generalError').textContent = "";
            document.getElementById('diagnostic').textContent = "";
        }
        document.getElementById('editProfileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            clearMessages();
            const formData = new FormData(this);
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '../controller/edit_profile_handler.php', true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState !== 4) return;
                let res = null;
                try { res = JSON.parse(xhr.responseText); } catch(e) {}
                if (!res) {
                    document.getElementById('generalError').textContent = "Unexpected response from server.";
                    return;
                }
                if (res.status === 'success') {
                    document.getElementById('saveSuccess').textContent = res.message || "Profile updated!";
                    if (res.name)  document.getElementById('editName').value  = res.name;
                    if (res.email) document.getElementById('editEmail').value = res.email;
                    if (res.avatar) {
                        const img = document.getElementById('profile-preview');
                        img.src = "../" + res.avatar + "?t=" + Date.now();
                        img.style.display = 'inline-block';
                    }
                } else {
                    if (res.errors && typeof res.errors === 'object') {
                        if (res.errors.name)   document.getElementById('nameError').textContent   = res.errors.name;
                        if (res.errors.email)  document.getElementById('emailError').textContent  = res.errors.email;
                        if (res.errors.avatar) document.getElementById('avatarError').textContent = res.errors.avatar;
                        if (res.errors.general) document.getElementById('generalError').textContent = res.errors.general;
                    }
                    if (res.message && typeof res.message === 'string') document.getElementById('generalError').textContent = res.message;
                    if (res.diagnostic) document.getElementById('diagnostic').textContent = res.diagnostic;
                }
            };
            xhr.onerror = function() { document.getElementById('generalError').textContent = "Network error. Please try again."; };
            xhr.send(formData);
        });
    </script>
</body>
</html>
