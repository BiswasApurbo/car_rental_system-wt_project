<?php
session_start();
require_once('../model/userModel.php');        
require_once('../model/bookingModel.php');     

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

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$vehicleId   = isset($_GET['vehicle_id']) ? (int)$_GET['vehicle_id'] : 0; 
$pickupDate  = $_POST['pickup'] ?? '';
$returnDate  = $_POST['return'] ?? '';
$pickupTime  = $_POST['time'] ?? '';

$errors = ['vehicle'=>'','pickup'=>'','return'=>'','time'=>'','availability'=>'','top'=>''];
$successMessage = '';

$vehicleRow = ($vehicleId > 0) ? bm_getVehicleById($vehicleId) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    
    if ($vehicleId <= 0 || !$vehicleRow) {
        $errors['vehicle'] = 'Please select a vehicle from the inventory.';
    }

    
    if ($pickupDate === '') {
        $errors['pickup'] = 'Pickup Date is required.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $pickupDate)) {
        $errors['pickup'] = 'Pickup Date format is invalid (YYYY-MM-DD).';
    }

    
    if ($returnDate === '') {
        $errors['return'] = 'Return Date is required.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $returnDate)) {
        $errors['return'] = 'Return Date format is invalid (YYYY-MM-DD).';
    }


    if ($pickupTime === '') {
        $errors['time'] = 'Pickup Time is required.';
    } elseif (!preg_match('/^\d{2}:\d{2}$/', $pickupTime)) {
        $errors['time'] = 'Pickup Time format is invalid (HH:MM).';
    }


    if ($errors['pickup'] === '' && $errors['return'] === '') {
        $p = strtotime($pickupDate);
        $r = strtotime($returnDate);
        if ($p === false || $r === false) {
            $errors['top'] = 'Invalid date values.';
        } else {
            if ($p > $r) {
                $errors['return'] = 'Return Date must be on or after Pickup Date.';
            }
        
            $today = strtotime(date('Y-m-d'));
            if ($p < $today) {
                $errors['pickup'] = 'Pickup Date cannot be in the past.';
            }
        }
    }


    if (!array_filter($errors)) {
        $available = bm_isVehicleAvailable($vehicleId, $pickupDate, $returnDate);
        if (!$available) {
            $errors['availability'] = 'Selected vehicle is not available for the chosen dates.';
        } else {
           
            $userId = 0;
            if (!empty($_SESSION['username'])) {
                $userId = bm_getUserIdByUsername($_SESSION['username']);
            }
            if ($userId <= 0) {
                $errors['top'] = 'Unable to resolve current user. Please log in again.';
            } else {
               
                $bookingId = bm_createBooking($userId, $vehicleId, $pickupDate, $returnDate, $pickupTime, 0.0);
                if ($bookingId === false) {
                    $errors['top'] = 'Failed to create booking. Please try again.';
                } else {
                    $successMessage = 'Booking confirmed! Reference #'.$bookingId.
                        ' — '.h($vehicleRow['make'].' '.$vehicleRow['model']).
                        " | Pickup: $pickupDate $pickupTime | Return: $returnDate";
                
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Booking Calendar</title>
  <link rel="stylesheet" href="../asset/ad.css">
  <style>
    .card{max-width:900px;margin:18px auto;padding:18px;background:#fff;border-radius:8px;box-shadow:0 0 8px #ddd}
    fieldset{border:none;padding:0;margin:0 0 12px}
    label{display:inline-block;min-width:140px;margin-right:8px}
    input[type="date"],input[type="time"]{padding:6px 8px}
    .error-message{color:#c0392b;font-weight:600;margin:4px 0 10px;white-space:pre-line}
    .validation{font-weight:700;margin:8px 0;color:#2e7d32}
    .btn{display:inline-block;padding:8px 12px;border:1px solid #2c3e50;border-radius:6px;background:#2c3e50;color:#fff;cursor:pointer}
    .btn.secondary{background:#fff;color:#2c3e50}
    .muted{color:#666}
  </style>
  <script>
    function setTodayDefaults(){
      const p = document.getElementById('pickup');
      const r = document.getElementById('return');
      const today = new Date().toISOString().slice(0,10);
      if (!p.value) p.value = today;
      if (!r.value) r.value = today;
    }
    document.addEventListener('DOMContentLoaded', setTodayDefaults);
  </script>
</head>
<body>
<div class="card">
  <h2>Booking Calendar</h2>

  <?php if ($vehicleRow): ?>
    <p class="muted">Vehicle: <strong><?= h($vehicleRow['make'].' '.$vehicleRow['model']) ?></strong> (ID <?= (int)$vehicleRow['id'] ?>)</p>
  <?php else: ?>
    <div class="error-message">No vehicle selected. Go back to <a href="vehicle_inventory.php">Vehicle Inventory</a> and choose one.</div>
  <?php endif; ?>

  <?php if ($errors['top']): ?><div class="error-message"><?= h($errors['top']) ?></div><?php endif; ?>
  <?php if ($errors['availability']): ?><div class="error-message"><?= h($errors['availability']) ?></div><?php endif; ?>
  <?php if ($successMessage): ?><div class="validation"><?= $successMessage ?></div><?php endif; ?>

  <form method="POST" action="">
    <fieldset>
      <label for="pickup">Pickup Date:</label>
      <input type="date" id="pickup" name="pickup" value="<?= h($pickupDate) ?>"><br>
      <?php if ($errors['pickup']): ?><div class="error-message"><?= h($errors['pickup']) ?></div><?php endif; ?>

      <label for="return">Return Date:</label>
      <input type="date" id="return" name="return" value="<?= h($returnDate) ?>"><br>
      <?php if ($errors['return']): ?><div class="error-message"><?= h($errors['return']) ?></div><?php endif; ?>

      <label for="time">Pickup Time:</label>
      <input type="time" id="time" name="time" value="<?= h($pickupTime) ?>"><br>
      <?php if ($errors['time']): ?><div class="error-message"><?= h($errors['time']) ?></div><?php endif; ?>
    </fieldset>

    <fieldset>
      <button type="submit" class="btn">Confirm Booking</button>
      <button type="button" class="btn secondary" onclick="window.location.href='vehicle_inventory.php'">Back to Inventory</button>
      <button type="button" class="btn secondary" onclick="window.location.href='user_dashboard.php'">Back to Dashboard</button>
    </fieldset>
  </form>
</div>
</body>
</html>
