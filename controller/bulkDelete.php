<?php
session_start();
require_once('../model/userModel.php');

// Ensure the user is an admin
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$userIds = json_decode($_POST['user_ids'] ?? '[]');

if (!empty($userIds)) {
    $deleted = 0;
    foreach ($userIds as $userId) {
        if (deleteUser($userId)) {
            $deleted++;
        }
    }
    echo json_encode(['status' => 'success', 'message' => "$deleted user(s) deleted successfully"]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No users selected for deletion']);
}
?>
