<?php
session_start();
require_once('../model/userModel.php');
require_once('../model/customerModel.php');

if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) {
    header('location: ../view/login.php?error=badrequest');
    exit;
}

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$currentUsername = $_SESSION['username'] ?? '';
$user = getUserByName($currentUsername);
if (!$user) {
    header('location: ../view/login.php?error=badrequest');
    exit;
}
$userId = (int)$user['id'];

$profile = getCustomerProfile($userId);

$name = $profile['full_name'] ?? '';
$licenseNo = $profile['license_no'] ?? '';
$seat = $profile['seat_pref'] ?? '';
$mirror = $profile['mirror_pref'] ?? '';
$licenseFile = $profile['license_file'] ?? '';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $licenseNo = trim($_POST['licenseNo'] ?? '');
    $seat = $_POST['seat'] ?? '';
    $mirror = $_POST['mirror'] ?? '';

    if ($name === '') $errors['name'] = 'Please fill up the name.';
    if ($licenseNo === '') $errors['licenseNo'] = 'Please enter license number.';
    if ($seat === '') $errors['seat'] = 'Please select seat preference.';
    if ($mirror === '') $errors['mirror'] = 'Please select mirror preference.';

    $newFilePath = null;
    if (!empty($_FILES['license']['tmp_name'])) {
        $ext = strtolower(pathinfo($_FILES['license']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'])) {
            $fileName = 'license_' . $userId . '_' . time() . '.' . $ext;
            $destPath = '../asset/uploads/' . $fileName;
            if (move_uploaded_file($_FILES['license']['tmp_name'], $destPath)) {
                $newFilePath = 'asset/uploads/' . $fileName;
            } else {
                $errors['file'] = 'File upload failed.';
            }
        } else {
            $errors['file'] = 'Only JPG, PNG, PDF allowed.';
        }
    }

    if (empty($errors)) {
        if ($profile) {
            updateCustomerProfile($userId, $name, $licenseNo, $seat, $mirror, $newFilePath);
        } else {
            insertCustomerProfile($userId, $name, $licenseNo, $seat, $mirror, $newFilePath);
        }
        $success = 'Preferences saved successfully!';
        $profile = getCustomerProfile($userId);
        $licenseFile = $profile['license_file'];
    }
}

$history = getRentalHistory($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Profile</title>
    <link rel="stylesheet" href="../asset/ad.css">
    <style>
        .error { color: red; font-weight: bold; }
        .success { color: green; font-weight: bold; }
        .card { max-width: 900px; margin: 18px auto; padding: 18px; background: #fff; border-radius: 8px; box-shadow: 0 0 8px #ddd; }
        fieldset { border: none; padding: 0; margin: 0 0 12px; }
        label { display: inline-block; min-width: 140px; margin-right: 8px; }
        input[type="text"], select { padding: 6px 8px; }
        input[type="file"] { padding: 6px 8px; }
        input[type="submit"], input[type="button"] { padding: 8px 12px; background-color: #2c3e50; color: #fff; border: none; border-radius: 6px; cursor: pointer; }
        input[type="submit"]:hover, input[type="button"]:hover { background-color: #34495e; }
        #profile-preview { border-radius: 8px; border: 1px solid #ccc; object-fit: cover; width: 120px; height: 120px; }
    </style>
</head>
<body>

<div class="card">
    <h1>Customer Profile</h1>

    <!-- Success Message -->
    <?php if ($success): ?>
        <div class="success"><?= h($success) ?></div>
    <?php endif; ?>

    <!-- Validation Messages -->
    <?php foreach ($errors as $e): ?>
        <div class="error"><?= h($e) ?></div>
    <?php endforeach; ?>

    <form id="profileForm" method="POST" enctype="multipart/form-data">
        <fieldset>
            <label>Upload License:</label>
            <input type="file" name="license" id="license"><br>
            <?php if ($licenseFile): ?>
                Current: <a href="../<?= h($licenseFile) ?>" target="_blank">View</a><br>
            <?php endif; ?>

            <label>Full Name:</label>
            <input type="text" name="name" id="name" value="<?= h($name) ?>" onblur="checkEditName()"><br>
            <p id="nameError" class="error"><?= isset($errors['name']) ? h($errors['name']) : '' ?></p>

            <label>License No:</label>
            <input type="text" name="licenseNo" id="licenseNo" value="<?= h($licenseNo) ?>" onblur="checkEditLicenseNo()"><br>
            <p id="licenseNoError" class="error"><?= isset($errors['licenseNo']) ? h($errors['licenseNo']) : '' ?></p>

            <label>Seat Position:</label>
            <select name="seat" id="seat">
                <option value="">--Select--</option>
                <?php foreach (['Front-Left', 'Front-Right', 'Back-Left', 'Back-Right'] as $opt): ?>
                    <option value="<?= h($opt) ?>" <?= ($seat === $opt) ? 'selected' : '' ?>><?= h($opt) ?></option>
                <?php endforeach; ?>
            </select><br>
            <p id="seatError" class="error"><?= isset($errors['seat']) ? h($errors['seat']) : '' ?></p>

            <label>Mirror Position:</label>
            <select name="mirror" id="mirror">
                <option value="">--Select--</option>
                <?php foreach (['Standard', 'Wide Angle', 'Blind Spot Adjusted'] as $opt): ?>
                    <option value="<?= h($opt) ?>" <?= ($mirror === $opt) ? 'selected' : '' ?>><?= h($opt) ?></option>
                <?php endforeach; ?>
            </select><br>
            <p id="mirrorError" class="error"><?= isset($errors['mirror']) ? h($errors['mirror']) : '' ?></p>

            <input type="submit" value="Save Preferences">
        </fieldset>
    </form>

    <fieldset>
        <legend>Rental History</legend>
        <?php if (!$history): ?>
            <p>No rentals yet.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($history as $hrow): ?>
                    <li>
                        <?= h($hrow['make'] . ' ' . $hrow['model']) ?> —
                        <?= h($hrow['pickup_date']) ?> to <?= h($hrow['return_date']) ?>
                        (Booking #<?= (int)$hrow['booking_id'] ?>, <?= h($hrow['status']) ?>)
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </fieldset>

    <input type="button" value="Back to Dashboard" onclick="window.location.href='user_dashboard.php'">
</div>

<script>
    // Client-side validation for name and license number
    function checkEditName() {
        const name = document.getElementById('name').value.trim();
        document.getElementById('nameError').innerHTML = name === "" ? "Please enter name!" : "";
    }

    function checkEditLicenseNo() {
        const licenseNo = document.getElementById('licenseNo').value.trim();
        document.getElementById('licenseNoError').innerHTML = licenseNo === "" ? "Please enter license number!" : "";
    }

    // Form submission via AJAX to processProfile.php
    document.getElementById('profileForm').onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '../controller/processProfile.php', true);  // Path to processProfile.php in controller
        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.status === 'success') {
                    document.getElementById('saveSuccess').innerHTML = response.message;
                } else {
                    document.getElementById('saveSuccess').innerHTML = response.message;
                }
            }
        };
        xhr.send(formData);
    };
</script>

</body>
</html>
