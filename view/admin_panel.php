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
$validationMessage = '';
$roleFilter = $_POST['roleFilter'] ?? 'All';

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

if (isset($_GET['deleteUserId'])) {
    $userIdToDelete = (int) $_GET['deleteUserId'];
    if ($userIdToDelete > 0) {
        if (deleteUser($userIdToDelete)) {
            $validationMessage = "1 user deleted.";
        } else {
            $validationMessage = "Delete failed. Try again.";
        }
    }
    header("Location: " . basename(__FILE__));
    exit;
}

if (isset($_POST['bulkDelete'])) {
    if (!empty($_POST['user_ids']) && is_array($_POST['user_ids'])) {
        $ids = array_map('intval', $_POST['user_ids']);
        $deleted = 0;
        foreach ($ids as $id) {
            if ($id > 0) {
                if (deleteUser($id)) $deleted++;
            }
        }
        $validationMessage = $deleted . " user(s) deleted.";
    } else {
        $validationMessage = "Please select at least one user to delete.";
    }
}

if (isset($_POST['exportCSV'])) {
    $all = getAlluser();
    $rows = [];
    if ($roleFilter === 'All') {
        $rows = $all;
    } else {
        foreach ($all as $r) {
            if (isset($r['role']) && $r['role'] === $roleFilter) $rows[] = $r;
        }
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="users.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['User ID','Username','Email','Role']);
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['id'] ?? '',
            $r['username'] ?? '',
            $r['email'] ?? '',
            $r['role'] ?? ''
        ]);
    }
    fclose($out);
    exit;
}

$allUsers = getAlluser();
$roleList = ['All'];
foreach ($allUsers as $u) {
    $roleVal = $u['role'] ?? '';
    if ($roleVal === '') continue;
    if (!in_array($roleVal, $roleList, true)) $roleList[] = $roleVal;
}

$filteredUsers = [];
if ($roleFilter === 'All') {
    $filteredUsers = $allUsers;
} else {
    foreach ($allUsers as $u) {
        if (isset($u['role']) && $u['role'] === $roleFilter) $filteredUsers[] = $u;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - User Management</title>
    <link rel="stylesheet" type="text/css" href="../asset/ad.css">
    <style>
        .admin-card{max-width:1100px;margin:18px auto;padding:18px;background:#fff;border-radius:8px;box-shadow:0 0 8px #ddd}
        table.users{width:100%;border-collapse:collapse;margin-top:12px}
        table.users th, table.users td{border:1px solid #e1e1e1;padding:8px;text-align:left}
        table.users th{background:#f7f7f7}
        table.users tr:nth-child(even){background:#fafafa}
        .actions button{margin-right:6px}
        fieldset.controls{border:none;padding:0;margin:0}
        .validation{font-weight:700;margin:8px 0;color:green}
        .warn{color:red;font-weight:700}
    </style>
    <script>
        // Function to apply the role filter using AJAX
        function applyFilter() {
            let roleFilter = document.getElementById('roleFilter').value;
            fetchUsers(roleFilter); // Fetch filtered users via AJAX
        }

        // Function to fetch users with AJAX and update the table
        function fetchUsers(roleFilter = 'All') {
            let xhttp = new XMLHttpRequest();
            xhttp.open('POST', '../controller/loadUsers.php', true);  // Corrected path
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send('roleFilter=' + roleFilter);

            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    let response = JSON.parse(this.responseText);
                    let userList = document.getElementById('userList');
                    userList.innerHTML = ''; // Clear the table first

                    if (response.status === 'success') {
                        response.users.forEach(function(user) {
                            userList.innerHTML += `<tr>
                                                        <td><input type='checkbox' name='user_ids[]' value='${user.id}'></td>
                                                        <td>${user.id}</td>
                                                        <td>${user.username}</td>
                                                        <td>${user.email}</td>
                                                        <td>${user.role}</td>
                                                        <td class='actions'>
                                                            <button type='button' onclick='window.location.href="editUser.php?id=${user.id}"'>Edit</button>
                                                            <button type='button' onclick='deleteUser(${user.id})'>Delete</button>
                                                        </td>
                                                    </tr>`;
                        });
                    } else {
                        userList.innerHTML = `<tr><td colspan="6">No users found.</td></tr>`;
                    }
                }
            };
        }

        // Function to delete a user via AJAX
        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                let xhttp = new XMLHttpRequest();
                xhttp.open('GET', '../controller/deleteUser.php?id=' + userId, true);  // Corrected path
                xhttp.send();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        fetchUsers(); // Refresh the user list after deletion
                    }
                };
            }
        }

        // Function to handle bulk deletion via AJAX
        function bulkDelete() {
            let selectedUsers = [];
            document.querySelectorAll("input[name='user_ids[]']:checked").forEach(function(checkbox) {
                selectedUsers.push(checkbox.value);
            });

            if (selectedUsers.length > 0 && confirm("Are you sure you want to delete the selected users?")) {
                let xhttp = new XMLHttpRequest();
                xhttp.open('POST', '../controller/bulkDelete.php', true);  // Corrected path
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send('user_ids=' + JSON.stringify(selectedUsers));
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        fetchUsers(); // Refresh the user list after bulk delete
                    }
                };
            } else {
                alert("Please select at least one user to delete.");
            }
        }

        window.onload = function() {
            fetchUsers(); // Load users on page load
        }
    </script>
</head>
<body>
    <div class="admin-card">
        <h1>Admin Panel - User Management</h1>

        <!-- Filter by role -->
        <form id="filterForm" method="POST" action="" onsubmit="event.preventDefault(); applyFilter();">
            <fieldset class="controls">
                <label for="roleFilter">Filter by role:</label>
                <select id="roleFilter" name="roleFilter" onchange="applyFilter()">
                    <?php foreach ($roleList as $r): ?>
                        <option value="<?= h($r) ?>" <?= ($r === $roleFilter) ? 'selected' : '' ?>><?= h($r) ?></option>
                    <?php endforeach; ?>
                </select>
            </fieldset>
        </form>

        <?php if ($validationMessage): ?>
            <div class="validation"><?= h($validationMessage) ?></div>
        <?php endif; ?>

        <form id="bulkDeleteForm" method="POST" action="">
            <fieldset>
                <label>Users List:</label>
                <table class="users" aria-describedby="users-list">
                    <caption id="users-list">All registered users</caption>
                    <thead>
                        <tr>
                            <th style="width:6%"><input type="checkbox" onclick="toggleSelectAll(this)"></th>
                            <th style="width:6%">User ID</th>
                            <th style="width:24%">Username</th>
                            <th style="width:34%">Email</th>
                            <th style="width:12%">Role</th>
                            <th style="width:18%">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="userList">
                        <!-- The user list will be populated dynamically with AJAX -->
                    </tbody>
                </table>

                <br>
                <input type="button" value="Bulk Delete" onclick="bulkDelete()">
                <input type="button" value="Export CSV" onclick="exportCSV()">
                <input type="button" value="Back to Dashboard" onclick="window.location.href='admin_dashboard.php'">
            </fieldset>
        </form>
        <form id="exportCSVForm" method="POST" action="" style="display:none;">
            <input type="hidden" name="exportCSV" value="true">
            <input type="hidden" name="roleFilter" value="<?= h($roleFilter) ?>">
        </form>
    </div>
</body>
</html>
