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
if (($_SESSION['role'] ?? '') !== 'admin') {
    header('location: ../view/login.php?error=badrequest');
    exit;
}

$username = '';
$role = 'User';
$errorUser = '';
$errorRole = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $role = $_POST['role'] ?? 'User';
    $allowed = ['User','Editor','Admin'];

    if ($username === '') {
        $errorUser = 'Please enter username!';
    }
    if (!in_array($role, $allowed, true)) {
        $errorRole = 'Invalid role selected!';
    }

    if ($errorUser === '' && $errorRole === '') {
        $success = 'Assigned role: ' . htmlspecialchars($role) . ' to ' . htmlspecialchars($username);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Role Assignment</title>
    <link rel="stylesheet" type="text/css" href="../asset/auth.css">
    <style>
        .error-msg{color:red;font-weight:600;margin:4px 0}
        .ok{color:green;font-weight:700;margin-top:8px}
    </style>
</head>
<body>
    <h1>Assign Roles</h1>

    <?php if ($success): ?>
        <p class="ok" style="text-align:center; font-weight:bold;"><?= $success ?></p>
    <?php endif; ?>

    <form method="post" action="" onsubmit="return assignRoleCheck()">
        <fieldset>
            Username:
            <input type="text" id="username" name="username" placeholder="Enter username" value="<?= htmlspecialchars($username) ?>">
            <p id="userError" class="error-msg"><?= htmlspecialchars($errorUser) ?></p>

            Select Role:
            <select id="role" name="role">
                <option value="User"   <?= $role==='User'?'selected':'' ?>>User</option>
                <option value="Editor" <?= $role==='Editor'?'selected':'' ?>>Editor</option>
                <option value="Admin"  <?= $role==='Admin'?'selected':'' ?>>Admin</option>
            </select>
            <p id="roleError" class="error-msg"><?= htmlspecialchars($errorRole) ?></p>

            <br><br>
            <input type="submit" value="Assign Role">
            <input type="button" value="Back to Dashboard" onclick="window.location.href='admin_dashboard.php'">
            <p id="assignSuccess" class="ok"></p>
        </fieldset>
    </form>

    <script>
        function assignRoleCheck() {
            const u = document.getElementById('username').value.trim();
            const r = document.getElementById('role').value;
            let ok = true;

            if (u === '') {
                document.getElementById('userError').innerHTML = 'Please enter username!';
                ok = false;
            } else {
                document.getElementById('userError').innerHTML = '';
            }

            if (!['User','Editor','Admin'].includes(r)) {
                document.getElementById('roleError').innerHTML = 'Invalid role selected!';
                ok = false;
            } else {
                document.getElementById('roleError').innerHTML = '';
            }

            if (ok) {
                document.getElementById('assignSuccess').innerHTML = 'Submitting…';
            } else {
                document.getElementById('assignSuccess').innerHTML = '';
            }
            return ok;
        }
    </script>
</body>
</html>
