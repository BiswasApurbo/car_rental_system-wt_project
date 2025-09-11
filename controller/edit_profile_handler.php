<?php
session_start();
require_once('../model/userModel.php');

header('Content-Type: application/json; charset=utf-8');

error_reporting(E_ALL);
ini_set('display_errors', '0');

set_error_handler(function($severity, $message, $file, $line){
    if (!(error_reporting() & $severity)) return;
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Server error','errors'=>['general'=>$message],'diagnostic'=>"$file:$line"]);
    exit;
});
set_exception_handler(function($e){
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Server exception','errors'=>['general'=>$e->getMessage()],'diagnostic'=>$e->getFile().':'.$e->getLine()]);
    exit;
});

function json_out($arr, $code = 200) {
    http_response_code($code);
    echo json_encode($arr);
    exit;
}

if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) {
    if (isset($_COOKIE['status']) && (string)$_COOKIE['status'] === '1') {
        $_SESSION['status'] = true;
        if (!isset($_SESSION['username']) && isset($_COOKIE['remember_user'])) $_SESSION['username'] = $_COOKIE['remember_user'];
        if (!isset($_SESSION['role']) && isset($_COOKIE['remember_role'])) {
            $c = strtolower(trim((string)$_COOKIE['remember_role']));
            $_SESSION['role'] = ($c === 'admin') ? 'Admin' : 'User';
        }
    } else {
        json_out(['status'=>'error','message'=>'Unauthorized. Please login again.'], 401);
    }
}

$id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($id === 0 && isset($_SESSION['username'])) {
    $tmp = getUserByUsername($_SESSION['username'] ?? '');
    $id = isset($tmp['id']) ? (int)$tmp['id'] : 0;
    if ($id) $_SESSION['user_id'] = $id;
}
if ($id === 0) json_out(['status'=>'error','message'=>'User not found in session.'], 400);

$name   = trim($_POST['name'] ?? '');
$email  = trim($_POST['email'] ?? '');
$avatar = $_FILES['avatar'] ?? null;

$errors = [];
if ($name === '') $errors['name'] = 'Please enter name!';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter valid email!';

if (empty($errors['email'])) {
    $allUsers = getAlluser();
    if (is_array($allUsers)) {
        foreach ($allUsers as $u) {
            $uEmail = $u['email'] ?? null;
            if ($uEmail && strcasecmp($uEmail, $email) === 0) {
                $otherId = isset($u['id']) ? (int)$u['id'] : 0;
                if ($otherId !== $id) { $errors['email'] = 'This email is already registered to another user!'; break; }
            }
        }
    }
}

function detect_mime($tmp, $origName) {
    $mime = null;
    if (function_exists('finfo_open')) {
        $f = @finfo_open(FILEINFO_MIME_TYPE);
        if ($f) { $mime = @finfo_file($f, $tmp); @finfo_close($f); }
    }
    if (!$mime && function_exists('mime_content_type')) $mime = @mime_content_type($tmp);
    if (!$mime) {
        $ext = strtolower(pathinfo($origName ?? '', PATHINFO_EXTENSION));
        $map = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','webp'=>'image/webp'];
        $mime = $map[$ext] ?? '';
    }
    return $mime ?: '';
}

function ensure_uploads_writable($uploadsDir) {
    if (!is_dir($uploadsDir)) @mkdir($uploadsDir, 0755, true);
    clearstatcache();
    if (!is_writable($uploadsDir)) { @chmod($uploadsDir, 0755); clearstatcache(); }
    if (!is_writable($uploadsDir)) { @chmod($uploadsDir, 0775); clearstatcache(); }
    if (!is_writable($uploadsDir)) { @chmod($uploadsDir, 0777); clearstatcache(); }
    $test = rtrim($uploadsDir,'/\\').'/.writetest_'.getmypid().'.tmp';
    $ok = @file_put_contents($test, 'ok') !== false;
    if ($ok) @unlink($test);
    $perm = substr(sprintf('%o', @fileperms($uploadsDir)), -4);
    $real = @realpath($uploadsDir) ?: $uploadsDir;
    $who  = function_exists('get_current_user') ? @get_current_user() : 'unknown';
    return [$ok, "dir=$real perms=$perm user=$who"];
}

$avatarPath = null;
$diag = null;

if ($avatar && ($avatar['error'] !== UPLOAD_ERR_NO_FILE)) {
    if ($avatar['error'] !== UPLOAD_ERR_OK) {
        $errors['avatar'] = 'Upload failed. Try again.';
    } else {
        $tmpName = $avatar['tmp_name'] ?? '';
        $size    = (int)($avatar['size'] ?? 0);
        $mime    = $tmpName ? detect_mime($tmpName, $avatar['name'] ?? '') : '';
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        if (!$mime || !in_array($mime, $allowed, true)) $errors['avatar'] = 'Only JPG, PNG, GIF, or WEBP allowed.';
        if ($size > 2 * 1024 * 1024) $errors['avatar'] = 'Max file size is 2MB.';
        $uploadsDir = __DIR__ . '/..' . '/asset/uploads';
        if (!isset($errors['avatar'])) {
            [$ok, $diag] = ensure_uploads_writable($uploadsDir);
            if (!$ok) $errors['avatar'] = 'Uploads directory is not writable.';
        }
        if (!isset($errors['avatar'])) {
            $ext = strtolower(preg_replace('/[^a-z0-9]/i','', pathinfo($avatar['name'] ?? '', PATHINFO_EXTENSION) ?: 'jpg'));
            try { $safe = 'profile_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext; }
            catch (Throwable $e) { $safe = 'profile_' . time() . '_' . mt_rand(100000,999999) . '.' . $ext; }
            $dest = rtrim($uploadsDir,'/\\') . '/' . $safe;
            if (!@move_uploaded_file($tmpName, $dest)) {
                $errors['avatar'] = 'Unable to save uploaded file.';
            } else {
                $avatarPath = 'asset/uploads/' . $safe;
            }
        }
    }
}

if (!empty($errors)) {
    json_out(['status'=>'error','message'=>'Please fix the highlighted fields.','errors'=>$errors,'diagnostic'=>$diag], 200);
}

$update = ['id'=>$id,'username'=>$name,'email'=>$email];
if (!empty($avatarPath)) $update['profile'] = $avatarPath;

if (updateUser($update)) {
    json_out(['status'=>'success','message'=>'Profile updated successfully!','name'=>$name,'email'=>$email,'avatar'=>$avatarPath], 200);
} else {
    json_out(['status'=>'error','message'=>'Update failed. Try again.','errors'=>['general'=>'Database update failed']], 200);
}
