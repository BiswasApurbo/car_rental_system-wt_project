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
?>
<html>
<head>
    <title>Role Assignment</title>
    <link rel="stylesheet" type="text/css" href="../asset/auth.css">
</head>
<body>
    <h1>Assign Roles</h1>
    <form method="post" action="role_assignment.html" onsubmit="return assignRoleCheck()">
        <fieldset>
            Username:
            <input type="text" id="username" placeholder="Enter username">
            <p id="userError" class="error-msg"></p>
            Select Role:
            <select id="role">
                <option value="User">User</option>
                <option value="Editor">Editor</option>
                <option value="Admin">Admin</option>
            </select>
            <br><br>
            <input type="submit" value="Assign Role">
            
            <input type="button" value="Back to Dashboard" onclick="window.location.href='admin_dashboard.php'" />
            <p id="assignSuccess"></p>
        </fieldset>
    </form>
    <script>
        function assignRoleCheck() {
            let username = document.getElementById('username').value;
            if (username == "") {
                document.getElementById('userError').innerHTML = "Please enter username!";
                return false;
            }
            document.getElementById('userError').innerHTML = "";
            let role = document.getElementById('role').value;
            document.getElementById('assignSuccess').innerHTML = 
                "Assigned role :" + role;
            return false;
        }
    </script>
</body>
</html>
