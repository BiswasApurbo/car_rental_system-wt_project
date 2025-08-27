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
            <input type="button" value="Notifications" onclick="window.location.href='notification.php'" />
            <input type="button" value="Contact Us" onclick="window.location.href='contact.html'" />
            <input type="button" value="Logout" onclick="window.location.href='../controller/logout.php'" />
        </fieldset>
    </form>
</body>
</html>
