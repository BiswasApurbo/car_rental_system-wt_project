<?php
session_start();
require_once('../model/userModel.php');
require_once('../model/bookingModel.php');

if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$vehicleId   = $_POST['vehicle_id'] ?? 0;
$pickupDate  = $_POST['pickup'] ?? '';
$returnDate  = $_POST['return'] ?? '';
$pickupTime  = $_POST['time'] ?? '';

$errors = [];
$successMessage = '';

if ($vehicleId <= 0) {
    $errors['vehicle'] = 'Please select a vehicle from the inventory.';
}

if ($pickupDate === '') {
    $errors['pickup'] = 'Pickup Date is required.';
} elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $pickupDate)) {
    $errors['pickup'] = 'Pickup Date format is invalid (YYYY-MM-DD).';
}

if ($returnDate === '') {
    $errors['return'] = 'Return Date is required.';
} elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $returnDate)) {
    $errors['return'] = 'Return Date format is invalid (YYYY-MM-DD).';
}

if ($pickupTime === '') {
    $errors['time'] = 'Pickup Time is required.';
} elseif (!preg_match('/^\d{2}:\d{2}$/', $pickupTime)) {
    $errors['time'] = 'Pickup Time format is invalid (HH:MM).';
}

// Process booking if no errors
if (empty($errors)) {
    $available = bm_isVehicleAvailable($vehicleId, $pickupDate, $returnDate);
    if (!$available) {
        $errors['availability'] = 'Selected vehicle is not available for the chosen dates.';
    } else {
        $userId = 0;
        if (!empty($_SESSION['username'])) {
            $userId = bm_getUserIdByUsername($_SESSION['username']);
        }

        if ($userId <= 0) {
            $errors['top'] = 'Unable to resolve current user. Please log in again.';
        } else {
            $bookingId = bm_createBooking($userId, $vehicleId, $pickupDate, $returnDate, $pickupTime, 0.0);
            if ($bookingId === false) {
                $errors['top'] = 'Failed to create booking. Please try again.';
            } else {
                $successMessage = 'Booking confirmed! Reference #'.$bookingId.
                    ' — Vehicle: ' . h($vehicleRow['make'].' '.$vehicleRow['model']) .
                    " | Pickup: $pickupDate $pickupTime | Return: $returnDate";
            }
        }
    }
}

if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'errors' => $errors]);
} else {
    echo json_encode(['status' => 'success', 'message' => $successMessage]);
}
?>
