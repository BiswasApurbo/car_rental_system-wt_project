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

// Call the functions from userModel.php
$totalUsers = getTotalUsers();
$activeBookings = getActiveBookings();
$fleetVehicles = getFleetVehicles();
$pendingDamageReports = getPendingDamageReports();
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
            <span class="dashboard-number"><?php echo $totalUsers; ?></span><br><br>
            <label>Active Bookings:</label>
            <span class="dashboard-number"><?php echo $activeBookings; ?></span><br><br>
            <label>Fleet Vehicles:</label>
            <span class="dashboard-number"><?php echo $fleetVehicles; ?></span><br><br>
            <label>Pending Damage Reports:</label>
            <span class="dashboard-number"><?php echo $pendingDamageReports; ?></span><br><br>
        </fieldset>
    </form>
    <form class="dashboard-form">
        <fieldset>
            <legend>Quick Actions</legend>
            <input type="button" value="Role Assignment" onclick="window.location.href='role_assignment.php'" />
            <input type="button" value="Search Filter" onclick="window.location.href='search_filter.php'" />
            <input type="button" value="Pages" onclick="window.location.href='pagination.php'" />
            <input type="button" value="Panel" onclick="window.location.href='admin_panel.php'" />
            <input type="button" value="Logout" onclick="window.location.href='../controller/logout.php'" />
        </fieldset>
    </form>
</body>
</html>
