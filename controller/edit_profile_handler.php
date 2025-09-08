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
            $_SESSION['role'] = ($c === 'user') ? 'User' : 'Admin';
        }
    } else {
        header('location: ../view/login.php?error=badrequest');
        exit;
    }
}

$id = $_SESSION['user_id'];
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$avatar = $_FILES['avatar'] ?? null;

$errors = [];

if ($name === '') {
    $errors['name'] = 'Please enter name!';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter valid email!';
}

if (empty($errors['email'])) {
    $allUsers = getAlluser();
    foreach ($allUsers as $u) {
        if (isset($u['email']) && $u['email'] === $email) {
            $otherId = isset($u['id']) ? (int)$u['id'] : 0;
            if ($otherId !== $id) {
                $errors['email'] = 'This email is already registered to another user!';
                break;
            }
        }
    }
}

if ($avatar && $avatar['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($avatar['error'] !== UPLOAD_ERR_OK) {
        $errors['avatar'] = 'Upload failed. Try again.';
    } else {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $tmpName = $avatar['tmp_name'];
        $mime = mime_content_type($tmpName);
        if (!in_array($mime, $allowed, true)) {
            $errors['avatar'] = 'Only JPG, PNG, GIF, or WEBP allowed.';
        }
        if ($avatar['size'] > 2 * 1024 * 1024) {
            $errors['avatar'] = 'Max file size is 2MB.';
        }

        if (empty($errors['avatar'])) {
            $uploadsDir = __DIR__ . '/../asset/uploads';
            if (!is_dir($uploadsDir)) {
                @mkdir($uploadsDir, 0755, true);
            }
            $ext = pathinfo($avatar['name'], PATHINFO_EXTENSION);
            $safeName = 'profile_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $destPath = $uploadsDir . '/' . $safeName;
            if (!move_uploaded_file($tmpName, $destPath)) {
                $errors['avatar'] = 'Unable to save uploaded file.';
            } else {
                $avatarPath = 'asset/uploads/' . $safeName;
            }
        }
    }
}

if (empty($errors)) {
    $update = ['id' => $id, 'username' => $name, 'email' => $email];
    if (!empty($avatarPath)) {
        $update['profile'] = $avatarPath;
    }
    if (updateUser($update)) {
        echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Update failed. Try again.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => $errors]);
}
?>
