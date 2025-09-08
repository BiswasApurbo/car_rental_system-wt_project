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

$userId = $_SESSION['user_id'];
$totalBookings = getUserBookings($userId);
$upcomingPickups = getUpcomingPickups($userId);
$loyaltyPoints = getLoyaltyPoints($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" type="text/css" href="../asset/dashboard.css">
    <script>
        function updateDashboardData() {
            const xhttp = new XMLHttpRequest();
            xhttp.open('GET', '../controller/get_user_dashboard_data.php', true);
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    const data = JSON.parse(this.responseText);
                    if (data.status === 'success') {
                        document.getElementById('totalBookings').innerText = data.totalBookings;
                        document.getElementById('upcomingPickups').innerText = data.upcomingPickups;
                        document.getElementById('loyaltyPoints').innerText = data.loyaltyPoints;
                    } else {
                        console.error("Error fetching data:", data.message);
                    }
                }
            };
            xhttp.send();
        }

        setInterval(updateDashboardData, 15000);
    </script>
</head>
<body>
<h1>User Dashboard</h1>

<form class="dashboard-form">
    <fieldset>
        <legend>Summary</legend>
        <label>My Bookings:</label>
        <span class="dashboard-number" id="totalBookings"><?php echo $totalBookings; ?></span><br><br>
        <label>Upcoming Pickups:</label>
        <span class="dashboard-number" id="upcomingPickups"><?php echo $upcomingPickups; ?></span><br><br>
        <label>Loyalty Points:</label>
        <span class="dashboard-number" id="loyaltyPoints"><?php echo $loyaltyPoints; ?></span><br><br>
    </fieldset>
</form>

<form class="dashboard-form">
    <fieldset>
        <legend>Quick Actions</legend>
        <input type="button" value="My Profile" onclick="window.location.href='profile.php'" />
        <input type="button" value="Licence Info" onclick="window.location.href='customer_profile.php'" />
        <input type="button" value="Contact Us" onclick="window.location.href='contact.php'" />
        <input type="button" value="Rent Car" onclick="window.location.href='vehicle_inventory.php'" />
        <input type="button" value="Export" onclick="window.location.href='export.php'" />
        <input type="button" value="Activity" onclick="window.location.href='activitylog.php'" />
        <input type="button" value="Logout" onclick="window.location.href='../controller/logout.php'" />
    </fieldset>
</form>
</body>
</html>
