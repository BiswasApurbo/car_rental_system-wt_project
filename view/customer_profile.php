<?php
session_start();
require_once('../model/userModel.php');
require_once('../model/customerModel.php');

if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) {
    header('location: ../view/login.php?error=badrequest');
    exit;
}

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$currentUsername = $_SESSION['username'] ?? '';
$user = getUserByName($currentUsername);
if (!$user) {
    header('location: ../view/login.php?error=badrequest');
    exit;
}
$userId = (int)$user['id'];

$profile = getCustomerProfile($userId);

$name       = $profile['full_name']   ?? '';
$licenseNo  = $profile['license_no']  ?? '';
$seat       = $profile['seat_pref']   ?? '';
$mirror     = $profile['mirror_pref'] ?? '';
$licenseFile= $profile['license_file']?? '';

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = trim($_POST['name'] ?? '');
    $licenseNo = trim($_POST['licenseNo'] ?? '');
    $seat      = $_POST['seat']   ?? '';
    $mirror    = $_POST['mirror'] ?? '';

    if ($name === '') $errors['name'] = 'Please fill up the name.';
    if ($licenseNo === '') $errors['licenseNo'] = 'Please enter license number.';
    if ($seat === '') $errors['seat'] = 'Please select seat preference.';
    if ($mirror === '') $errors['mirror'] = 'Please select mirror preference.';

    $newFilePath = null;
    if (!empty($_FILES['license']['tmp_name'])) {
        $ext = strtolower(pathinfo($_FILES['license']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','pdf'])) {
            $fileName = 'license_'.$userId.'_'.time().'.'.$ext;
            $destPath = '../asset/uploads/'.$fileName;
            if (move_uploaded_file($_FILES['license']['tmp_name'], $destPath)) {
                $newFilePath = 'asset/uploads/'.$fileName;
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
  <style>.error{color:red;font-weight:bold}.success{color:green;font-weight:bold}</style>
</head>
<body>
<div class="admin-card">
  <h1>Customer Profile</h1>

  <?php if ($success): ?><div class="success"><?= h($success) ?></div><?php endif; ?>
  <?php foreach ($errors as $e): ?><div class="error"><?= h($e) ?></div><?php endforeach; ?>

  <form method="POST" enctype="multipart/form-data">
    <fieldset>
      <label>Upload License:</label>
      <input type="file" name="license"><br>
      <?php if ($licenseFile): ?>
        Current: <a href="../<?= h($licenseFile) ?>" target="_blank">View</a><br>
      <?php endif; ?>
      <label>Full Name:</label>
      <input type="text" name="name" value="<?= h($name) ?>"><br>
      <label>License No:</label>
      <input type="text" name="licenseNo" value="<?= h($licenseNo) ?>"><br>
      <label>Seat Position:</label>
      <select name="seat">
        <option value="">--Select--</option>
        <?php foreach (['Front-Left','Front-Right','Back-Left','Back-Right'] as $opt): ?>
          <option value="<?= h($opt) ?>" <?= ($seat===$opt)?'selected':'' ?>><?= h($opt) ?></option>
        <?php endforeach; ?>
      </select><br>
      <label>Mirror Position:</label>
      <select name="mirror">
        <option value="">--Select--</option>
        <?php foreach (['Standard','Wide Angle','Blind Spot Adjusted'] as $opt): ?>
          <option value="<?= h($opt) ?>" <?= ($mirror===$opt)?'selected':'' ?>><?= h($opt) ?></option>
        <?php endforeach; ?>
      </select><br>
      <button type="submit">Save Preferences</button>
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
            <?= h($hrow['make'].' '.$hrow['model']) ?> — 
            <?= h($hrow['pickup_date']) ?> to <?= h($hrow['return_date']) ?> 
            (Booking #<?= (int)$hrow['booking_id'] ?>, <?= h($hrow['status']) ?>)
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </fieldset>

  <input type="button" value="Back to Dashboard" onclick="window.location.href='user_dashboard.php'">
</div>
</body>
</html>
