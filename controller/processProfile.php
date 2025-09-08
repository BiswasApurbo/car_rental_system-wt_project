<?php
session_start();
require_once('../model/userModel.php');
require_once('../model/customerModel.php');

if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in.']);
    exit;
}

$userId = $_SESSION['user_id'];
$name = trim($_POST['name']);
$licenseNo = trim($_POST['licenseNo']);
$seat = $_POST['seat'];
$mirror = $_POST['mirror'];

$errors = [];

if (empty($name)) $errors['name'] = 'Please enter your name.';
if (empty($licenseNo)) $errors['licenseNo'] = 'Please enter your license number.';
if (empty($seat)) $errors['seat'] = 'Please select a seat preference.';
if (empty($mirror)) $errors['mirror'] = 'Please select a mirror preference.';

if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'message' => $errors]);
    exit;
}

// Process profile update
$newFilePath = null;
// Process file upload for license
if (!empty($_FILES['license']['tmp_name'])) {
    $ext = strtolower(pathinfo($_FILES['license']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'])) {
        $fileName = 'license_' . $userId . '_' . time() . '.' . $ext;
        $destPath = '../asset/uploads/' . $fileName;
        if (move_uploaded_file($_FILES['license']['tmp_name'], $destPath)) {
            $newFilePath = 'asset/uploads/' . $fileName;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'File upload failed.']);
            exit;
        }
    }
}

// Update customer profile in DB
if (updateCustomerProfile($userId, $name, $licenseNo, $seat, $mirror, $newFilePath)) {
    echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Profile update failed. Please try again.']);
}
?>
