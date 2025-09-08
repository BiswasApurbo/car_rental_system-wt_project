<?php
session_start();
require_once "../model/LoyaltyModel.php"; 

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; 
}
$user_id = $_SESSION['user_id'];

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['redeemPoints'])) {
    $redeem = intval($_POST['redeemPoints']);
    $result = redeemLoyaltyPoints($user_id, $redeem); 
    if ($result['success']) {
        $message = "<p class='alert-box alert-success'>✅ Redeemed {$redeem} points! Remaining points: {$result['points']}</p>";
    } else {
        $message = "<p class='alert-box alert-danger'>❌ Not enough points to redeem.</p>";
    }
}


$currentData = getLoyaltyPoints($user_id);
$currentPoints = $currentData['points'];
$tier = $currentData['tier'];
$maxPoints = 5000;

if ($tier === "Platinum") {
    $nextTierPoints = "Max tier reached 🎉";
    $progress = ($currentPoints / $maxPoints) * 100;
} elseif ($tier === "Gold") {
    $nextTierPoints = 4000 - $currentPoints;
    $progress = ($currentPoints / $maxPoints) * 100;
} else {
    $nextTierPoints = 2000 - $currentPoints;
    $progress = ($currentPoints / $maxPoints) * 100;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Loyalty Program</title>
<link rel="stylesheet" href="../asset/LoyaltyProgram.css">
</head>
<body>
<div class="form-wrapper">
<fieldset>
<h1>Loyalty Program Dashboard</h1>

<?= $message ?>

<h2>💎 Points Tracker</h2>
<p>Current Points: <strong><?= $currentPoints ?></strong> / <?= $maxPoints ?></p>
<p>Current Tier: <strong><?= $tier ?></strong></p>
<p>Points to Next Tier: <strong><?= $nextTierPoints ?></strong></p>
<div class="points-bar">
    <div class="points-progress" style="width: <?= $progress ?>%;"></div>
</div>

<h2>🎁 Reward Catalog</h2>
<table>
<tr><th>Reward</th><th>Points Required</th><th>Action</th></tr>
<tr>
<td>Free Coffee</td><td>100</td>
<td>
<form method="post" onsubmit="return validateRedeem(100)">
<input type="hidden" name="redeemPoints" value="100">
<input type="submit" value="Redeem">
</form>
</td>
</tr>
<tr>
<td>Discount Voucher tk.10000</td><td>500</td>
<td>
<form method="post" onsubmit="return validateRedeem(500)">
<input type="hidden" name="redeemPoints" value="500">
<input type="submit" value="Redeem">
</form>
</td>
</tr>
<tr>
<td>Free Upgrade</td><td>1000</td>
<td>
<form method="post" onsubmit="return validateRedeem(1000)">
<input type="hidden" name="redeemPoints" value="1000">
<input type="submit" value="Redeem">
</form>
</td>
</tr>
</table>

<h2>🏆 Tier Benefits</h2>
<ul>
<li>Silver: 0-1999 points – Basic benefits</li>
<li>Gold: 2000-3999 points – Free upgrades, priority support</li>
<li>Platinum: 4000-5000 points – Exclusive rewards, VIP support</li>
</ul>

<br><br>
<input type="button" value="Back to services" onclick="window.location.href='customer_services.php'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">
<input type="button" value="Back to Profile" onclick="window.location.href='profile.php'" style="background-color:#1f6feb;color:#fff;border:none;padding:10px 16px;border-radius:6px;cursor:pointer;">

</fieldset>
</div>

<script>
function validateRedeem(requiredPoints) {
    const currentPoints = <?= $currentPoints ?>;
    if (requiredPoints > currentPoints) {
        alert("You do not have enough points to redeem this reward.");
        return false;
    }
    return true;
}
</script>
</body>
</html>
