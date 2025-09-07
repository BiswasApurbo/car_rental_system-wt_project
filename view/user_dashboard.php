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
?>
<html>
<head>
    <title>User Dashboard</title>
    <link rel="stylesheet" type="text/css" href="../asset/dashboard.css">
</head>
<body>
    <h1>User Dashboard</h1>
    <form class="dashboard-form">
        <fieldset>
            <legend>Summary</legend>
            <label>My Bookings:</label>
            <span class="dashboard-number">2</span><br><br>
            <label>Upcoming Pickups:</label>
            <span class="dashboard-number">1</span><br><br>
            <label>Loyalty Points:</label>
            <span class="dashboard-number">120</span><br><br>
            <label>Unread Notifications:</label>
            <span class="dashboard-number">3</span><br><br>
        </fieldset>
    </form>
    <form class="dashboard-form">
        <fieldset>
            <legend>Quick Actions</legend>
            <input type="button" value="My Profile" onclick="window.location.href='profile.php'" />
             <input type="button" value="Licence Info" onclick="window.location.href='customer_profile.php'" />
            <input type="button" value="Contact Us" onclick="window.location.href='contact.php'" />
            <input type="button" value="Rent Car" onclick="window.location.href='vehicle_inventory.php'" />
            <input type="button" value="Notification" onclick="window.location.href='notification.php'" />
            <input type="button" value="Info_Export" onclick="window.location.href='export.php'" />
            <input type="button" value="Logout" onclick="window.location.href='../controller/logout.php'" />
        </fieldset>
    </form>
</body>
</html>
