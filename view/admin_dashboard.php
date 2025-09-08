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

$totalUsers = getTotalUsers();
$activeBookings = getActiveBookings();
$fleetVehicles = getFleetVehicles();
$pendingDamageReports = getPendingDamageReports();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" type="text/css" href="../asset/auth.css">
    <style>
        .error-msg { color: red; font-weight: 600; margin: 4px 0; }
        .ok { color: green; font-weight: 700; margin-top: 10px; text-align: center; }
        .center-under { text-align: center; font-weight: 700; margin: 6px 0 14px; color: green; }
        .notice { text-align:center; font-weight:700; color:green; margin-bottom:12px; }
    </style>
</head>
<body>

<h1>Admin Dashboard</h1>

<form class="dashboard-form">
    <fieldset>
        <legend>Summary</legend>
        <label>Total Users:</label>
        <span class="dashboard-number" id="totalUsers"><?php echo $totalUsers; ?></span><br><br>
        <label>Active Bookings:</label>
        <span class="dashboard-number" id="activeBookings"><?php echo $activeBookings; ?></span><br><br>
        <label>Fleet Vehicles:</label>
        <span class="dashboard-number" id="fleetVehicles"><?php echo $fleetVehicles; ?></span><br><br>
        <label>Pending Damage Reports:</label>
        <span class="dashboard-number" id="pendingDamageReports"><?php echo $pendingDamageReports; ?></span><br><br>
    </fieldset>
</form>

<form class="dashboard-form">
    <fieldset>
        <legend>Quick Actions</legend>
        <div class="button-container">
            <input type="button" value="Role Assignment" onclick="window.location.href='role_assignment.php'" />
            <input type="button" value="Search Filter" onclick="window.location.href='search_filter.php'" />
            <input type="button" value="Pages" onclick="window.location.href='pagination.php'" />
            <input type="button" value="Panel" onclick="window.location.href='admin_panel.php'" />
            <input type="button" value="Logout" onclick="window.location.href='../controller/logout.php'" />
        </div>
    </fieldset>
</form>

<script>
    function updateDashboardData() {
        const xhttp = new XMLHttpRequest();
        xhttp.open('GET', '../controller/get_dashboard_data.php', true);
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                const data = JSON.parse(this.responseText);
                if (data.status === 'success') {
                    document.getElementById('totalUsers').innerText = data.totalUsers;
                    document.getElementById('activeBookings').innerText = data.activeBookings;
                    document.getElementById('fleetVehicles').innerText = data.fleetVehicles;
                    document.getElementById('pendingDamageReports').innerText = data.pendingDamageReports;
                } else {
                    console.error("Error fetching data:", data.message);
                }
            }
        };
        xhttp.send();
    }

    setInterval(updateDashboardData, 15000);
</script>

</body>
</html>
