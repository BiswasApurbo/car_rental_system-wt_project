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

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$role = isset($_POST['role']) ? $_POST['role'] : 'User';
$errorUser = '';
$errorRole = '';
$success = '';

if ($username === '') {
    $errorUser = 'Please enter username!';
    echo json_encode(['status' => 'error', 'message' => $errorUser]);
    exit;
}

if (!in_array($role, ['User', 'Admin'], true)) {
    $errorRole = 'Invalid role selected!';
    echo json_encode(['status' => 'error', 'message' => $errorRole]);
    exit;
}

$allUsers = getAlluser();
$foundId = 0;
foreach ($allUsers as $u) {
    if (isset($u['username']) && $u['username'] === $username) {
        $foundId = (int)$u['id'];
        break;
    }
}

if ($foundId === 0) {
    $errorUser = 'User not found!';
    echo json_encode(['status' => 'error', 'message' => $errorUser]);
    exit;
}

$con = getConnection();
$safeRole = mysqli_real_escape_string($con, $role);
$sql = "UPDATE users SET role='{$safeRole}' WHERE id={$foundId}";

if (mysqli_query($con, $sql)) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Role assigned successfully.',
        'id' => $foundId,
        'newRole' => $role
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update role.']);
}
?>
