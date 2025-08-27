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
if (($_SESSION['role'] ?? '') !== 'admin') {
    header('location: ../view/login.php?error=badrequest');
    exit;
}
?>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" type="text/css" href="../asset/auth.css">
</head>
<body>
    <h1>Admin Dashboard</h1>
    <form class="dashboard-form">
        <fieldset>
            <legend>Summary</legend>
            <label>Total Users:</label>
            <span class="dashboard-number">12</span><br><br>
            <label>Active Bookings:</label>
            <span class="dashboard-number">5</span><br><br>
            <label>Fleet Vehicles:</label>
            <span class="dashboard-number">20</span><br><br>
            <label>Pending Damage Reports:</label>
            <span class="dashboard-number">2</span><br><br>
        </fieldset>
    </form>
    <form class="dashboard-form">
        <fieldset>
            <legend>Quick Actions</legend>
            <input type="button" value="Role Assignment" onclick="window.location.href='role_assignment.php'" />
            <input type="button" value="Permission Settings" onclick="window.location.href='permission_settings.php'" />
            <input type="button" value="Profile" onclick="window.location.href='profile.php'" />
            <input type="button" value="Notifications" onclick="window.location.href='notification.php'" />
            <input type="button" value="Logout" onclick="window.location.href='../controller/logout.php'" />
        </fieldset>
    </form>
</body>
</html>
