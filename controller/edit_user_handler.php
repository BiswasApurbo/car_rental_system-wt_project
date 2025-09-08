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
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$profilePath = '';

if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid user ID.']);
    exit;
}

if ($username === '') {
    echo json_encode(['status' => 'error', 'message' => 'Username is required.']);
    exit;
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Valid email is required.']);
    exit;
}

$profileFile = $_FILES['profile'] ?? null;
if ($profileFile && $profileFile['error'] !== UPLOAD_ERR_NO_FILE) {
    $ext = strtolower(pathinfo($profileFile['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        $fileName = 'profile_' . $id . '_' . time() . '.' . $ext;
        $destPath = '../asset/uploads/' . $fileName;
        if (move_uploaded_file($profileFile['tmp_name'], $destPath)) {
            $profilePath = 'asset/uploads/' . $fileName;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Profile picture upload failed.']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file type.']);
        exit;
    }
}

$con = getConnection();
$safeUsername = mysqli_real_escape_string($con, $username);
$safeEmail = mysqli_real_escape_string($con, $email);

$sql = "UPDATE users SET username='{$safeUsername}', email='{$safeEmail}'";
if ($profilePath) {
    $sql .= ", profile='{$profilePath}'";
}
$sql .= " WHERE id={$id}";

if (mysqli_query($con, $sql)) {
    echo json_encode(['status' => 'success', 'message' => 'User updated successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Update failed. Try again.']);
}
?>
