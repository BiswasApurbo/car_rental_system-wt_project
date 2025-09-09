<?php
session_start();
require_once('../model/bookingModel.php');

// Guard: Ensure the user is logged in
if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$pickupDate = $_POST['pickup'] ?? '';
$returnDate = $_POST['return'] ?? '';
$pickupTime = $_POST['time'] ?? '';
$vehicleId  = $_POST['vehicle_id'] ?? 0;

// Validate input fields
if ($pickupDate === '' || $returnDate === '' || $pickupTime === '' || $vehicleId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit;
}

// Check vehicle availability
$available = bm_isVehicleAvailable($vehicleId, $pickupDate, $returnDate);
if (!$available) {
    echo json_encode(['status' => 'error', 'message' => 'Selected vehicle is not available for the chosen dates.']);
    exit;
}

// Get user ID
$userId = bm_getUserIdByUsername($_SESSION['username']);
if ($userId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Unable to resolve current user. Please log in again.']);
    exit;
}

// Create booking
$bookingId = bm_createBooking($userId, $vehicleId, $pickupDate, $returnDate, $pickupTime, 0.0);
if ($bookingId === false) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to create booking. Please try again.']);
    exit;
}

// Send success response
echo json_encode([
    'status' => 'success',
    'message' => "Booking confirmed! Reference #{$bookingId} — Vehicle ID: {$vehicleId} | Pickup: {$pickupDate} {$pickupTime} | Return: {$returnDate}"
]);
?>
