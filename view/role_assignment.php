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

$username = '';
$role = 'User';
$errorUser = '';
$errorRole = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $role = $_POST['role'] ?? 'User';
    $allowed = ['User', 'Admin'];

    if ($username === '') {
        $errorUser = 'Please enter username!';
    }
    if (!in_array($role, $allowed, true)) {
        $errorRole = 'Invalid role selected!';
    }

    if ($errorUser === '' && $errorRole === '') {
        $all = getAlluser();
        $foundId = 0;
        foreach ($all as $u) {
            if (isset($u['username']) && $u['username'] === $username) {
                $foundId = (int)$u['id'];
                break;
            }
        }

        if ($foundId === 0) {
            $errorUser = 'User not found!';
        } else {
            $con = getConnection();
            $safeRole = mysqli_real_escape_string($con, $role);
            $sql = "UPDATE users SET role='{$safeRole}' WHERE id={$foundId}";
            if (mysqli_query($con, $sql)) {
                $success = 'Assigned role: ' . htmlspecialchars($role) . ' to ' . htmlspecialchars($username);
                $username = '';
                $role = 'User';
            } else {
                $errorRole = 'Database update failed. Try again.';
            }
        }
    }
}

$usersForTable = getAlluser();

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
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
        .users-table{width:90%;max-width:1000px;margin:20px auto 10px;border-collapse:collapse;font-size:0.95em}
        .users-table th, .users-table td{border:1px solid #ddd;padding:8px;text-align:left}
        .users-table th{background:#f7f7f7;font-weight:bold}
        .users-table tr:nth-child(even){background:#fafafa}
        .users-table caption{caption-side:top;text-align:left;font-weight:bold;margin-bottom:8px}
        .table-wrap{width:100%;overflow-x:auto;padding:0 10px}
        fieldset.form-fieldset{width:60%;min-width:320px;margin:20px auto;padding:18px}
    </style>
</head>
<body>
    <h1>Assign Roles</h1>

    <?php if ($success): ?>
        <p class="ok" style="text-align:center; font-weight:bold;"><?= $success ?></p>
    <?php endif; ?>

    <form id="assignRoleForm" method="post" action="">
        <fieldset class="form-fieldset">
            Username:
            <input type="text" id="username" name="username" placeholder="Enter username" value="<?= h($username) ?>">
            <p id="userError" class="error-msg"><?= h($errorUser) ?></p>

            Select Role:
            <select id="role" name="role">
                <option value="User"   <?= $role==='User'?'selected':'' ?>>User</option>
                <option value="Admin"  <?= $role==='Admin'?'selected':'' ?>>Admin</option>
            </select>
            <p id="roleError" class="error-msg"><?= h($errorRole) ?></p>

            <br><br>
            <input type="submit" value="Assign Role">
            <input type="button" value="Back to Dashboard" onclick="window.location.href='admin_dashboard.php'">
            <p id="assignSuccess" class="ok"></p>
        </fieldset>
    </form>

    <div class="table-wrap">
        <table class="users-table" aria-describedby="users-list">
            <caption id="users-list">All registered users</caption>
            <thead>
                <tr>
                    <th style="width:6%;">ID</th>
                    <th style="width:28%;">Username</th>
                    <th style="width:40%;">Email</th>
                    <th style="width:18%;">Role</th>
                </tr>
            </thead>
            <tbody id="users-list-body">
                <?php if (!empty($usersForTable)): ?>
                    <?php foreach ($usersForTable as $u): ?>
                        <tr id="user-<?= h($u['id']) ?>">
                            <td><?= h($u['id']) ?></td>
                            <td><?= h($u['username']) ?></td>
                            <td><?= h($u['email'] ?? '—') ?></td>
                            <td id="role-<?= h($u['id']) ?>"><?= h($u['role'] ?? 'User') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        document.getElementById('assignRoleForm').onsubmit = function(e) {
            e.preventDefault();
            const username = document.getElementById('username').value.trim();
            const role = document.getElementById('role').value;
            const userError = document.getElementById('userError');
            const roleError = document.getElementById('roleError');
            const assignSuccess = document.getElementById('assignSuccess');

            let valid = true;

            if (username === '') {
                userError.innerText = 'Please enter username!';
                valid = false;
            } else {
                userError.innerText = '';
            }

            if (!['User', 'Admin'].includes(role)) {
                roleError.innerText = 'Invalid role selected!';
                valid = false;
            } else {
                roleError.innerText = '';
            }

            if (valid) {
                const formData = new FormData();
                formData.append('username', username);
                formData.append('role', role);

                const xhr = new XMLHttpRequest();
                xhr.open('POST', '../controller/role_assignment_handler.php', true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            assignSuccess.innerText = response.message;
                            assignSuccess.style.color = 'green';

                            const updatedUserRow = document.getElementById(`user-${response.id}`);
                            if (updatedUserRow) {
                                const roleCell = updatedUserRow.querySelector('td#role-' + response.id);
                                if (roleCell) {
                                    roleCell.innerText = response.newRole;
                                }
                            }
                        } else {
                            userError.innerText = response.message;
                            assignSuccess.innerText = '';
                        }
                    }
                };
                xhr.send(formData);
            }
        };
    </script>
</body>
</html>
