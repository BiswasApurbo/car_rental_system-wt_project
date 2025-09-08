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
        echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
        exit;
    }
}

if (strtolower($_SESSION['role']) !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$totalUsers = getTotalUsers();
$activeBookings = getActiveBookings();
$fleetVehicles = getFleetVehicles();
$pendingDamageReports = getPendingDamageReports();

echo json_encode([
    'status' => 'success',
    'totalUsers' => $totalUsers,
    'activeBookings' => $activeBookings,
    'fleetVehicles' => $fleetVehicles,
    'pendingDamageReports' => $pendingDamageReports
]);
?>
