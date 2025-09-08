<?php
session_start();
require_once('../model/userModel.php');

// Ensure the user is an admin (for security)
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Get role filter from the POST request (defaults to 'All' if not provided)
$roleFilter = $_POST['roleFilter'] ?? 'All';

// Fetch all users from the database
$allUsers = getAlluser(); // getAlluser() should return an array of all users
$filteredUsers = [];

if ($roleFilter === 'All') {
    // If filter is 'All', return all users
    $filteredUsers = $allUsers;
} else {
    // Otherwise, filter users based on the role
    foreach ($allUsers as $user) {
        if (isset($user['role']) && $user['role'] === $roleFilter) {
            $filteredUsers[] = $user;
        }
    }
}

// Check if we have users to return
if (count($filteredUsers) > 0) {
    // Return the filtered users as JSON
    echo json_encode([
        'status' => 'success',
        'users' => $filteredUsers
    ]);
} else {
    // If no users are found, return an error message
    echo json_encode([
        'status' => 'error',
        'message' => 'No users found.'
    ]);
}
?>
