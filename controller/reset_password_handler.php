<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = trim($_POST['new_password'] ?? '');

    if (strlen($newPassword) < 4) {
        echo json_encode(['status' => 'error', 'message' => 'Password must be at least 4 characters!']);
        exit;
    }

    // Simulate saving the new password (you should update this in the database)
    $_SESSION['password'] = $newPassword;  // This is just for demonstration. Update this logic to save the password in the database.

    echo json_encode(['status' => 'success', 'message' => 'Password has been reset successfully!']);
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}
